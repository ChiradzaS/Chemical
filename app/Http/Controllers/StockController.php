<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\Porduct;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use DB;
use Auth;

class StockController extends Controller
{


public function index(Request $request)
{
    // ── Ensure every chemical product has a stock row ─────────────────────
    $chemProducts = DB::table('chemical_products')->get();

    foreach ($chemProducts as $product) {
        if (!Stock::where('productId', $product->id)->first()) {
            $stock = new Stock;
            $stock->productId   = $product->id;
            $stock->productType = null;
            $stock->userId      = Auth::id();
            $stock->qnt         = 0;
            $stock->prvqnt      = 0;
            $stock->lostTransId = 0;
            $stock->save();
        }
    }

    // ── Sync stock_on_hand back into chemical_products ────────────────────
    DB::table('stocks')
        ->join('chemical_products', 'chemical_products.id', '=', 'stocks.productId')
        ->select('stocks.productId', 'stocks.qnt')
        ->get()
        ->each(function ($row) {
            DB::table('chemical_products')
                ->where('id', $row->productId)
                ->update(['stock_on_hand' => $row->qnt]);
        });

    // ── Optional filtered query ───────────────────────────────────────────
    $action = $request->get('action');

    if ($action !== null && trim($action) === 'query') {
        $productId   = $request->get('productId');
        $productComp = ($productId > 0) ? '=' : '<>';

        $data['stocks'] = Stock::where('productId', $productComp, $productId)
            ->orderBy('updated_at', 'desc')
            ->paginate(500);

        return view('stocks.index', $data);
    }

    // ── Full stock list ───────────────────────────────────────────────────
    $data['stocks'] = DB::table('stocks')
        ->leftJoin('chemical_products', 'chemical_products.id', '=', 'stocks.productId')
        ->select(
            'stocks.id',
            'stocks.productId',
            'stocks.qnt',
            'stocks.prvqnt',
            'stocks.updated_at',
            'chemical_products.name as product_name',
            'chemical_products.sku  as product_sku'
        )
        ->orderBy('stocks.updated_at', 'desc')
        ->orderBy('stocks.qnt', 'desc')
        ->paginate(500);

    return view('stocks.index', $data);
}

    public function create(Request $request)
    {
        $stockId = $request->get('stockId');

        $stockDetails = DB::table('stocks_trans')
            ->where('stockId', $stockId)
            ->orderBy('id', 'desc')
            ->get();

        return view('stocks.create', ['stockDetails' => $stockDetails]);
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        $data['stocks'] = Stock::orderBy('id', 'desc')->paginate(200);
        return view('stocks.show', $data);
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}