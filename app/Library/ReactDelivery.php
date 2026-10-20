<?php

//require_once 'App/Library/Barcode/php-barcode.php';
require_once 'Barcode/php-barcode.php';
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;


class ReactDelivery extends Fpdf
{
    /* ── Company details printed on the document ─────────────────────────── */
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

    /** Set before AddPage(); used by Footer() */
    public $docLabel = 'Delivery Note';

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

    // Page header is drawn by drawDeliveryNote(), so FPDF's automatic header stays empty
    function Header() {}

    // Signatures + company line on every page
    function Footer()
    {
        $L = self::L; $W = self::W;

        // Receipt / signature block
        $this->SetY(-52);
        $this->SetDrawColor(180, 180, 180);
        $this->SetLineWidth(0.2);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', 'B', 8.5);
        $this->SetX($L);
        $this->Cell($W, 5, 'Goods received in good order and condition', 0, 1, 'L');
        $this->Ln(9);

        $col = ($W - 10) / 2;      // two columns with a 10mm gap
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

    // Column header row of the items table
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
    }

    /**
     * Draw the whole delivery note.
     *
     * $doc = [
     *   'number' => 'DN-000123', 'date' => '15 Oct 2026', 'invoiceNo' => '…', 'reference' => '…',
     *   'customer' => ['name' => '…', 'lines' => ['…', '…'], 'vatNo' => '…', 'contact' => '…', 'phone' => '…'],
     *   'driver' => '…', 'vehicle' => '…', 'notes' => '…',
     *   'items' => [['name' => '…', 'pack' => '…', 'qty' => 12], …],
     * ]
     */
    function drawDeliveryNote(array $doc)
    {
        $L = self::L; $W = self::W;

        /* ── Company (left) ─────────────────────────────────────────────── */
        $this->SetXY($L, 15);
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(100, 8, $this->t(self::CO_NAME), 0, 1, 'L');
        $this->SetX($L);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(90, 90, 90);
        foreach ([self::CO_LEGAL, self::CO_REG, self::CO_ADDRESS, self::CO_ADDR2, self::CO_TEL, self::CO_EMAIL] as $line) {
            $this->SetX($L);
            $this->Cell(110, 4.2, $this->t($line), 0, 1, 'L');
        }
        $leftBottom = $this->GetY();
        $this->SetTextColor(0, 0, 0);

        /* ── Document title + meta (right) ─────────────────────────────── */
        $rx = $L + $W - 70;
        $this->SetXY($rx, 15);
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(70, 9, 'DELIVERY NOTE', 0, 1, 'R');
        $this->Ln(2);
        $this->metaRow($rx, 'Delivery No', $doc['number']);
        $this->metaRow($rx, 'Date',        $doc['date']);
        if (!empty($doc['invoiceNo'])) $this->metaRow($rx, 'Invoice No', $doc['invoiceNo']);
        if (!empty($doc['reference'])) $this->metaRow($rx, 'Reference',  $doc['reference']);
        $rightBottom = $this->GetY();

        $y = max($leftBottom, $rightBottom) + 6;
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.5);
        $this->Line($L, $y, $L + $W, $y);
        $this->SetLineWidth(0.2);

        /* ── Deliver to (left) / Delivery details (right) ──────────────── */
        $y += 6;
        $c  = $doc['customer'];
        $this->SetXY($L, $y);
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetTextColor(110, 110, 110);
        $this->Cell(100, 4, 'DELIVER TO', 0, 1, 'L');
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
        if ($extra) {
            $this->SetTextColor(90, 90, 90);
            foreach ($extra as $line) {
                $this->SetX($L);
                $this->Cell(100, 4.4, $this->t($line), 0, 1, 'L');
            }
            $this->SetTextColor(0, 0, 0);
        }
        $leftBottom = $this->GetY();

        $details = array_filter([
            'Driver'  => $doc['driver']  ?? '',
            'Vehicle' => $doc['vehicle'] ?? '',
        ]);
        $this->SetXY($rx, $y);
        if ($details) {
            $this->SetFont('Arial', 'B', 7.5);
            $this->SetTextColor(110, 110, 110);
            $this->Cell(70, 4, 'DELIVERY DETAILS', 0, 1, 'L');
            $this->SetTextColor(0, 0, 0);
            foreach ($details as $label => $value) {
                $this->metaRow($rx, $label, $value);
            }
        }
        $rightBottom = $this->GetY();

        /* ── Items ─────────────────────────────────────────────────────── */
        $this->SetY(max($leftBottom, $rightBottom) + 8);
        $cols = [['#', 10, 'C'], ['Description', 110, 'L'], ['Pack', 35, 'L'], ['Qty', 25, 'R']];
        $this->itemsHeader($cols);

