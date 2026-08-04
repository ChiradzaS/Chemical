<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

class OrderController extends Controller
{
    private const STATE_PENDING  = 1;
    private const STATE_COMPLETE = 2;

    /*
    |---------------------------------------------------------------------------
    | GET /orders/index — orders joined with their items
    |---------------------------------------------------------------------------
    */
    public function index(Request $request): JsonResponse
    {
        $rows = DB::table('orders as o')
            ->leftJoin('order_items as i', 'i.ordersId', '=', 'o.id')
            ->select(
                'o.*',
                'i.id           as item_id',
                'i.productId    as item_productId',
                'i.unitId       as item_unitId',
                'i.quantity     as item_quantity',
                'i.price        as item_price',
                'i.totalPrice   as item_totalPrice',
                'i.stateId      as item_stateId',
                'i.dueDate      as item_dueDate',
                'i.DateComplete as item_DateComplete',
                'i.other        as item_other',
                'i.job_card_id  as item_job_card_id',
                'i.manufactured as item_manufactured',
                'i.openningQNT  as item_openningQNT'
            )
            ->orderBy('o.created_at', 'desc')
            ->orderBy('i.id')
            ->get();

        return response()->json($this->nest($rows));
    }

    /*
    |---------------------------------------------------------------------------
    | GET /orders/store?data={"order":{...},"items":[{...}]}
    | Values go in as they come from the page.
    |---------------------------------------------------------------------------
    */
    public function store(Request $request): JsonResponse
    {
        $data  = json_decode(urldecode($request->query('data')), true);
        $order = $data['order'] ?? [];
        $items = $data['items'] ?? [];

        if (empty($order['customerId']) || empty($items)) {
            return response()->json(['message' => 'Customer and at least one item are required'], 422);
        }

        // An untouched <input> sends '' — MySQL rejects that for int and date
        // columns, so blanks become null on the way in.
        $clean = fn(array $row) => array_map(fn($v) => $v === '' ? null : $v, $row);

        try {
            $orderId = DB::transaction(function () use ($order, $items, $clean) {
                $now    = now();
                $userId = auth()->id();

                $orderId = DB::table('orders')->insertGetId($clean($order) + [
                    'stateId'    => self::STATE_PENDING,
                    'userId'     => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $rows = [];
                foreach ($items as $i) {
                    $rows[] = $clean($i) + [
                        'ordersId'   => $orderId,
                        'customerId' => $order['customerId'],
                        'stateId'    => self::STATE_PENDING,
                        'userId'     => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                DB::table('order_items')->insert($rows);

                return $orderId;
            });
        } catch (\Throwable $e) {
            \Log::error('order store failed: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'saved', 'id' => $orderId], 200);
    }

    /*
    |---------------------------------------------------------------------------
    | GET /orders/completeitem?data={"id":44,"complete":true}
    |---------------------------------------------------------------------------
    */
    public function completeItem(Request $request): JsonResponse
    {
        $data = json_decode(urldecode($request->query('data')), true);

        $item = DB::table('order_items')->where('id', $data['id'] ?? 0)->first();
        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $complete = filter_var($data['complete'] ?? true, FILTER_VALIDATE_BOOLEAN);

        DB::table('order_items')->where('id', $item->id)->update([
            'stateId'      => $complete ? self::STATE_COMPLETE : self::STATE_PENDING,
            'DateComplete' => $complete ? now()->toDateString() : null,
            'updated_at'   => now(),
        ]);

        // header follows its lines — complete only when none are still pending
        $pending = DB::table('order_items')
            ->where('ordersId', $item->ordersId)
            ->where('stateId', '!=', self::STATE_COMPLETE)
            ->count();

        DB::table('orders')->where('id', $item->ordersId)->update([
            'stateId'    => $pending === 0 ? self::STATE_COMPLETE : self::STATE_PENDING,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'updated', 'order' => $this->fetchOrder($item->ordersId)]);
    }

    /*
    |---------------------------------------------------------------------------
    | GET /orders/completeorder?data={"id":12,"complete":true}
    |---------------------------------------------------------------------------
    */
    public function completeOrder(Request $request): JsonResponse
    {
        $data     = json_decode(urldecode($request->query('data')), true);
        $orderId  = $data['id'] ?? 0;
        $complete = filter_var($data['complete'] ?? true, FILTER_VALIDATE_BOOLEAN);

        if (!DB::table('orders')->where('id', $orderId)->exists()) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $now = now();

        DB::table('order_items')->where('ordersId', $orderId)->update([
            'stateId'      => $complete ? self::STATE_COMPLETE : self::STATE_PENDING,
            'DateComplete' => $complete ? $now->toDateString() : null,
            'updated_at'   => $now,
        ]);

        DB::table('orders')->where('id', $orderId)->update([
            'stateId'    => $complete ? self::STATE_COMPLETE : self::STATE_PENDING,
            'updated_at' => $now,
        ]);

        return response()->json(['message' => 'updated', 'order' => $this->fetchOrder($orderId)]);
    }

    /*
    |---------------------------------------------------------------------------
    | GET /orders/destroy?data={"id":12}
    |---------------------------------------------------------------------------
    */
    public function destroy(Request $request): JsonResponse
    {
        $data    = json_decode(urldecode($request->query('data')), true);
        $orderId = $data['id'] ?? 0;

        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // The page hides the button when these hold, but never trust the page —
        // a stale tab or a hand-typed URL reaches this endpoint too.
        $items    = DB::table('order_items')->where('ordersId', $orderId)->get();
        $blockers = [];

        if ((int) $order->stateId === self::STATE_COMPLETE) {
            $blockers[] = 'the order is marked complete';
        }
        if ($items->contains(fn($i) => (int) $i->stateId === self::STATE_COMPLETE)) {
            $blockers[] = 'some items are already ticked off';
        }
        if ($items->contains(fn($i) => !empty($i->job_card_id))) {
            $blockers[] = 'items are linked to a job card';
        }
        if ($items->contains(fn($i) => (float) $i->manufactured > 0)) {
            $blockers[] = 'production has started';
        }
        if ($items->contains(fn($i) => !is_null($i->openningQNT)
                                       && (float) $i->quantity != (float) $i->openningQNT)) {
            $blockers[] = 'quantities have changed since the order was placed';
        }

        if ($blockers) {
            return response()->json([
                'message' => 'Cannot delete this order: ' . implode('; ', $blockers) . '.',
            ], 409);
        }

        DB::transaction(function () use ($orderId) {
            DB::table('order_items')->where('ordersId', $orderId)->delete();
            DB::table('orders')->where('id', $orderId)->delete();
        });

        return response()->json(['message' => 'deleted', 'id' => $orderId]);
    }

    /*
    |---------------------------------------------------------------------------
    | Helpers
    |---------------------------------------------------------------------------
    */

    /** One order, same shape as an index() row. */
    private function fetchOrder($orderId)
    {
        $rows = DB::table('orders as o')
            ->leftJoin('order_items as i', 'i.ordersId', '=', 'o.id')
            ->select(
                'o.*',
                'i.id           as item_id',
                'i.productId    as item_productId',
                'i.unitId       as item_unitId',
                'i.quantity     as item_quantity',
                'i.price        as item_price',
                'i.totalPrice   as item_totalPrice',
                'i.stateId      as item_stateId',
                'i.dueDate      as item_dueDate',
                'i.DateComplete as item_DateComplete',
                'i.other        as item_other',
                'i.job_card_id  as item_job_card_id',
                'i.manufactured as item_manufactured',
                'i.openningQNT  as item_openningQNT'
            )
            ->where('o.id', $orderId)
            ->orderBy('i.id')
            ->get();

        return $this->nest($rows)->first();
    }

    /** Flat join rows → one row per order with an `items` array. */
    private function nest($rows)
    {
        return $rows->groupBy('id')->map(function ($group) {
            $order = clone $group->first();

            $order->items = $group
                ->filter(fn($r) => !is_null($r->item_id))
                ->map(fn($r) => [
                    'id'           => $r->item_id,
                    'ordersId'     => $order->id,
                    'productId'    => $r->item_productId,
                    'unitId'       => $r->item_unitId,
                    'quantity'     => $r->item_quantity,
                    'price'        => $r->item_price,
                    'totalPrice'   => $r->item_totalPrice,
                    'stateId'      => $r->item_stateId,
                    'dueDate'      => $r->item_dueDate,
                    'DateComplete' => $r->item_DateComplete,
                    'other'        => $r->item_other,
                    'job_card_id'  => $r->item_job_card_id,
                    'manufactured' => $r->item_manufactured,
                    'openningQNT'  => $r->item_openningQNT,
                ])
                ->values();

            // drop the flat item_* columns from the order row
            foreach (get_object_vars($order) as $k => $v) {
                if (str_starts_with($k, 'item_')) unset($order->$k);
            }

            return $order;
        })->values();
    }
}