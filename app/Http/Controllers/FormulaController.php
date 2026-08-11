<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FormulaController extends Controller
{
    /* ------------------------------------------------------------------
     | Page
     |------------------------------------------------------------------*/

    public function index()
    {
        $chemicalTypes = DB::table('types')
                ->where('groupType', 'ChemicalType')
                ->orderBy('name')
                ->get();

        $unittypes = DB::table('types')
                ->where('groupType', 'ChemicalUnitType')
                ->orderBy('id')
                ->get();

        return view('formulas', compact('chemicalTypes', 'unittypes'));
    }

    /* ------------------------------------------------------------------
     | List — feeds the Name dropdown
     |------------------------------------------------------------------*/

    public function list()
    {
        $rows = DB::table('formulas')
            ->orderBy('name')
            ->get([
                'id', 'code', 'name', 'chemical_type',
                'base_batch_qty', 'density_kg_per_l', 'status',
            ]);

        return response()->json($rows);
    }

    /* ------------------------------------------------------------------
     | Show — header + ingredient lines + linked products
     | /formulas/show?data={"id":4}
     |------------------------------------------------------------------*/

    public function show(Request $request)
    {
        $payload = json_decode($request->query('data', '{}'), true) ?: [];
        $id      = $payload['id'] ?? null;

        $formula = DB::table('formulas')->where('id', $id)->first();

        if (!$formula) {
            return response()->json(['status' => 'error', 'message' => 'Formula not found']);
        }

        $items = DB::table('formula_items as fi')
            ->leftJoin('raw_materials as rm', 'rm.id', '=', 'fi.raw_material_id')
            ->where('fi.formula_id', $id)
            ->orderBy('fi.sequence')
            ->get([
                'fi.id', 'fi.raw_material_id', 'fi.material_type', 'fi.percentage',
                'fi.quantity', 'fi.uom', 'fi.entry_mode', 'fi.is_balance',
                'fi.sequence', 'fi.instruction',
                'rm.name as material_name', 'rm.code as material_code',
                'rm.stock_on_hand',
            ]);

        return response()->json([
            'formula'  => $formula,
            'items'    => $items,
            'products' => $this->productsFor($id),
        ]);
    }

    /* ------------------------------------------------------------------
     | Products using a formula — called when the Name dropdown changes
     | /formulas/products?data={"formula_id":4}
     |------------------------------------------------------------------*/

    public function products(Request $request)
    {
        $payload = json_decode($request->query('data', '{}'), true) ?: [];

        return response()->json($this->productsFor($payload['formula_id'] ?? null));
    }

    private function productsFor($formulaId)
    {
        if (!$formulaId) {
            return [];
        }

        return DB::table('chemical_products')
            ->where('formula_id', $formulaId)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'container_size_id']);
    }

    /* ------------------------------------------------------------------
     | Save — header and every line in one transaction
     |------------------------------------------------------------------*/

    public function save(Request $request)
    {
        $payload = json_decode($request->query('data', '{}'), true) ?: [];

        // an omitted density falls back to water rather than failing validation
        if (!isset($payload['density_kg_per_l']) || $payload['density_kg_per_l'] === '') {
            $payload['density_kg_per_l'] = 1;
        }

        $validator = Validator::make($payload, [
            'code'             => 'required|string|max:50',
            'name'             => 'required|string|max:150',
            'chemical_type'    => 'required|string|max:50',
            'base_batch_qty'   => 'required|numeric|min:0.001',
            // 0 would divide by zero everywhere downstream; 99 is well past
            // any aqueous product and catches a misplaced decimal
            'density_kg_per_l' => 'required|numeric|min:0.0001|max:99',
            'status'           => 'nullable|in:draft,active,archived',
            'items'            => 'required|array|min:1',
            'items.*.raw_material_id' => 'required|integer',
            'items.*.percentage'      => 'required|numeric|min:0|max:100',
            'items.*.quantity'        => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ]);
        }

        $id      = $payload['id'] ?? null;
        $items   = $payload['items'];
        $base    = (float) $payload['base_batch_qty'];
        $density = (float) $payload['density_kg_per_l'];

        // one material may only appear once in a formula
        $ids = array_column($items, 'raw_material_id');
        if (count($ids) !== count(array_unique($ids))) {
            return response()->json([
                'status'  => 'error',
                'message' => 'The same material appears more than once',
            ]);
        }

        // percentages must total 100 (a balance line absorbs the remainder)
        $hasBalance = collect($items)->contains(fn ($i) => !empty($i['is_balance']));
        $sum = collect($items)
            ->reject(fn ($i) => !empty($i['is_balance']))
            ->sum(fn ($i) => (float) $i['percentage']);

        if (!$hasBalance && abs($sum - 100) > 0.01) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ingredients total ' . round($sum, 4) . '%, must be 100%',
            ]);
        }

        if ($hasBalance && $sum > 100.0001) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ingredients exceed 100%',
            ]);
        }

        // code must be unique
        $clash = DB::table('formulas')
            ->whereRaw('LOWER(code) = ?', [strtolower(trim($payload['code']))])
            ->when($id, fn ($q) => $q->where('id', '!=', $id))
            ->first();

        if ($clash) {
            return response()->json([
                'status'  => 'error',
                'message' => "Code already used by {$clash->name}",
            ]);
        }

        try {
            $formulaId = DB::transaction(function () use ($payload, $id, $items, $base, $density, $hasBalance, $sum) {

                $header = [
                    'code'             => trim($payload['code']),
                    'name'             => trim($payload['name']),
                    'chemical_type'    => $payload['chemical_type'] ?? null,
                    'base_batch_qty'   => $base,
                    'density_kg_per_l' => $density,
                    'status'           => $payload['status'] ?? 'draft',
                    'notes'            => $payload['notes'] ?? null,
                    'updated_at'       => now(),
                ];

                if ($id) {
                    DB::table('formulas')->where('id', $id)->update($header);
                } else {
                    $header['created_at'] = now();
                    $id = DB::table('formulas')->insertGetId($header);
                }

                // replace the lines wholesale — simpler and safer than diffing
                DB::table('formula_items')->where('formula_id', $id)->delete();

                $rows = [];
                $seq  = 1;

                foreach ($items as $item) {
                    $isBalance = !empty($item['is_balance']);

                    // server recomputes both figures — never trust browser floats
                    $pct = $isBalance
                        ? round(max(0, 100 - $sum), 4)
                        : round((float) $item['percentage'], 4);

                    $rows[] = [
                        'formula_id'      => $id,
                        'raw_material_id' => $item['raw_material_id'],
                        'material_type'   => $item['material_type'] ?? null,
                        'percentage'      => $pct,
                        'quantity'        => round($base * $pct / 100, 4),
                        'uom'             => $item['uom'] ?? 'kg',
                        'entry_mode'      => ($item['entry_mode'] ?? 'percent') === 'quantity'
                                                ? 'quantity' : 'percent',
                        'is_balance'      => $isBalance ? 1 : 0,
                        'sequence'        => $seq++,
                        'instruction'     => $item['instruction'] ?? null,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }

                DB::table('formula_items')->insert($rows);

                return $id;
            });

            return response()->json([
                'status'  => 'ok',
                'id'      => $formulaId,
                'formula' => DB::table('formulas')->where('id', $formulaId)->first(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not save: ' . $e->getMessage(),
            ]);
        }
    }

    /* ------------------------------------------------------------------
     | Materials for the ingredient dropdown
     |------------------------------------------------------------------*/

    public function materials()
    {
        $rows = DB::table('raw_materials')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get([
                'id', 'code', 'name', 'material_type', 'uom',
                'cost_per_kg', 'stock_on_hand',
            ]);

        return response()->json($rows);
    }
}