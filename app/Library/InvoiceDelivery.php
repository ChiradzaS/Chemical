<?php

//require_once 'App/Library/Barcode/php-barcode.php';
require_once 'Barcode/php-barcode.php';
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;


class InvoiceDelivery extends Fpdf
{
    /* ── Company details printed on the documents ────────────────────────── */
    const CO_NAME    = 'SAILING PACKAGING';
    const CO_LEGAL   = 'Sailing Packaging (Pty) Ltd';
    const CO_REG     = 'Reg No 2016/367235/07   |   VAT No 4910276528';
    const CO_ADDRESS = 'Shop 3, Cnr Smits & Refinery Road, South Germiston, Germiston';
    const CO_ADDR2   = '13 Smits Street, Germiston South';
    const CO_TEL     = 'Tel +27 61 731 6406';
    const CO_EMAIL   = 'sales@sailingpackaging.com   |   accounts@sailingpackaging.com';

    /* ── Page geometry (A4 portrait, mm) ─────────────────────────────────── */
    const L = 15;            // left margin
    const W = 180;           // usable width (210 - 15 - 15)

    /** Set straight after AddPage(); read by Footer() for that page */
    public $docLabel   = 'Tax Invoice';
    public $signatures = false;

    static public function digit_to_fpdf_renderer( $pdf, $color, $x, $y, $angle, $type, $datas, $width = null, $height = null )
    {
        $digit = '';
        
        $hri = '';
        
        list( $digit, $hri ) = Barcode::raw( $type, $datas );
        
        $type = strtolower( $type );
        
        if ( $digit == '' )
        {
            return false;
        }
        
        if ( $type == 'datamatrix' )
        {
            $width = is_null( $width ) ? 5 : $width;
            
            $height = $width;
        }
        else
        {
            $width = is_null( $width ) ? 1 : $width;
            
            $height = is_null( $height ) ? 50 : $height;
            
            $digit = Barcode::bitStringTo2DArray( $digit );
        }
        
        if ( ! is_array( $color ) )
        {
            if ( preg_match( '`([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})`i', $color, $m ) )
            {
                $color = array( hexdec( $m[ 1 ] ), hexdec( $m[ 2 ] ), hexdec( $m[ 3 ] ) );
            }
            else
            {
                $color = array( 0, 0, 0 );
            }
        }
        
        $color = array_values( $color );
        
        $pdf->SetDrawColor( $color[ 0 ], $color[ 1 ], $color[ 2 ] );
        
        $pdf->SetFillColor( $color[ 0 ], $color[ 1 ], $color[ 2 ] );
        
        $fn = function( $points ) use ( $pdf )
        {
            $op = 'f';
            
            $h = $pdf->h;
            
            $k = $pdf->k;
            
            $points_string = '';
            
            for ( $i = 0; $i < 8; $i += 2 )
            {
                $points_string .= sprintf( '%.2F %.2F', $points[ $i ] * $k, ( $h - $points[ $i + 1 ] ) * $k );
                
                $points_string .= $i ? ' l ' : ' m ';
            }
            
            $pdf->_out( $points_string . $op );
        };
        
        $result = Barcode::digitToRenderer( $fn, $x, $y, $angle, $width, $height, $digit );
        
        $result[ 'hri' ] = $hri;
        
        return $result;
    }



    /* ═════════════════════════════ Layout ══════════════════════════════ */

    // Latin-1 text for the core fonts (FPDF can't print UTF-8 directly)
    function t($s)
    {
        $s = (string) ($s ?? '');
        $out = @iconv('UTF-8', 'windows-1252//TRANSLIT', $s);
        return $out === false ? $s : $out;
    }

    function money($n)
    {
        return 'R ' . number_format((float) $n, 2, '.', ' ');
    }

    function qtyText($q)
    {
        $q = (float) $q;
        return floor($q) == $q ? number_format($q, 0, '.', ' ') : number_format($q, 2, '.', ' ');
    }

    // Each document draws its own header, so FPDF's automatic header stays empty
    function Header() {}

