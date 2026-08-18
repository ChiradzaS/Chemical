<?php

namespace App\Http\Controllers;

use App\Models\ChemicalProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
USE DB;

class ChemicalProductController extends Controller
{

public function index(Request $request): JsonResponse
{
    $products = ChemicalProduct::orderBy('updated_at', 'desc')->get();
    return response()->json($products);
}



public function destroy(Request $request): JsonResponse
{
    $data    = json_decode(urldecode($request->query('data')), true);
    $product = ChemicalProduct::findOrFail($data['id']);
    $product->delete();
    return response()->json(['message' => 'deleted']);
}



    // GET /chemicalproducts/show?data={id}
    public function show(Request $request): JsonResponse
    {

        //Log::info('almost saving chgemical product');
        $data    = json_decode(urldecode($request->query('data')), true);
        $product = ChemicalProduct::findOrFail($data['id']);

        return response()->json($product);
    }

// GET /chemicalproducts/store?data={...}
public function store(Request $request): JsonResponse
{
    $data = json_decode(urldecode($request->query('data')), true);

    if (ChemicalProduct::where('name', $data['name'])->exists()) {
        return response()->json(['message' => 'Product name already exists'], 409);
    }

    $data['created_by'] = auth()->id();

    $product = ChemicalProduct::create($data);

    DB::statement('CALL sp_register_chemical_stock(?, ?)', [
        $product->id,
        auth()->id(),
    ]);

    return response()->json(['message' => 'saved', 'id' => $product->id], 200);
}


// GET /chemicalproducts/update?data={...}
public function update(Request $request): JsonResponse
{
    $data    = json_decode(urldecode($request->query('data')), true);
    $id      = $data['id'];
    $product = ChemicalProduct::findOrFail($id);

    $validated = validator($data, [
        'name'                   => 'required|string|max:255',
        'sku'                    => 'nullable|string|max:100',
        'category'               => 'nullable|string|max:100',
        'brand'                  => 'nullable|string|max:100',
        'barcode'                => 'nullable|string|max:100',
        'description'            => 'nullable|string',
        'invoice_description'    => 'nullable|string|max:500',

        'stock_on_hand'          => 'nullable|numeric|min:0',
        'stock_unit_id'          => 'nullable|integer',

        'formula_code'           => 'nullable|string|max:100',
        'ph_level'               => 'nullable|numeric|min:0|max:14',
        'viscosity_id'           => 'nullable|integer',
        'active_ingredient_id'   => 'nullable|integer',
        'fragrance_id'           => 'nullable|integer',
        'colour_id'              => 'nullable|integer',
        'concentration'          => 'nullable|numeric|min:0|max:100',
        'dilution_ratio'         => 'nullable|string|max:50',

        'bag_type_id'            => 'nullable|integer',
        'container_size_id'      => 'nullable|integer',
        'material_type_id'       => 'nullable|integer',
        'cap_type_id'            => 'nullable|integer',
        'label_type_id'          => 'nullable|integer',
        'units_per_carton'       => 'nullable|integer|min:1',
        'carton_weight_kg'       => 'nullable|numeric|min:0',

        'batch_size_litres'      => 'nullable|numeric|min:0',
        'units_per_batch'        => 'nullable|integer|min:1',
        'mixing_time_minutes'    => 'nullable|integer|min:0',
        'filling_speed_per_hour' => 'nullable|integer|min:0',
        'yield_percentage'       => 'nullable|numeric|min:0|max:100',
        'shelf_life_months'      => 'nullable|integer|min:0',
        'weight_source'          => 'nullable|in:formula,manual',
        'weight_per_unit_grams'  => 'nullable|numeric|min:0',

        'raw_material_cost'      => 'nullable|numeric|min:0',
        'packaging_cost'         => 'nullable|numeric|min:0',
        'labour_cost_per_unit'   => 'nullable|numeric|min:0',
        'overhead_cost'          => 'nullable|numeric|min:0',
        'markup_percentage'      => 'nullable|numeric|min:0',
        'price'                  => 'nullable|numeric|min:0',
        'vat_applicable'         => 'nullable|boolean',
        'vat_rate'               => 'nullable|numeric|min:0',
    ])->validate();

    $validated['updated_by'] = auth()->id();

    // stock_on_hand is owned by the stocks table — never write it to chemical_products
    unset($validated['id']);
    unset($validated['stock_on_hand']);

    $product->update($validated);

    // ── Fetch current stock balance from stocks table ─────────────────────
    $stock = DB::table('stocks')
        ->where('productId', $product->id)
        ->first();

    return response()->json([
        'product'   => $product,
        'stock_qnt' => $stock->qnt ?? 00,
        'stock_id'  => $stock->id  ?? null,
    ]);
}


// GET /chemicalproducts/updatebatch?data={"id":12,"sku":"BATCH-0091"}
public function updateBatch(Request $request): JsonResponse
{
    $data = json_decode(urldecode($request->query('data')), true);

    if (empty($data['id'])) {
        return response()->json(['message' => 'No product supplied'], 422);
    }

    $id = $data['id'];

    $validated = validator($data, [
        'id'  => 'required|integer|exists:chemical_products,id',
        'sku' => [
            'required', 'string', 'max:100',
            Rule::unique('chemical_products', 'sku')->ignore($id),
        ],
    ], [
        'sku.unique'   => 'That batch code is already used by another product',
        'sku.required' => 'Batch code cannot be blank',
    ])->validate();

    $product             = ChemicalProduct::findOrFail($id);
    $product->sku        = $validated['sku'];
    $product->updated_by = auth()->id();
    $product->save();

    return response()->json([
        'message' => 'batch updated',
        'id'      => $product->id,
        'sku'     => $product->sku,
    ], 200);
}


}