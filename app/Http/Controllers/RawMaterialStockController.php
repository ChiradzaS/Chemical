<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RawMaterialStockController extends Controller
{
    /* ───────────────────────────────────────────────────────────────────
       TABLE / COLUMN MAP — edit here if a name differs. The report page
       names any table or column it can't find.
       ─────────────────────────────────────────────────────────────────── */
    private const MAT          = 'raw_materials';
    private const MAT_CODE     = 'code';
    private const MAT_NAME     = 'name';
    private const MAT_TYPE     = 'material_type';
    private const MAT_UOM      = 'uom';
    private const MAT_COST     = 'cost_per_kg';
    private const MAT_STOCK    = 'stock_on_hand';
    private const MAT_REORDER  = 'reorder_level';
    private const MAT_ACTIVE   = 'is_active';
    private const MAT_UNLIMITED = 'allow_negative';     // 1 = never runs out (e.g. water)

    // Materials with these names are always unlimited, even if allow_negative isn't set
    private const UNLIMITED_NAMES = ['water'];

    // Used as the Low level for materials with no reorder level set (0)
    private const DEFAULT_LOW_QTY = 10;

    /* ─────────────────────────────── Pages ─────────────────────────────── */

    public function page()
    {
        return view('reports.rawmaterialstock');
    }

    public function data()
    {
        try {
            if ($err = $this->configError()) {
                return response()->json(['status' => 'error', 'message' => $err]);
            }

            $materials = DB::table(self::MAT)
                ->where(self::MAT_ACTIVE, 1)
                ->orderBy(self::MAT_NAME)
                ->get([
                    'id',
                    self::MAT_CODE    . ' as code',
                    self::MAT_NAME    . ' as name',
                    self::MAT_TYPE    . ' as type',
                    self::MAT_UOM     . ' as uom',
                    self::MAT_COST    . ' as cost',
                    self::MAT_STOCK   . ' as stock',
                    self::MAT_REORDER . ' as reorder',
                    self::MAT_UNLIMITED . ' as unlimited',
                ]);

            $rows = $materials->map(function ($m) {
                $onHand  = (float) $m->stock;
                $cost    = (float) ($m->cost ?? 0);
                $reorder = (float) $m->reorder;
                $lowAt   = $reorder > 0 ? $reorder : self::DEFAULT_LOW_QTY;
                $isUnlimited = (int) $m->unlimited === 1
                    || in_array(mb_strtolower(trim((string) $m->name)), self::UNLIMITED_NAMES, true);

                return [
                    'id'        => $m->id,
                    'code'      => $m->code,
                    'name'      => $m->name,
                    'type'      => $m->type,
                    'uom'       => $m->uom ?: 'kg',
                    'onHand'    => round($onHand, 2),
                    'reorder'   => round($reorder, 2),
                    'lowAt'     => round($lowAt, 2),
                    'valueCost' => round(max(0, $onHand) * $cost, 2),
                    'noCost'    => $cost <= 0,
                    'status'    => $isUnlimited ? 'unlimited'
                                 : ($onHand <= 0 ? 'out' : ($onHand <= $lowAt ? 'low' : 'in')),
                ];
            })->all();

            return response()->json([
                'status'        => 'ok',
                'defaultLowQty' => self::DEFAULT_LOW_QTY,
                'rows'          => $rows,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    private function configError(): ?string
    {
        if (!Schema::hasTable(self::MAT)) {
            return "Table '" . self::MAT . "' was not found. Set the right name at the top of RawMaterialStockController.php.";
        }
        foreach (['id', self::MAT_CODE, self::MAT_NAME, self::MAT_TYPE, self::MAT_UOM, self::MAT_COST,
                  self::MAT_STOCK, self::MAT_REORDER, self::MAT_ACTIVE, self::MAT_UNLIMITED] as $col) {
            if (!Schema::hasColumn(self::MAT, $col)) {
                return "Column '" . self::MAT . ".$col' was not found. Set the right name at the top of RawMaterialStockController.php.";
            }
        }

        return null;
    }
}