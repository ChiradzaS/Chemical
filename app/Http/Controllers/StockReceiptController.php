<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use App\Models\RawMaterialTrans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Receiving raw materials in from a supplier.
 *
 * Everything lands in raw_material_trans — no receipt header, no generated id.
 * The delivery note number the user types is the document number.
 *
 *   /stock-receipts/list?data={"search":"","limit":100}
 *   /stock-receipts/history?data={"raw_material_id":7,"limit":50}
 *   /stock-receipts/save?data={...}
 */
class StockReceiptController extends Controller
{
    private const DOC_TYPE = RawMaterialTrans::RECEIPT;

    /**
     * How raw_materials.cost_per_kg is kept up to date when stock is received.
     *   'latest'   — cost becomes the price on this delivery
     *   'weighted' — cost becomes the weighted average of old stock and new stock
     *   'none'     — receiving never touches the material cost
     */
    private const COST_METHOD = 'latest';

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /** Laravel has already url-decoded the query string, so this is just a json_decode. */
    private function payload(Request $request): array
    {
        $data = json_decode($request->query('data', '{}'), true);

        return is_array($data) ? $data : [];
    }

    private function fail(string $message)
    {
        return response()->json(['status' => 'error', 'message' => $message]);
    }

    /**
     * Keep the material's own cost_per_kg in step with what was actually paid.
     * Runs BEFORE stock_on_hand is increased, so the weighted average still
     * sees the quantity that was on the floor when the truck arrived.
     */
    private function syncMaterialCost(RawMaterial $material, int $qtyIn, float $unitCost): void
    {
        if (self::COST_METHOD === 'none' || $qtyIn <= 0 || $unitCost <= 0) {
            return;
        }

        if (self::COST_METHOD === 'latest') {
            $material->cost_per_kg = $unitCost;
            $material->save();

            return;
        }

        // weighted average
        $onHand  = max(0, (int) $material->stock_on_hand);
        $oldCost = (float) ($material->cost_per_kg ?? 0);

        // no stock (or no cost) to average against — the new price is the cost
        if ($onHand <= 0 || $oldCost <= 0) {
            $material->cost_per_kg = $unitCost;
            $material->save();

            return;
        }

        $material->cost_per_kg = round(
            (($onHand * $oldCost) + ($qtyIn * $unitCost)) / ($onHand + $qtyIn),
            4
        );
        $material->save();
    }

