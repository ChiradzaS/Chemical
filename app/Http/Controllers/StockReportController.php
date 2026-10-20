<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StockReportController extends Controller
{
    /* ───────────────────────────────────────────────────────────────────
       TABLE / COLUMN MAP — edit here if a name differs. The report page
       names any table or column it can't find.
       ─────────────────────────────────────────────────────────────────── */
    private const PROD           = 'chemical_products';
    private const PROD_NAME      = 'name';
    private const PROD_SKU       = 'sku';                 // optional
    private const PROD_STOCK     = 'stock_on_hand';
    private const PROD_PRICE     = 'price_incl_vat';          // selling price incl. VAT
    private const PROD_COST_COLS = ['raw_material_cost', 'packaging_cost', 'labour_cost_per_unit', 'overhead_cost'];

    // A product with this many units or fewer (but more than 0) shows as Low
    private const LOW_STOCK_QTY  = 10;

    /* ─────────────────────────────── Pages ─────────────────────────────── */

    public function page()
    {
        return view('reports.stockreport');
    }

    public function data()
    {
        try {
            if ($err = $this->configError()) {
                return response()->json(['status' => 'error', 'message' => $err]);
            }

            $sku = Schema::hasColumn(self::PROD, self::PROD_SKU) ? 'p.`' . self::PROD_SKU . '`' : 'NULL';

            $products = DB::table(self::PROD . ' as p')->selectRaw("
                p.`id`                                    AS id,
                p.`" . self::PROD_NAME . "`               AS name,
                $sku                                      AS sku,
                COALESCE(p.`" . self::PROD_STOCK . "`, 0) AS stock,
                COALESCE(p.`" . self::PROD_PRICE . "`, 0) AS price,
                {$this->costExpr()}                       AS unit_cost
            ")->orderBy('p.' . self::PROD_NAME)->get();

            $rows = $products->map(function ($p) {
                $onHand   = (float) $p->stock;
                $unitCost = (float) $p->unit_cost;

                return [
                    'id'        => $p->id,
                    'name'      => $p->name,
                    'sku'       => $p->sku,
                    'onHand'    => round($onHand, 2),
                    'valueCost' => round(max(0, $onHand) * $unitCost, 2),
                    'valueSell' => round(max(0, $onHand) * (float) $p->price, 2),
                    'noCost'    => $unitCost <= 0,
                    'status'    => $onHand <= 0 ? 'out' : ($onHand <= self::LOW_STOCK_QTY ? 'low' : 'in'),
                ];
            })->all();

            return response()->json([
                'status' => 'ok',
                'lowQty' => self::LOW_STOCK_QTY,
                'rows'   => $rows,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /* ────────────────────────────── Helpers ────────────────────────────── */

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

    private function configError(): ?string
    {
        if (!Schema::hasTable(self::PROD)) {
            return "Table '" . self::PROD . "' was not found. Set the right name at the top of StockReportController.php.";
        }
        foreach (['id', self::PROD_NAME, self::PROD_STOCK, self::PROD_PRICE] as $col) {
            if (!Schema::hasColumn(self::PROD, $col)) {
                return "Column '" . self::PROD . ".$col' was not found. Set the right name at the top of StockReportController.php.";
            }
        }

        return null;
    }
}