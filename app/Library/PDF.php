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
use Illuminate\Support\Facades\View;





class PDF extends Fpdf
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
		$this->Cell(16.3,5,$col,1);
	$this->Ln();
	
	// Data
	for ($x = 0; $x <= 11; $x++) {
		for ($y = 0; $y <= 11; $y++) {
		   $this->Cell(16.3,5,'  ',1);
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

    $jobCardId = $request->get('jobCardId');
    $jobcard = JobCard::find($jobCardId);
    View::share('jobcard', $jobcard);
   

    $pdf = new PDF();
    $title = ' Job Card Print';
    $pdf->SetTitle($title);
    
    
    $pdf->AddPage();
    
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
    
    $id = ''.$jobCardId;
    $qnt =''.$jobcard->qnt;
    $qntUnit = ''.$jobcard->qntUnit;
    $startDate = ''.$jobcard->created_at;
    $endDate = ''.$jobcard->updated_at;
    $tmpWidth = ''.$jobcard->name;
    $tmpLength = ''.$jobcard->name;
    $tmpThickness = ''.$jobcard->name;
    $tmpColour = ''.$jobcard->name;
    $tmpMaterial = ''.$jobcard->name;
    $employee = ''.$jobcard->name;
    $machine =''.$jobcard->name;
    $barcode =''.$jobcard->name;
    $jobCardGroupProductName = ''.$jobcard->name;
    $customerJobCardGroup = ''.$jobcard->name;
    $other = ''.$jobcard->name;
    $dataPP = ''.$jobcard->name;
    $dataIngredientList = ''.$jobcard->name;
    $processObj = ''.$jobcard->name;
    $rollWeight = ''.$jobcard->name;
    $processObj = ''.$jobcard->name;
    $rollGenerate = ''.$jobcard->name;
    $noRolls = ''.$jobcard->name;
    $lengthPerRoll = ''.$jobcard->name;
    $lastRollLength = ''.$jobcard->name;
    $processObj = ''.$jobcard->name;
    
    $pdf->Cell(12,4,'ID : ',1);

    $pdf->Cell(18,4,'Quantity: ',1);

    $pdf->Cell(18,4,'Unit: ',1);

    $pdf->Cell(22,4,'Start Date:',1);

    $pdf->Cell(22,4,'End Date:',1);

    $pdf->Cell(18,4,'Width(mm):',1);

    $pdf->Cell(18,4,'Length(mm):',1);

    $pdf->Cell(19,4,'Thickness(micron)',1);

    $pdf->Cell(18,4,'Material Type: ',1);

    $pdf->Cell(18,4,'Colour: ',1);

    $pdf->Ln();
    
    $pdf->Cell(12,4,$id,1);
    
    $pdf->Cell(18,4,$qnt,1);
 
    $pdf->Cell(18,4,$qntUnit,1);
    
    $pdf->Cell(22,4,$startDate,1);
   
    $pdf->Cell(22,4,$endDate,1);

    $pdf->Cell(18,4,$tmpWidth,1,);
  
    $pdf->Cell(18,4,$tmpLength,1,);
   
    $pdf->Cell(19,4,$tmpThickness,1,);

    $pdf->Cell(18,4,$tmpMaterial,1,);
   
    $pdf->Cell(18,4,$tmpColour,1);
  
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();

    $pdf->Cell(16,4,'Operator:',1);
    $pdf->Cell(20,4,$employee,1,'R');
    $pdf->Cell(16,4,'Packer:',1);
    $pdf->Cell(20,4,'',1,'R');
    $pdf->Cell(16,4,'Machine:',1);
    $pdf->Cell(20,4,$machine,1,'R');
    $pdf->Cell(16,4,'Shift b: ',1);
    $pdf->Cell(20,4,1,'R');
    $pdf->Cell(18,4,'Barcode: ',1);
    $pdf->Cell(26,4,$barcode,1,'R');
    $pdf->Ln();
    
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
    
    $barcodeVal = PDF::uniqidRealVal();

    $pdf->Cell(16,4,'Process: ',1);
    $pdf->Cell(20,4,$tmpProcessName,1,'R');
    $pdf->Cell(16,4,'Product b : ',1);
    $pdf->Cell(60,4,$barcodeVal,1,'R');
    $pdf->Cell(16,4,'Customer: ',1);
    $pdf->Cell(60,4,$customerJobCardGroup,1,'R');
    $pdf->Ln();
    $pdf->Cell(16,4,'Comment: ',1);
    $pdf->Cell(172,4,$other,1);
    
    $pdf->Ln();
    $pdf->Ln();
    
    // Packaging List ....
    
    $headerPackingProduct = array('Id', 'Outer Packaging', 'Min Weight', 'Avg Weight', 'Max Weight', 'Unit Type');
    //$packagingProductPersist = new packagingProductPersist();
    //$dataPP = $packagingProductPersist->fpdfObjectsByProductId($productId);
    $sizesList = array(25, 50, 25, 25, 25, 25);
    //$pdf->BasicTableWithSizes($headerPackingProduct, $dataPP,$sizesList);
    
    
    
    
    $pdf->Ln();
    $pdf->Ln();
    //$ingredientJobCardPersist = new ingredientJobCardPersist();
    //$dataIngredientList = $ingredientJobCardPersist->fetchFpdfObjectsByJobCardToAddQnt($id);
    
    $pdf->Cell(195,4,'Material Mix :  '.$qnt." ".$qntUnit,1,0,'');
    $pdf->Ln();
    $ingredListHeader = array( 'Id', 'Product', 'Qnt Allocation', 'Qnt Allocation Type',	'Quantity',	'Quantity Usage', 'Qnt Unit');
    //$ingredientJobCardPersist = new ingredientJobCardPersist();
    //$dataIngredientList = $ingredientJobCardPersist->fetchFpdfObjectsByJobCardToAddQnt($id);
    
    $sizesList = array(25, 50, 25, 25, 25, 25, 20);
    //$pdf->BasicTableWithSizes($ingredListHeader, $dataIngredientList, $sizesList);
    
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
    
    
    
    $pdf->Cell(195.5,4,'Production',1,0,'C');
    
    $pdf->Ln();
    //$pdf->SetXY(0,0);
    
    // Column headings
    $header1 = array('Date', 'Operator', 'Job Card No.', 'Roll No.', "Kg's", 'Width', 'Length', 'Qty P/Roll', 'Mic', 'Qty/Roll', 'Bale Size', 'Scrap - KG');
    $pdf->newBasicTable($header1);
    
    //$pdf->AddPage();
    //$pdf->FancyTable($header,$data);
    $pdf->Output();
}


   
public static function fpdfObjectsByProductId($productId){

    //$productId = $request->productId;
  
    $porduct = Package::select('*')->where('productId', $productId)->get();
   
  

//   echo "<pre>";
//     print_r( $porduct);
//     exit;
View::share('package', $porduct);

    return ($porduct);

 }

public static function Method(Request $request) {

    $jobcarditemId = $request->get('jobcarditemId');
   

    $productId = $request->get('productId');
    $recipeList=DB::table('recipes')->where('productId',  $productId)->get();
      Log::info("list ------------------------------------------- : ".$recipeList); 
      $recipe = Recipe::find($productId);
      View::share('recipe',$recipe);
      


      Log::info("/////////// ------------------------------------------- : ".$recipe);
      $productId = $request->get('productId');
      $packageList=DB::table('packages')->where('productId',  $productId)->get();
      Log::info("list ------------------------------------------- : ".$packageList);
      $package = Package::find($productId);
      View::share('package',$package);
      View::share('packages',$packageList);
      Log::info("/////////// ------------------------------------------- : ".$package);
   
    //   echo "<pre>";
    //   print_r($package);
    //   exit;

    Log::info("Product id------------------------------------------- : ".$productId);
    $jobcarditemId = $request->get('jobcarditemId');

    Log::info("Ahhhhhhhhhhhhhhhhh ------------------------------------------- : ".$jobcarditemId);
    $jobcarditemList=DB::table('jobcarditems')->where('id',  $jobcarditemId)->get();
    Log::info("jpcard loist ------------------------------------------- : ".$jobcarditemList); 
    $jobcarditem = Jobcarditem::find($jobcarditemId);
    View::share('jobcarditem', $jobcarditem);
    Log::info("/////////// ------------------------------------------- : ".$jobcarditem); 

   
   
    Log::info("Packagelist ------------------------------------------- : ".$package);
    Log::info("Recipelist ------------------------------------------- : ".$recipeList);
   

    $pdf = new PDF();
    $title = ' Job Card Print';
    $pdf->SetTitle($title);
    
    
    $pdf->AddPage();

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
    $pdf->SetFont('Helvetica','',12);
    $pdf->Text(30, 15, $code);
    
    //$len = $pdf->GetStringWidth( $data[ 'hri' ] );
    
    //Barcode::rotate( - $len / 2, ( $data[ 'height' ] / 2 ) + $fontSize + $marge, $angle, $xt, $yt );
    
    //$pdf->TextWithRotation( $x + $xt, $y + $yt, $data[ 'hri' ], $angle );
    
    
    
    // Column headings
    //$header = array('Country', 'Capital', 'Area (sq km)', 'Pop. (thousands)');
    // Data loading
    //$data = $pdf->LoadData('countries.txt');
    $pdf->Ln();

    $pdf->Cell(191,6,'Job Card Item',1,0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',7.5);
    $id = ''.$jobcarditemId;
    $jobCardId = ''.$jobcarditem->jobCardId;
    $productId = ''.$jobcarditem->productId;
    $name =''.$jobcarditem->name;
    $processId = ''.$jobcarditem->processId ;
    $barcode = ''.$jobcarditem->barcode;
    $other = ''.$jobcarditem->other;
    $stateId = ''.$jobcarditem->stateId;  
    $qnt = ''.$jobcarditem->qnt;
    $qntId = ''.$jobcarditem->qntId;
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
    
    $pdf->Cell(15,4,'item Id : ',1);

    $pdf->Cell(18,4,'Jobacard Id: ',1);

    $pdf->Cell(18,4,'Product Id:',1);

    $pdf->Cell(20,4,'Name: ',1);

    $pdf->Cell(18,4,'Process:',1);

    $pdf->Cell(22,4,'Barcode:',1);

    $pdf->Cell(20,4,'state:',1);

    $pdf->Cell(22,4,'Other:',1);

 

    $pdf->Cell(18,4,'Quantity:',1);

    $pdf->Cell(20,4,'Quantity Type: ',1);

    $pdf->Ln();
    $pdf->SetFont('Arial','',6);
    $pdf->Cell(15,4,$id,1);

    $pdf->Cell(18,4,$jobCardId,1);

    $pdf->Cell(18,4,$productId,1,);
    
    $pdf->Cell(20,4,$name,1);
 

    
    $pdf->Cell(18,4,$processId,1);
   
    $pdf->Cell(22,4,$barcode,1);

    $pdf->Cell(20,4,$stateId,1,);
  
    $pdf->Cell(22,4,$other,1,);
   
 

    $pdf->Cell(18,4,$qnt,1,);
   
    $pdf->Cell(20,4,$qntId,1);
  
    $pdf->Ln();
    $pdf->Ln();
    
    
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
    
    $barcodeVal = PDF::uniqidRealVal();

    
    $pdf->Ln();
    $pdf->Cell(16,8,'Comment: ',1);
    $pdf->Cell(175,8,$other,1);
    
    $pdf->Ln();
    $pdf->Ln();
    
    $pdf->SetFont('Arial','',12);
    // Packaging List ....
    $pdf->Cell(191,6,'Packaging List',1,0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',7.5);
    $id = ''.$jobcarditemId;
    $headerPackingProduct = array('Id', 'Outer Packaging', 'Min Weight', 'Avg Weight', 'Max Weight', 'Unit Type');
    $packagingProductPersist = new PDF();
    //$dataPP = $packagingProductPersist;
    $packageArray = $packagingProductPersist->fpdfObjectsByProductId($productId);
    // echo "<pre>";
    // print_r($packageList);
    
    //$packageArray = array (1 => array ('a','b','c','d','e','f'), 2 => array (7,8,9,10,11,12));
    // echo "<pre>";
    // print_r( $i);
    // exit;


    $sizesList = array(21, 50, 30, 30, 30, 30);
    $pdf->BasicTableWithSizes($headerPackingProduct, $packageArray, $sizesList);
    

    
    
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    //$ingredientJobCardPersist = new ingredientJobCardPersist();
    //$dataIngredientList = $ingredientJobCardPersist->fetchFpdfObjectsByJobCardToAddQnt($id);
    $pdf->SetFont('Arial','',12);
    $pdf->Text(30, 15, $code);
    $pdf->Cell(191,6,'Recipe List',1,0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',7.5);
    $ingredListHeader = array( 'Id', 'Product', 'Qnt Allocation', 'Qnt Allocation Type',	'Quantity',	'Quantity Usage', 'Qnt Unit');
    //$ingredientJobCardPersist = new ingredientJobCardPersist();
    //$dataIngredientList = $ingredientJobCardPersist->fetchFpdfObjectsByJobCardToAddQnt($id);

    $dataIngredientList = array (1 => array ('a','b','c','d','e','f','g'), 2 => array (7,8,9,10,11,12,13));
    //$dataIngredientList =  $recipeList;
    // echo "<pre>";
    // print_r($recipeList);
    // exit;
    $sizesList = array(21,50,24,24,24,24,24);
    $pdf->BasicTableWithSizes($ingredListHeader, $dataIngredientList, $sizesList);
    
    $pdf->Ln();
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
    $pdf->Cell(195.5,6,'Production',1,0,'C');
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


