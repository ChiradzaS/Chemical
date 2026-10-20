<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesReportController extends Controller
{
    /* ───────────────────────────────────────────────────────────────────
       TABLE / COLUMN MAP — the only part you may need to edit.
       If a name below doesn't match your database, the report page says
       exactly which table or column is missing.
       ─────────────────────────────────────────────────────────────────── */
    private const INV            = 'invoices';        // invoice header table
    private const INV_ID         = 'id';
    private const INV_NO         = 'reference';       // invoice number shown in the export
    private const INV_DATE       = 'created_at';
    private const INV_CUSTOMER   = 'customerId';
    private const INV_TOTAL_EXCL = 'totalValue';      // invoice total excl. VAT
    private const INV_TOTAL_VAT  = 'totalVat';        // invoice VAT (exact, decimal)

    private const ITEM           = 'invoice_items';   // invoice lines
    private const ITEM_INV       = 'invoicesId';
    private const ITEM_PRODUCT   = 'productId';
    private const ITEM_QTY       = 'quantity';
    private const ITEM_LINE_EXCL = 'totalPrice';      // line total excl. VAT (quantity × price)

    // invoices.stateId values to leave out of the report (e.g. cancelled). Empty = include all.
    private const EXCLUDE_STATES = [];

    private const CUST         = 'customers';
    private const CUST_NAME    = 'name';

    private const PROD         = 'chemical_products';
    private const PROD_NAME    = 'name';
    // Unit cost = the product form's "Total cost price". Missing columns are skipped.
    private const PROD_COST_COLS = ['raw_material_cost', 'packaging_cost', 'labour_cost_per_unit', 'overhead_cost'];

    /* ─────────────────────────────── Pages ─────────────────────────────── */

    public function page()
    {
        $customers = Schema::hasTable(self::CUST)
            ? DB::table(self::CUST)->select('id', self::CUST_NAME . ' as name')->orderBy(self::CUST_NAME)->get()
            : collect();

        return view('reports.sales', ['customers' => $customers]);
    }

    /* GET /reports/sales/data?data={"from":"2026-10-01","to":"2026-10-14","customerId":null} */
    public function data(Request $request)
    {
        try {
            if ($err = $this->configError()) {
                return response()->json(['status' => 'error', 'message' => $err]);
            }

            [$from, $to, $customerId] = $this->filters($request);

            // The prior period is the same length, immediately before
            $days     = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;
            $prevTo   = $from->copy()->subDay()->endOfDay();
            $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();

            return response()->json([
                'status'      => 'ok',
                'period'      => ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'days' => $days],
                'prior'       => ['from' => $prevFrom->toDateString(), 'to' => $prevTo->toDateString()],
                'totals'      => $this->totals($from, $to, $customerId),
                'priorTotals' => $this->totals($prevFrom, $prevTo, $customerId),
                'trend'       => $this->trend($from, $to, $customerId, $days),
                'customers'   => $this->byCustomer($from, $to, $customerId),
                'products'    => $this->byProduct($from, $to, $customerId),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /* GET /reports/sales/export?data=... — every invoice line in the period.
       Real .xlsx when PhpSpreadsheet is installed, otherwise a CSV Excel can open. */
    public function export(Request $request)
    {
        if ($err = $this->configError()) {
            return response($err, 500);
        }

        [$from, $to, $customerId] = $this->filters($request);

        $noCol = Schema::hasColumn(self::INV, self::INV_NO) ? self::INV_NO : self::INV_ID;
        $cost  = $this->costExpr();

        $rows = $this->base($from, $to, $customerId)
            ->leftJoin(self::CUST . ' as c', 'c.id', '=', 'inv.' . self::INV_CUSTOMER)
            ->selectRaw("
                DATE(inv.`" . self::INV_DATE . "`)               AS sale_date,
                inv.`$noCol`                                     AS invoice_no,
                c.`" . self::CUST_NAME . "`                      AS customer,
                p.`" . self::PROD_NAME . "`                      AS product,
                {$this->qty()}                                   AS qty,
                {$this->excl()}                                  AS excl,
                {$this->vat()}                                   AS vat,
                {$this->incl()}                                  AS incl,
                {$this->qty()} * $cost                           AS cost
            ")
            ->orderBy('inv.' . self::INV_DATE)
            ->orderBy('inv.' . self::INV_ID)
            ->get();

        $base   = 'Sales_' . $from->format('Y-m-d') . '_to_' . $to->format('Y-m-d');
        $header = ['Date', 'Invoice', 'Customer', 'Product', 'Qty', 'Excl. VAT', 'VAT', 'Incl. VAT', 'Cost', 'Profit'];

        $line = fn ($r) => [
            $r->sale_date, $r->invoice_no, $r->customer ?? '', $r->product ?? '',
            (float) $r->qty, round((float) $r->excl, 2), round((float) $r->vat, 2), round((float) $r->incl, 2),
            round((float) $r->cost, 2), round((float) $r->excl - (float) $r->cost, 2),
        ];

        if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            $book  = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $book->getActiveSheet();
            $sheet->setTitle('Sales');
            $sheet->fromArray($header, null, 'A1');

            $i = 2;
            foreach ($rows as $r) {
                $sheet->fromArray($line($r), null, 'A' . $i++);
            }
            $last = $i - 1;

            if ($last >= 2) {
                $sheet->setCellValue('D' . $i, 'TOTAL');
                foreach (['E', 'F', 'G', 'H', 'I', 'J'] as $col) {
                    $sheet->setCellValue($col . $i, "=SUM({$col}2:{$col}{$last})");
                }
                $sheet->getStyle("A{$i}:J{$i}")->getFont()->setBold(true);
            }

            $sheet->getStyle('A1:J1')->getFont()->setBold(true);
            $sheet->getStyle("F2:J{$i}")->getNumberFormat()->setFormatCode('#,##0.00');
            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            $sheet->freezePane('A2');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book);

            return response()->streamDownload(fn () => $writer->save('php://output'), $base . '.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return response()->streamDownload(function () use ($rows, $header, $line) {
            $out = fopen('php://output', 'w');
            fwrite($out, "sep=,\n"); // makes Excel split on commas whatever the PC's regional settings
            fputcsv($out, $header);
            foreach ($rows as $r) {
                fputcsv($out, $line($r));
            }
            fclose($out);
        }, $base . '.csv', ['Content-Type' => 'text/csv']);
    }

    /* ────────────────────────────── Queries ────────────────────────────── */

    private function totals(Carbon $from, Carbon $to, ?int $customerId): array
    {
        $cost = $this->costExpr();

        $row = $this->base($from, $to, $customerId)->selectRaw("
            COUNT(DISTINCT inv.`" . self::INV_ID . "`)                AS invoices,
            COALESCE(SUM({$this->qty()}), 0)                          AS units,
            COALESCE(SUM({$this->excl()}), 0)                         AS excl,
            COALESCE(SUM({$this->vat()}), 0)                          AS vat,
            COALESCE(SUM({$this->incl()}), 0)                         AS incl,
            COALESCE(SUM({$this->qty()} * $cost), 0)                  AS cost,
            COALESCE(SUM(CASE WHEN $cost <= 0 THEN 1 ELSE 0 END), 0)  AS uncosted_lines
        ")->first();

        $t = $this->money($row);
        $t['invoices']       = (int) $row->invoices;
        $t['uncosted_lines'] = (int) $row->uncosted_lines;

        return $t;
    }

    private function trend(Carbon $from, Carbon $to, ?int $customerId, int $days): array
    {
        $byMonth = $days > 62;
        $fmt     = $byMonth ? '%Y-%m' : '%Y-%m-%d';
        $date    = 'inv.`' . self::INV_DATE . '`';

        $rows = $this->base($from, $to, $customerId)
            ->selectRaw("DATE_FORMAT($date, '$fmt') AS k,
                         COALESCE(SUM({$this->excl()}), 0) AS excl,
                         COALESCE(SUM({$this->incl()}), 0) AS incl")
            ->groupBy('k')
            ->get()
            ->keyBy('k');

        // Every day/month in the range appears, including ones with no sales
        $points = [];
        $cur    = $byMonth ? $from->copy()->startOfMonth() : $from->copy()->startOfDay();

        while ($cur->lte($to)) {
            $key = $byMonth ? $cur->format('Y-m') : $cur->format('Y-m-d');
            $row = $rows->get($key);

            $points[] = [
                'key'   => $key,
                'label' => $byMonth ? $cur->format('M y') : $cur->format('d M'),
                'excl'  => round((float) ($row->excl ?? 0), 2),
                'incl'  => round((float) ($row->incl ?? 0), 2),
            ];

            if ($byMonth) {
                $cur->addMonthNoOverflow();
            } else {
                $cur->addDay();
            }
        }

        return ['granularity' => $byMonth ? 'month' : 'day', 'points' => $points];
    }

    private function byCustomer(Carbon $from, Carbon $to, ?int $customerId): array
    {
        $cost = $this->costExpr();
        $cid  = 'inv.`' . self::INV_CUSTOMER . '`';

        return $this->base($from, $to, $customerId)
            ->leftJoin(self::CUST . ' as c', 'c.id', '=', 'inv.' . self::INV_CUSTOMER)
            ->selectRaw("
                $cid                                                         AS id,
                COALESCE(c.`" . self::CUST_NAME . "`, CONCAT('Customer #', $cid)) AS name,
                COUNT(DISTINCT inv.`" . self::INV_ID . "`)                   AS invoices,
                COALESCE(SUM({$this->qty()}), 0)                             AS units,
                COALESCE(SUM({$this->excl()}), 0)                            AS excl,
                COALESCE(SUM({$this->vat()}), 0)                             AS vat,
                COALESCE(SUM({$this->incl()}), 0)                            AS incl,
                COALESCE(SUM({$this->qty()} * $cost), 0)                     AS cost
            ")
            ->groupBy('inv.' . self::INV_CUSTOMER, 'c.' . self::CUST_NAME)
            ->orderByDesc('incl')
            ->limit(25)
            ->get()
            ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'invoices' => (int) $r->invoices] + $this->money($r))
            ->all();
    }

    private function byProduct(Carbon $from, Carbon $to, ?int $customerId): array
    {
        $cost = $this->costExpr();
        $pid  = 'it.`' . self::ITEM_PRODUCT . '`';

        return $this->base($from, $to, $customerId)
            ->selectRaw("
                $pid                                                          AS id,
                COALESCE(p.`" . self::PROD_NAME . "`, CONCAT('Product #', $pid)) AS name,
                COALESCE(SUM({$this->qty()}), 0)                              AS units,
                COALESCE(SUM({$this->excl()}), 0)                             AS excl,
                COALESCE(SUM({$this->vat()}), 0)                              AS vat,
                COALESCE(SUM({$this->incl()}), 0)                             AS incl,
                COALESCE(SUM({$this->qty()} * $cost), 0)                      AS cost
            ")
            ->groupBy('it.' . self::ITEM_PRODUCT, 'p.' . self::PROD_NAME)
            ->orderByDesc('incl')
            ->limit(25)
            ->get()
            ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name] + $this->money($r))
            ->all();
    }

    /* ────────────────────────────── Helpers ────────────────────────────── */

    private function base(Carbon $from, Carbon $to, ?int $customerId)
    {
        $q = DB::table(self::ITEM . ' as it')
            ->join(self::INV . ' as inv', 'inv.' . self::INV_ID, '=', 'it.' . self::ITEM_INV)
            ->leftJoin(self::PROD . ' as p', 'p.id', '=', 'it.' . self::ITEM_PRODUCT)
            ->whereBetween('inv.' . self::INV_DATE, [$from->toDateTimeString(), $to->toDateTimeString()]);

        if ($customerId) {
            $q->where('inv.' . self::INV_CUSTOMER, $customerId);
        }

        if (self::EXCLUDE_STATES) {
            $q->whereNotIn('inv.stateId', self::EXCLUDE_STATES);
        }

        return $q;
    }

    private function filters(Request $request): array
    {
        $d = json_decode((string) $request->query('data', '{}'), true) ?: [];

        $from = !empty($d['from']) ? Carbon::parse($d['from'])->startOfDay() : now()->startOfMonth();
        $to   = !empty($d['to'])   ? Carbon::parse($d['to'])->endOfDay()     : now()->endOfDay();

        if ($to->lt($from)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $customerId = !empty($d['customerId']) ? (int) $d['customerId'] : null;

        return [$from, $to, $customerId];
    }

    // Excl/VAT/incl/cost → rounded floats, plus profit and margin
    private function money($r): array
    {
        $excl = (float) $r->excl;
        $cost = (float) $r->cost;

        return [
            'units'  => round((float) $r->units, 2),
            'excl'   => round($excl, 2),
            'vat'    => round((float) $r->vat, 2),
            'incl'   => round((float) $r->incl, 2),
            'cost'   => round($cost, 2),
            'profit' => round($excl - $cost, 2),
            'margin' => $excl > 0 ? round(($excl - $cost) / $excl * 100, 1) : null,
        ];
    }

    private function qty():  string { return 'it.`' . self::ITEM_QTY . '`'; }
    private function excl(): string { return 'it.`' . self::ITEM_LINE_EXCL . '`'; }

    /* Line VAT = the line's share of the invoice's VAT.
       invoice_items.vatAmnt is a whole number (cents lost), so the exact
       decimal totalVat on the invoice header is used instead. An invoice
       with no VAT gives 0; the shares always add up to the invoice's VAT. */
    private function vat(): string
    {
        return "COALESCE({$this->excl()} * inv.`" . self::INV_TOTAL_VAT . "` / NULLIF(inv.`" . self::INV_TOTAL_EXCL . "`, 0), 0)";
    }

    private function incl(): string { return "({$this->excl()} + {$this->vat()})"; }

    // Unit cost from whichever cost columns exist on the product table
    private function costExpr(): string
    {
        $parts = [];
        foreach (self::PROD_COST_COLS as $col) {
            if (Schema::hasColumn(self::PROD, $col)) {
                $parts[] = "COALESCE(p.`$col`, 0)";
            }
        }

        return $parts ? '(' . implode(' + ', $parts) . ')' : '0';
    }

    // Names a wrong table/column in the map above, instead of an SQL error
    private function configError(): ?string
    {
        $need = [
            self::INV  => [self::INV_ID, self::INV_DATE, self::INV_CUSTOMER, self::INV_TOTAL_EXCL, self::INV_TOTAL_VAT],
            self::ITEM => [self::ITEM_INV, self::ITEM_PRODUCT, self::ITEM_QTY, self::ITEM_LINE_EXCL],
            self::CUST => ['id', self::CUST_NAME],
            self::PROD => ['id', self::PROD_NAME],
        ];

        foreach ($need as $table => $cols) {
            if (!Schema::hasTable($table)) {
                return "Table '$table' was not found. Set the right name at the top of SalesReportController.php.";
            }
            foreach ($cols as $col) {
                if (!Schema::hasColumn($table, $col)) {
                    return "Column '$table.$col' was not found. Set the right name at the top of SalesReportController.php.";
                }
            }
        }

        return null;
    }
}