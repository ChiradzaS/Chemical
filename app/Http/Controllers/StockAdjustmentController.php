<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentController extends Controller
{
    /**
     * GET /api/chemicalproducts/stocklist
     * Returns all chemical products with their current stock-on-hand.
     */
    public function productsList(Request $request)
    {
        $products = DB::table('chemical_products')
            ->select(
                'id as productId',
                'name as name',
                'stock_on_hand as qnt'
            )
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    /**
     * GET /api/stock_adjustments/
     * Returns adjustment history, most recent first.
     */
    public function index(Request $request)
    {
        $adjustments = StockAdjustment::orderBy('created_at', 'desc')
            ->limit(200)
            ->get()
            ->map(function ($adj) {
                $productName = DB::table('chemical_products')->where('id', $adj->productId)->value('name');
                $userName    = $adj->userId
                    ? DB::table('users')->where('id', $adj->userId)->value('name')
                    : null;

                return [
                    'id'              => $adj->id,
                    'created_at'      => $adj->created_at,
                    'product_name'    => $productName,
                    'adjustment_type' => $adj->adjustment_type,
                    'old_quantity'    => $adj->old_quantity,
                    'new_quantity'    => $adj->new_quantity,
                    'change'          => $adj->change,
                    'comment'         => $adj->comment,
                    'adjusted_by'     => $userName ?? 'System',
                ];
            });

        return response()->json($adjustments);
    }

    /**
     * POST /api/stock_adjustments/
     * Creates an adjustment record AND applies it to the stocks table.
     */
    public function store(Request $request)
    {



        $validated = $request->validate([
            'product_id'      => 'required|integer',
            'adjustment_type' => 'required|in:set,add,subtract',
            'old_quantity'    => 'required|numeric',
            'new_quantity'    => 'required|numeric',
            'change'          => 'required|numeric',
            'comment'         => 'required|string',
        ]);

        Log::info('Stock adjustment request received', [

            'raw_input' => $request->all(),
            'method'    => $request->method(),
            
        ]);


        try {
            $result = DB::transaction(function () use ($validated) {

                $stock = DB::table('stocks')
                    ->where('productId', $validated['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    // Create a stock row if it doesn't exist yet
                    $stockId = DB::table('stocks')->insertGetId([
                        'productId'  => $validated['product_id'],
                        'qnt'        => 0,
                        'prvqnt'     => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $stock = DB::table('stocks')->where('id', $stockId)->first();
                }

                $newQty = $validated['new_quantity'];

                // Update stock balance
                DB::table('stocks')
                    ->where('id', $stock->id)
                    ->update([
                        'prvqnt'     => $stock->qnt,
                        'qnt'        => $newQty,
                        'updated_at' => now(),
                    ]);

                // Log to stocks_trans -- signed qnt so it's clear whether it's in or out
                $stocksTransId = DB::table('stocks_trans')->insertGetId([
                    'stockId'    => $stock->id,
                    'docId'      => null, // filled in after we know the adjustment id
                    'docType'    => 215,  // 110 = manual stock adjustment (pick an unused code)
                    'qnt'        => $validated['change'],
                    'userId'     => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create the adjustment record
                $adjustment = StockAdjustment::create([
                    'productId'       => $validated['product_id'],
                    'adjustment_type' => $validated['adjustment_type'],
                    'old_quantity'    => $validated['old_quantity'],
                    'new_quantity'    => $newQty,
                    'change'          => $validated['change'],
                    'comment'         => $validated['comment'],
                    'userId'          => Auth::id(),
                ]);

                // Backfill docId on the stocks_trans row now that we have the adjustment id
                DB::table('stocks_trans')
                    ->where('id', $stocksTransId)
                    ->update(['docId' => $adjustment->id]);

                return $adjustment;
            });

            $productName = DB::table('chemical_products')->where('id', $result->productId)->value('name');

            return response()->json([
                'id'              => $result->id,
                'created_at'      => $result->created_at,
                'product_name'    => $productName,
                'adjustment_type' => $result->adjustment_type,
                'old_quantity'    => $result->old_quantity,
                'new_quantity'    => $result->new_quantity,
                'change'          => $result->change,
                'comment'         => $result->comment,
                'adjusted_by'     => Auth::user()->name ?? 'Current User',
            ]);

        } catch (\Throwable $e) {
            Log::error('Stock adjustment failed', [
                'message' => $e->getMessage(),
                'payload' => $validated,
            ]);

            return response()->json(['message' => 'Failed to save adjustment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/stock_adjustments/{id}/
     * Deletes the adjustment record AND reverses its effect on stock.
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {

                $adjustment = StockAdjustment::findOrFail($id);

                $stock = DB::table('stocks')
                    ->where('productId', $adjustment->productId)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    // Reverse the change: subtract what was added, add back what was subtracted
                    $revertedQty = $stock->qnt - $adjustment->change;

                    DB::table('stocks')
                        ->where('id', $stock->id)
                        ->update([
                            'prvqnt'     => $stock->qnt,
                            'qnt'        => $revertedQty,
                            'updated_at' => now(),
                        ]);

                    DB::table('stocks_trans')->insert([
                        'stockId'    => $stock->id,
                        'docId'      => $adjustment->id,
                        'docType'    => 215, // 111 = reversal of manual adjustment
                        'qnt'        => -$adjustment->change,
                        'userId'     => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $adjustment->delete();
            });

            return response()->json(['message' => 'Adjustment deleted and stock reverted']);

        } catch (\Throwable $e) {
            Log::error('Stock adjustment delete failed', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Failed to delete adjustment: ' . $e->getMessage()], 500);
        }
    }
}