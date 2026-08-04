<?php

use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChemicalDocumentPdf extends Fpdf
{
    // ── Palette ───────────────────────────────────────────────────────────────
    const GREY_DARK   = [130, 135, 140]; // section bars — light enough not to drain B&W toner
    const GREY_MID    = [240, 241, 242]; // very light row fill — barely visible on B&W
    const GREY_LINE   = [190, 192, 195]; // hairlines

    // ── Company info (fetched from DB, not hardcoded) ──────────────────────────
    private object $company;

    public function __construct(object $company, string $orientation = 'P', string $unit = 'mm', string $size = 'A4')
    {
        parent::__construct($orientation, $unit, $size);
        $this->company = $company;
    }

    // ─────────────────────────────────────────────────────────────────────────
    function Header()
    {
        // intentionally empty — drawn manually per document
    }

    // ─────────────────────────────────────────────────────────────────────────
    function Footer()
    {
        $this->SetY(-48);

        $this->SetDrawColor(...self::GREY_LINE);
        $this->SetLineWidth(0.3);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(3);

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(60, 60, 60);

        $this->Cell(95, 5, 'Received by (Print name):', 0, 0, 'L');
        $this->Cell(95, 5, 'Authorised by (Print name):', 0, 1, 'L');
        $this->Ln(7);

        $this->Cell(90, 0, '', 'T', 0, 'L');
        $this->Cell(5,  0, '',  0,  0);
        $this->Cell(90, 0, '', 'T', 1, 'L');
        $this->Ln(2);

        $this->SetFont('Arial', '', 7);
        $this->Cell(45, 4, 'Signature',            0, 0, 'L');
        $this->Cell(45, 4, 'Date: _______________', 0, 0, 'L');
        $this->Cell(5,  4, '',                      0, 0);
        $this->Cell(45, 4, 'Signature',            0, 0, 'L');
        $this->Cell(45, 4, 'Date: _______________', 0, 1, 'L');

        $this->SetY(-8);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 5, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    // ── Letterhead ────────────────────────────────────────────────────────────
    private function drawLetterhead(): void
    {
        $company = $this->company;

        $this->SetFont('Arial', 'B', 22);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(192, 8, $company->trading_name ?: $company->name, 0, 1, 'L');

        $this->Ln(6); // padding between logo/name and the divider line

        $this->SetDrawColor(...self::GREY_DARK);
        $this->SetLineWidth(0.6);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(7);
    }

    // ── Company + customer + meta block ─────────────────────────────────────────
    private function drawCustomerMeta(object $doc, object $customer, string $docTitle, string $docRef, ?int $linkedInvoiceId = null): void
    {
        $company = $this->company;

        // Title banner
        $this->SetFillColor(...self::GREY_DARK);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(192, 10, $docTitle, 0, 1, 'C', true);
        $this->Ln(3);

        // ── Doc meta line (doc no / date / reference) — right-aligned, above the boxes ──
        $metaParts = [
            $docRef,
            Carbon::parse($doc->created_at)->format('d M Y'),
        ];
        if (!empty($doc->reference)) {
            $metaParts[] = 'Ref: ' . $doc->reference;
        }
        if ($linkedInvoiceId) {
            $metaParts[] = 'Linked Invoice: INV #' . $linkedInvoiceId;
        }
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(90, 90, 90);
        $this->Cell(192, 5, implode('   |   ', $metaParts), 0, 1, 'R');
        $this->Ln(2);

        // ── Build both columns first, so the cards can be sized to fit ──────────
        $companyAddress = implode(', ', array_filter([
            $company->shop_no ? 'Shop ' . $company->shop_no : null,
            $company->physical_address,
            $company->suburb,
            $company->city,
        ]));

        $leftLines = array_values(array_filter([
            $companyAddress ?: null,
            !empty($company->tel_number) ? 'Tel: ' . $company->tel_number  : null,
            !empty($company->email)      ? $company->email                 : null,
            !empty($company->vat_number) ? 'VAT: ' . $company->vat_number  : null,
        ]));

        // Address lines come pre-assembled from fetchCustomer()
        $rightLines = array_merge(
            $customer->addressLines ?? [],
            array_values(array_filter([
                !empty($customer->contactPerson) ? 'Attn: ' . $customer->contactPerson : null,
                !empty($customer->phone)         ? 'Tel: '  . $customer->phone         : null,
                !empty($customer->email)         ? $customer->email                    : null,
                !empty($customer->vatNumber)     ? 'VAT: '  . $customer->vatNumber     : null,
                !empty($customer->accountNumber) ? 'Acc: '  . $customer->accountNumber : null,
            ]))
        );

        // 3mm top pad + 4mm label + 6mm name + lines + 4mm bottom pad
        $cardH  = max(38, 13 + (max(count($leftLines), count($rightLines)) * 4) + 4);
        $startY = $this->GetY();

        // ── Left card: Company Details (FROM) ───────────────────────────────────
        $this->SetFillColor(...self::GREY_MID);
        $this->Rect(10, $startY, 92, $cardH, 'F');

        $this->SetXY(13, $startY + 3);
        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor(110, 115, 122);
        $this->Cell(86, 4, 'FROM', 0, 2, 'L');

        $this->SetX(13);
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(30, 30, 30);
        $this->Cell(86, 6, $company->trading_name ?: $company->name, 0, 2, 'L');

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(70, 70, 70);
        foreach ($leftLines as $line) {
            $this->SetX(13);
            $this->Cell(86, 4, $line, 0, 2, 'L');
        }

        // ── Right card: Customer Details (BILL TO / DELIVER TO) ─────────────────
        $this->SetFillColor(...self::GREY_MID);
        $this->Rect(108, $startY, 84, $cardH, 'F');

        $this->SetXY(111, $startY + 3);
        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor(110, 115, 122);
        $this->Cell(78, 4, 'BILL TO / DELIVER TO', 0, 2, 'L');

        $this->SetX(111);
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(30, 30, 30);
        $this->Cell(78, 6, $customer->name ?? '—', 0, 2, 'L');

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(70, 70, 70);
        foreach ($rightLines as $line) {
            $this->SetX(111);
            $this->Cell(78, 4, $line, 0, 2, 'L');
        }

        $this->SetY($startY + $cardH + 6);

        if (!empty($doc->other)) {
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(100, 100, 100);
            $this->Cell(192, 5, 'Note: ' . $doc->other, 0, 1, 'L');
            $this->Ln(2);
        }
    }

    // ── Invoice items (with pricing) ──────────────────────────────────────────
    // Zero VAT on a line prints as an empty cell rather than "0%" / "R 0.00" —
    // the columns stay put so the table lines up either way.
    private function drawInvoiceItems(array $items): void
    {
        $widths = [8, 60, 30, 14, 22, 14, 20, 24];
        $cols   = ['#', 'Product', 'Pack / Container', 'Qty', 'Unit Price', 'VAT', 'VAT Amt', 'Total'];

        $this->SetFillColor(...self::GREY_DARK);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 8);
        $this->SetY($this->GetY());
        foreach ($cols as $i => $col) {
            $this->Cell($widths[$i], 8, $col, 0, 0, $i >= 3 ? 'R' : 'L', true);
        }
        $this->Ln();
        $this->Ln(1);

        $fill = false;
        foreach ($items as $idx => $item) {
            if ($fill) {
                $this->SetFillColor(...self::GREY_MID);
            } else {
                $this->SetFillColor(255, 255, 255);
            }
            $this->SetTextColor(30, 30, 30);
            $this->SetFont('Arial', '', 8);

            $vatAmount = (float) ($item->vatAmnt ?? 0);
            $hasVat    = $vatAmount > 0;

            $vatLabel  = $hasVat ? '15%' : '';
            $vatValue  = $hasVat ? 'R ' . number_format($vatAmount, 2) : '';

            $this->Cell($widths[0], 7, $idx + 1,                                    0, 0, 'L', $fill);
            $this->Cell($widths[1], 7, $item->productName   ?? '—',                 0, 0, 'L', $fill);
            $this->Cell($widths[2], 7, $item->containerName ?? '—',                 0, 0, 'L', $fill);
            $this->Cell($widths[3], 7, $item->quantity,                             0, 0, 'R', $fill);
            $this->Cell($widths[4], 7, 'R ' . number_format($item->price,      2),  0, 0, 'R', $fill);
            $this->Cell($widths[5], 7, $vatLabel,                                   0, 0, 'C', $fill);
            $this->Cell($widths[6], 7, $vatValue,                                   0, 0, 'R', $fill);
            $this->Cell($widths[7], 7, 'R ' . number_format($item->totalPrice, 2),  0, 0, 'R', $fill);
            $this->Ln();
            $fill = !$fill;
        }

        $this->SetDrawColor(...self::GREY_LINE);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(4);
    }

    // ── Delivery items (no pricing) ───────────────────────────────────────────
    private function drawDeliveryItems(array $items): void
    {
        $widths = [8, 112, 42, 30];
        $cols   = ['#', 'Product', 'Pack / Container', 'Qty'];

        $this->SetFillColor(...self::GREY_DARK);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 8);
        foreach ($cols as $i => $col) {
            $this->Cell($widths[$i], 8, $col, 0, 0, $i >= 3 ? 'R' : 'L', true);
        }
        $this->Ln();
        $this->Ln(1);

        $fill = false;
        foreach ($items as $idx => $item) {
            $this->SetFillColor($fill ? self::GREY_MID[0] : 255, $fill ? self::GREY_MID[1] : 255, $fill ? self::GREY_MID[2] : 255);
            $this->SetTextColor(30, 30, 30);
            $this->SetFont('Arial', '', 8);

            $this->Cell($widths[0], 7, $idx + 1,                    0, 0, 'L', $fill);
            $this->Cell($widths[1], 7, $item->productName   ?? '—', 0, 0, 'L', $fill);
            $this->Cell($widths[2], 7, $item->containerName ?? '—', 0, 0, 'L', $fill);
            $this->Cell($widths[3], 7, $item->quantity,             0, 0, 'R', $fill);
            $this->Ln();
            $fill = !$fill;
        }

        $this->SetDrawColor(...self::GREY_LINE);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(4);
    }

    // ── Totals ────────────────────────────────────────────────────────────────
    private function drawTotals(float $excl, float $vat): void
    {
        $incl   = $excl + $vat;
        $startX = 108;
        $labelW = 50;
        $valW   = 34;
        $boxY   = $this->GetY();

        $this->SetFillColor(...self::GREY_MID);
        $this->Rect($startX, $boxY, $labelW + $valW, 24, 'F');

        $this->SetXY($startX, $boxY + 3);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(70, 70, 70);
        $this->Cell($labelW, 6, '  Subtotal (excl. VAT)', 0, 0, 'L');
        $this->Cell($valW,   6, number_format($excl, 2) . '  ', 0, 1, 'R');

        $this->SetX($startX);
        $this->Cell($labelW, 6, '  VAT (15%)', 0, 0, 'L');
        $this->Cell($valW,   6, $vat > 0 ? number_format($vat, 2) . '  ' : '', 0, 1, 'R');

        $this->SetX($startX);
        $this->SetFillColor(...self::GREY_DARK);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell($labelW, 10, '  TOTAL (incl. VAT)', 0, 0, 'L', true);
        $this->Cell($valW,   10, 'R ' . number_format($incl, 2) . '  ', 0, 1, 'R', true);
    }

    // ── Receipt comment — bordered box, pinned near the bottom of the page ────
    private function drawCommentBox(): void
    {
        $comment = trim($this->company->receipt_comment ?? '');
        if ($comment === '') {
            return;
        }

        $boxX = 10;
        $boxW = 190;

        // Estimate wrapped line count so the box fits the text
        $this->SetFont('Arial', 'BI', 9);
        $lineHeight = 5;
        $charsPerLine = 100; // rough estimate for Arial 9pt across 190mm
        $lineCount = max(1, (int) ceil(mb_strlen($comment) / $charsPerLine));
        $boxH = ($lineCount * $lineHeight) + 8;

        // Pin the box just above the footer's reserved space (footer starts at -48)
        $boxY = $this->GetPageHeight() - 48 - $boxH - 4;
        $this->SetY($boxY);

        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.3);
        $this->Rect($boxX, $boxY, $boxW, $boxH); // border only, no fill

        $this->SetXY($boxX + 4, $boxY + 4);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', 'BI', 9);
        $this->MultiCell($boxW - 8, $lineHeight, $comment, 0, 'L');
    }

    // ═════════════════════════════════════════════════════════════════════════
    // Public entry points — call from ChemicalDeliveryController
    // ═════════════════════════════════════════════════════════════════════════

    private static function getCompanyInfo(): object
    {
        $company = DB::table('company_info')->first();

        // Fallback so PDF generation never hard-fails if company info hasn't been set up yet
        return $company ?? (object) [
            'name'             => 'Company Name Not Set',
            'trading_name'     => '',
            'vat_number'       => '',
            'reg_number'       => '',
            'tel_number'       => '',
            'email'            => '',
            'web_address'      => '',
            'suburb'           => '',
            'shop_no'          => '',
            'physical_address' => '',
            'city'             => '',
            'country'          => '',
            'receipt_comment'  => '',
        ];
    }

    /*
    |---------------------------------------------------------------------------
    | Customer details for the document header.
    |
    | The customers table uses the CRM column names (pO* for the postal address,
    | vatNo, emailAddress, phoneNumber) — this maps them onto the short names
    | the PDF draws with, and pre-assembles the address into printable lines.
    |---------------------------------------------------------------------------
    */
    private static function fetchCustomer(?int $customerId): object
    {
        $blank = (object) [
            'name'          => '—',
            'addressLines'  => [],
            'contactPerson' => '',
            'phone'         => '',
            'email'         => '',
            'vatNumber'     => '',
            'accountNumber' => '',
        ];

        if (!$customerId) {
            return $blank;
        }

        $c = DB::table('customers')->where('id', $customerId)->first();
        if (!$c) {
            return $blank;
        }

        $trim = fn($v) => trim((string) ($v ?? ''));

        // City and region share a line; everything else gets its own
        $cityRegion = implode(', ', array_filter([
            $trim($c->pOCity ?? null),
            $trim($c->pORegion ?? null),
        ]));

        $addressLines = array_values(array_filter([
            $trim($c->pOAttentionTo ?? null),
            $trim($c->pOAddressLine1 ?? null),
            $trim($c->pOAddressLine2 ?? null),
            $trim($c->pOAddressLine3 ?? null),
            $trim($c->pOAddressLine4 ?? null),
            $cityRegion,
            $trim($c->pOPostalCode ?? null),
            $trim($c->pOCountry ?? null),
        ], fn($v) => $v !== ''));

        $contact = trim(
            $trim($c->contactPerson ?? null) . ' ' . $trim($c->contactPersonLastName ?? null)
        );

        return (object) [
            // legal name is what belongs on a tax invoice when it differs
            'name'          => $trim($c->legalName ?? null) ?: ($trim($c->name ?? null) ?: '—'),
            'addressLines'  => $addressLines,
            'contactPerson' => $contact,
            'phone'         => $trim($c->phoneNumber ?? null) ?: $trim($c->mobileNumber ?? null),
            'email'         => $trim($c->emailAddress ?? null),
            'vatNumber'     => $trim($c->vatNo ?? null),
            'accountNumber' => $trim($c->accountNumber ?? null),
        ];
    }

    // ── Shared output helper — base64/iframe/auto-print, same pattern as job cards ──
    private static function renderAndPrint(self $pdf, string $title)
    {
        $pdfContent = $pdf->Output('', 'S');
        $base64     = base64_encode($pdfContent);

        return response()->make(
            '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>' . htmlspecialchars($title) . '</title>
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

    public static function printInvoice(Request $request)
    {
        $invoiceId = (int) $request->get('id');

        $invoice = DB::table('invoices')->where('id', $invoiceId)->first();
        if (!$invoice) abort(404, 'Invoice not found');

        $customer = self::fetchCustomer($invoice->customerId ?? null);
        $items    = self::fetchInvoiceItems($invoiceId);
        $company  = self::getCompanyInfo();

        $pdf = new self($company, 'P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(true, 52);
        $pdf->AddPage();
        $pdf->drawLetterhead();
        $pdf->drawCustomerMeta($invoice, $customer, 'TAX INVOICE', 'INV #' . $invoiceId);
        $pdf->drawInvoiceItems($items);
        $pdf->drawTotals($invoice->totalValue, $invoice->totalVat);
        $pdf->drawCommentBox();

        return self::renderAndPrint($pdf, 'Invoice_' . $invoiceId);
    }

    public static function printDelivery(Request $request)
    {
        $deliveryId = (int) $request->get('id');

        $delivery = DB::table('tb_deliveries')->where('id', $deliveryId)->first();
        if (!$delivery) abort(404, 'Delivery note not found');

        $customer = self::fetchCustomer($delivery->customerId ?? null);
        $items    = self::fetchDeliveryItems($deliveryId);
        $company  = self::getCompanyInfo();

        $pdf = new self($company, 'P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(true, 52);
        $pdf->AddPage();
        $pdf->drawLetterhead();
        $pdf->drawCustomerMeta($delivery, $customer, 'DELIVERY NOTE', 'DN #' . $deliveryId, $delivery->invoiceNo ?? null);
        $pdf->drawDeliveryItems($items);
        $pdf->drawCommentBox();

        return self::renderAndPrint($pdf, 'DeliveryNote_' . $deliveryId);
    }

    public static function printBoth(Request $request)
    {
        $invoiceId  = (int) $request->get('invoiceId');
        $deliveryId = (int) $request->get('deliveryId');

        $invoice = DB::table('invoices')->where('id', $invoiceId)->first();
        if (!$invoice) abort(404, 'Invoice not found');

        $delivery = DB::table('tb_deliveries')->where('id', $deliveryId)->first();
        if (!$delivery) abort(404, 'Delivery note not found');

        $customer = self::fetchCustomer($invoice->customerId ?? null);
        $company  = self::getCompanyInfo();

        $pdf = new self($company, 'P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(true, 52);

        // Page 1 — Invoice
        $pdf->AddPage();
        $pdf->drawLetterhead();
        $pdf->drawCustomerMeta($invoice, $customer, 'TAX INVOICE', 'INV #' . $invoiceId);
        $pdf->drawInvoiceItems(self::fetchInvoiceItems($invoiceId));
        $pdf->drawTotals($invoice->totalValue, $invoice->totalVat);
        $pdf->drawCommentBox();

        // Page 2 — Delivery Note
        $pdf->AddPage();
        $pdf->drawLetterhead();
        $pdf->drawCustomerMeta($delivery, $customer, 'DELIVERY NOTE', 'DN #' . $deliveryId, $invoiceId);
        $pdf->drawDeliveryItems(self::fetchDeliveryItems($deliveryId));
        $pdf->drawCommentBox();

        return self::renderAndPrint($pdf, 'Invoice_DN_' . $invoiceId);
    }

    // ── DB fetchers ───────────────────────────────────────────────────────────
    private static function fetchInvoiceItems(int $invoiceId): array
    {
        return DB::table('invoice_items')
            ->leftJoin('chemical_products', 'chemical_products.id', '=', 'invoice_items.productId')
            ->leftJoin('types as container_sizes', 'container_sizes.id', '=', 'invoice_items.unitId')
            ->select(
                'chemical_products.name as productName',
                'container_sizes.name   as containerName',
                'invoice_items.quantity',
                'invoice_items.price',
                'invoice_items.VatType',
                'invoice_items.vatAmnt',
                'invoice_items.totalPrice'
            )
            ->where('invoice_items.invoicesId', $invoiceId)
            ->get()
            ->toArray();
    }

    private static function fetchDeliveryItems(int $deliveryId): array
    {
        return DB::table('tb_delivery_items')
            ->leftJoin('chemical_products', 'chemical_products.id', '=', 'tb_delivery_items.productId')
            ->leftJoin('types as container_sizes', 'container_sizes.id', '=', 'tb_delivery_items.unitId')
            ->select(
                'chemical_products.name as productName',
                'container_sizes.name   as containerName',
                'tb_delivery_items.quantity'
            )
            ->where('tb_delivery_items.deliveryId', $deliveryId)
            ->get()
            ->toArray();
    }
}