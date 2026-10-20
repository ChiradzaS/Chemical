<?php

use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChemicalDocumentPdf extends Fpdf
{
    // ── Page geometry (A4 portrait, mm) ───────────────────────────────────────
    const L = 15;           // left margin
    const W = 180;          // usable width (210 - 15 - 15)

    // ── Company info (from company_info, not hardcoded) ──────────────────────
    private object $company;

    /** Set straight after AddPage(); read by Footer() for that page */
    public string $docLabel   = '';
    public bool   $signatures = false;

    public function __construct(object $company, string $orientation = 'P', string $unit = 'mm', string $size = 'A4')
    {
        parent::__construct($orientation, $unit, $size);
        $this->company = $company;
        $this->SetMargins(self::L, 15, self::L);
        $this->AliasNbPages();
    }

    // ═════════════════════════════════════════════════════════════════════════
    // Small helpers
    // ═════════════════════════════════════════════════════════════════════════

    // Latin-1 text for the core fonts (FPDF can't print UTF-8 directly)
    private function t($s): string
    {
        $s   = (string) ($s ?? '');
        $out = @iconv('UTF-8', 'windows-1252//TRANSLIT', $s);
        return $out === false ? $s : $out;
    }

    private function money($n): string
    {
        return 'R ' . number_format((float) $n, 2, '.', ' ');
    }

    private function qty($q): string
    {
        $q = (float) $q;
        return floor($q) == $q ? number_format($q, 0, '.', ' ') : number_format($q, 2, '.', ' ');
    }

    private function companyName(): string
    {
        return $this->company->trading_name ?: $this->company->name;
    }

    // Start a page for one document type
    private function newPage(string $label, bool $signatures): void
    {
        $this->SetAutoPageBreak(true, $signatures ? 56 : 22);
        $this->AddPage();
        $this->docLabel   = $label;
        $this->signatures = $signatures;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // Header / footer
    // ═════════════════════════════════════════════════════════════════════════

    function Header()
    {
        // intentionally empty — drawn per document by drawHeader()
    }

    function Footer()
    {
        $L = self::L; $W = self::W;
        $this->SetDrawColor(180, 180, 180);
        $this->SetLineWidth(0.2);

        // Delivery notes: receipt / signature block
        if ($this->signatures) {
            $this->SetY(-52);
            $this->SetTextColor(0, 0, 0);
            $this->SetFont('Arial', 'B', 8.5);
            $this->SetX($L);
            $this->Cell($W, 5, 'Goods received in good order and condition', 0, 1, 'L');
            $this->Ln(9);

            $col = ($W - 10) / 2;
            $y   = $this->GetY();
            foreach ([['Received by', $L], ['Authorised by', $L + $col + 10]] as [$who, $x]) {
                $this->Line($x, $y, $x + $col, $y);
                $this->SetXY($x, $y + 1);
                $this->SetFont('Arial', '', 7.5);
                $this->SetTextColor(110, 110, 110);
                $this->Cell($col, 4, $who . ' (signature)', 0, 0, 'L');

                $this->Line($x, $y + 12, $x + $col * 0.62, $y + 12);
                $this->Line($x + $col * 0.68, $y + 12, $x + $col, $y + 12);
                $this->SetXY($x, $y + 13);
                $this->Cell($col * 0.62, 4, 'Print name', 0, 0, 'L');
                $this->SetX($x + $col * 0.68);
                $this->Cell($col * 0.32, 4, 'Date', 0, 0, 'L');
            }
        }

        // Bottom rule + company line + page number
        $this->SetY(-14);
        $this->Line($L, $this->GetY(), $L + $W, $this->GetY());
        $this->Ln(1.5);
        $this->SetX($L);
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(130, 130, 130);
        $left = implode('  |  ', array_filter([
            $this->companyName(),
            !empty($this->company->tel_number) ? 'Tel ' . $this->company->tel_number : null,
            $this->company->web_address ?? null,
        ]));
        $this->Cell($W * 0.65, 4, $this->t($left), 0, 0, 'L');
        $this->Cell($W * 0.35, 4, $this->t($this->docLabel) . '  |  Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'R');
        $this->SetTextColor(0, 0, 0);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // Document parts
    // ═════════════════════════════════════════════════════════════════════════

    // "Label   value" row in the top-right meta block
    private function metaRow(float $x, string $label, string $value): void
    {
        $this->SetX($x);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(110, 110, 110);
        $this->Cell(28, 5.2, $this->t($label), 0, 0, 'L');
        $this->SetFont('Arial', 'B', 8.5);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(42, 5.2, $this->t($value), 0, 1, 'R');
    }

    // Company (left) + document title and meta (right) + customer block
    private function drawHeader(string $title, array $meta, object $customer, string $customerLabel, array $details = []): void
    {
        $L  = self::L; $W = self::W;
        $c  = $this->company;
        $rx = $L + $W - 70;

        /* ── Company ── */
        $this->SetXY($L, 15);
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(105, 8, $this->t(strtoupper($this->companyName())), 0, 1, 'L');

        $address = implode(', ', array_filter([
            !empty($c->shop_no) ? 'Shop ' . $c->shop_no : null,
            $c->physical_address ?? null,
            $c->suburb ?? null,
            $c->city ?? null,
        ]));
        $regVat = implode('   |   ', array_filter([
            !empty($c->reg_number) ? 'Reg No ' . $c->reg_number : null,
            !empty($c->vat_number) ? 'VAT No ' . $c->vat_number : null,
        ]));
        $lines = array_filter([
            (!empty($c->trading_name) && $c->trading_name !== $c->name) ? $c->name : null,
            $regVat ?: null,
            $address ?: null,
            !empty($c->tel_number) ? 'Tel ' . $c->tel_number : null,
            implode('   |   ', array_filter([$c->email ?? null, $c->web_address ?? null])) ?: null,
        ]);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(90, 90, 90);
        foreach ($lines as $line) {
            $this->SetX($L);
            $this->Cell(105, 4.2, $this->t($line), 0, 1, 'L');
        }
        $leftBottom = $this->GetY();
        $this->SetTextColor(0, 0, 0);

        /* ── Title + meta ── */
        $this->SetXY($rx, 15);
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(70, 9, $this->t($title), 0, 1, 'R');
        $this->Ln(2);
        foreach ($meta as $label => $value) {
            if ($value !== '' && $value !== null) $this->metaRow($rx, $label, (string) $value);
        }

        $y = max($leftBottom, $this->GetY()) + 6;
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.5);
        $this->Line($L, $y, $L + $W, $y);
        $this->SetLineWidth(0.2);
        $y += 6;

        /* ── Customer (left) ── */
        $this->SetXY($L, $y);
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetTextColor(110, 110, 110);
        $this->Cell(100, 4, $this->t($customerLabel), 0, 1, 'L');
        $this->SetTextColor(0, 0, 0);
        $this->SetX($L);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(100, 6, $this->t($customer->name ?? '-'), 0, 1, 'L');
        $this->SetFont('Arial', '', 8.5);
        foreach ($customer->addressLines ?? [] as $line) {
            $this->SetX($L);
            $this->Cell(100, 4.4, $this->t($line), 0, 1, 'L');
        }
        $extra = array_filter([
            !empty($customer->contactPerson) ? 'Attn: '   . $customer->contactPerson : null,
            !empty($customer->phone)         ? 'Tel: '    . $customer->phone         : null,
            !empty($customer->email)         ? $customer->email                      : null,
            !empty($customer->vatNumber)     ? 'VAT No: ' . $customer->vatNumber     : null,
            !empty($customer->accountNumber) ? 'Account: '. $customer->accountNumber : null,
        ]);
        $this->SetTextColor(90, 90, 90);
        foreach ($extra as $line) {
            $this->SetX($L);
            $this->Cell(100, 4.4, $this->t($line), 0, 1, 'L');
        }
        $this->SetTextColor(0, 0, 0);
        $leftBottom = $this->GetY();

        /* ── Optional details (right) ── */
        $details = array_filter($details, fn ($v) => $v !== '' && $v !== null);
        $this->SetXY($rx, $y);
        if ($details) {
            $this->SetFont('Arial', 'B', 7.5);
            $this->SetTextColor(110, 110, 110);
            $this->Cell(70, 4, 'DETAILS', 0, 1, 'L');
            $this->SetTextColor(0, 0, 0);
            foreach ($details as $k => $v) $this->metaRow($rx, $k, (string) $v);
        }

        $this->SetY(max($leftBottom, $this->GetY()) + 8);
    }

    // Column header row of an items table
    private function tableHeader(array $cols): void
    {
        $this->SetX(self::L);
        $this->SetFillColor(242, 242, 242);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(60, 60, 60);
        $y = $this->GetY();
        $this->Line(self::L, $y, self::L + self::W, $y);
        foreach ($cols as [$label, $w, $align]) {
            $this->Cell($w, 7, $this->t(strtoupper($label)), 0, 0, $align, true);
        }
        $this->Ln();
        $y = $this->GetY();
        $this->Line(self::L, $y, self::L + self::W, $y);
        $this->SetTextColor(0, 0, 0);
        $this->SetLineWidth(0.2);
        $this->SetDrawColor(215, 215, 215);
        $this->SetFont('Arial', '', 9);
    }

    // New page (repeating the table header) when the next row won't fit
    private function rowBreak(array $cols): void
    {
        if ($this->GetY() + 8 > $this->GetPageHeight() - $this->bMargin) {
            $label = $this->docLabel; $sig = $this->signatures;
            $this->AddPage();
            $this->docLabel = $label; $this->signatures = $sig;
            $this->SetY(20);
            $this->tableHeader($cols);
        }
    }

    // ── Invoice items (with pricing) ──────────────────────────────────────────
    private function drawInvoiceItems(array $items): void
    {
        $cols = [['#', 8, 'C'], ['Description', 64, 'L'], ['Pack', 24, 'L'], ['Qty', 16, 'R'],
                 ['Unit price', 24, 'R'], ['VAT', 20, 'R'], ['Amount', 24, 'R']];
        $this->tableHeader($cols);

        foreach ($items as $i => $item) {
            $this->rowBreak($cols);
            $vat = (float) ($item->vatAmnt ?? 0);

            $this->SetX(self::L);
            $this->SetTextColor(120, 120, 120);
            $this->Cell(8, 8, $i + 1, 'B', 0, 'C');
            $this->SetTextColor(0, 0, 0);
            $this->Cell(64, 8, $this->t($item->productName ?? '-'), 'B', 0, 'L');
            $this->Cell(24, 8, $this->t($item->containerName ?? ''), 'B', 0, 'L');
            $this->Cell(16, 8, $this->qty($item->quantity), 'B', 0, 'R');
            $this->Cell(24, 8, $this->money($item->price), 'B', 0, 'R');
            $this->Cell(20, 8, $vat > 0 ? $this->money($vat) : '', 'B', 0, 'R');
            $this->Cell(24, 8, $this->money($item->totalPrice), 'B', 1, 'R');
        }

        if (!$items) {
            $this->SetX(self::L);
            $this->SetTextColor(130, 130, 130);
            $this->Cell(self::W, 10, 'No items on this invoice', 'B', 1, 'C');
            $this->SetTextColor(0, 0, 0);
        }
        $this->Ln(3);
    }

    // ── Delivery items (no pricing) ───────────────────────────────────────────
    private function drawDeliveryItems(array $items): void
    {
        $cols = [['#', 10, 'C'], ['Description', 110, 'L'], ['Pack', 35, 'L'], ['Qty', 25, 'R']];
        $this->tableHeader($cols);

        $total = 0;
        foreach ($items as $i => $item) {
            $this->rowBreak($cols);
            $total += (float) $item->quantity;

            $this->SetX(self::L);
            $this->SetTextColor(120, 120, 120);
            $this->Cell(10, 8, $i + 1, 'B', 0, 'C');
            $this->SetTextColor(0, 0, 0);
            $this->Cell(110, 8, $this->t($item->productName ?? '-'), 'B', 0, 'L');
            $this->Cell(35, 8, $this->t($item->containerName ?? ''), 'B', 0, 'L');
            $this->SetFont('Arial', 'B', 9);
            $this->Cell(25, 8, $this->qty($item->quantity), 'B', 1, 'R');
            $this->SetFont('Arial', '', 9);
        }

        if (!$items) {
            $this->SetX(self::L);
            $this->SetTextColor(130, 130, 130);
            $this->Cell(self::W, 10, 'No items on this delivery', 'B', 1, 'C');
            $this->SetTextColor(0, 0, 0);
        }

        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetX(self::L + 120);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(35, 8, 'Total units', 'B', 0, 'L');
        $this->Cell(25, 8, $this->qty($total), 'B', 1, 'R');
        $this->SetLineWidth(0.2);
    }

    // ── Totals ────────────────────────────────────────────────────────────────
    private function drawTotals(float $excl, float $vat): void
    {
        if ($this->GetY() + 28 > $this->GetPageHeight() - $this->bMargin) {
            $this->newPage($this->docLabel, $this->signatures);
            $this->SetY(20);
        }

        $tx = self::L + self::W - 80;
        foreach ([['Subtotal (excl. VAT)', $excl], ['VAT 15%', $vat]] as [$label, $val]) {
            $this->SetX($tx);
            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(80, 80, 80);
            $this->Cell(50, 6.5, $label, 0, 0, 'L');
            $this->SetTextColor(0, 0, 0);
            $this->Cell(30, 6.5, $this->money($val), 0, 1, 'R');
        }
        $this->SetX($tx);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.4);
        $this->SetFillColor(242, 242, 242);
        $this->SetFont('Arial', 'B', 10.5);
        $this->Cell(50, 9, 'Total (incl. VAT)', 'TB', 0, 'L', true);
        $this->Cell(30, 9, $this->money($excl + $vat), 'TB', 1, 'R', true);
        $this->SetLineWidth(0.2);
    }

    // ── Notes from the document + company receipt comment ────────────────────
    private function drawNotes(?string $notes): void
    {
        $notes   = trim((string) $notes);
        $comment = trim((string) ($this->company->receipt_comment ?? ''));

        if ($notes !== '') {
            $this->Ln(6);
            $this->SetX(self::L);
            $this->SetFont('Arial', 'B', 7.5);
            $this->SetTextColor(110, 110, 110);
            $this->Cell(self::W, 4, 'NOTES', 0, 1, 'L');
            $this->SetTextColor(0, 0, 0);
            $this->SetX(self::L);
            $this->SetFont('Arial', '', 8.5);
            $this->MultiCell(self::W, 4.5, $this->t($notes), 0, 'L');
        }

        if ($comment !== '') {
            $this->Ln(6);
            $this->SetX(self::L);
            $this->SetDrawColor(180, 180, 180);
            $this->SetFont('Arial', '', 8.5);
            $this->SetTextColor(60, 60, 60);
            $this->MultiCell(self::W, 4.6, $this->t($comment), 'T', 'L');
            $this->SetTextColor(0, 0, 0);
        }
    }

    // ═════════════════════════════════════════════════════════════════════════
    // Whole documents
    // ═════════════════════════════════════════════════════════════════════════

    private function invoicePage(object $invoice, object $customer, array $items, ?int $deliveryId = null): void
    {
        $this->newPage('Tax Invoice', false);
        $this->drawHeader('TAX INVOICE', [
            'Invoice No'  => 'INV #' . $invoice->id,
            'Date'        => Carbon::parse($invoice->created_at)->format('d M Y'),
            'Reference'   => $invoice->reference ?? '',
            'Delivery No' => $deliveryId ? 'DN #' . $deliveryId : '',
        ], $customer, 'BILL TO');

        $this->drawInvoiceItems($items);
        $this->drawTotals((float) $invoice->totalValue, (float) $invoice->totalVat);
        $this->drawNotes($invoice->other ?? '');
    }

    private function deliveryPage(object $delivery, object $customer, array $items, ?int $invoiceId = null): void
    {
        $this->newPage('Delivery Note', true);
        $this->drawHeader('DELIVERY NOTE', [
            'Delivery No' => 'DN #' . $delivery->id,
            'Date'        => Carbon::parse($delivery->created_at)->format('d M Y'),
            'Invoice No'  => $invoiceId ? 'INV #' . $invoiceId : '',
            'Reference'   => $delivery->reference ?? '',
        ], $customer, 'DELIVER TO');

        $this->drawDeliveryItems($items);
        $this->drawNotes($delivery->other ?? '');
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
            'name'          => '-',
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
            'name'          => $trim($c->legalName ?? null) ?: ($trim($c->name ?? null) ?: '-'),
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

        $pdf = new self(self::getCompanyInfo(), 'P', 'mm', 'A4');
        $pdf->invoicePage($invoice, self::fetchCustomer($invoice->customerId ?? null), self::fetchInvoiceItems($invoiceId));

        return self::renderAndPrint($pdf, 'Invoice_' . $invoiceId);
    }

    public static function printDelivery(Request $request)
    {
        $deliveryId = (int) $request->get('id');

        $delivery = DB::table('tb_deliveries')->where('id', $deliveryId)->first();
        if (!$delivery) abort(404, 'Delivery note not found');

        $pdf = new self(self::getCompanyInfo(), 'P', 'mm', 'A4');
        $pdf->deliveryPage(
            $delivery,
            self::fetchCustomer($delivery->customerId ?? null),
            self::fetchDeliveryItems($deliveryId),
            !empty($delivery->invoiceNo) ? (int) $delivery->invoiceNo : null
        );

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

        $pdf = new self(self::getCompanyInfo(), 'P', 'mm', 'A4');
        $pdf->invoicePage($invoice, $customer, self::fetchInvoiceItems($invoiceId), $deliveryId);     // page 1
        $pdf->deliveryPage($delivery, $customer, self::fetchDeliveryItems($deliveryId), $invoiceId);  // page 2

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