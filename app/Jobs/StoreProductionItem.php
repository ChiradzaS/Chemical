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

    /** Set by formulaLines() so the log can say where the percentages came from. */
    private $formulaSource = 'unknown';

    /** Set by batchGrams() so the log can say how the batch size was arrived at. */
    private $weightSource = 'unknown';

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

    /* ── How big is this batch? ───────────────────────────────────────────────
       Answered in whole grams, because the product carries its fill weight in
       grams and the quantity is an integer — so the batch size is exact by
       construction, with no float multiplication anywhere near it.

       There is no scale on the line yet. When one arrives, its reading is the
       truth about what physically came off the machine and should take priority
       here; the branch below is already written for it. */
    private function batchGrams(Productionitem $item): int
    {
        $qnt = max(1, (int) ($item->qnt ?? 1));

        /* Despite the column name, weight_per_unit_grams holds KILOGRAMS —
           5.40 is a filled 5 L bottle, not 5.4 g. Convert once, here, and work
           in whole grams from this point on. */
        $unitKg    = (float) DB::table('chemical_products')
            ->where('id', $item->productId)
            ->value('weight_per_unit_grams');

        $unitGrams = (int) round($unitKg * 1000);

        if ($unitGrams <= 0) {
            throw new \RuntimeException(sprintf(
                'PROD-%d: product %d has no weight_per_unit_grams. '
              . 'Set the fill weight on the product before producing against it.',
                $item->id, $item->productId
            ));
        }

        $expected = $unitGrams * $qnt;

        /* Once a scale is fitted, prefer its reading — but only if it is in the
           same world as the nominal figure. Half or double is not a fill
           variance, it is a wrong number, and issuing against it would corrupt
           the ledger silently. */
        $recordedGrams = (int) round(((float) ($item->weight ?? 0)) * 1000);

        if ($recordedGrams > 0) {
            $ratio = $recordedGrams / $expected;

            if ($ratio >= 0.5 && $ratio <= 2.0) {
                $this->weightSource = sprintf(
                    'weighed (nominal %.3f kg x %d = %.3f kg)',
                    $unitKg, $qnt, $expected / 1000
                );
                return $recordedGrams;
            }

            Log::warning(sprintf(
                'PROD-%d: ignoring a recorded weight of %s g — the product says %d x %.3f kg = %.3f kg',
                $item->id,
                number_format($recordedGrams, 0, '.', ' '),
                $qnt, $unitKg,
                $expected / 1000
            ));
        }

        $this->weightSource = sprintf('product %.3f kg x %d units (no scale)', $unitKg, $qnt);

        return $expected;
    }

    /* ── Raw materials out ────────────────────────────────────────────────────
       Scaled off the weight actually produced, against the formula snapshot
       saved with the job card where there is one, so a formula edited mid-run
       does not change what earlier production consumed.

       All arithmetic is done in whole grams. Working in kg at two decimals
       loses anything under 5 g, which is well inside normal dosing for a trace
       additive — a 0.1% ingredient in a 1 kg batch is 1 g and would vanish. */
    private function issueRawMaterials(Productionitem $item): void
    {
        $qnt        = max(1, (int) ($item->qnt ?? 1));
        $batchGrams = $this->batchGrams($item);
        $batchKg    = $batchGrams / 1000;

        if ($batchGrams <= 0) {
            Log::warning('No usable batch weight — no raw materials issued', [
                'productionItemId' => $item->id,
                'weight'           => $item->weight,
                'qnt'              => $item->qnt,
                'productId'        => $item->productId,
            ]);
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

        // A formula that does not total 100% means the operator is weighing to a
        // different recipe than the ledger is being charged for. The React
        // builder enforces this on save; a row edited straight in the database,
        // or an old snapshot, does not go through it.
        $pctTotal = round($lines->sum(fn ($l) => (float) $l->percentage), 4);

        if (abs($pctTotal - 100) > 0.01) {
            throw new \RuntimeException(sprintf(
                'PROD-%d: %s totals %.4f%%, not 100%%. Fix the formula before producing against it.',
                $item->id, $this->formulaSource, $pctTotal
            ));
        }

        $perUnitGrams = intdiv($batchGrams, $qnt);
        $perUnitKg    = $perUnitGrams / 1000;

        /* The client sends no weight, so the column would otherwise stay empty
           and every downstream report would have to recalculate this. Store what
           the materials were actually issued against. */
        if ((float) $item->weight <= 0) {
            DB::table('productionitems')->where('id', $item->id)->update([
                'weight'     => $batchKg,
                'updated_at' => now(),
            ]);
            $item->weight = $batchKg;
        }

        // ── Apportion the batch across the lines, in whole grams ──────────────
        // Floor every line, then hand the leftover grams to the largest
        // fractional parts. The issued lines then sum to the batch exactly, so
        // there is never a stray gram to reconcile and nothing to absorb it.
        $exact = [];
        foreach ($lines as $i => $line) {
            $exact[$i] = $batchGrams * ((float) $line->percentage) / 100;
        }

        $grams     = array_map('floor', $exact);
        $leftover  = $batchGrams - (int) array_sum($grams);
        $remainder = [];

        foreach ($exact as $i => $v) {
            $remainder[$i] = $v - floor($v);
        }
        arsort($remainder);

        foreach (array_keys($remainder) as $i) {
            if ($leftover <= 0) break;
            $grams[$i]++;
            $leftover--;
        }

        // one readable block per production item, so a run can be read back off
        // the log without joining anything
        $issued     = [];
        $skipped    = [];
        $short      = [];
        $totalGrams = 0;

        Log::info(sprintf(
            'RAW MATERIALS OUT  PROD-%d  %d x %.3f kg = %.3f kg (%s g)  weight: %s  formula: %s  (%d lines)',
            $item->id,
            $qnt,
            $perUnitKg,
            $batchKg,
            number_format($batchGrams, 0, '.', ' '),
            $this->weightSource,
            $this->formulaSource,
            $lines->count()
        ));

        foreach ($lines as $i => $line) {
            $lineGrams = (int) $grams[$i];
            $qty       = $lineGrams / 1000;          // kg, for storage and display

            $material = DB::table('raw_materials')->where('id', $line->raw_material_id)->first();

            if (!$material) {
                Log::warning('  ! material missing', ['raw_material_id' => $line->raw_material_id]);
                $skipped[] = ['raw_material_id' => $line->raw_material_id, 'why' => 'material missing'];
                continue;
            }

            /* Under a gram is not a rounding question, it is a production one:
               nobody can weigh it. Silently dropping an active ingredient would
               hand the operator a jobcard that makes something other than the
               formula, so this stops the batch instead. Either the batch is too
               small for this formula, or the additive needs to be dosed as a
               pre-diluted premix. */
            if ($lineGrams < 1) {
                throw new \RuntimeException(sprintf(
                    'PROD-%d: %s (%s) works out to under 1 g at a %.3f kg batch (%.4f%%). '
                  . 'Increase the batch size or dose it as a premix.',
                    $item->id, $material->code, $material->name, $batchKg, (float) $line->percentage
                ));
            }

            /* Short stock never blocks production. What came off the machine
               came off the machine — refusing to record it would leave the
               finished goods counted and the materials not, which is worse than
               a negative balance. The balance goes negative and says so. */
            $onHand       = (float) $material->stock_on_hand;
            $onHandGrams  = (int) round($onHand * 1000);
            $shortGrams   = $lineGrams - $onHandGrams;

            if ($shortGrams > 0) {
                Log::warning(sprintf(
                    '  ! %-12s %-28s SHORT by %.3f %s — %.3f on hand, %.3f needed, going to %.3f',
                    $material->code,
                    substr($material->name, 0, 28),
                    $shortGrams / 1000,
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
                    'shortfall' => $shortGrams / 1000,
                    // the column is optional — an absent flag is not a crash
                    'flagged'   => !($material->allow_negative ?? 0),
                ];
            }

            // previous and current on the material row, exactly like stocks.
            // prvqnt is assigned before the balance moves — MySQL evaluates the
            // SET list left to right, so the order of these two keys matters
            DB::table('raw_materials')
                ->where('id', $material->id)
                ->update([
                    'prv_stock_on_hand' => DB::raw('stock_on_hand'),
                    'stock_on_hand'     => DB::raw('stock_on_hand - ' . sprintf('%.4f', $qty)),
                    'updated_at'        => now(),
                ]);

            $balance     = (float) DB::table('raw_materials')->where('id', $material->id)->value('stock_on_hand');
            $before      = $onHand;
            $totalGrams += $lineGrams;

            $unitCost = (float) $material->cost_per_kg;
            $lineCost = round($qty * $unitCost, 2);

            // code | name | % | per unit | batch kg | grams | balance before -> after
            Log::info(sprintf(
                '  - %-12s %-28s %9.4f%%  %9.3f g/unit  %10.3f kg  %12s g   %10.3f -> %10.3f %s',
                $material->code,
                substr($material->name, 0, 28),
                (float) $line->percentage,
                $lineGrams / $qnt,
                $qty,
                number_format($lineGrams, 0, '.', ' '),
                $before,
                $balance,
                $material->uom
            ));

            $issued[] = [
                'code'          => $material->code,
                'name'          => $material->name,
                'percentage'    => (float) $line->percentage,
                'kg'            => $qty,
                'grams'         => $lineGrams,
                'grams_per_unit'=> round($lineGrams / $qnt, 3),
                'before'        => $before,
                'after'         => $balance,
                'uom'           => $material->uom,
                'unit_cost'     => $unitCost,
                'line_cost'     => $lineCost,
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
                                      . ' (' . $qnt . ' x ' . number_format($perUnitKg, 3) . ' kg)'
                                      . ($shortGrams > 0 ? ' — short ' . number_format($shortGrams / 1000, 3) : ''),
                'created_by'      => $item->userId,
            ]);
        }

        $totalKg = $totalGrams / 1000;

        // the structured version — searchable, and what you want when something
        // does not add up
        Log::info('RAW MATERIALS OUT summary', [
            'production_item'  => $item->id,
            'production'       => $item->productionId,
            'product'          => $item->productId,
            'qnt'              => $qnt,
            'kg_per_unit'      => round($perUnitKg, 3),
            'grams_per_unit'   => $perUnitGrams,
            'recorded_weight'  => (float) $item->weight,
            'batch_kg'         => round($batchKg, 3),
            'batch_grams'      => $batchGrams,
            'weight_source'    => $this->weightSource,
            'issued_kg'        => round($totalKg, 3),
            'issued_grams'     => $totalGrams,
            'variance_grams'   => $batchGrams - $totalGrams,
            'batch_cost'       => round(array_sum(array_column($issued, 'line_cost')), 2),
            'cost_per_unit'    => round(array_sum(array_column($issued, 'line_cost')) / $qnt, 4),
            'formula_source'   => $this->formulaSource,
            'formula_total_pc' => $pctTotal,
            'lines'            => $issued,
            'skipped'          => $skipped,
            'short'            => $short,
        ]);

        // negative balances need chasing the same day — a stock count was missed,
        // a delivery was never booked in, or the formula is wrong
        if ($short) {
            Log::warning(sprintf(
                'RAW MATERIALS OUT  PROD-%d  %d material(s) went negative: %s',
                $item->id,
                count($short),
                implode(', ', array_map(
                    fn ($s) => $s['code'] . ' short ' . number_format($s['shortfall'], 3),
                    $short
                ))
            ));
        }

        /* With whole-gram apportionment the issued lines sum to the batch
           exactly, so any variance here means a line was skipped for a missing
           material — not a rounding drift. */
        if ($batchGrams !== $totalGrams) {
            Log::warning(sprintf(
                'RAW MATERIALS OUT  PROD-%d  issued %s g against a %s g batch (%s g out) — check the skipped lines',
                $item->id,
                number_format($totalGrams, 0, '.', ' '),
                number_format($batchGrams, 0, '.', ' '),
                number_format($batchGrams - $totalGrams, 0, '.', ' ')
            ));
        }
    }

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
                    return $snapshot->values();
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
            ->get(['raw_material_id', 'percentage'])
            ->values();
    }
}