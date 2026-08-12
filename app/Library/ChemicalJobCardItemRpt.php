<?php

require_once 'Barcode/php-barcode.php';
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\ChemicalJobCard;
use App\Models\ChemicalJobCardItem;
use App\Models\ChemicalProduct;

class ChemicalJobCardItemRpt extends Fpdf
{
    // ── Barcode renderer ──────────────────────────────────────────────────────
    static public function digit_to_fpdf_renderer($pdf, $color, $x, $y, $angle, $type, $datas, $width = null, $height = null)
    {
        $type = 'code39';
        list($digit, $hri) = Barcode::raw($type, $datas);
        $type = strtolower($type);
        if ($digit == '') return false;
        $width  = is_null($width)  ? 1  : $width;
        $height = is_null($height) ? 50 : $height;
        $digit  = Barcode::bitStringTo2DArray($digit);
        if (!is_array($color)) {
            $color = preg_match('`([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})`i', $color, $m)
                ? [hexdec($m[1]), hexdec($m[2]), hexdec($m[3])]
                : [0, 0, 0];
        }
        $color = array_values($color);
        $pdf->SetDrawColor($color[0], $color[1], $color[2]);
        $pdf->SetFillColor($color[0], $color[1], $color[2]);
        $fn = function($points) use ($pdf) {
            $op = 'f'; $h = $pdf->h; $k = $pdf->k; $s = '';
            for ($i = 0; $i < 8; $i += 2)
                $s .= sprintf('%.2F %.2F', $points[$i] * $k, ($h - $points[$i+1]) * $k) . ($i ? ' l ' : ' m ');
            $pdf->_out($s . $op);
        };
        $result = Barcode::digitToRenderer($fn, $x, $y, $angle, $width, $height, $digit);
        $result['hri'] = $hri;
        return $result;
    }

    function Header() {}

    // ── Section title — light grey to save ink ────────────────────────────────
    private function sectionTitle($text)
    {
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(200, 200, 200);
        $this->SetTextColor(30, 30, 30);
        $this->Cell(277, 6, $text, 0, 1, 'C', true);
        $this->SetTextColor(0, 0, 0);
    }

    // ── Label + value pair helper ─────────────────────────────────────────────
    private function lv($label, $value, $lw, $vw, $h = 5, $ln = false)
    {
        $this->SetFont('Arial', 'B', 7);
        $this->Cell($lw, $h, $label, 1, 0, 'L');
        $this->SetFont('Arial', '', 7);
        $this->Cell($vw, $h, $value, 1, 0, 'L');
        if ($ln) $this->Ln();
    }

    /* ── Materials for this job ───────────────────────────────────────────────
       Prefers the snapshot saved with the job card. A formula edited after the
       job was raised must not change what this printed card says was issued.
       Falls back to the live formula only when no snapshot exists (older cards,
       or before the snapshot table was added). */
    private static function materialsFor($jobCard, $product, $batchKg)
    {
        if (Schema::hasTable('chemical_job_card_materials')) {
            $rows = DB::table('chemical_job_card_materials as jm')
                ->leftJoin('raw_materials as rm', 'rm.id', '=', 'jm.raw_material_id')
                ->where('jm.jobCardId', $jobCard->id)
                ->orderBy('jm.id')
                ->get([
                    'rm.code as material_code',
                    'rm.name as material_name',
                    'jm.percentage',
                    'jm.required_kg',
                    'jm.uom',
                ]);

            if ($rows->count()) {
                return ['rows' => $rows, 'live' => false, 'formula' => null];
            }
        }

        // No snapshot — rebuild from the formula the product points at
        $code = $product->formula_code ?? null;
        if (!$code) return ['rows' => collect(), 'live' => true, 'formula' => null];

        $formula = DB::table('formulas')->whereRaw('LOWER(code) = ?', [strtolower($code)])->first();
        if (!$formula) return ['rows' => collect(), 'live' => true, 'formula' => null];

        $rows = DB::table('formula_items as fi')
            ->leftJoin('raw_materials as rm', 'rm.id', '=', 'fi.raw_material_id')
            ->where('fi.formula_id', $formula->id)
            ->orderBy('fi.sequence')
            ->get([
                'rm.code as material_code',
                'rm.name as material_name',
                'fi.percentage',
                'fi.uom',
                'fi.instruction',
            ])
            ->map(function ($r) use ($batchKg) {
                $r->required_kg = round($batchKg * (float) $r->percentage / 100, 3);
                return $r;
            });

        return ['rows' => $rows, 'live' => true, 'formula' => $formula];
    }