    /** The flat line shape both list() and history() return. */
    private function lineQuery()
    {
        return DB::table('raw_material_trans as t')
            ->leftJoin('suppliers as s', 's.id', '=', 't.supplier_id')
            ->join('raw_materials as m', 'm.id', '=', 't.raw_material_id')
            ->where('t.doc_type', self::DOC_TYPE)
            ->select(
                't.id',
                't.trans_date as received_date',
                't.doc_no as reference',
                't.notes',
                't.supplier_id',
                's.name as supplier_name',
                't.raw_material_id',
                'm.code as material_code',
                'm.name as material_name',
                'm.uom',
                't.qty_in as qty',
                't.unit_cost',
                't.balance_after'
            );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Endpoints
    // ─────────────────────────────────────────────────────────────────────────

    /** Recent deliveries, newest first. */
    public function list(Request $request)
    {
        $data   = $this->payload($request);
        $search = trim($data['search'] ?? '');
        $limit  = (int) ($data['limit'] ?? 100);
        $limit  = $limit > 0 && $limit <= 500 ? $limit : 100;

        $query = $this->lineQuery();

        if (! empty($data['supplier_id'])) {
            $query->where('t.supplier_id', (int) $data['supplier_id']);
        }

        if (! empty($data['from'])) {
            $query->whereDate('t.trans_date', '>=', $data['from']);
        }

        if (! empty($data['to'])) {
            $query->whereDate('t.trans_date', '<=', $data['to']);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('t.doc_no', 'like', "%{$search}%")
                  ->orWhere('s.name', 'like', "%{$search}%")
                  ->orWhere('m.code', 'like', "%{$search}%")
                  ->orWhere('m.name', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->orderByDesc('t.trans_date')
                  ->orderByDesc('t.id')
                  ->limit($limit)
                  ->get()
        );
    }

    /** Every delivery of one material, newest first — drives the Supply history popup. */
    public function history(Request $request)
    {
        $data       = $this->payload($request);
        $materialId = (int) ($data['raw_material_id'] ?? 0);

        if ($materialId <= 0) {
            return $this->fail('No material was given.');
        }

        $limit = (int) ($data['limit'] ?? 50);
        $limit = $limit > 0 && $limit <= 500 ? $limit : 50;

        return response()->json(
            $this->lineQuery()
                ->where('t.raw_material_id', $materialId)
                ->orderByDesc('t.trans_date')
                ->orderByDesc('t.id')
                ->limit($limit)
                ->get()
        );
    }

    /** Book a delivery in: one ledger row per line, stock up, cost synced. */
    public function save(Request $request)
    {
        $data = $this->payload($request);

        $supplierId   = (int) ($data['supplier_id'] ?? 0);
        $reference    = trim($data['reference'] ?? '');
        $receivedDate = trim($data['received_date'] ?? '');
        $notes        = trim($data['notes'] ?? '');
        $lines        = $data['lines'] ?? [];

        // ── Validate before touching anything ────────────────────────────────
        if ($supplierId <= 0) {
            return $this->fail('Choose a supplier.');
        }

        $supplier = DB::table('suppliers')->where('id', $supplierId)->first();
        if (! $supplier) {
            return $this->fail('That supplier no longer exists.');
        }

        if ($receivedDate === '' || ! strtotime($receivedDate)) {
            return $this->fail('Choose the date this was received.');
        }

        if (strtotime($receivedDate) > strtotime(date('Y-m-d'))) {
            return $this->fail('The received date cannot be in the future.');
        }

        if (! is_array($lines) || count($lines) === 0) {
            return $this->fail('Add at least one material.');
        }

        $clean = [];
        $seen  = [];

        foreach ($lines as $line) {
            $materialId = (int) ($line['raw_material_id'] ?? 0);
            $qty        = round((float) ($line['qty'] ?? 0), 2);
            $unitCost   = (float) ($line['unit_cost'] ?? 0);

            if ($materialId <= 0) {
                return $this->fail('One of the lines has no material on it.');
            }

            if (isset($seen[$materialId])) {
                return $this->fail('The same material appears on two lines.');
            }
            $seen[$materialId] = true;

            $material = RawMaterial::find($materialId);
            if (! $material) {
                return $this->fail('One of the materials no longer exists.');
            }

            if ($qty <= 0) {
                return $this->fail("Enter a quantity for {$material->name}.");
            }

            if ($unitCost <= 0) {
                return $this->fail("Enter the price paid for {$material->name}.");
            }

            $clean[] = ['material' => $material, 'qty' => $qty, 'unit_cost' => round($unitCost, 4)];
        }

        $date = date('Y-m-d', strtotime($receivedDate));

        // ── Write ────────────────────────────────────────────────────────────
        try {
            DB::transaction(function () use ($supplierId, $reference, $date, $notes, $clean) {
                foreach ($clean as $line) {
                    /** @var RawMaterial $material */
                    $material = $line['material'];

                    // cost first — a weighted average needs the pre-delivery quantity
                    $this->syncMaterialCost($material, $line['qty'], $line['unit_cost']);

                    // previous and current on the material row, exactly like stocks
                    DB::table('raw_materials')
                        ->where('id', $material->id)
                        ->update([
                            'prv_stock_on_hand' => DB::raw('stock_on_hand'),
                            'stock_on_hand'     => DB::raw('stock_on_hand + ' . sprintf('%.2f', $line['qty'])),
                            'updated_at'        => now(),
                        ]);

                    $balance = (float) DB::table('raw_materials')->where('id', $material->id)->value('stock_on_hand');

                    RawMaterialTrans::create([
                        'raw_material_id' => $material->id,
                        'supplier_id'     => $supplierId,
                        'doc_type'        => self::DOC_TYPE,
                        'doc_no'          => $reference !== '' ? $reference : null,
                        'qty_in'          => $line['qty'],
                        'qty_out'         => 0,
                        'balance_after'   => $balance,
                        'unit_cost'       => $line['unit_cost'],
                        'trans_date'      => $date,
                        'notes'           => $notes !== '' ? $notes : null,
                        'created_by'      => auth()->id(),
                    ]);
                }
            });
        } catch (Throwable $e) {
            report($e);

            return $this->fail('Could not save this delivery. Nothing was received into stock.');
        }

        return response()->json([
            'reference'     => $reference,
            'received_date' => $date,
            'supplier_id'   => $supplier->id,
            'supplier_name' => $supplier->name,
            'lines'         => count($clean),
        ]);
    }
}