        $this->SetFont('Arial', '', 9);
        $this->SetDrawColor(215, 215, 215);
        $n = 0; $totalQty = 0;
        foreach ($doc['items'] as $it) {
            // new page if this row would run into the signature block
            if ($this->GetY() + 8 > $this->GetPageHeight() - 56) {
                $this->AddPage();
                $this->SetY(20);
                $this->itemsHeader($cols);
                $this->SetFont('Arial', '', 9);
                $this->SetDrawColor(215, 215, 215);
            }
            $n++;
            $qty = (float) $it['qty'];
            $totalQty += $qty;
            $this->SetX($L);
            $this->SetTextColor(120, 120, 120);
            $this->Cell(10, 8, $n, 'B', 0, 'C');
            $this->SetTextColor(0, 0, 0);
            $this->Cell(110, 8, $this->t($it['name']), 'B', 0, 'L');
            $this->Cell(35, 8, $this->t($it['pack']), 'B', 0, 'L');
            $this->SetFont('Arial', 'B', 9);
            $this->Cell(25, 8, $this->qtyText($qty), 'B', 1, 'R');
            $this->SetFont('Arial', '', 9);
        }

        if ($n === 0) {
            $this->SetX($L);
            $this->SetTextColor(130, 130, 130);
            $this->Cell($W, 10, 'No items on this delivery', 'B', 1, 'C');
            $this->SetTextColor(0, 0, 0);
        }

        // Total units
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetX($L + 120);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(35, 8, 'Total units', 'B', 0, 'L');
        $this->Cell(25, 8, $this->qtyText($totalQty), 'B', 1, 'R');
        $this->SetLineWidth(0.2);

        /* ── Notes ─────────────────────────────────────────────────────── */
        if (!empty($doc['notes'])) {
            $this->Ln(6);
            $this->SetX($L);
            $this->SetFont('Arial', 'B', 7.5);
            $this->SetTextColor(110, 110, 110);
            $this->Cell($W, 4, 'NOTES', 0, 1, 'L');
            $this->SetTextColor(0, 0, 0);
            $this->SetX($L);
            $this->SetFont('Arial', '', 8.5);
            $this->MultiCell($W, 4.5, $this->t($doc['notes']), 0, 'L');
        }
    }

    function qtyText($q)
    {
        $q = (float) $q;
        return floor($q) == $q ? number_format($q, 0, '.', ' ') : number_format($q, 2, '.', ' ');
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

    public static function deliveryreact(Request $request, $data = [])
    {
        $deliveryId = trim((string) ($data['deliveryId'] ?? $request->get('deliveryId', '')), '"');

        $delivery = DB::table('tb_deliveries')->where('id', $deliveryId)->first();

        $pdf = new ReactDelivery('P', 'mm', 'A4');
        $pdf->SetTitle('Delivery Note ' . $deliveryId);
        $pdf->SetMargins(self::L, 15, self::L);
        $pdf->AliasNbPages();
        $pdf->SetAutoPageBreak(true, 56);     // keep clear of the signature block
        $pdf->AddPage();

        if (!$delivery) {
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'Delivery note ' . $deliveryId . ' was not found.', 0, 1, 'L');
            return $pdf->Output('I', 'delivery-note.pdf');
        }

        $get = fn ($obj, $field) => ($obj && isset($obj->$field)) ? trim((string) $obj->$field) : '';

        /* ── Customer ─────────────────────────────────────────────────── */
        $cust = DB::table('customers')->where('id', $delivery->customerId)->first();

        // Street address first, postal as the fallback
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

        /* ── Items: product + pack names in two lookups ───────────────── */
        $items = DB::table('tb_delivery_items')->where('deliveryId', $deliveryId)->get();

        $productNames = DB::table('porducts')
            ->whereIn('id', $items->pluck('productId')->filter()->unique())
            ->pluck('name', 'id');
        $unitNames = DB::table('types')
            ->whereIn('id', $items->pluck('unitId')->filter()->unique())
            ->pluck('name', 'id');

        $rows = $items->map(fn ($it) => [
            'name' => $productNames[$it->productId] ?? ('Product #' . $it->productId),
            'pack' => $unitNames[$it->unitId] ?? '',
            'qty'  => $it->quantity,
        ])->all();

        /* ── Driver / vehicle: names when they are ids ────────────────── */
        $driver = $get($delivery, 'driver');
        if ($driver !== '' && ctype_digit($driver) && Schema::hasTable('employees')) {
            $driver = DB::table('employees')->where('id', $driver)->value('name') ?? $driver;
        }
        $vehicle = $get($delivery, 'vehicleReg');
        if ($vehicle !== '' && ctype_digit($vehicle)) {
            $vehicle = DB::table('types')->where('id', $vehicle)->value('name') ?? $vehicle;
        }

        $created = $get($delivery, 'created_at');

        $pdf->drawDeliveryNote([
            'number'    => 'DN-' . str_pad($delivery->id, 6, '0', STR_PAD_LEFT),
            'date'      => ($created ? Carbon::parse($created) : Carbon::today())->format('d M Y'),
            'invoiceNo' => $get($delivery, 'invoiceNo'),
            'reference' => $get($delivery, 'reference'),
            'customer'  => $customer,
            'driver'    => $driver,
            'vehicle'   => $vehicle,
            'notes'     => $get($delivery, 'other') ?: $get($delivery, 'notes'),
            'items'     => $rows,
        ]);

        return $pdf->Output('I', 'delivery-note-' . $delivery->id . '.pdf');
    }
}