    // ── Main print ────────────────────────────────────────────────────────────
    public static function printJobCard(Request $request)
    {
        $jobCardId     = $request->get('jobCardId');
        $jobcarditemId = $request->get('jobcarditemId');
        $productId     = $request->get('productId');

        $jobCard = ChemicalJobCard::find($jobCardId);
        if (!$jobCard) abort(404, 'Job card not found');

        $items   = ChemicalJobCardItem::where('jobCardId', $jobCardId)->get();
        $product = ChemicalProduct::find($productId ?? $jobCard->productId);

        // Single lookup query keyed by id
        $types = DB::table('types')->get()->keyBy('id');

        // Resolve all names up front
        $customerName   = DB::table('customers')->where('id', $jobCard->customerId)->value('name') ?? '—';
        $colourName     = $types[$product->colour_id           ?? 0]->name ?? '—';
        $viscosityName  = $types[$product->viscosity_id        ?? 0]->name ?? '—';
        $ingredientName = $types[$product->active_ingredient_id ?? 0]->name ?? '—';
        $fragranceName  = $types[$product->fragrance_id        ?? 0]->name ?? '—';
        $bottleTypeName = $types[$product->bag_type_id         ?? 0]->name ?? '—';
        $containerName  = $types[$product->container_size_id   ?? 0]->name ?? '—';

        // Batch weight drives every issue quantity below
        $batchKg = (float) ($jobCard->totalWeight ?? 0);
        if ($batchKg <= 0) {
            $batchKg = (float) ($jobCard->quantity ?? 0) * (float) ($jobCard->weightPerUnit ?? 0);
        }

        $mat        = self::materialsFor($jobCard, $product, $batchKg);
        $materials  = $mat['rows'];
        $formulaRow = $mat['formula'];

        // Pre-load all product names for items in one query
        $itemProductIds = $items->pluck('productId')->filter()->unique()->toArray();
        $itemProducts   = ChemicalProduct::whereIn('id', $itemProductIds)->get()->keyBy('id');

        $pdf = new ChemicalJobCardItemRpt();
        $pdf->SetTitle('Chemical Job Card');
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage('L'); // 297mm wide, 10mm margins = 277mm usable
        $pdf->SetAutoPageBreak(true, 10); // materials can push past one page

        // ── Barcode ───────────────────────────────────────────────────────────
        $barcode = $jobCard->barcode ?? '0000000000';
        $pdf->digit_to_fpdf_renderer($pdf, '000000', 118, 5, 0, 'code128', ['code' => $barcode], 0.5, 10);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(10, 16);
        $pdf->Cell(277, 4, 'Barcode: ' . $barcode, 0, 1, 'C');
        $pdf->Ln(2);

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 1 — Job Card Header  (277mm per row)
        // ══════════════════════════════════════════════════════════════════════
        $pdf->sectionTitle('CHEMICAL JOB CARD');
        $pdf->Ln(1);

        // Row 1
        $pdf->lv('JC ID',        $jobCardId,                    15, 30);
        $pdf->lv('Customer',     $customerName,                 25, 55);
        $pdf->lv('Start Date',   $jobCard->startDate   ?? '—',  22, 38);
        $pdf->lv('Quantity',     $jobCard->quantity    ?? '—',  18, 22);
        $pdf->lv('Batch Wt(kg)', number_format($batchKg, 3),    22, 30, 5, true);

        // Row 2
        $pdf->lv('Product',     $product->name          ?? '—', 18, 80);
        $pdf->lv('Package',     $containerName,                 28, 38);
        $pdf->lv('Wt/Unit(kg)', $jobCard->weightPerUnit ?? '—', 28, 38);
        $pdf->lv('BATCH',       $product->sku           ?? '—', 18, 29, 5, true);

        $pdf->Ln(3);

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 2 — Formulation  (34+35)*4 = 276 +1 on last = 277
        // ══════════════════════════════════════════════════════════════════════
        $pdf->sectionTitle('FORMULATION');
        $pdf->Ln(1);

        // Row 1
        $pdf->lv('Colour',            $colourName,      34, 35);
        $pdf->lv('Viscosity',         $viscosityName,   34, 35);
        $pdf->lv('Active Ingredient', $ingredientName,  34, 35);
        $pdf->lv('Fragrance',         $fragranceName,   34, 36, 5, true);

        // Row 2
        $pdf->lv('Bottle Type',     $bottleTypeName,                           34, 35);
        $pdf->lv('Concentration %', (string)($product->concentration ?? '—'),  34, 35);
        $pdf->lv('Dilution Ratio',  $product->dilution_ratio         ?? '—',   34, 35);
        $pdf->lv('pH Level',        (string)($product->ph_level      ?? '—'),  34, 36, 5, true);

        $pdf->Ln(3);

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 3 — Materials to issue
        // 8+70+24+16+26+26+107 = 277
        // ══════════════════════════════════════════════════════════════════════
        $pdf->sectionTitle('MATERIALS TO ISSUE');
        $pdf->Ln(1);

        // Which formula these quantities came from, and what they were scaled to
        $pdf->SetFont('Arial', '', 7);
        $formulaLabel = $formulaRow
            ? ($formulaRow->name . '  (' . $formulaRow->code . ')')
            : ($product->formula_code ?? '—');
        $densityLabel = $formulaRow && isset($formulaRow->density_kg_per_l)
            ? number_format((float) $formulaRow->density_kg_per_l, 4) . ' kg/L'
            : '—';

        $pdf->lv('Formula',  $formulaLabel,                    20, 90);
        $pdf->lv('Density',  $densityLabel,                    20, 40);
        $pdf->lv('Batch',    number_format($batchKg, 3) . ' kg', 20, 40);
        $pdf->lv('Source',   $mat['live'] ? 'Live formula' : 'Saved with job', 20, 27, 5, true);
        $pdf->Ln(1);

        if ($materials->isEmpty()) {
            $pdf->SetFont('Arial', 'I', 7);
            $pdf->Cell(277, 6, 'No formula linked to this product - materials must be issued manually.', 1, 1, 'C');
        } else {
            // 8+66+22+15+26+26+26+88 = 277
            $mw = [8, 66, 22, 15, 26, 26, 26, 88];

            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetFillColor(220, 220, 220);
            $pdf->Cell($mw[0], 5, '#',          1, 0, 'C', true);
            $pdf->Cell($mw[1], 5, 'Material',   1, 0, 'C', true);
            $pdf->Cell($mw[2], 5, 'Code',       1, 0, 'C', true);
            $pdf->Cell($mw[3], 5, '%',          1, 0, 'C', true);
            // per-tonne is the figure operators know by heart, so it doubles as
            // a sanity check against the scaled batch column beside it
            $pdf->Cell($mw[4], 5, 'Per 1000kg', 1, 0, 'C', true);
            $pdf->Cell($mw[5], 5, 'Issue kg',   1, 0, 'C', true);
            $pdf->Cell($mw[6], 5, 'Actual kg',  1, 0, 'C', true);
            $pdf->Cell($mw[7], 5, 'Weighed by / Time', 1, 0, 'C', true);
            $pdf->Ln();

            $n = 1; $fill = false; $totalKg = 0; $totalPct = 0;
            foreach ($materials as $m) {
                $req       = (float) ($m->required_kg ?? 0);
                $totalKg  += $req;
                $totalPct += (float) $m->percentage;

                $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
                $pdf->SetFont('Arial', '', 7);
                $pdf->Cell($mw[0], 5, $n++,                                       1, 0, 'C', $fill);
                $pdf->Cell($mw[1], 5, $m->material_name ?? '—',                   1, 0, 'L', $fill);
                $pdf->Cell($mw[2], 5, $m->material_code ?? '—',                   1, 0, 'L', $fill);
                $pdf->Cell($mw[3], 5, number_format((float) $m->percentage, 2),   1, 0, 'R', $fill);
                // 1% of a tonne is 10 kg, so this is just the percentage x 10
                $pdf->Cell($mw[4], 5, number_format((float) $m->percentage * 10, 2), 1, 0, 'R', $fill);
                // the figure the operator weighs to — set bold so it reads at arm's length
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell($mw[5], 5, number_format($req, 3),                     1, 0, 'R', $fill);
                $pdf->SetFont('Arial', '', 7);
                $pdf->Cell($mw[6], 5, '',                                         1, 0, 'R', $fill); // filled in on the floor
                $pdf->Cell($mw[7], 5, '',                                         1, 0, 'L', $fill);
                $pdf->Ln();

                // Mixing instruction sits under its own line, where it is read
                if (!empty($m->instruction)) {
                    $pdf->SetFont('Arial', 'I', 6.5);
                    $pdf->Cell($mw[0], 4, '', 1, 0, 'C', $fill);
                    $pdf->Cell(269,    4, '  ' . $m->instruction, 1, 0, 'L', $fill);
                    $pdf->Ln();
                    $pdf->SetFont('Arial', '', 7);
                }

                $fill = !$fill;
            }

            // Total — a batch that does not add up to the batch weight is a red flag
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell($mw[0] + $mw[1] + $mw[2] + $mw[3], 5, 'TOTAL', 1, 0, 'R', true);
            $pdf->Cell($mw[4], 5, number_format($totalPct * 10, 2), 1, 0, 'R', true);
            $pdf->Cell($mw[5], 5, number_format($totalKg, 3),       1, 0, 'R', true);
            $pdf->Cell($mw[6], 5, '',                               1, 0, 'R', true);
            $pdf->Cell($mw[7], 5, '',                               1, 0, 'L', true);
            $pdf->Ln();
        }

        $pdf->Ln(3);

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 4 — Process Lines  8+50+80+30+50+59 = 277
        // ══════════════════════════════════════════════════════════════════════
        $pdf->sectionTitle('PROCESS LINES');
        $pdf->Ln(1);

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell( 8, 5, '#',             1, 0, 'C', true);
        $pdf->Cell(50, 5, 'Process',       1, 0, 'C', true);
        $pdf->Cell(80, 5, 'Product',       1, 0, 'C', true);
        $pdf->Cell(30, 5, 'Quantity',      1, 0, 'C', true);
        $pdf->Cell(50, 5, 'Unit',          1, 0, 'C', true);
        $pdf->Cell(59, 5, 'Operator Sign', 1, 0, 'C', true);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 7);
        $n = 1; $fill = false;
        foreach ($items as $item) {
            $procName = $item->processName ?? ($types[$item->processId ?? 0]->name ?? '—');
            $prodName = $itemProducts[$item->productId]->name ?? '—';
            $unitN    = $types[$item->unitId ?? 0]->name ?? '—';

            $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
            $pdf->Cell( 8, 5, $n++,                   1, 0, 'C', $fill);
            $pdf->Cell(50, 5, $procName,               1, 0, 'L', $fill);
            $pdf->Cell(80, 5, $prodName,               1, 0, 'L', $fill);
            $pdf->Cell(30, 5, $item->quantity ?? '—',  1, 0, 'R', $fill);
            $pdf->Cell(50, 5, $unitN,                  1, 0, 'L', $fill);
            $pdf->Cell(59, 5, '',                      1, 0, 'L', $fill);
            $pdf->Ln();
            $fill = !$fill;
        }

