<?php


//require_once 'App/Library/Barcode/php-barcode.php';
require_once 'Barcode/php-barcode.php';
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\JobCard;
use App\Models\Jobcarditem;
use App\Models\Recipe;
use App\Models\Package;
use App\Models\Porduct;
use App\Models\DocumentAudit;
use App\Models\UserDetails;
use App\Models\Type;
use App\Models\Job_schedule;
use App\Models\Clocking;
use Illuminate\Support\Facades\View;






class ClockingList extends Fpdf
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



public static function myMethod(Request $request) { 


    $clockings =DB::table('clockings')->get();

    $pdf = new ClockingList();
    $title = ' Job Card Item Print';
    $pdf->SetTitle($title);
    
    
    $pdf->AddPage('O');
    //$pdf=new FPDF();
   // $pdf->AddPage('O');

    //$fontSize = 10;
    
    //$marge = 10; // between barcode and hri in pixel
    
  //  $x = 50; // barcode center
    
   // $y = 7; // barcode center
    
   // $height = 9; // barcode height in 1D ; module size in 2D
    
   // $width = 0.5; // barcode height in 1D ; not use in 2D
    
   // $angle = 0; // rotation in degrees
    
    ///$code = 0; // barcode, of course ; )
    //$code = $barcode;
    
    //$type = 'ean13';
    //$type = 'code128';
   // $black = '000000'; // color in hexa
    
    // -------------------------------------------------- //
    //            ALLOCATE FPDF RESSOURCE
    // -------------------------------------------------- //
    
    
  ///  $data = $pdf->digit_to_fpdf_renderer( $pdf, $black, $x, $y, $angle, $type, array( 'code' => $code ), $width, $height );
    $pdf->SetFont('Helvetica','',12);
    //$pdf->Text(30, 15, $code);
    
   // $pdf->Ln();

    //$pdf->Cell(191,6,'Job Card Item',1,0,'C');
   // $pdf->Ln();
    $pdf->SetFont('Arial','',7.5);
   // $id = ''.$jobcarditemId;
    $jobCardId = 0;
    $productType = 0;
    //echo "<pre>";
    //print_r( $productType);
   // $val = ''.$productType->name;
    //$productType = ''.$val;
    //echo "<pre>";
    //print_r( $val);
    //exit;
    //$productId = ''.$jobcarditem->productId;
    $name =0;
    $processId = 0;
    $barcode = 0;
    $other = 0;
    $stateId = 0;  
    $qnt = 0;
    $unitType = 0; 
    $qntId = 0;
  




    

    $material  = 0;
    $color = 0;
   

    $jobCardGroupProductName = '0';
    $customerJobCardGroup = '0';
    $dataPP = '0';
    $dataIngredientList = '0';
    $processObj = '0';
    $processObj = '0';
    $tmpColour = '0';
    $tmpMaterial = '0';
    $employee = '0';
    $machine ='0';
    $jobCardGroupProductName = '0';
    $customerJobCardGroup = '0';
    $other = '0';
    $dataPP = '0';
    $dataIngredientList = '0';
    $processObj = '0';
    $rollWeight = '0';
    $processObj = '0';
    $rollGenerate = '0';
    $noRolls = '0';
    $lengthPerRoll = '0';
    $lastRollLength = '0';
    $processObj = '0';
    $qntUnit = '0';

 $tmpY = $pdf->getY();

    
    $barcodeVal = ClockingList::uniqidRealVal();

    

    
    $pdf->SetFont('Arial','',18);
   
    $pdf->Cell(279.8,16,'Processed Clocking Info',1,0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','B',7);
    
    
    $headerPackingProduct = array('Name','Date','Clock In-Out','Shift','Job Description','ClockInTime','ClockOutTime','Arrival','Depature','name','days');
    $packagingProductPersist = new ClockingList();

    $packageArray = array ();
    $sizesList = array(40, 20.2, 25.2,15.2, 30, 22.2,22.2,31.2,31.2,21.2,21.2,21.2);
    $pdf->BasicTableWithSizes($headerPackingProduct, $packageArray ,$sizesList);
    $clockings = DB::table('clockings')
    ->select('name', 'date', 'day', 'clockInTime', 'clockOutTime', 'shift')
    ->groupBy('name','id','date','clockInTime','shift','clockOutTime','day','created_at','updated_at')
    ->get();
    
    $pdf->SetFont('Arial','',7);

    $prvname = null; 

    foreach ($clockings as $clocking) {

        if ($clocking->name != $prvname) {

            $job_description = UserDetails::where('name',$clocking->name)->value('userPosition');
            


            $type = Type::where('id', $job_description)->value('name');

            $shift =   Type::where('name',$clocking->shift )->value('id');
            $description = trim( $job_description);
         
            $day   =   Type::where('name',$clocking->day )->value('id');

       
            


            $jobSchedule = Job_schedule::where('job_description', $job_description)
                                        ->where('shift', $shift)
                                        ->where('day', $day)
                                        ->first();

         
        if ($jobSchedule) {

            $timestamp = strtotime($jobSchedule->start_time);
            $timestamp2 = strtotime($jobSchedule->end_time);
            $start = date("H:i", $timestamp);
            $end = date("H:i", $timestamp2);
        
          
   





   
    if (!function_exists('calculateMinutesDifference')) {
       
        function calculateMinutesDifference($scheduledTime, $actualTime) {
            $scheduledDateTime = DateTime::createFromFormat('H:i', $scheduledTime);
            $actualDateTime = DateTime::createFromFormat('H:i', $actualTime);
    
           
            if ($scheduledDateTime === false || $actualDateTime === false) {
                return "--";
            }
    
            $interval = $actualDateTime->diff($scheduledDateTime);
    
            return $interval->format('%r%i'); 
        }
    }



    





   



} else {

    $start = '--';
    $end = '--';
    
 
}
            
$pdf->Cell(237.5, 6, '', 1);

$days = Clocking::whereNotNull('clockInTime')->whereNotNull('clockOutTime')->where('name',$clocking->name)->count() ;
$pdf->SetFont('Arial','B',9);

$words = explode(' ', $clocking->name);


$firstwordname = isset($words[0]) ? $words[0] : '';

$pdf->Cell(21.2, 6, $firstwordname, 1, 0, 'C');
            

            if($days  > 0){
                $pdf->SetFillColor(200, 255, 200); 
            }else{
                $pdf->SetFillColor(255, 200, 200); 
            }

            




            $pdf->Cell(21.2, 6, $days.' days' , 1,0,'C',1);
          
            $pdf->SetFont('Arial','',7);
            $pdf->Ln();
        }

        $headerPackingProduct;

        $pdf->Cell(40, 6, $clocking->name, 1);
        $pdf->Cell(20.2, 6, date("D M j", strtotime($clocking->date)), 1);
        $pdf->Cell(25.2, 6, $clocking->clockInTime.' - '.$clocking->clockOutTime, 1);
        $pdf->Cell(15.2, 6, $clocking->shift, 1);
        $pdf->Cell(30.2, 6, $type, 1);
        $pdf->Cell(22.2, 6, $start, 1);
        $pdf->Cell(22.2, 6, $end, 1);
    
        $scheduledTime = $clocking->clockInTime;
        $actualTime =  $start;
        $minutesDifference = calculateMinutesDifference($scheduledTime, $actualTime);
        if ($minutesDifference) {
            $minutesDifference = (float)$minutesDifference; 
            $minutesDifference = ($minutesDifference == 0) ?  "--" :
            $minutesDifference = ($minutesDifference >= 0) ? $minutesDifference . " minutes later" : 
            abs($minutesDifference) . " minutes earlier";

        }

        if ($minutesDifference >= 0) {
            $pdf->SetFillColor(255, 255, 255); 
        } elseif ($minutesDifference < 0) {
            $pdf->SetFillColor(255, 255, 255);  
        } else {
            $pdf->SetFillColor(255, 255, 255);  
        }
        
        $pdf->Cell(31.2, 6, $minutesDifference, 1, 0, 'C', 1);

        $scheduledTime = $clocking->clockOutTime;
        $actualTime =  $end;
        $minutesDifferencedepature = calculateMinutesDifference($scheduledTime, $actualTime);
        if ($minutesDifferencedepature) {
            $minutesDifferencedepature = (float)$minutesDifferencedepature; 
            $$minutesDifferencedepature = ($minutesDifferencedepature == 0) ?  "--" :
                $minutesDifferencedepature = ($minutesDifferencedepature >= 0) ? $minutesDifferencedepature . " minutes later" : 
            abs($minutesDifferencedepature) . " minutes earlier";

        }
        $pdf->Cell(31.2, 6,$minutesDifferencedepature, 1);


        if($clocking->clockOutTime){

            $daycount = 1;

        }else{
            $daycount = 0;
        }

        $pdf->Cell(21.2, 6, $daycount, 1, 0, 'C');
        $pdf->Cell(21.2, 6, '', 1, 0, 'C');

    
        

        $pdf->Ln();
    
     
    
        
        $prvname = $clocking->name;
    }


    
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $rollGenerate = 'no';
 
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
    
    

    
  
    $pdf->Output();
}

}



?>