<?php

//require_once 'App/Library/Barcode/php-barcode.php';
require_once 'Barcode/php-barcode.php';
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\JobCard;
use App\Models\Jobcarditem;
use Illuminate\Support\Facades\View;





class JobCardsRpt extends Fpdf
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
        $rowCnt = 0;   
        foreach($row as $col) {
                $size = $sizeList[$rowCnt];
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
		$this->Cell(23.3,5,$col,1);
	$this->Ln();
	
	// Data
	for ($x = 0; $x <= 11; $x++) {
		for ($y = 0; $y <= 11; $y++) {
		   $this->Cell(23.3,5,'  ',1);
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



public static function myMethod($jobcardList) {

     
    $types = DB::table('types')->get();

    $typesKeys = array();
    foreach ($types as $type) {
        $typesKeys[$type->id] = $type;
    } 

    $products=DB::table('porducts')->get();
    $productKeys = array();
    foreach ($products as $product) {
        $productKeys[$product->id] = $product;
    }

    $customers=DB::table('customers')->get();
    $customerKeys = array();
    foreach ($customers as $customer) {
        $customerKeys[$customer->id] = $customer;
    }

    $pdf = new JobCardsRpt();
    $title = ' Job Card Print';
    $pdf->SetTitle($title);
    
    
    $pdf->AddPage('O');
    
    //$fontSize = 10;
    
    //$marge = 10; // between barcode and hri in pixel
    
    $x = 50; // barcode center
    
    $y = 7; // barcode center
    
    $height = 9; // barcode height in 1D ; module size in 2D
    
    $width = 0.5; // barcode height in 1D ; not use in 2D
    
    $angle = 0; // rotation in degrees
    
    $code = '2234567890121'; // barcode, of course ; )
    //$code = $barcode;
    
    //$type = 'ean13';
    $type = 'code128';
    $black = '000000'; // color in hexa
    
    // -------------------------------------------------- //
    //            ALLOCATE FPDF RESSOURCE
    // -------------------------------------------------- //
    
    
    $data = $pdf->digit_to_fpdf_renderer( $pdf, $black, $x, $y, $angle, $type, array( 'code' => $code ), $width, $height );
    $pdf->SetFont('Arial','',9);
    $pdf->Text(30, 15, $code);
    
    //$len = $pdf->GetStringWidth( $data[ 'hri' ] );
    
    //Barcode::rotate( - $len / 2, ( $data[ 'height' ] / 2 ) + $fontSize + $marge, $angle, $xt, $yt );
    
    //$pdf->TextWithRotation( $x + $xt, $y + $yt, $data[ 'hri' ], $angle );
    
    
    
    // Column headings
    //$header = array('Country', 'Capital', 'Area (sq km)', 'Pop. (thousands)');
    // Data loading
    //$data = $pdf->LoadData('countries.txt');
    $pdf->SetFont('Arial','',6);
    
    //$pdf->AddPage();
    
    //$pdf->SetFont('Arial','',5);
    
    $id = '1';
    $qnt ='1';
    $qntUnit = '1';
    $startDate = '1';
    $endDate = '1';
    $tmpWidth = '1';
    $tmpLength = '1';
    $tmpThickness = '1';
    $tmpColour = '1';
    $tmpMaterial = '1';
    $employee = '1';
    $machine ='1';
    $barcode ='1';
    $jobCardGroupProductName = '1';
    $customerJobCardGroup = '1';
    $other = '1';
    $dataPP = '1';
    $dataIngredientList = '1';
    $processObj = '1';
    $rollWeight = '1';
    $processObj = '1';
    $rollGenerate = '1';
    $noRolls = '1';
    $lengthPerRoll = '1';
    $lastRollLength = '1';
    $processObj = '1';
    
    //$pdf->Cell(16,4,'Packer:',1);
    //$pdf->Cell(20,4,'',1,'R');
    //$pdf->Cell(16,4,'Machine:',1);
    //$pdf->Cell(20,4,$machine,1,'R');
    //$pdf->Cell(16,4,'Shift b: ',1);
    //$pdf->Cell(20,4,1,'R');
    //$pdf->Cell(18,4,'Barcode: ',1);
    //$pdf->Cell(26,4,$barcode,1,'R');
    
    $tmpY = $pdf->getY();
    //$pdf->BasicTable($header,$data);
    //$pdf->AddPage();
    //$pdf->SetY($tmpY);
    //$pdf->ImprovedTable($header,$data);
    
    //$pdf->Cell(40,4,'',0);
    
    //$processPers = new processPersist();
    //$processObj = $processPers->fetchObj($processId);
    
    $tmpProcessName = " Hello ";
    //if ($processObj <> null) {
    //    $tmpProcessName = $processObj->getName();
    //}
    
    $barcodeVal = JobCardsRpt::uniqidRealVal();

    //$pdf->Cell(16,4,'Process: ',1);
    //$pdf->Cell(20,4,$tmpProcessName,1,'R');
    //$pdf->Cell(16,4,'Product b : ',1);
    //$pdf->Cell(60,4,$barcodeVal,1,'R');
    //$pdf->Cell(16,4,'Customer: ',1);
    //$pdf->Cell(60,4,$customerJobCardGroup,1,'R');
    //$pdf->Cell(16,4,'Comment: ',1);
    //$pdf->Cell(172,4,$other,1);
    $pdf->SetFont('Arial','',15);
    //$id = ''.$jobcard;
    $pdf->Cell(280,12,'Job Card List',1,0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',8);
    $headerPackingProduct = array('Id','Created_at', 'State', 'Start Date', 'Customer', 'Product', 'Qnt', 'UnitType','Oustanding');
    $packagingProductPersist = new JobCardsRpt();
    //$dataPP = $packagingProductPersist;
    //$packageArray = $packagingProductPersist->fpdfObjectsByProductId($productId);
    // echo "<pre>";
    // print_r($packageList);
    $jobcardArray = array ();
    $sizesList = array(10,30,20,20,70,70,20,20,20,20);
    $pdf->BasicTableWithSizes($headerPackingProduct, $jobcardArray ,$sizesList);
    //$jobcardList = DB::table('job_cards')->get(['id','refNo','startDate', 'customerId', 'productId','qnt','unitId']);
    $pdf->SetFont('Arial','',6);
    // echo "<pre>";
    // print_r($jobcardList);
    // exit;
    foreach($jobcardList as $jobcard)
	{
        $jobCardId = $jobcard->id;
        $productId = $jobcard->productId;
    

        $jobCardItemId = DB::table('jobcarditems')
        ->where('jobCardId', $jobCardId)
        ->where('productId', $productId)
        ->value('id');

        $outstanding = DB::table('jobcarditems')
       ->selectRaw("qnt - (select sum(qnt) as qnt from `productionitems` where jobcardItemId = '".$jobCardItemId."') as outstanding")
       ->where('id', $jobCardItemId)
       ->value('outstanding'); 

        // echo "<pre>";
        // print_r($outstanding);
        // exit;

        $jobcard->oustanding = $outstanding;

        if( $jobcard->oustanding > 0)
        {   
            JobCard::where('id',$jobCardId )
                   ->update(['stateId' => 62]);
        }
        elseif($jobcard->oustanding < 0){
            JobCard::where('id',$jobCardId )
            ->update(['stateId' => 45]);

        }


        

        

        $i=0;
        foreach($jobcard as $val) 
        {
            if ($i==0) {
                $pdf->Cell(10,6,$val,1);
            } 
            else if ($i==4)
            {
              $tmpCustomer = $customerKeys[$val]; 
              $val = ''.$tmpCustomer->name;
              $pdf->Cell(70,6,$val,1);
            }
            else if ($i==3)
            {
             
              $pdf->Cell(20,6,$val,1);
            }
            else if ($i==1)
            {
             
              $pdf->Cell(30,6,$val,1);
            }
            else if ($i==6)
            {
             
              $pdf->Cell(20,6,$val,1);
            }
            else if ($i==5)
            {
              $tmpProduct = $productKeys[$val]; 
              $val = ''.$tmpProduct->name;
              $pdf->Cell(70,6,$val,1);
            }
            else if ($i==6)  
            {
              $pdf->Cell(30,6,$val,1);
            }
            else if ($i==7)  
            {
              $unitType = $typesKeys[$val]; 
              $val = ''.$unitType->name;
              $pdf->Cell(20,6,$val,1);
            }
            else if ($i==2)  
            {
              $state = $typesKeys[$val]; 
              $val = ''.$state->name;
              $pdf->Cell(20,6,$val,1);
            }
            else if ($i==8)  
            {
                $val= $jobcard->oustanding = $outstanding;
                $pdf->Cell(20,6,$val,1);
            }
            // else { 
            //   $pdf->Cell(40,6,$val,1);
            // }
          $i = $i + 1;
        }
        $pdf->Ln();
    } 
    for ($x = 0; $x < 9; $x++) {
        for ($y = 0; $y < 7; $y++) {
            if ($y==0) {
                $pdf->Cell(30,6,' ',1);
            }
            else if ($y==3) {
                $pdf->Cell(60,6,' ',1);
            }
            else if ($y==1) {
                $pdf->Cell(20,6,' ',1);
            }
             else if ($y==4) {
                $pdf->Cell(60,6,' ',1);
            }
            else if ($y==6) {
                $pdf->Cell(20,6,' ',1);
            }
            else if ($y==5) {
                $pdf->Cell(30,6,' ',1);
            }   
            else { 
                $pdf->Cell(40,6,' ',1);
            } 
        }
        $pdf->Ln();
    }
    $pdf->Ln();
    $pdf->Ln();
    
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
     
    $pdf->SetFont('Arial','',15);
    //$id = ''.$jobcard;
    $pdf->Cell(279.6,12,'Production',1,0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',6);
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