        $pdf->Ln(3);

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 5 — Production Log  30+40+32+32+32+111 = 277
        // ══════════════════════════════════════════════════════════════════════
        $pdf->sectionTitle('PRODUCTION LOG');
        $pdf->Ln(1);

        $cols = ['Date', 'Operator', 'Batch No.', 'Qty Produced', 'Qty Rejected', 'Notes'];
        $cw   = [30,      40,          32,           32,             32,             111];

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetFillColor(220, 220, 220);
        foreach ($cols as $i => $col)
            $pdf->Cell($cw[$i], 5, $col, 1, 0, 'C', true);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 7);
        for ($r = 0; $r < 6; $r++) {
            foreach ($cw as $w)
                $pdf->Cell($w, 6, '', 1);
            $pdf->Ln();
        }

        $pdf->Ln(3);

        // ══════════════════════════════════════════════════════════════════════
        // SECTION 6 — Notes (only if present)
        // ══════════════════════════════════════════════════════════════════════
        if (!empty($jobCard->notes)) {
            $pdf->sectionTitle('NOTES');
            $pdf->Ln(1);
            $pdf->SetFont('Arial', '', 7);
            $pdf->MultiCell(277, 5, $jobCard->notes, 1);
            $pdf->Ln(2);
        }

        // ── Signature line  93+92+92 = 277 ───────────────────────────────────
        $pdf->Ln(4);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(93, 5, 'Supervisor: ____________________________', 0);
        $pdf->Cell(92, 5, 'QC Check: ______________________________', 0);
        $pdf->Cell(92, 5, 'Date: __________________________________', 0);
        $pdf->Ln();

        // ── Output as base64 and trigger browser native print dialog ──────────
        // No Adobe extension required — uses the browser's built-in PDF viewer
        $pdfContent = $pdf->Output('', 'S');
        $base64     = base64_encode($pdfContent);

        return response()->make(
            '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Chemical Job Card Print</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { background: #525659; display: flex; align-items: center; justify-content: center; height: 100vh; }
  iframe { width: 100%; height: 100vh; border: none; display: block; }
</style>
</head>
<body>
<iframe id="pdfFrame" src="data:application/pdf;base64,' . $base64 . '"></iframe>
<script>
  var frame = document.getElementById("pdfFrame");
  frame.onload = function() {
    try {
      frame.contentWindow.focus();
      frame.contentWindow.print();
    } catch (e) {}
  };
</script>
</body>
</html>',
            200,
            ['Content-Type' => 'text/html']
        );
    }
}