    function Footer()
    {
        $L = self::L; $W = self::W;
        $this->SetDrawColor(180, 180, 180);
        $this->SetLineWidth(0.2);

        // Delivery note pages: receipt / signature block
        if ($this->signatures) {
            $this->SetY(-52);
            $this->SetTextColor(0, 0, 0);
            $this->SetFont('Arial', 'B', 8.5);
            $this->SetX($L);
            $this->Cell($W, 5, 'Goods received in good order and condition', 0, 1, 'L');
            $this->Ln(9);

            $col = ($W - 10) / 2;
            $y   = $this->GetY();
            foreach ([['Customer', $L], ['Driver', $L + $col + 10]] as [$who, $x]) {
                $this->Line($x, $y, $x + $col, $y);
                $this->SetXY($x, $y + 1);
                $this->SetFont('Arial', '', 7.5);
                $this->SetTextColor(110, 110, 110);
                $this->Cell($col, 4, $who . ' signature', 0, 0, 'L');

                $this->Line($x, $y + 12, $x + $col * 0.62, $y + 12);
                $this->Line($x + $col * 0.68, $y + 12, $x + $col, $y + 12);
                $this->SetXY($x, $y + 13);
                $this->Cell($col * 0.62, 4, 'Name', 0, 0, 'L');
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
        $this->Cell($W / 2, 4, $this->t(self::CO_LEGAL . '  |  ' . self::CO_TEL), 0, 0, 'L');
        $this->Cell($W / 2, 4, $this->t($this->docLabel) . '  |  Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'R');
        $this->SetTextColor(0, 0, 0);
    }

    // Small "Label   value" line used in the header meta box
    function metaRow($x, $label, $value, $wLabel = 26, $wValue = 44)
    {
        $this->SetX($x);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(110, 110, 110);
        $this->Cell($wLabel, 5.2, $this->t($label), 0, 0, 'L');
        $this->SetFont('Arial', 'B', 8.5);
        $this->SetTextColor(0, 0, 0);
        $this->Cell($wValue, 5.2, $this->t($value), 0, 1, 'R');
    }

    // Company block (left) + document title and meta rows (right); returns the Y below the rule
    function companyHeader($title, array $meta)
    {
        $L = self::L; $W = self::W;

        $this->SetXY($L, 15);
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(100, 8, $this->t(self::CO_NAME), 0, 1, 'L');
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(90, 90, 90);
        foreach ([self::CO_LEGAL, self::CO_REG, self::CO_ADDRESS, self::CO_ADDR2, self::CO_TEL, self::CO_EMAIL] as $line) {
            $this->SetX($L);
            $this->Cell(110, 4.2, $this->t($line), 0, 1, 'L');
        }
        $leftBottom = $this->GetY();
        $this->SetTextColor(0, 0, 0);

        $rx = $L + $W - 70;
        $this->SetXY($rx, 15);
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(70, 9, $this->t($title), 0, 1, 'R');
        $this->Ln(2);
        foreach ($meta as $label => $value) {
            if ($value !== '' && $value !== null) $this->metaRow($rx, $label, $value);
        }
        $rightBottom = $this->GetY();

        $y = max($leftBottom, $rightBottom) + 6;
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.5);
        $this->Line($L, $y, $L + $W, $y);
        $this->SetLineWidth(0.2);
        return $y + 6;
    }

    // Customer block (left) + optional detail rows (right); returns the Y below both
    function partyBlock($y, $label, array $c, $detailsLabel = '', array $details = [])
    {
        $L = self::L;
        $rx = $L + self::W - 70;

        $this->SetXY($L, $y);
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetTextColor(110, 110, 110);
        $this->Cell(100, 4, $this->t($label), 0, 1, 'L');
        $this->SetTextColor(0, 0, 0);
        $this->SetX($L);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(100, 6, $this->t($c['name']), 0, 1, 'L');
        $this->SetFont('Arial', '', 8.5);
        foreach ($c['lines'] as $line) {
            $this->SetX($L);
            $this->Cell(100, 4.4, $this->t($line), 0, 1, 'L');
        }
        $extra = array_filter([
            !empty($c['contact']) ? 'Attn: ' . $c['contact'] : '',
            !empty($c['phone'])   ? 'Tel: '  . $c['phone']   : '',
            !empty($c['vatNo'])   ? 'VAT No: ' . $c['vatNo'] : '',
        ]);
        $this->SetTextColor(90, 90, 90);
        foreach ($extra as $line) {
            $this->SetX($L);
            $this->Cell(100, 4.4, $this->t($line), 0, 1, 'L');
        }
        $this->SetTextColor(0, 0, 0);
        $leftBottom = $this->GetY();

        $details = array_filter($details, fn ($v) => $v !== '' && $v !== null);
        $this->SetXY($rx, $y);
        if ($details) {
            $this->SetFont('Arial', 'B', 7.5);
            $this->SetTextColor(110, 110, 110);
            $this->Cell(70, 4, $this->t($detailsLabel), 0, 1, 'L');
            $this->SetTextColor(0, 0, 0);
            foreach ($details as $k => $v) $this->metaRow($rx, $k, $v);
        }
        return max($leftBottom, $this->GetY()) + 8;
    }

    // Column header row of an items table
    function itemsHeader($cols)
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

    // Keep rows clear of the footer; repeat the header on a new page
    function rowBreak($cols, $bottomSpace)
    {
        if ($this->GetY() + 8 > $this->GetPageHeight() - $bottomSpace) {
            $label = $this->docLabel; $sig = $this->signatures;
            $this->AddPage();
            $this->docLabel = $label; $this->signatures = $sig;
            $this->SetY(20);
            $this->itemsHeader($cols);
        }
    }

    function notesBlock($notes)
    {
        if (empty($notes)) return;
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

    /* ── Page 1: tax invoice ─────────────────────────────────────────────── */
    function drawInvoice(array $doc)
    {
        $L = self::L; $W = self::W;

        $y = $this->companyHeader('TAX INVOICE', [
            'Invoice No'  => $doc['number'],
            'Date'        => $doc['date'],
            'Reference'   => $doc['reference'] ?? '',
            'Delivery No' => $doc['deliveryNo'] ?? '',
        ]);
        $y = $this->partyBlock($y, 'BILL TO', $doc['customer']);
        $this->SetY($y);

        $cols = [['#', 10, 'C'], ['Description', 90, 'L'], ['Qty', 20, 'R'], ['Unit price', 30, 'R'], ['Amount', 30, 'R']];
        $this->itemsHeader($cols);

        $n = 0;
        foreach ($doc['items'] as $it) {
            $this->rowBreak($cols, 30);
            $n++;
            $this->SetX($L);
            $this->SetTextColor(120, 120, 120);
            $this->Cell(10, 8, $n, 'B', 0, 'C');
            $this->SetTextColor(0, 0, 0);
            $this->Cell(90, 8, $this->t($it['name']), 'B', 0, 'L');
            $this->Cell(20, 8, $this->qtyText($it['qty']), 'B', 0, 'R');
            $this->Cell(30, 8, $this->money($it['price']), 'B', 0, 'R');
            $this->Cell(30, 8, $this->money($it['amount']), 'B', 1, 'R');
        }
        if ($n === 0) {
            $this->SetX($L);
            $this->SetTextColor(130, 130, 130);
            $this->Cell($W, 10, 'No items on this invoice', 'B', 1, 'C');
            $this->SetTextColor(0, 0, 0);
        }

        // Totals, right-aligned under the Amount column
        if ($this->GetY() + 30 > $this->GetPageHeight() - 30) {
            $this->AddPage(); $this->docLabel = 'Tax Invoice'; $this->signatures = false; $this->SetY(20);
        }
        $this->Ln(3);
        $tx = $L + $W - 80;
        foreach ([['Subtotal (excl. VAT)', $doc['excl']], ['VAT 15%', $doc['vat']]] as [$label, $val]) {
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
        $this->Cell(30, 9, $this->money($doc['total']), 'TB', 1, 'R', true);
        $this->SetLineWidth(0.2);

        $this->notesBlock($doc['notes'] ?? '');

        $this->Ln(8);
        $this->SetX($L);
        $this->SetFont('Arial', '', 8.5);
        $this->SetTextColor(90, 90, 90);
        $this->Cell($W, 5, 'Thank you for your business.', 0, 1, 'L');
        $this->SetTextColor(0, 0, 0);
    }

    /* ── Page 2: delivery note ───────────────────────────────────────────── */
    function drawDeliveryNote(array $doc)
    {
        $L = self::L; $W = self::W;

        $y = $this->companyHeader('DELIVERY NOTE', [
            'Delivery No' => $doc['number'],
            'Date'        => $doc['date'],
            'Invoice No'  => $doc['invoiceNo'] ?? '',
            'Reference'   => $doc['reference'] ?? '',
        ]);
        $y = $this->partyBlock($y, 'DELIVER TO', $doc['customer'], 'DELIVERY DETAILS', [
            'Driver'  => $doc['driver']  ?? '',
            'Vehicle' => $doc['vehicle'] ?? '',
        ]);
        $this->SetY($y);

        $cols = [['#', 10, 'C'], ['Description', 110, 'L'], ['Pack', 35, 'L'], ['Qty', 25, 'R']];
        $this->itemsHeader($cols);

        $n = 0; $totalQty = 0;
        foreach ($doc['items'] as $it) {
            $this->rowBreak($cols, 56);
            $n++;
            $totalQty += (float) $it['qty'];
            $this->SetX($L);
            $this->SetTextColor(120, 120, 120);
            $this->Cell(10, 8, $n, 'B', 0, 'C');
            $this->SetTextColor(0, 0, 0);
            $this->Cell(110, 8, $this->t($it['name']), 'B', 0, 'L');
            $this->Cell(35, 8, $this->t($it['pack']), 'B', 0, 'L');
            $this->SetFont('Arial', 'B', 9);
            $this->Cell(25, 8, $this->qtyText($it['qty']), 'B', 1, 'R');
            $this->SetFont('Arial', '', 9);
        }
        if ($n === 0) {
            $this->SetX($L);
            $this->SetTextColor(130, 130, 130);
            $this->Cell($W, 10, 'No items on this delivery', 'B', 1, 'C');
            $this->SetTextColor(0, 0, 0);
        }

        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetX($L + 120);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(35, 8, 'Total units', 'B', 0, 'L');
        $this->Cell(25, 8, $this->qtyText($totalQty), 'B', 1, 'R');
        $this->SetLineWidth(0.2);

        $this->notesBlock($doc['notes'] ?? '');
    }

// Load data
function LoadData($file)
{
	// Read file lines
	$lines = file($file);
	$data = array();
	foreach($lines as $line)
		$data[] = explode(';',trim($line));
	return $data;
}

// Simple table
function BasicTable($header, $data)
{
	// Header
	//$this->Cell(115);
	foreach($header as $col)
		$this->Cell(25,4,$col,1);
	$this->Ln();
	// Data
	
	foreach($data as $row)
	{
		//$this->Cell(115);
		foreach($row as $col)
			$this->Cell(25,4,$col,1);
		$this->Ln();
	}
	
}

// Simple table
function BasicTableWithSizes($header, $data, $sizeList)
{
    // Header
    //$this->Cell(115);
    $rowCnt = 0;
    foreach($header as $col) {
        
        $size = $sizeList[$rowCnt];
        $rowCnt = $rowCnt + 1;
        $this->Cell($size,4,$col,1);
    }
    $this->Ln();
        // Data
    foreach($data as $row)
    {
            //$this->Cell(115);
        $rowCnt = 1;   
        foreach($row as $col) {
                //$size = $sizeList[$rowCnt];
                $size = 20;
                $rowCnt = $rowCnt + 1;
                $this->Cell($size,4,$col,1);
        }
        $this->Ln();
    }
        
}


// Simple table
function newBasicTable($header)
{
	// Header
	foreach($header as $col) 
		$this->Cell(23.3,5,$col,1,false);
	$this->Ln();
	
	// Data
	for ($x = 0; $x <= 25; $x++) {
		for ($y = 0; $y <= 11; $y++) {
		   $this->Cell(23.3,4,'  ',1,false);
		}  
		$this->Ln();
	} 
}

// Better table
function ImprovedTable($header, $data)
{
	// Column widths
	$w = array(20, 20, 20, 20);
	// Header
	for($i=0;$i<count($header);$i++)
		$this->Cell($w[$i],7,$header[$i],1,0,'C');
	$this->Ln();
	// Data
	foreach($data as $row)
	{
		$this->Cell($w[0],6,$row[0],'LR');
		$this->Cell($w[1],6,$row[1],'LR');
		$this->Cell($w[2],6,number_format($row[2]),'LR',0,'R');
		$this->Cell($w[3],6,number_format($row[3]),'LR',0,'R');
		$this->Ln();
	}
	// Closing line
	$this->Cell(array_sum($w),0,'','T');
}

function Content($custval) {
    // Set cell background color to light gray
    $this->SetFillColor(211, 211, 211);

    // Add a cell with content
    $this->Cell(192, 12, 'Delivery Note To: ' . $custval, 1, 0, 'C', true); // The last parameter (true) is used to fill the cell with the specified color
}

// Colored table
function FancyTable($header, $data)
{
	// Colors, line width and bold font
	$this->SetFillColor(255,0,0);
	$this->SetTextColor(255);
	$this->SetDrawColor(128,0,0);
	$this->SetLineWidth(.3);
	$this->SetFont('','B');
	// Header
	$w = array(40, 35, 40, 45);
	for($i=0;$i<count($header);$i++)
		$this->Cell($w[$i],7,$header[$i],1,0,'C',true);
	$this->Ln();
	// Color and font restoration
	$this->SetFillColor(224,235,255);
	$this->SetTextColor(0);
	$this->SetFont('');
	// Data
	$fill = false;
	foreach($data as $row)
	{
		$this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
		$this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
		$this->Cell($w[2],6,number_format($row[2]),'LR',0,'R',$fill);
		$this->Cell($w[3],6,number_format($row[3]),'LR',0,'R',$fill);
		$this->Ln();
		$fill = !$fill;
	}
	// Closing line
	$this->Cell(array_sum($w),0,'','T');
}

static function uniqidRealVal($prefix = 11, $lenght = 13)
{
    // uniqid gives 13 chars, but you could adjust it to your needs.
    if (function_exists("random_bytes")) {
        $bytes = random_bytes(ceil($lenght / 2));
    } elseif (function_exists("openssl_random_pseudo_bytes")) {
        $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
    } else {
        throw new Exception("no cryptographically secure random function available");
    }
    $genStr = substr(bin2hex($bytes), 0, $lenght);
    
    return $prefix.$genStr;
}





public static function fpdfObjectsByProductId($productId){

    //$productId = $request->productId;
  
    $packageList = DB::table('packages')->where('productId',  $productId)->get();
    View::share('packageList', $packageList);

    return ($packageList);

 }






    /* ═══════════════════════════ Data + output ═══════════════════════════ */

    public static function delivery(Request $request)
    {
        $uniqu = trim((string) $request->query('data', ''), '"');

        $pdf = new InvoiceDelivery('P', 'mm', 'A4');
        $pdf->SetTitle('Invoice & Delivery Note');
        $pdf->SetMargins(self::L, 15, self::L);
        $pdf->AliasNbPages();

        // All delivery lines for this document share the same `uniqu`
        $lines = DB::table('deliveries')->where('uniqu', $uniqu)->get();
        $head  = $lines->first();

        if (!$head) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'Delivery ' . $uniqu . ' was not found.', 0, 1, 'L');
            return $pdf->Output('I', 'delivery.pdf');
        }

        $get = fn ($obj, $field) => ($obj && isset($obj->$field)) ? trim((string) $obj->$field) : '';

        /* ── Customer ─────────────────────────────────────────────────── */
        $cust = DB::table('customers')->where('id', $head->customerId)->first();

        $street = array_filter([
            $get($cust, 'sAAttentionLine1'), $get($cust, 'sAAttentionLine2'),
            $get($cust, 'sAAttentionLine3'), $get($cust, 'sAAttentionLine4'),
            implode(', ', array_filter([$get($cust, 'sACity'), $get($cust, 'sARegion')])),
            $get($cust, 'sAPostalCode'),
        ]);
        $postal = array_filter([
            $get($cust, 'pOAddressLine1'), $get($cust, 'pOAddressLine2'),
            $get($cust, 'pOAddressLine3'), $get($cust, 'pOAddressLine4'),
            implode(', ', array_filter([$get($cust, 'pOCity'), $get($cust, 'pORegion')])),
            $get($cust, 'pOPostalCode'),
        ]);
        $customer = [
            'name'    => $get($cust, 'legalName') ?: ($get($cust, 'name') ?: 'Unknown customer'),
            'lines'   => array_values($street ?: $postal),
            'vatNo'   => $get($cust, 'vatNo'),
            'contact' => trim($get($cust, 'contactPerson') . ' ' . $get($cust, 'contactPersonLastName')),
            'phone'   => $get($cust, 'phoneNumber') ?: $get($cust, 'mobileNumber'),
        ];

        /* ── Invoice (header + items) ─────────────────────────────────── */
        $invoiceNo = (int) $get($head, 'invoiceNo');
        $invoice   = ($invoiceNo > 0 && Schema::hasTable('invoices'))
            ? DB::table('invoices')->where('id', $invoiceNo)->first()
            : null;
        $invItems  = $invoiceNo > 0
            ? DB::table('invoice_items')->where('invoicesId', $invoiceNo)->get()
            : collect();

        /* ── Names for products and packs, looked up once ─────────────── */
        $productIds = $lines->pluck('productId')->merge($invItems->pluck('productId'))->filter()->unique();
        $productNames = DB::table('porducts')->whereIn('id', $productIds)->pluck('name', 'id');
        $unitNames    = DB::table('types')->whereIn('id', $lines->pluck('unitId')->filter()->unique())->pluck('name', 'id');

        // Same totals as before: totalPrice summed, VAT summed, excl = total - VAT
        $total = (float) $invItems->sum('totalPrice');
        $vat   = (float) $invItems->sum('vatAmnt');

        $created   = $get($invoice, 'created_at') ?: $get($head, 'created_at');
        $date      = ($created ? Carbon::parse($created) : Carbon::today())->format('d M Y');
        $reference = $get($invoice, 'reference') ?: $get($head, 'reference');
        $notes     = $get($head, 'other') ?: $get($head, 'notes');

        /* ── Page 1: tax invoice (only when the delivery has one) ─────── */
        if ($invoiceNo > 0) {
            $pdf->SetAutoPageBreak(true, 30);
            $pdf->AddPage();
            $pdf->docLabel = 'Tax Invoice';
            $pdf->signatures = false;

            $pdf->drawInvoice([
                'number'     => $invoiceNo,
                'date'       => $date,
                'reference'  => $reference,
                'deliveryNo' => $get($head, 'id'),
                'customer'   => $customer,
                'items'      => $invItems->map(fn ($it) => [
                    'name'   => $productNames[$it->productId] ?? ('Product #' . $it->productId),
                    'qty'    => $it->quantity,
                    'price'  => $it->price,
                    'amount' => $it->totalPrice,
                ])->all(),
                'excl'  => $total - $vat,
                'vat'   => $vat,
                'total' => $total,
                'notes' => $notes,
            ]);
        }

        /* ── Page 2: delivery note ────────────────────────────────────── */
        $driver = $get($head, 'driver');
        if ($driver !== '' && ctype_digit($driver) && Schema::hasTable('employees')) {
            $driver = DB::table('employees')->where('id', $driver)->value('name') ?? $driver;
        }
        $vehicle = $get($head, 'vehicleReg');
        if ($vehicle !== '' && ctype_digit($vehicle)) {
            $vehicle = DB::table('types')->where('id', $vehicle)->value('name') ?? $vehicle;
        }

        $pdf->SetAutoPageBreak(true, 56);
        $pdf->AddPage();
        $pdf->docLabel = 'Delivery Note';
        $pdf->signatures = true;

        $pdf->drawDeliveryNote([
            'number'    => $get($head, 'id') ?: $uniqu,
            'date'      => $date,
            'invoiceNo' => $invoiceNo > 0 ? $invoiceNo : '',
            'reference' => $reference,
            'customer'  => $customer,
            'driver'    => $driver,
            'vehicle'   => $vehicle,
            'notes'     => $notes,
            'items'     => $lines->map(fn ($d) => [
                'name' => $productNames[$d->productId] ?? ('Product #' . $d->productId),
                'pack' => $unitNames[$d->unitId] ?? '',
                'qty'  => $d->qnt,
            ])->all(),
        ]);

        return $pdf->Output('I', 'invoice-delivery-' . $uniqu . '.pdf');
    }
}