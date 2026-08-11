<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RawMaterialController extends Controller
{
    /* ------------------------------------------------------------------
     | Page — blade shell that injects the window globals
     |------------------------------------------------------------------*/

    public function index()
    {
        $unittypes = DB::table('types')
                ->where('groupType', 'ChemicalUnitType')
                ->orderBy('id')
                ->get();

        $materialtypes = DB::table('types')
                ->where('groupType', 'ChemicalMaterialType')
                ->orderBy('id')
                ->get();

        return view('chemical.rawmaterial', compact('unittypes', 'materialtypes'));
    }

    /* ------------------------------------------------------------------
     | List — /raw-materials/list?data={"search":"...","include_inactive":0}
     |------------------------------------------------------------------*/

    public function list(Request $request)
    {
        $payload = json_decode($request->query('data', '{}'), true) ?: [];
        $term    = trim($payload['search'] ?? '');

        $rows = DB::table('raw_materials')
            ->when(empty($payload['include_inactive']), function ($q) {
                $q->where('is_active', 1);
            })
            ->when($term !== '', function ($q) use ($term) {
                // escape LIKE wildcards so a search containing % or _ stays literal
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';

                $q->where(function ($w) use ($like) {
                    $w->where('code', 'like', $like)
                      ->orWhere('name', 'like', $like)
                      ->orWhere('material_type', 'like', $like);
                });
            })
            ->orderBy('name')
            ->limit(200)
            ->get();

        return response()->json($rows);
    }

    /* ------------------------------------------------------------------
     | Save — create when id is null, update otherwise
     |------------------------------------------------------------------*/

    public function save(Request $request)
    {
        $payload = json_decode($request->query('data', '{}'), true) ?: [];

        // an untouched cost box arrives as '' or null — both mean "not priced",
        // and '' would fail the numeric rule below
        if (array_key_exists('cost_per_kg', $payload) && $payload['cost_per_kg'] === '') {
            $payload['cost_per_kg'] = null;
        }

        $validator = Validator::make($payload, [
            'code'           => 'required|string|max:50',
            'name'           => 'required|string|max:150',
            'material_type'  => 'nullable|string|max:50',
            'uom'            => 'required|string|max:10',
            'cost_per_kg'    => 'nullable|numeric|min:0|max:99999999',
            'stock_on_hand'  => 'nullable|numeric|min:0',
            'reorder_level'  => 'nullable|numeric|min:0',
            'allow_negative' => 'nullable|boolean',
            'is_active'      => 'nullable|boolean',
            'notes'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ]);
        }

        $id   = $payload['id'] ?? null;
        $code = trim($payload['code']);

        $clash = DB::table('raw_materials')
            ->whereRaw('LOWER(code) = ?', [strtolower($code)])
            ->when($id, function ($q) use ($id) {
                $q->where('id', '!=', $id);
            })
            ->first();

        if ($clash) {
            return response()->json([
                'status'  => 'error',
                'message' => "Code already used by {$clash->name}",
            ]);
        }

        try {
            $fields = [
                'code'           => $code,
                'name'           => trim($payload['name']),
                'material_type'  => trim($payload['material_type'] ?? '') ?: null,
                'uom'            => $payload['uom'],
                // null means unpriced — distinct from a real cost of 0
                'cost_per_kg'    => isset($payload['cost_per_kg'])
                                        ? (float) $payload['cost_per_kg']
                                        : null,
                'reorder_level'  => $payload['reorder_level'] ?? 0,
                'allow_negative' => !empty($payload['allow_negative']) ? 1 : 0,
                'is_active'      => isset($payload['is_active'])
                                        ? (!empty($payload['is_active']) ? 1 : 0)
                                        : 1,
                'notes'          => $payload['notes'] ?? null,
                'updated_at'     => now(),
            ];

            $newId = DB::transaction(function () use ($fields, $payload, $id) {
                if ($id) {
                    // stock_on_hand is owned by the stocks ledger after creation —
                    // changes go through stock adjustment, never through this form
                    DB::table('raw_materials')->where('id', $id)->update($fields);
                    return $id;
                }

                $fields['stock_on_hand'] = $payload['stock_on_hand'] ?? 0;
                $fields['created_at']    = now();

                return DB::table('raw_materials')->insertGetId($fields);

                // TODO: if the opening balance is > 0, post a docType 111 movement
                // to stocks_trans so the ledger agrees with stock_on_hand.
            });

            $material = DB::table('raw_materials')->where('id', $newId)->first();

            return response()->json($material);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not save: ' . $e->getMessage(),
            ]);
        }
    }

    /* ------------------------------------------------------------------
     | Toggle — deactivate instead of delete; formulas may reference this row
     |------------------------------------------------------------------*/

    public function toggle(Request $request)
    {
        $payload = json_decode($request->query('data', '{}'), true) ?: [];
        $id      = $payload['id'] ?? null;

        $material = DB::table('raw_materials')->where('id', $id)->first();

        if (!$material) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Material not found',
            ]);
        }

        DB::table('raw_materials')->where('id', $id)->update([
            'is_active'  => $material->is_active ? 0 : 1,
            'updated_at' => now(),
        ]);

        return response()->json(
            DB::table('raw_materials')->where('id', $id)->first()
        );
    }

    /* ------------------------------------------------------------------
     | Lookup — active materials for the formula page dropdown
     |------------------------------------------------------------------*/

    public function lookup(Request $request)
    {
        $payload = json_decode($request->query('data', '{}'), true) ?: [];

        $rows = DB::table('raw_materials')
            ->where('is_active', 1)
            ->when($payload['material_type'] ?? null, function ($q, $type) {
                $q->where('material_type', $type);
            })
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'material_type', 'uom', 'cost_per_kg', 'stock_on_hand']);

        return response()->json($rows);
    }
}