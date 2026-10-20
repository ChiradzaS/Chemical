<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OutstandingOrdersController extends Controller
{
    /* ───────────────────────────────────────────────────────────────────
       TABLE / COLUMN MAP — edit here if a name differs. The report page
       names any table or column it can't find.
       ─────────────────────────────────────────────────────────────────── */
    private const ORD             = 'orders';
    private const ORD_NO          = 'reference';
    private const ORD_CUSTOMER    = 'customerId';
    private const ORD_STATE       = 'stateId';
    private const ORD_PLACED      = 'datePlaced';      // falls back to `date`, then created_at
    private const ORD_DUE         = 'dueDate';

    private const ITEM            = 'order_items';
    private const ITEM_ORDER      = 'ordersId';
    private const ITEM_PRODUCT    = 'productId';
    private const ITEM_QTY        = 'quantity';
    private const ITEM_PRICE      = 'price';
    private const ITEM_TOTAL      = 'totalPrice';
    private const ITEM_STATE      = 'stateId';

    private const CUST            = 'customers';
    private const CUST_NAME       = 'name';

    private const PROD            = 'chemical_products';
    private const PROD_NAME       = 'name';
    private const PROD_STOCK      = 'stock_on_hand';
    private const PROD_FORMULA    = 'formula_code';

    // stateId 2 = complete. Anything else (or empty) = still outstanding.
    // Add more values here if, say, a cancelled state is added later.
    private const CLOSED_ORDER_STATES = ['2'];   // orders.stateId is text
    private const DONE_ITEM_STATES    = [2];     // order_items.stateId is a number

    // Orders with no due date count as overdue after this many days
    private const OVERDUE_DAYS = 14;

    /* ─────────────────────────────── Pages ─────────────────────────────── */

    public function page()
    {
        return view('reports.outstandingorders');
    }

    public function data()
    {
        try {
            if ($err = $this->configError()) {
                return response()->json(['status' => 'error', 'message' => $err]);
            }

            $lines = $this->openLines();
            $today = Carbon::today();

            /* Share the stock out oldest order first — the order dispatch fills them */
            $left = [];   // product id → stock still unallocated
            foreach ($lines as $l) {
                if (!array_key_exists($l->product_id, $left)) {
                    $left[$l->product_id] = max(0.0, (float) $l->stock);
                }
                $qty   = (float) $l->qty;
                $avail = $left[$l->product_id];

                $l->status = $avail >= $qty ? 'ok' : ($avail > 0 ? 'partial' : 'none');
                $l->can_ship = round(min($qty, $avail), 2);
                $left[$l->product_id] = max(0.0, $avail - $qty);
            }

            /* Group the lines into orders */
            $orders = [];
            foreach ($lines as $l) {
                $id = $l->order_id;

                if (!isset($orders[$id])) {
                    $placed = $l->placed ? Carbon::parse($l->placed)->startOfDay() : null;
                    $due    = $l->due    ? Carbon::parse($l->due)->startOfDay()    : null;
                    $age    = $placed ? (int) $placed->diffInDays($today) : null;

                    $orders[$id] = [
                        'id'          => $id,
                        'reference'   => $l->reference ?: ('#' . $id),
                        'customerId'  => $l->customer_id,
                        'customer'    => $l->customer ?: ('Customer #' . $l->customer_id),
                        'placed'      => $placed?->toDateString(),
                        'due'         => $due?->toDateString(),
                        'age'         => $age,
                        'overdue'     => $due ? $due->lt($today) : ($age !== null && $age > self::OVERDUE_DAYS),
                        'daysLate'    => $due && $due->lt($today) ? (int) $due->diffInDays($today) : null,
                        'value'       => 0.0,
                        'units'       => 0.0,
                        'lines'       => [],
                    ];
                }

                $orders[$id]['value'] += (float) $l->value;
                $orders[$id]['units'] += (float) $l->qty;
                $orders[$id]['lines'][] = [
                    'id'       => $l->line_id,
                    'product'  => $l->product,
                    'formula'  => $l->formula,
                    'qty'      => round((float) $l->qty, 2),
                    'canShip'  => $l->can_ship,
                    'value'    => round((float) $l->value, 2),
                    'status'   => $l->status,
                ];
            }

            foreach ($orders as &$o) {
                $st = array_column($o['lines'], 'status');
                $o['status'] = !array_diff($st, ['ok']) ? 'ok' : (!array_diff($st, ['none']) ? 'none' : 'partial');
                $o['value']  = round($o['value'], 2);
                $o['units']  = round($o['units'], 2);
            }
            unset($o);

            /* What to make to clear every open order */
            $shortfall = [];
            foreach ($lines as $l) {
                $pid = $l->product_id;
                $shortfall[$pid] ??= [
                    'productId' => $pid,
                    'product'   => $l->product,
                    'formula'   => $l->formula,
                    'ordered'   => 0.0,
                    'stock'     => round(max(0.0, (float) $l->stock), 2),
                ];
                $shortfall[$pid]['ordered'] += (float) $l->qty;
            }
            foreach ($shortfall as &$s) {
                $s['ordered'] = round($s['ordered'], 2);
                $s['short']   = round(max(0.0, $s['ordered'] - $s['stock']), 2);
            }
            unset($s);
            usort($shortfall, fn ($a, $b) => $b['short'] <=> $a['short'] ?: strcmp($a['product'], $b['product']));

            return response()->json([
                'status'      => 'ok',
                'today'       => $today->toDateString(),
                'overdueDays' => self::OVERDUE_DAYS,
                'orders'      => array_values($orders),
                'shortfall'   => $shortfall,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /* ────────────────────────────── Queries ────────────────────────────── */

    // Every unfinished order line, oldest order first
    private function openLines()
    {
        $placedParts = [];
        foreach ([self::ORD_PLACED, 'date'] as $col) {
            if (Schema::hasColumn(self::ORD, $col)) {
                $placedParts[] = "o.`$col`";
            }
        }
        $placedParts[] = 'DATE(o.`created_at`)';
        $placed = 'COALESCE(' . implode(', ', $placedParts) . ')';

        $due     = Schema::hasColumn(self::ORD, self::ORD_DUE)        ? 'o.`' . self::ORD_DUE . '`'        : 'NULL';
        $stock   = Schema::hasColumn(self::PROD, self::PROD_STOCK)    ? 'p.`' . self::PROD_STOCK . '`'     : '0';
        $formula = Schema::hasColumn(self::PROD, self::PROD_FORMULA)  ? 'p.`' . self::PROD_FORMULA . '`'   : 'NULL';
        $qty     = 'it.`' . self::ITEM_QTY . '`';
        $value   = "COALESCE(NULLIF(it.`" . self::ITEM_TOTAL . "`, 0), $qty * it.`" . self::ITEM_PRICE . "`, 0)";

        $q = DB::table(self::ITEM . ' as it')
            ->join(self::ORD . ' as o', 'o.id', '=', 'it.' . self::ITEM_ORDER)
            ->leftJoin(self::PROD . ' as p', 'p.id', '=', 'it.' . self::ITEM_PRODUCT)
            ->leftJoin(self::CUST . ' as c', 'c.id', '=', 'o.' . self::ORD_CUSTOMER)
            ->selectRaw("
                o.`id`                                                     AS order_id,
                o.`" . self::ORD_NO . "`                                   AS reference,
                o.`" . self::ORD_CUSTOMER . "`                             AS customer_id,
                c.`" . self::CUST_NAME . "`                                AS customer,
                $placed                                                    AS placed,
                $due                                                       AS due,
                it.`id`                                                    AS line_id,
                it.`" . self::ITEM_PRODUCT . "`                            AS product_id,
                COALESCE(p.`" . self::PROD_NAME . "`, CONCAT('Product #', it.`" . self::ITEM_PRODUCT . "`)) AS product,
                $formula                                                   AS formula,
                $qty                                                       AS qty,
                $value                                                     AS value,
                COALESCE($stock, 0)                                        AS stock
            ")
            ->orderByRaw("$placed ASC, o.`id` ASC, it.`id` ASC");

        if (self::DONE_ITEM_STATES) {
            // an empty stateId counts as not complete
            $q->where(fn ($w) => $w->whereNull('it.' . self::ITEM_STATE)
                                   ->orWhereNotIn('it.' . self::ITEM_STATE, self::DONE_ITEM_STATES));
        }

        if (self::CLOSED_ORDER_STATES) {
            $q->where(fn ($w) => $w->whereNull('o.' . self::ORD_STATE)
                                   ->orWhereNotIn('o.' . self::ORD_STATE, self::CLOSED_ORDER_STATES));
        }

        return $q->get();
    }

    private function configError(): ?string
    {
        $need = [
            self::ORD  => ['id', self::ORD_NO, self::ORD_CUSTOMER, self::ORD_STATE, 'created_at'],
            self::ITEM => ['id', self::ITEM_ORDER, self::ITEM_PRODUCT, self::ITEM_QTY, self::ITEM_PRICE,
                           self::ITEM_TOTAL, self::ITEM_STATE],
            self::CUST => ['id', self::CUST_NAME],
            self::PROD => ['id', self::PROD_NAME],
        ];

        foreach ($need as $table => $cols) {
            if (!Schema::hasTable($table)) {
                return "Table '$table' was not found. Set the right name at the top of OutstandingOrdersController.php.";
            }
            foreach ($cols as $col) {
                if (!Schema::hasColumn($table, $col)) {
                    return "Column '$table.$col' was not found. Set the right name at the top of OutstandingOrdersController.php.";
                }
            }
        }

        return null;
    }
}