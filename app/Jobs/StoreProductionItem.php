<?php

namespace App\Jobs;

use App\Models\Chemicaljobcarditem;
use App\Models\Production;
use App\Models\Productionitem;
use App\Models\RawMaterialTrans;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class StoreProductionItem implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /** Retries are safe: the whole job is one transaction keyed on the client's uuid. */
    public $tries = 3;

    /** No second copy of this uuid sits on the queue while one is pending. */
    public $uniqueFor = 3600;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function uniqueId(): string
    {
        return (string) ($this->data['code'] ?? '');
    }

    public function handle()
    {
        $code = $this->data['code'] ?? null;

        if (!$code) {
            Log::error('StoreProductionItem: no unique code on the payload', ['data' => $this->data]);
            return;   // nothing to key on — do not guess
        }

        // Cheap pre-check. Not the guarantee — the unique index is. This just
        // avoids opening a transaction for the common replay case.
        if (Productionitem::where('unique_code', $code)->exists()) {
            Log::info('StoreProductionItem: already recorded, ignoring replay', ['code' => $code]);
            return;
        }

        $production = Production::find($this->data['productionId']);

        if (!$production) {
            Log::error('Production not found', [
                'productionId' => $this->data['productionId'],
                'code'         => $code,
            ]);
            return;
        }

        try {
            DB::transaction(function () use ($code, $production) {

                // ── The production item ───────────────────────────────────────
                $productionItem = Productionitem::create([
                    'productionId'    => $this->data['productionId'],
                    'jobcarditemId'   => $this->data['jobcarditemId'],
                    'other'           => 'none',
                    'productId'       => $this->data['productId'],
                    'userId'          => $production->userId,
                    'weight'          => $this->data['weight'],
                    'processId'       => 212,
                    'qnt'             => $this->data['qnt'] ?? 1,
                    'unitId'          => $this->data['unitId'],
                    'machineId'       => $production->machineryId,
                    'tms'             => now()->format('H:i:s'),
                    'employeeId'      => $production->userId,
                    'shiftId'         => $production->shiftId ?? 31,
                    'wpProduct'       => $this->data['wpProduct'],
                    'weightState'     => $this->data['weightState'],
                    'tempId'          => $this->data['tempId'] ?? 0,
                    'serialNo'        => $this->data['SerialNo'],
                    'unique_code'     => $code,
                    'rollId'          => $this->data['rollId'] ?? 0,
                    'weight_per_bale' => $this->data['weight_per_bale'],
                    'dateCreated'     => $this->data['dateCreated'],
                ]);

                $this->addToStock($productionItem);
                $this->deductJobCard($productionItem);
                $this->issueRawMaterials($productionItem);
            });

        } catch (QueryException $e) {
            // 23000 = integrity constraint. Two copies of the same scan raced and
            // the other one won — that is success, not failure.
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'unique_code')) {
                Log::info('StoreProductionItem: duplicate uuid rejected by the database', ['code' => $code]);
                return;
            }

            Log::error('StoreProductionItem: database error', ['code' => $code, 'error' => $e->getMessage()]);
            throw $e;

        } catch (\Throwable $e) {
            Log::error('Failed to store production item', [
                'code'      => $code,
                'exception' => $e->getMessage(),
                'data'      => $this->data,
            ]);
            throw $e;   // rolled back — nothing was written, safe to retry
        }
    }

    /* ── Finished goods into stock ────────────────────────────────────────────
       Written as one UPDATE with an expression rather than read-then-write, so
       two machines finishing at the same moment cannot lose each other's units. */
    private function addToStock(Productionitem $item): void
    {
        $stock = DB::table('stocks')->where('productId', $item->productId)->first();

        if (!$stock) {
            Log::warning('No stock row found for product', [
                'productId'        => $item->productId,
                'productionItemId' => $item->id,
            ]);
            return;
        }

        DB::table('stocks')
            ->where('productId', $item->productId)
            ->update([
                'prvqnt'     => DB::raw('qnt'),
                'qnt'        => DB::raw('qnt + ' . (float) $item->qnt),
                'updated_at' => now(),
            ]);

        DB::table('stocks_trans')->insert([
            'stockId'    => $stock->id,
            'docId'      => $item->id,
            'docType'    => 105,          // 105 = production item
            'qnt'        => $item->qnt,
            'userId'     => $item->userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // ── Job card outstanding comes down ──────────────────────────────────────
    private function deductJobCard(Productionitem $item): void
    {
        if (empty($this->data['jobcarditemId'])) {
            return;
        }

        $jobcardItem = Chemicaljobcarditem::find($this->data['jobcarditemId']);

        if (!$jobcardItem) {
            Log::warning('JobcardItem not found', ['jobcarditemId' => $this->data['jobcarditemId']]);
            return;
        }

        Chemicaljobcarditem::where('id', $jobcardItem->id)->decrement('quantity', $item->qnt);
    }

    /* ── Raw materials out ────────────────────────────────────────────────────
       Scaled off the weight actually produced, against the formula snapshot
       saved with the job card where there is one, so a formula edited mid-run
       does not change what earlier production consumed. */
    private function issueRawMaterials(Productionitem $item): void
    {
        $batchKg = (float) ($item->weight ?? 0);

        if ($batchKg <= 0) {
            Log::warning('No weight on production item — no raw materials issued', ['productionItemId' => $item->id]);
            return;
        }

        $lines = $this->formulaLines($item);

        if ($lines->isEmpty()) {
            Log::warning('No formula for this product — no raw materials issued', [
                'productId'        => $item->productId,
                'productionItemId' => $item->id,
            ]);
            return;
        }

        // one readable block per production item, so a run can be read back off
        // the log without joining anything
        $issued   = [];
        $skipped  = [];
        $short    = [];
        $totalKg  = 0.0;

        Log::info(sprintf(
            'RAW MATERIALS OUT  PROD-%d  batch %.2f kg  formula source: %s  (%d lines)',
            $item->id,
            $batchKg,
            $this->formulaSource,
            $lines->count()
        ));

        foreach ($lines as $line) {
            // two decimals — a 0.05% fragrance in a 500kg batch is 0.25kg and
            // must not round away to nothing
            $qty = round($batchKg * (float) $line->percentage / 100, 2);

            $material = DB::table('raw_materials')->where('id', $line->raw_material_id)->first();

            if (!$material) {
                Log::warning('  ! material missing', ['raw_material_id' => $line->raw_material_id]);
                $skipped[] = ['raw_material_id' => $line->raw_material_id, 'why' => 'material missing'];
                continue;
            }

            if ($qty <= 0) {
                // rounds to nothing at this batch size — worth seeing, because a
                // trace ingredient silently not being issued looks like a bug
                Log::notice(sprintf(
                    '  - %-12s %-28s %9.4f%%  rounds to 0.00 kg at this batch size — skipped',
                    $material->code,
                    substr($material->name, 0, 28),
                    (float) $line->percentage
                ));
                $skipped[] = ['code' => $material->code, 'why' => 'rounds to zero'];
                continue;
            }

            /* Short stock never blocks production. What came off the machine
               came off the machine — refusing to record it would leave the
               finished goods counted and the materials not, which is worse than
               a negative balance. The balance goes negative and says so. */
            $onHand    = (float) $material->stock_on_hand;
            $shortfall = round($qty - $onHand, 2);

            if ($shortfall > 0.005) {
                Log::warning(sprintf(
                    '  ! %-12s %-28s SHORT by %.2f %s — %.2f on hand, %.2f needed, going to %.2f',
                    $material->code,
                    substr($material->name, 0, 28),
                    $shortfall,
                    $material->uom,
                    $onHand,
                    $qty,
                    $onHand - $qty
                ));

                $short[] = [
                    'code'      => $material->code,
                    'name'      => $material->name,
                    'on_hand'   => $onHand,
                    'needed'    => $qty,
                    'shortfall' => $shortfall,
                    'flagged'   => !$material->allow_negative,   // this one was not meant to go negative
                ];
            }

            // previous and current on the material row, exactly like stocks.
            // prvqnt is assigned before the balance moves — MySQL evaluates the
            // SET list left to right, so the order of these two keys matters
            DB::table('raw_materials')
                ->where('id', $material->id)
                ->update([
                    'prv_stock_on_hand' => DB::raw('stock_on_hand'),
                    'stock_on_hand'     => DB::raw('stock_on_hand - ' . sprintf('%.2f', $qty)),
                    'updated_at'        => now(),
                ]);

            $balance = (float) DB::table('raw_materials')->where('id', $material->id)->value('stock_on_hand');
            $before  = (float) $material->stock_on_hand;
            $grams   = $qty * 1000;
            $totalKg += $qty;

            // code | name | % of batch | kg | grams | balance before -> after
            Log::info(sprintf(
                '  - %-12s %-28s %9.4f%%  %10.2f kg  %12s g   %10.2f -> %10.2f %s',
                $material->code,
                substr($material->name, 0, 28),
                (float) $line->percentage,
                $qty,
                number_format($grams, 0, '.', ' '),
                $before,
                $balance,
                $material->uom
            ));

            $issued[] = [
                'code'       => $material->code,
                'name'       => $material->name,
                'percentage' => (float) $line->percentage,
                'kg'         => $qty,
                'grams'      => $grams,
                'before'     => $before,
                'after'      => $balance,
                'uom'        => $material->uom,
                'unit_cost'  => (float) $material->cost_per_kg,
                'line_cost'  => round($qty * (float) $material->cost_per_kg, 2),
            ];

            RawMaterialTrans::create([
                'raw_material_id' => $material->id,
                'supplier_id'     => null,
                'doc_type'        => RawMaterialTrans::ISSUE,
                'doc_id'          => $item->id,          // production item
                'doc_no'          => 'PROD-' . $item->id,
                'qty_in'          => 0,
                'qty_out'         => $qty,
                'balance_after'   => $balance,
                'unit_cost'       => $material->cost_per_kg,
                'trans_date'      => now()->toDateString(),
                'notes'           => 'Production ' . $item->productionId
                                      . ($shortfall > 0.005 ? ' (short ' . number_format($shortfall, 2) . ')' : ''),
                'created_by'      => $item->userId,
            ]);
        }

        // the structured version — searchable, and what you want when something
        // does not add up
        Log::info('RAW MATERIALS OUT summary', [
            'production_item' => $item->id,
            'production'      => $item->productionId,
            'product'         => $item->productId,
            'batch_kg'        => round($batchKg, 2),
            'issued_kg'       => round($totalKg, 2),
            'issued_grams'    => round($totalKg * 1000, 0),
            'variance_kg'     => round($batchKg - $totalKg, 2),
            'batch_cost'      => round(array_sum(array_column($issued, 'line_cost')), 2),
            'source'          => $this->formulaSource,
            'lines'           => $issued,
            'skipped'         => $skipped,
            'short'           => $short,
        ]);

        // negative balances need chasing the same day — a stock count was missed,
        // a delivery was never booked in, or the formula is wrong
        if ($short) {
            Log::warning(sprintf(
                'RAW MATERIALS OUT  PROD-%d  %d material(s) went negative: %s',
                $item->id,
                count($short),
                implode(', ', array_map(
                    fn ($s) => $s['code'] . ' short ' . number_format($s['shortfall'], 2),
                    $short
                ))
            ));
        }

        // a formula that does not add up to the batch weight means percentages
        // that do not total 100 — the operator is weighing to a different recipe
        // than the stock ledger is being charged for
        if (abs($batchKg - $totalKg) > 0.05) {
            Log::warning(sprintf(
                'RAW MATERIALS OUT  PROD-%d  issued %.2f kg against a %.2f kg batch (%.2f kg out)',
                $item->id,
                $totalKg,
                $batchKg,
                $batchKg - $totalKg
            ));
        }
    }

    /** Set by formulaLines() so the log can say where the percentages came from. */
    private $formulaSource = 'unknown';

    /** Snapshot saved with the job card if there is one, else the live formula. */
    private function formulaLines(Productionitem $item)
    {
        // The snapshot table is optional — it may not exist yet, and older job
        // cards will not have rows in it even once it does
        if (!empty($this->data['jobcarditemId']) && Schema::hasTable('chemical_job_card_materials')) {
            $jobCardId = DB::table('chemical_job_card_items')
                ->where('id', $this->data['jobcarditemId'])
                ->value('jobCardId');

            if ($jobCardId) {
                $snapshot = DB::table('chemical_job_card_materials')
                    ->where('jobCardId', $jobCardId)
                    ->get(['raw_material_id', 'percentage']);

                if ($snapshot->count()) {
                    $this->formulaSource = 'job card snapshot';
                    return $snapshot;
                }
            }
        }

        $formulaCode = DB::table('chemical_products')->where('id', $item->productId)->value('formula_code');

        if (!$formulaCode) {
            return collect();
        }

        $formulaId = DB::table('formulas')
            ->whereRaw('LOWER(code) = ?', [strtolower($formulaCode)])
            ->value('id');

        if (!$formulaId) {
            return collect();
        }

        $this->formulaSource = 'live formula ' . $formulaCode;

        return DB::table('formula_items')
            ->where('formula_id', $formulaId)
            ->orderBy('sequence')
            ->get(['raw_material_id', 'percentage']);
    }
}