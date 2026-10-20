<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductionReportController extends Controller
{
    /* ───────────────────────────────────────────────────────────────────
       TABLE / COLUMN MAP — edit here if a name differs. The report page
       names any table or column it can't find.
       ─────────────────────────────────────────────────────────────────── */
    // Table names are found automatically — the first one that exists is used
    private const RUN_TABLES    = ['productions', 'production'];                         // one row per production run
    private const RUN_DATE      = 'prodDate';           // falls back to created_at

    private const ITEM_TABLES   = ['productionitems', 'production_items', 'productionitem', 'production_item']; // what each run produced
    private const ITEM_RUN      = 'productionId';
    private const ITEM_PRODUCT  = 'productId';
    private const ITEM_QTY      = 'qnt';                // stored as text — converted to a number
    private const ITEM_DATE     = 'prodDate';           // optional — used first when filled in
    private const ITEM_STATE    = 'stateId';

    private const PROD          = 'chemical_products';
    private const PROD_NAME     = 'name';

    // production_items.stateId values to leave out (e.g. cancelled / rejected). Empty = count everything.
    private const EXCLUDE_ITEM_STATES = [];

    /* ─────────────────────────────── Pages ─────────────────────────────── */

    public function page()
    {
        return view('reports.production');
    }

    /* GET /reports/production/data?data={"from":"2026-10-14","to":"2026-10-14"} */
    public function data(Request $request)
    {
        try {
            if ($err = $this->configError()) {
                return response()->json(['status' => 'error', 'message' => $err]);
            }

            $d    = json_decode((string) $request->query('data', '{}'), true) ?: [];
            $from = !empty($d['from']) ? Carbon::parse($d['from'])->startOfDay() : Carbon::today();
            $to   = !empty($d['to'])   ? Carbon::parse($d['to'])->endOfDay()     : Carbon::today()->endOfDay();
            if ($to->lt($from)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            // The prior period is the same length, immediately before
            $days     = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;
            $prevTo   = $from->copy()->subDay()->endOfDay();
            $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();

            return response()->json([
                'status'      => 'ok',
                'period'      => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
                'prior'       => ['from' => $prevFrom->toDateString(), 'to' => $prevTo->toDateString()],
                'totals'      => $this->totals($from, $to),
                'priorTotals' => $this->totals($prevFrom, $prevTo),
                'products'    => $this->byProduct($from, $to),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /* ────────────────────────────── Queries ────────────────────────────── */

    private function totals(Carbon $from, Carbon $to): array
    {
        $row = $this->base($from, $to)->selectRaw("
            COUNT(DISTINCT r.`id`)                                  AS runs,
            COUNT(DISTINCT it.`" . self::ITEM_PRODUCT . "`)         AS products,
            COALESCE(SUM({$this->qty()}), 0)                        AS units
        ")->first();

        return [
            'runs'     => (int) $row->runs,
            'products' => (int) $row->products,
            'units'    => round((float) $row->units, 2),
        ];
    }

    private function byProduct(Carbon $from, Carbon $to): array
    {
        $pid = 'it.`' . self::ITEM_PRODUCT . '`';

        return $this->base($from, $to)
            ->leftJoin(self::PROD . ' as pr', 'pr.id', '=', 'it.' . self::ITEM_PRODUCT)
            ->selectRaw("
                $pid                                                              AS id,
                COALESCE(pr.`" . self::PROD_NAME . "`, CONCAT('Product #', $pid)) AS name,
                COUNT(DISTINCT r.`id`)                                            AS runs,
                COALESCE(SUM({$this->qty()}), 0)                                  AS units,
                MAX({$this->dateExpr()})                                          AS last_made
            ")
            ->groupBy('it.' . self::ITEM_PRODUCT, 'pr.' . self::PROD_NAME)
            ->orderByDesc('units')
            ->get()
            ->map(fn ($r) => [
                'id'       => $r->id,
                'name'     => $r->name,
                'runs'     => (int) $r->runs,
                'units'    => round((float) $r->units, 2),
                'lastMade' => $r->last_made,
            ])
            ->all();
    }

    /* ────────────────────────────── Helpers ────────────────────────────── */

    private function base(Carbon $from, Carbon $to)
    {
        $q = DB::table($this->item() . ' as it')
            ->join($this->run() . ' as r', 'r.id', '=', 'it.' . self::ITEM_RUN)
            ->whereRaw($this->dateExpr() . ' BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()]);

        if (self::EXCLUDE_ITEM_STATES) {
            $q->where(fn ($w) => $w->whereNull('it.' . self::ITEM_STATE)
                                   ->orWhereNotIn('it.' . self::ITEM_STATE, self::EXCLUDE_ITEM_STATES));
        }

        return $q;
    }

    // qnt is stored as text; non-numbers count as 0
    private function qty(): string
    {
        return 'CAST(COALESCE(NULLIF(TRIM(it.`' . self::ITEM_QTY . '`), \'\'), \'0\') AS DECIMAL(14,2))';
    }

    // Item date first, then the run's date, then when the run was captured
    private function dateExpr(): string
    {
        $parts = [];
        if (Schema::hasColumn($this->item(), self::ITEM_DATE)) {
            $parts[] = 'it.`' . self::ITEM_DATE . '`';
        }
        if (Schema::hasColumn($this->run(), self::RUN_DATE)) {
            $parts[] = 'r.`' . self::RUN_DATE . '`';
        }
        $parts[] = 'DATE(r.`created_at`)';

        return 'COALESCE(' . implode(', ', $parts) . ')';
    }

    private ?string $runTable  = null;
    private ?string $itemTable = null;

    private function run(): string  { return $this->runTable  ??= $this->firstTable(self::RUN_TABLES)  ?? self::RUN_TABLES[0]; }
    private function item(): string { return $this->itemTable ??= $this->firstTable(self::ITEM_TABLES) ?? self::ITEM_TABLES[0]; }

    private function firstTable(array $names): ?string
    {
        foreach ($names as $n) {
            if (Schema::hasTable($n)) {
                return $n;
            }
        }
        return null;
    }

    private function configError(): ?string
    {
        if (!$this->firstTable(self::RUN_TABLES)) {
            return "No production table found (tried: " . implode(', ', self::RUN_TABLES) . "). Add your table's name to RUN_TABLES at the top of ProductionReportController.php.";
        }
        if (!$this->firstTable(self::ITEM_TABLES)) {
            return "No production items table found (tried: " . implode(', ', self::ITEM_TABLES) . "). Add your table's name to ITEM_TABLES at the top of ProductionReportController.php.";
        }

        $need = [
            $this->run()  => ['id', 'created_at'],
            $this->item() => [self::ITEM_RUN, self::ITEM_PRODUCT, self::ITEM_QTY, self::ITEM_STATE],
            self::PROD    => ['id', self::PROD_NAME],
        ];

        foreach ($need as $table => $cols) {
            if (!Schema::hasTable($table)) {
                return "Table '$table' was not found. Set the right name at the top of ProductionReportController.php.";
            }
            foreach ($cols as $col) {
                if (!Schema::hasColumn($table, $col)) {
                    return "Column '$table.$col' was not found. Set the right name at the top of ProductionReportController.php.";
                }
            }
        }

        return null;
    }
}