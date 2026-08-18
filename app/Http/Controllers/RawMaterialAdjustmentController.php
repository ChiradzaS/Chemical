<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use App\Models\RawMaterialTrans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Stock adjustments for raw materials.
 *
 *   /raw-material-adjustments/list?data={"limit":50}
 *   /raw-material-adjustments/save?data={...}
 *   /raw-material-adjustments/reverse?data={"id":12}
 */
class RawMaterialAdjustmentController extends Controller
{
    private const DOC_TYPE = RawMaterialTrans::ADJUSTMENT;

    private function payload(Request $request): array
    {
        $data = json_decode($request->query('data', '{}'), true);

        return is_array($data) ? $data : [];
    }

    private function fail(string $message)
    {
        return response()->json(['status' => 'error', 'message' => $message]);
    }

    /** Adjustment rows with the balance before and after each one. */
    public function list(Request $request)
    {
        $data  = $this->payload($request);
        $limit = (int) ($data['limit'] ?? 50);
        $limit = $limit > 0 && $limit <= 500 ? $limit : 50;

        $query = DB::table('raw_material_trans as t')
            ->join('raw_materials as m', 'm.id', '=', 't.raw_material_id')
            ->leftJoin('users as u', 'u.id', '=', 't.created_by')
            ->where('t.doc_type', self::DOC_TYPE)
            ->select(
                't.id',
                't.trans_date',
                't.created_at',
                't.raw_material_id',
                'm.code as material_code',
                'm.name as material_name',
                'm.uom',
                't.balance_after',
                't.notes',
                'u.name as user_name',
                DB::raw('(t.qty_in - t.qty_out) as `change`'),
                DB::raw('(t.balance_after - (t.qty_in - t.qty_out)) as old_quantity')
            );

        if (! empty($data['raw_material_id'])) {
            $query->where('t.raw_material_id', (int) $data['raw_material_id']);
        }

        return response()->json(
            $query->orderByDesc('t.id')->limit($limit)->get()
        );
    }

    /** Post an adjustment: one ledger row, stock moved to match. */
    public function save(Request $request)
    {
        $data = $this->payload($request);

        $materialId = (int) ($data['raw_material_id'] ?? 0);
        $type       = trim($data['adjustment_type'] ?? '');
        $newQty     = round((float) ($data['new_quantity'] ?? 0), 2);
        $comment    = trim($data['comment'] ?? '');

        if ($materialId <= 0) {
            return $this->fail('Choose a material.');
        }

        if (! in_array($type, ['set', 'add', 'subtract'], true)) {
            return $this->fail('Choose an adjustment type.');
        }

        if ($comment === '') {
            return $this->fail('A reason is required for every adjustment.');
        }

        $material = RawMaterial::find($materialId);
        if (! $material) {
            return $this->fail('That material no longer exists.');
        }

        // the balance is read here, not taken from the browser — the page may
        // have been open a while and someone else may have received stock since
        $onHand = (float) $material->stock_on_hand;
        $change = round($newQty - $onHand, 2);

        if (abs($change) < 0.005) {
            return $this->fail('That leaves the stock unchanged.');
        }

        if ($newQty < 0 && ! $material->allow_negative) {
            return $this->fail("{$material->name} cannot go negative — {$onHand} on hand.");
        }

        try {
            $balance = DB::transaction(function () use ($material, $change, $comment) {
                // previous and current on the material row, exactly like stocks
                DB::table('raw_materials')
                    ->where('id', $material->id)
                    ->update([
                        'prv_stock_on_hand' => DB::raw('stock_on_hand'),
                        'stock_on_hand'     => DB::raw('stock_on_hand + ' . sprintf('%.2f', $change)),
                        'updated_at'        => now(),
                    ]);

                $balance = (float) DB::table('raw_materials')->where('id', $material->id)->value('stock_on_hand');

                RawMaterialTrans::create([
                    'raw_material_id' => $material->id,
                    'supplier_id'     => null,
                    'doc_type'        => self::DOC_TYPE,
                    'doc_no'          => null,
                    'qty_in'          => $change > 0 ? $change : 0,
                    'qty_out'         => $change < 0 ? abs($change) : 0,
                    'balance_after'   => $balance,
                    'unit_cost'       => $material->cost_per_kg,
                    'trans_date'      => date('Y-m-d'),
                    'notes'           => $comment,
                    'created_by'      => auth()->id(),
                ]);

                return $balance;
            });
        } catch (Throwable $e) {
            report($e);

            return $this->fail('Could not save this adjustment. Nothing was changed.');
        }

        return response()->json([
            'raw_material_id' => $material->id,
            'name'            => $material->name,
            'old_quantity'    => $onHand,
            'balance_after'   => $balance,
            'change'          => $change,
        ]);
    }

    /**
     * Undo an adjustment by posting the opposite movement.
     * The original row is never edited or deleted, so the trail stays intact.
     */
    public function reverse(Request $request)
    {
        $data = $this->payload($request);
        $id   = (int) ($data['id'] ?? 0);

        $original = RawMaterialTrans::find($id);

        if (! $original || (int) $original->doc_type !== self::DOC_TYPE) {
            return $this->fail('That adjustment no longer exists.');
        }

        $material = RawMaterial::find($original->raw_material_id);
        if (! $material) {
            return $this->fail('That material no longer exists.');
        }

        $undo = round(-1 * ((float) $original->qty_in - (float) $original->qty_out), 2);

        if ((((float) $material->stock_on_hand) + $undo) < -0.005 && ! $material->allow_negative) {
            return $this->fail('Reversing this would take the stock negative.');
        }

        try {
            $balance = DB::transaction(function () use ($material, $undo, $original) {
                DB::table('raw_materials')
                    ->where('id', $material->id)
                    ->update([
                        'prv_stock_on_hand' => DB::raw('stock_on_hand'),
                        'stock_on_hand'     => DB::raw('stock_on_hand + ' . sprintf('%.2f', $undo)),
                        'updated_at'        => now(),
                    ]);

                $balance = (float) DB::table('raw_materials')->where('id', $material->id)->value('stock_on_hand');

                RawMaterialTrans::create([
                    'raw_material_id' => $material->id,
                    'supplier_id'     => null,
                    'doc_type'        => self::DOC_TYPE,
                    'doc_no'          => null,
                    'qty_in'          => $undo > 0 ? $undo : 0,
                    'qty_out'         => $undo < 0 ? abs($undo) : 0,
                    'balance_after'   => $balance,
                    'unit_cost'       => $material->cost_per_kg,
                    'trans_date'      => date('Y-m-d'),
                    'notes'           => "Reversal of adjustment #{$original->id}: {$original->notes}",
                    'created_by'      => auth()->id(),
                ]);

                return $balance;
            });
        } catch (Throwable $e) {
            report($e);

            return $this->fail('Could not reverse this adjustment. Nothing was changed.');
        }

        return response()->json([
            'raw_material_id' => $material->id,
            'balance_after'   => $balance,
        ]);
    }
}