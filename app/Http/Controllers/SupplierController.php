<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Suppliers and the prices they quote per raw material.
 *
 *   /suppliers/list?data={"search":"","include_inactive":1}
 *   /suppliers/save?data={...}
 *   /suppliers/toggle?data={"id":4}
 */
class SupplierController extends Controller
{
    private function payload(Request $request): array
    {
        $data = json_decode($request->query('data', '{}'), true);

        return is_array($data) ? $data : [];
    }

    private function fail(string $message)
    {
        return response()->json(['status' => 'error', 'message' => $message]);
    }

    /** Suppliers, each with a nested materials array the pages read directly. */
    public function list(Request $request)
    {
        $data     = $this->payload($request);
        $search   = trim($data['search'] ?? '');
        $inactive = (int) ($data['include_inactive'] ?? 0) === 1;

        $query = Supplier::query();

        if (! $inactive) {
            $query->active();
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name')->get();

        // pivot rows joined to the material, keyed by supplier
        $prices = DB::table('raw_material_supplier as p')
            ->join('raw_materials as m', 'm.id', '=', 'p.raw_material_id')
            ->select(
                'p.supplier_id',
                'p.raw_material_id',
                'p.supplier_part_code',
                'p.cost_per_kg',
                'p.is_preferred',
                'p.last_quoted_at',
                'm.code as material_code',
                'm.name as material_name',
                'm.uom'
            )
            ->whereIn('p.supplier_id', $suppliers->pluck('id'))
            ->orderBy('m.code')
            ->get()
            ->groupBy('supplier_id');

        $suppliers->each(function ($s) use ($prices) {
            $s->materials = $prices->get($s->id, collect())->values();
        });

        return response()->json($suppliers);
    }

    /**
     * Create or update a supplier.
     *
     * The `materials` key is optional on purpose: the quick-add popup on the
     * receiving page omits it, and an absent key must leave the price list
     * alone rather than wipe it.
     */
    public function save(Request $request)
    {
        $data = $this->payload($request);

        $id   = ! empty($data['id']) ? (int) $data['id'] : null;
        $code = strtoupper(trim($data['code'] ?? ''));
        $name = trim($data['name'] ?? '');

        if ($code === '') {
            return $this->fail('Enter a supplier code.');
        }

        if ($name === '') {
            return $this->fail('Enter a supplier name.');
        }

        $clash = Supplier::where('code', $code)
            ->when($id, fn ($q) => $q->where('id', '!=', $id))
            ->exists();

        if ($clash) {
            return $this->fail("Code {$code} is already used by another supplier.");
        }

        $email = trim($data['email'] ?? '');
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('That email address does not look right.');
        }

        // validate the price rows before opening a transaction
        $rows = null;
        if (array_key_exists('materials', $data) && is_array($data['materials'])) {
            $rows = [];
            $seen = [];

            foreach ($data['materials'] as $row) {
                $materialId = (int) ($row['raw_material_id'] ?? 0);
                $cost       = (float) ($row['cost_per_kg'] ?? 0);

                if ($materialId <= 0) {
                    return $this->fail('One of the material rows is empty.');
                }

                if (isset($seen[$materialId])) {
                    return $this->fail('The same material appears on two rows.');
                }
                $seen[$materialId] = true;

                if ($cost <= 0) {
                    return $this->fail('Every material row needs a price above 0.');
                }

                $rows[] = [
                    'raw_material_id'    => $materialId,
                    'supplier_part_code' => trim($row['supplier_part_code'] ?? '') ?: null,
                    'cost_per_kg'        => round($cost, 4),
                    'is_preferred'       => (int) ($row['is_preferred'] ?? 0) === 1 ? 1 : 0,
                ];
            }
        }

        try {
            $supplier = DB::transaction(function () use ($id, $code, $name, $email, $data, $rows) {
                $supplier = $id ? Supplier::find($id) : new Supplier();

                if (! $supplier) {
                    throw new \RuntimeException('missing');
                }

                $supplier->fill([
                    'code'           => $code,
                    'name'           => $name,
                    'contact_person' => trim($data['contact_person'] ?? '') ?: null,
                    'phone'          => trim($data['phone'] ?? '') ?: null,
                    'email'          => $email ?: null,
                    'address'        => trim($data['address'] ?? '') ?: null,
                    'notes'          => trim($data['notes'] ?? '') ?: null,
                    'is_active'      => array_key_exists('is_active', $data)
                        ? ((int) $data['is_active'] === 1 ? 1 : 0)
                        : 1,
                ])->save();

                if ($rows !== null) {
                    // replace the whole price list for this supplier
                    DB::table('raw_material_supplier')->where('supplier_id', $supplier->id)->delete();

                    foreach ($rows as $row) {
                        DB::table('raw_material_supplier')->insert(array_merge($row, [
                            'supplier_id' => $supplier->id,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]));

                        // one preferred source per material — starring here unstars the rest
                        if ($row['is_preferred'] === 1) {
                            DB::table('raw_material_supplier')
                                ->where('raw_material_id', $row['raw_material_id'])
                                ->where('supplier_id', '!=', $supplier->id)
                                ->update(['is_preferred' => 0, 'updated_at' => now()]);
                        }
                    }
                }

                return $supplier;
            });
        } catch (Throwable $e) {
            report($e);

            return $this->fail('Could not save that supplier.');
        }

        return response()->json([
            'id'        => $supplier->id,
            'code'      => $supplier->code,
            'name'      => $supplier->name,
            'is_active' => $supplier->is_active ? 1 : 0,
        ]);
    }

    /** Deactivate rather than delete, so old deliveries keep their supplier. */
    public function toggle(Request $request)
    {
        $data     = $this->payload($request);
        $supplier = Supplier::find((int) ($data['id'] ?? 0));

        if (! $supplier) {
            return $this->fail('That supplier no longer exists.');
        }

        $supplier->is_active = ! $supplier->is_active;
        $supplier->save();

        return response()->json([
            'id'        => $supplier->id,
            'name'      => $supplier->name,
            'is_active' => $supplier->is_active ? 1 : 0,
        ]);
    }
}