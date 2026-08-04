<?php


//require_once 'App/Library/Barcode/php-barcode.php';
require_once 'Barcode/php-barcode.php';
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\JobCard;
use App\Models\Jobcarditem;
use App\Models\TbDeliveryItem;
use App\Models\TbDelivery;
use App\Models\Recipe;
use App\Models\Package;
use App\Models\Porduct;
use App\Models\DocumentAudit;
use Carbon\Carbon;

use Illuminate\Support\Facades\View;





class ReactDelivery extends Fpdf
{
    
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
    
    
function Header()
{
		// Page header
		global $title;
		
		$this->SetFont('Arial','B',15);
		$w = $this->GetStringWidth($title)+6;
		$this->SetX((210-$w)/2);
		//$this->SetDrawColor(0,80,180);
		//$this->SetFillColor(230,230,0);
		//$this->SetTextColor(220,50,50);
		//$this->SetLineWidth(1);
		//$this->Cell($w,9,$title,1,1,'C',true);
		$this->Cell($w,9,$title,0);
		$this->Ln(10);
		// Save ordinate
		$this->y0 = $this->GetY();
}

function Footer()
{
    // Only add footer on first page
    if ($this->PageNo() == 1) {
        $this->SetY(-50);
        $this->SetFont('Arial','',9);
        
        // Signature section
        $this->Cell(95, 6, 'Customer Signature:', 0, 0, 'L');
        $this->Cell(95, 6, 'Driver Signature:', 0, 1, 'L');
        
        $this->Ln(2);
        
        // Signature lines
        $this->Cell(95, 0, '', 'T', 0, 'L');
        $this->Cell(95, 0, '', 'T', 1, 'L');
        
        $this->Ln(3);
        
        // Date and Name fields
        $this->SetFont('Arial','',8);
        $this->Cell(47.5, 5, 'Date: _______________', 0, 0, 'L');
        $this->Cell(47.5, 5, 'Name: _______________', 0, 0, 'L');
        $this->Cell(47.5, 5, 'Date: _______________', 0, 0, 'L');
        $this->Cell(47.5, 5, 'Name: _______________', 0, 1, 'L');
    }
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

public static function deliveryreact(Request $request,$data = []) { 

    //  echo "<pre>";
    // print_r('WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWOW');
    // exit;

    $jobcarditemId = $request->get('jobcarditemId');
    //$jsonData = $request->input('data');
    $jsonData = $request->json()->all();
   // $specificValue = $jsonData['value'];
    //$dataValue = $request->query('deliveryId');
    $deliveryId = $data['deliveryId'] ?? null;
   // $dataValue = $request->get('deliveryId');

    //dd($deliveryId);

    $originalString = $deliveryId ;
    $cleanedString = trim($originalString, '"');


    

    
   

    $customers = DB::table('customers')->get();

    $custKeys = array();
    foreach ( $customers as $customer) {
        $custKeys[ $customer->id] =  $customer;
    } 

    $unittypes = DB::table('types')->get();

    $unitKeys = array();
    foreach (  $unittypes as $unit) {
        $unitKeys[ $unit->id] =  $unit;
    }
   
    $productId = $request->get('productId');

    $recipeList=DB::table('recipes')->where('productId',  $productId)->get();

    $porduct = Porduct::find($productId);
    View::share('porduct', $porduct); 

    $products=DB::table('porducts')->get();
    $productKeys = array();
    foreach ($products as $product) {
        $productKeys[$product->id] = $product;
    }

    $product =DB::table('porducts')->where('id',$productId )->first();
    View::share('porduct', $porduct);

    //echo "<pre>";
    //print_r($product);
    //exit;


     // Log::info("/////////// ------------------------------------------- : ".$recipe);
      $packageList=DB::table('packages')->where('productId',  $productId)->get();

      $package = Package::find($productId);
      View::share('package',$package);
      View::share('packages',$packageList);
      
   
    

    
    $OdersLists=DB::table('orders')->get();


    // $deliveries = DB::table('tb_deliveries')
    //                 ->where('id',$cleanedString  )
    //                 ->select('productId', 'unitId', 'qnt','customerId')
    //                 ->get();

    $customerInfo = DB::table('tb_deliveries')
    ->where('id',$cleanedString  )
    ->first();

    //dd($customerInfo);

   

    if ($customerInfo) {
        $customerName = $customerInfo->customerId;
        $customerdriver = $customerInfo->driver;
        $customeraddress = $customerInfo->addressId;
        $customerInvoice = $customerInfo->invoiceNo;
    
    }




   
    
  
  

     $pdf = new ReactDelivery();
     $title = 'Delivery NOTE ';
     $pdf->SetTitle($title);
     $pdf->SetAutoPageBreak(true, 55);
     $pdf->AddPage();
  
    
    
     $x = 50; // barcode center
     
     $y = 7; // barcode center
     
     $height = 9; // barcode height in 1D ; module size in 2D
     
     $width = 0.5; // barcode height in 1D ; not use in 2D
     
     $angle = 0; // rotation in degrees
     
     
     
     $type = 'code128';
     $black = '000000'; // color in hexa
     
     // -------------------------------------------------- //
     //            ALLOCATE FPDF RESSOURCE
     // -------------------------------------------------- //
     
     
     //$data = $pdf->digit_to_fpdf_renderer( $pdf, $black, $x, $y, $angle, $type, array( 'code' => $code ), $width, $height );
     $pdf->SetFont('Helvetica','',12);
   
     $pdf->SetFont('Arial','',7.5);
 
     
  
     
 
    
 
    
  
 
  
   
 
  $tmpY = $pdf->getY();
  
    
     
     $barcodeVal = InvoiceRpt::uniqidRealVal();
 
     
    // $pdf->Ln();
     //$pdf->Cell(16,5,'Comment ',1);
     //$pdf->Cell(175,5,$other,1);
     
     
     $pdf->SetFont('Arial','',27);
 
     // Packaging List ....
    
     
     $pdf->Cell(40, 5, 'SAILING PACKAGING', 0, 0, 'L', false);
     $pdf->Ln();
     $pdf->SetFont('Arial','',7);
     $pdf->Cell(40, 10, 'SAILING PACKAGING (PTY) LTD.', 0, 0, 'L', false);
     $pdf->Ln();
     $pdf->Ln();
    
     $pdf->SetFont('Arial','',7);
     $pdf->Cell(40, 4, 'VAT no 4910276528 | Reg no 2016/367235/07', 0, 0, 'L', false);
     $pdf->Ln();
     $pdf->Cell(40, 4, 'Shop 3, Corner Smits & Refinary Road,South Germiston, Germiston', 0, 0, 'L', false);
     $pdf->Ln();
     $pdf->Cell(40, 4, 'Tel +27 61 731 6406', 0, 0, 'L', false);
     $pdf->Ln();
     $pdf->Cell(40, 4, 'Email sales@sailingpackaging.com / accounts@sailingpackaging.com', 0, 0, 'L', false);
     $pdf->Ln();
     $pdf->Cell(40, 4, 'Address : 13 Smits str ,Germiston South,', 0, 0, 'L', false);
     $pdf->Ln();
     $pdf->Ln();
     $pdf->Ln();
     $pdf->Ln();
     $pdf->Ln();
     $pdf->Ln();
     
     $cust = $custKeys[$customerInfo->customerId];
     $custval = $cust->name;
 
     $pdf->SetFont('Arial','B',15);
    // $pdf->Cell(192,12,'Delivery Note  To : '.$custval,1,0,'C');
    $pdf->SetFillColor(230, 230, 230);
     $pdf->Cell(192, 12, 'Delivery Note To: ' . $custval, 1, 0, 'C', 1);
     $pdf->Ln();
     $pdf->SetFont('Arial','',7);
     $id = 134;
 
     $todayDate = Carbon::today();
     $formattedDate = $todayDate->format('Y-m-d');
     
     $headerPackingProduct = array(' Date : '.$formattedDate,'Invoice no :','Reference :');
     $packagingProductPersist = new InvoiceRpt();
     //$dataPP = $packagingProductPersist;
     //$packageArray = $packagingProductPersist->fpdfObjectsByProductId($productId);
     // echo "<pre>";
     // print_r($packageList);
     $packageArray = array ();
     $sizesList = array(64, 64, 64,);
     $pdf->BasicTableWithSizes($headerPackingProduct, $packageArray ,$sizesList);
     $jobcardLists = DB::table('job_cards')->get(['created_at','unitId','productId','qnt','startDate']);
     //$pdf->Cell(50,4, $recipeList->count(),1);
     $pdf->Ln();
     $pdf->Ln();
     $pdf->Ln();
     $pdf->SetFont('Arial','B',12);
     
     $pdf->Cell(90,10, 'Description', 0, 0, 'L', false);
     $pdf->Cell(70,10, 'Package', 0, 0, 'L', false);
     $pdf->Cell(70,10, 'Qty', 0, 0, 'L', false);


     $pdf->Ln();
     
     $itemsLists = DB::table('tb_delivery_items')->where('deliveryId',$deliveryId)->get();
     // echo "<pre>";
     // print_r(  $customerInvoice);
     // exit;
     $pdf->SetFont('Arial','',7);
     foreach($itemsLists as $itemsList){
 
      
         $productId = $productKeys[$itemsList->productId];
         $val = ''.$productId->name;

         $unitId = $unitKeys[$itemsList->unitId];
         $valunit = ''.$unitId->name;
 
        
 
      
 
         $pdf->Cell(90,7, ''. $val , 0, 0, 'L', false);
         $pdf->Cell(70,7, ''.$valunit, 0, 0, 'L', false);
         $pdf->Cell(70,7, ''.$itemsList->quantity.'', 0, 0, 'L', false);

         $pdf->Ln();
 
     }
     $pdf->Ln();
 

 
   
     $pdf->Ln();
     $pdf->Ln();
     $pdf->Ln();
     
   
     $pdf->Ln();
     $pdf->Ln();
     $pdf->Ln();
     
   
     $pdf->Ln();
     $pdf->Ln();
     $pdf->Ln();
     
   
     $pdf->Ln();
     $pdf->Ln();
     $pdf->Ln();

    


    
    

    //////////////////////////////////////////////////////////////////////////////////////////////////////////

 
    
    
    
    $rollGenerate = 'no';
    //if ($processObj <> null) {
    //    $rollGenerate = $processObj->getRollGenerate();
    //}
    if ($rollGenerate == 'yes') {
        
        $pdf->Cell(25,4,'Weight per Roll (kg):',1);
        $pdf->Cell(20,4,$rollWeight,1,'R');
        $pdf->Cell(25,4,'Number of Rolls:',1);
        $pdf->Cell(20,4,$noRolls,1,'R');
        $pdf->Cell(25,4,'Length per roll (meters):',1);
        $pdf->Cell(20,4,$lengthPerRoll,1,'R');
        $pdf->Cell(16,4,'Last roll length:',1);
        $pdf->Cell(20,4,$lastRollLength,1,'R');  
        $pdf->Ln();
        $pdf->Ln();
    }
    
    $pdf->Ln();
    $pdf->Ln();  
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->AddPage('O');
    $pdf->SetFont('Arial','',15);
    //$pdf->Text(30, 15, $code);
    $pdf->Cell(279.6,20,'Orders',1,0,'C');
  //  $pdf->Cell(23.3,5,'  ',1,false);
    $pdf->SetFont('Arial','',7.5);
    $pdf->Ln();
    //$pdf->SetXY(0,0);
    
    // Column headings
    $header1 = array('Date', 'Operator', 'Job Card No.', 'Roll No.', "Kg's", 'Width', 'Length', 'Qty P/Roll', 'Mic', 'Qty/Roll', 'Bale Size', 'Scrap - KG');
    $pdf->newBasicTable($header1);
    
    //$pdf->AddPage();
    //$pdf->FancyTable($header,$data);
    $pdf->Output();
}

}



?>