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

use Illuminate\Support\Facades\View;





class AllJobCardList extends Fpdf
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

public static function fpdfObjectsByProductId($productId){

    //$productId = $request->productId;
  
    $packageList = DB::table('packages')->where('productId',  $productId)->get();
    View::share('packageList', $packageList);

    return ($packageList);

 }

public static function myMethod(Request $request) { 

    $jobcarditemId = $request->get('jobcarditemId');
   

    $types = DB::table('types')->get();

    $typesKeys = array();
    foreach ($types as $type) {
        $typesKeys[$type->id] = $type;
    } 
   
    $productId = $request->get('productId');
    //echo "<pre>";
    //print_r( $productId);
    //exit;
    $recipeList=DB::table('recipes')->where('productId',  $productId)->get();
    //Log::info("list ------------------------------------------- : ".$recipeList); 
    //$recipe = Recipe::find($productId);
    //View::share('recipe',$recipe);
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
      Log::info("list ------------------------------------------- : ".$packageList);
      $package = Package::find($productId);
      View::share('package',$package);
      View::share('packages',$packageList);
      Log::info("/////////// ------------------------------------------- : ".$package);
   
    Log::info("Product id------------------------------------------- : ".$productId);

    Log::info("Ahhhhhhhhhhhhhhhhh ------------------------------------------- : ".$jobcarditemId);
    $jobcarditemList=DB::table('jobcarditems')->where('id',  $jobcarditemId)->get();
    Log::info("jpcard loist ------------------------------------------- : ".$jobcarditemList); 
    $jobcarditem = Jobcarditem::find($jobcarditemId);
    View::share('jobcarditem', $jobcarditem);
    Log::info("/////////// ------------------------------------------- : ".$jobcarditem); 

    //$processTypesList = View::share('processtypes');
    //$processtype = $processTypesList[$jobcarditem->processId]; 
    
    
   
    Log::info("Packagelist ------------------------------------------- : ".$package);
    Log::info("Recipelist ------------------------------------------- : ".$recipeList);
    $pdf = new AllJobCardList();
    $title = ' Job Card Item Print';
    $pdf->SetTitle($title);
    
    
    $pdf->AddPage('O');
    //$pdf=new FPDF();
   // $pdf->AddPage('O');

    //$fontSize = 10;
    
    //$marge = 10; // between barcode and hri in pixel
    
    $x = 50; // barcode center
    
    $y = 7; // barcode center
    
    $height = 9; // barcode height in 1D ; module size in 2D
    
    $width = 0.5; // barcode height in 1D ; not use in 2D
    
    $angle = 0; // rotation in degrees
    
    $code = 0; // barcode, of course ; )
    //$code = $barcode;
    
    //$type = 'ean13';
    $type = 'code128';
    $black = '000000'; // color in hexa
    
    // -------------------------------------------------- //
    //            ALLOCATE FPDF RESSOURCE
    // -------------------------------------------------- //
    
    
    $data = $pdf->digit_to_fpdf_renderer( $pdf, $black, $x, $y, $angle, $type, array( 'code' => $code ), $width, $height );
    $pdf->SetFont('Helvetica','',12);
    $pdf->Text(30, 15, $code);
    
   // $pdf->Ln();

    //$pdf->Cell(191,6,'Job Card Item',1,0,'C');
   // $pdf->Ln();
    $pdf->SetFont('Arial','',7.5);
    $id = ''.$jobcarditemId;
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
  




    
    //$tmMaterialType =$typesKeys[$porduct->materialTypeId];
    //$val = ''.$tmMaterialType ->name;
    //$material = ''.$val;
    $material  = 0;
    $color = 0;
   // $tmpProductType = $porducts[$jobcarditem->productId];

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
    
    // $pdf->Cell(15,4,'item Id ',1);

    // $pdf->Cell(18,4,'Jobacard Id',1);

    // $pdf->Cell(18,4,'Product Id',1);

    // $pdf->Cell(20,4,'Name',1);

    // $pdf->Cell(18,4,'Process',1);

    // $pdf->Cell(24,4,'Barcode',1);

    

    // $pdf->Cell(16,4,'Material',1);

    // $pdf->Cell(16,4,'Colour',1);

 

    // $pdf->Cell(18,4,'Quantity',1);

    // $pdf->Cell(20,4,'Quantity Type',1);
    // $pdf->Cell(8,4,'state',1);

    // $pdf->Ln();
    // $pdf->SetFont('Arial','',6);
    //$pdf->Cell(15,4,$id,1);

    //$pdf->Cell(18,4,$jobCardId,1);

  //  $pdf->Cell(18,4,$productId,1,);
   // $pdf->Cell(20,4,$name,1);
 

    //$processtype = $processtypes[$jobcarditem->processId]; 
   // $pdf->Cell(18,4,$processId,1);
   
   // $pdf->Cell(24,4,$barcode,1);

   
  
   // $pdf->Cell(16,4,$material,1,);

   // $pdf->Cell(16,4,$color,1,);
   
 

    //$pdf->Cell(18,4,$qnt,1,);
   
   // $pdf->Cell(20,4,$qntId,1);

   // $pdf->Cell(8,4,$stateId,1,);
  
   // $pdf->Ln();
    //$pdf->Ln();
 $tmpY = $pdf->getY();
    //$pdf->BasicTable($header,$data);
    //$pdf->AddPage();
    //$pdf->SetY($tmpY);
    //$pdf->ImprovedTable($header,$data);
    
    //$pdf->Cell(40,4,'',0);
    
    //$processPers = new processPersist();
    //$processObj = $processPers->fetchObj($processId);
    
    
    //if ($processObj <> null) {
    //    $tmpProcessName = $processObj->getName();
    //}
    
    $barcodeVal = AllJobCardList::uniqidRealVal();

    
   // $pdf->Ln();
    //$pdf->Cell(16,5,'Comment ',1);
    //$pdf->Cell(175,5,$other,1);
    
    
    $pdf->SetFont('Arial','',18);
    // Packaging List ....
    $pdf->Cell(276,12,'Jobcard List',1,0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',9);
    $id = ''.$jobcarditemId;
    
    $headerPackingProduct = array('Created','Description','Product','Unit','Quantity','Start Date');
    $packagingProductPersist = new AllJobCardList();
    //$dataPP = $packagingProductPersist;
    //$packageArray = $packagingProductPersist->fpdfObjectsByProductId($productId);
    // echo "<pre>";
    // print_r($packageList);
    $packageArray = array ();
    $sizesList = array(46, 46, 46, 46, 46, 46);
    $pdf->BasicTableWithSizes($headerPackingProduct, $packageArray ,$sizesList);
    $jobcardLists = DB::table('job_cards')->get(['created_at','unitId','productId','qnt','startDate']);
    //$pdf->Cell(50,4, $recipeList->count(),1);
    $pdf->SetFont('Arial','',7);
    foreach($jobcardLists as $jobcardList)
	{
        $i=0;
        foreach($jobcardList as $val) 
        {
          if ($i==2)  
          {
            $unitType = $typesKeys[$val]; 
            $val = ''.$unitType->name;
          }
          else if ($i==3)  
          {
            $tmpProduct = $productKeys[$val]; 
            $val = ''.$tmpProduct->name;
          }
          $pdf->Cell(46,7,$val,1);
          $i = $i + 1;
        }
        $pdf->Ln();
    } 

    // for ($x = 0; $x < 10; $x++) {
    //     for ($y = 0; $y < 6; $y++) {
    //        $pdf->Cell(46,5,' ',1);
    //     }
    //     $pdf->Ln();
    // }
    
    $pdf->Ln();
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
    
    
    $pdf->SetFont('Arial','',12);
    $pdf->Text(30, 15, $code);
    $pdf->Cell(279.6,12,'Production',1,0,'C');
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