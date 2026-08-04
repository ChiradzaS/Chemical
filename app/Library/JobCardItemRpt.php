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





class JobCardItemRpt extends Fpdf
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
function newBasicTable($header,$pdf)
{
	// Header
	foreach($header as $col) 
		$this->Cell(23,5,$col,1);
	$this->Ln();
	
	// Data
	for ($x = 0; $x <= 11; $x++) {
		for ($y = 0; $y <= 11; $y++) {
		   $this->Cell(23,5,'  ',1);
		}  
		$this->Ln();

        if ($pdf->GetY() > $pdf->PageBreakTrigger) {
            return;  // Stop printing further content
        }
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




    $id =  $request->job;

   
    $url = env('APP_URL1');
  
 
    
  
  
    
    $response = Http::get($url.'/qryjobcarditempdf/qry1');
     
     
    if ($response->successful()) {
   
     $jsonResponse = json_decode( $response, true);
    
     View::share('packageList', $packageList);
  
    } else {
        
        dd('Sorry , there an error with your request');
    
    }

    

 }

public static function generate(Request $request) { 



    $url = env('APP_URL1');

    $jobcardId = $request->get('jobCardId');
  
 
    //$response = Http::get($url.'/qryjobcarditempdf/qry1?jobCardId='.$jobcardId);

    $response = Http::retry(3, 3000)
    ->get($url . '/qryjobcarditempdf/qry1?jobCardId=' . $jobcardId);

     

     
    if ($response->successful()) {
   
     $jobcard = json_decode( $response, true);

     //dd($jobcard);


    // return view($jobcard);
  
    } else {
        
        dd('Sorry , there an error with your request');
    
    }




    $url = env('APP_URL1');

    $jobcardId = $request->get('jobCardId');
  
 
    // $response = Http::get($url.'/qryjobcarditempdf/qry1?jobCardId='.$jobcardId);

    $response = Http::retry(5, 1000) // 3 attempts, 1000ms delay
    ->get($url . '/qryjobcarditempdf/qry1', [
        'jobCardId' => $jobcardId
    ]);

     

     
    if ($response->successful()) {
   
     $jobcard = json_decode( $response, true);

     //dd($jobcard);


    // return view($jobcard);
  
    } else {
        
        dd('Sorry , there an error with your request');
    
    }


  


  
   

  

$items =DB::table('job_cards')->where('id',$jobcardId )->get();

foreach (  $items as  $item){
    $wproductId =  $item->productId;

    // echo "<pre>";
    // print_r($product );
    // exit;
}



$workInpogress = DB::table('porducts')->where('id',$wproductId  )->get();

foreach ( $workInpogress as  $workInpogres){
    $workInp =  $workInpogres->workInProgressId;
 
    // echo "<pre>";
    // print_r($workInp);
    // exit;
}

$wbagtypes = DB::table('porducts')->where('id',$workInp  )->get();

foreach ( $wbagtypes as  $wbagtype){
    $workInprogressbagtype =   $wbagtype->bagType;

    // echo "<pre>";
    // print_r($workInprogressbagtype);
    // exit;
}





    

    $types = DB::table('types')->get();


    $response5 = Http::get($url.'/qryjobcarditempdf/qry6');
     

     
    if ($response5->successful()) {
   
     $types = json_decode( $response5, true);

  
    } else {
        
        dd('Sorry , there an error with your request');
    
    }





    $typesKeys = array();
    foreach ($types as $type) {
        $typesKeys[$type['id']] = $type;
    } 
   
    $productId = $request->get('productId');
    



    $recipeList=DB::table('recipes')->where('productId',  $productId)->get();
    //Log::info("list ------------------------------------------- : ".$recipeList); 
    //$recipe = Recipe::find($productId);
    //View::share('recipe',$recipe);


 



    $response = Http::get($url.'/qryjobcarditempdf/qry3?productId='.$productId);
     

     
    if ($response->successful()) {
   
     $porduct = json_decode( $response, true);

  
    } else {
        
        dd('Sorry , there an error with your request');
    
    }




    
    $response2 = Http::get($url.'/qryjobcarditempdf/qry4');
     

     
    if ($response2->successful()) {
   
     $products = json_decode( $response2, true);

  
    } else {
        
        dd('Sorry , there an error with your request');
    
    }

    //---------------------------------------------------------------------------------

    $productKeys = array();
    foreach ($products as $product) {
        $productKeys[$product['id']] = $product;
    }

    $customers=DB::table('customers')->get();

        
    $response3 = Http::get($url.'/qryjobcarditempdf/qry5');
     

     
    if ($response3->successful()) {
   
     $customers = json_decode( $response3, true);

  
    } else {
        
        dd('Sorry , there an error with your request');
    
    }

    $customerKeys = array();
    foreach ($customers as $customer) {
        $customerKeys[$customer['id']] = $customer;
    }

    //=============================================================================

    //$product =DB::table('porducts')->where('id',$productId )->first();
   // View::share('porduct', $porduct);

    $response = Http::get($url.'/qryjobcarditempdf/qry7?productId='.$productId);
     

     
    if ($response->successful()) {
   
     $product = json_decode( $response, true);

  
    } else {
        
        dd('Sorry , there an error with your request');
    
    }

    
     
      $packageList=DB::table('packages')->where('productId',  $productId)->get();
      
      $package = Package::find($productId);
      View::share('package',$package);
      View::share('packages',$packageList);
      
   
    

   
    //$jobcarditemList=DB::table('jobcarditems')->where('id',  $jobcarditemId)->get();
     
    // $jobcarditem = Jobcarditem::find($jobcarditemId);
    // View::share('jobcarditem', $jobcarditem);


    
    $jobcarditemId = $request->get('jobcarditemId');
  
 
    $response1 = Http::get($url.'/qryjobcarditempdf/qry2?jobcarditemId='.$jobcarditemId);
     

     
    if ($response1->successful()) {
   
     $jobcarditem = json_decode($response1, true);

     


  
    } else {
        
        dd('Sorry , there an error with your request');
    
    }

    // echo "<pre>";
    // print_r($jobcarditem );
    // exit;
     

    //$processTypesList = View::share('processtypes');
    //$processtype = $processTypesList[$jobcarditem->processId]; 
    
    
    

   
    Log::info("Packagelist ------------------------------------------- : ".$package);
    Log::info("Recipelist ------------------------------------------- : ".$recipeList);
    $pdf = new JobCardItemRpt();
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

    $barcode = $jobcarditem['id'];

    // Get the length of the barcode
    $barcode_length = strlen($barcode);

    // Add leading zeros to make the barcode 11 digits long
    $padded_barcode = str_pad($barcode, 11, "0", STR_PAD_LEFT);
    
    $code = $padded_barcode ; // barcode, of course ; )
    //$code = $barcode;
    
    //$type = 'ean13';
    $type = 'code128';
    $black = '000000'; // color in hexa
    
    // -------------------------------------------------- //
    //            ALLOCATE FPDF RESSOURCE
    // -------------------------------------------------- //
    
    
    $data = $pdf->digit_to_fpdf_renderer( $pdf, $black, $x, $y, $angle, $type, array( 'code' => $code ), $width, $height );
    $currentDateTime = $jobcard['created_at'];
    $pdf->SetFont('Arial','',10,);
    $pdf->Cell(40, 10, 'Date / Time '.$currentDateTime. ' || Job ('.$jobcard['id'].')', 0, 0, 'L', false);
    $pdf->Ln();
    $pdf->SetFont('Arial','',12,);
    $pdf->Text(30, 15, $code);
    $tmpProcessType = $typesKeys[$jobcarditem['processId']]; 
    $processId = ''.$tmpProcessType['name'];

 
    
    

    $pdf->Cell(275,16,'Job Card : '.$tmpProcessType['name'] ,1,0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',7.5);
    $id = ''.$jobcarditemId;
    $jobCardId = ''.$jobcarditem['jobCardId'];
    $productType = $productKeys[$jobcarditem['productId']];
    //echo "<pre>";
    //print_r( $productType);
    $val = ''.$productType['name'];
    $productType = ''.$val;
    //echo "<pre>";
    //print_r( $val);
    //exit;
    //$productId = ''.$jobcarditem->productId;
    $name =''.$jobcarditem['name'];
    $tmpCustomerName = $customerKeys[$jobcard['customerId']]; 
    $customerId = ''.$tmpCustomerName['name'];
    $barcode = ''.$jobcarditem['barcode'];
    $stateId = ''.$jobcarditem['stateId'];  
    $qnt = ''.intval($jobcarditem['outstanding']);
    $unitType = $typesKeys[$jobcarditem['unitId']]; 
    $val = ''.$unitType['name'];
    $type_value = $unitType['value'];
    $qntId = ''.$val;
    $tmpMaterialType = $typesKeys[$product['materialTypeId']];
    $vall = ''.$tmpMaterialType['name'];
    $Width = ''.intval($porduct['totalWidth']);
    $length = ''.intval($porduct['product_length']);
    $thickness = ''.intval($porduct['thickness']);
    $date = ''.$jobcarditem['created_at'];
   // $unit = ''.$product->unitPackId;
    $tmpunit = $typesKeys[$jobcarditem['unitId']]; 
    $unit =  $tmpunit['name'];
    $value = $tmpunit['value'];
    $total =  $value * $qnt;
    $tmpunitpack = $typesKeys[$product['unitPackId']]; 
    $balepack = $tmpunitpack['value'];
    $productWidth = ''.intval($porduct['product_Width']);
    $tmppack = $typesKeys[$product['unitPackId']]; 
    $unitpack =  $tmppack['name'];
    $valuepack = $tmppack['value'];

    if ( $processId == "Extruding"  || $processId == "Printing" ){

        $tmpbagType = $typesKeys[$workInprogressbagtype]; 
        $bagtype = ''.$tmpbagType['name'];

        $productType = $productKeys[$workInp];
        $val = ''.$productType['name'];
        $productType = ''.$val;
        
        
   

     
    }
    else{

        $tmpbagType = $typesKeys[$jobcarditem['bagType']]; 
        $bagtype = ''.$tmpbagType['name'];

        $productType = $productKeys[$jobcard['productId']];
        $val = ''.$productType['name'];
        $productType = ''.$val;
       
       
       
       
    }


    
 
    









    
    //$tmMaterialType =$typesKeys[$porduct->materialTypeId];
    //$val = ''.$tmMaterialType ->name;
    //$material = ''.$val;
    $material  = ''. $vall;
    $tmpColorType = $typesKeys[$product['color']];
    $valz = ''.$tmpColorType['name'];
    $color = ''.$valz;
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
    $si = "g";

    if ($tmpProcessType['name'] == "Extruding"){
        $testingweight = round($Width *  $thickness / 5600,4);
     
    }
    else{
        $testingweight = 0.000;
       
    }

    if ($tmpProcessType['name'] == "Extruding"){
        $qnt = ''.intval($jobcarditem['outstanding']).' kg';
        $total = $qnt.'';
    }
    else{
        $qnt = ''.intval($jobcarditem['outstanding']);

    }

    
 
   

    //  echo "<pre>";
    //       print_r($prdctforroll);
    //       exit;

    $tmpbagTyp = $typesKeys[$porduct['bagType']]; 
    $bagtyp = ''.$tmpbagTyp['name'];



    $productTy = $typesKeys[$porduct['productType']]; 
    $productTy = $productTy['name'];

    $productforrolls = $productKeys[$jobcard['productId']];
    $prdctforroll = ''.$productforrolls['name'];


    if ($tmpbagTyp['name'] ==  "Centre Fold" || "Rolls" && $productTy == "finished-Product" && $tmpProcessType['name'] != "Bagging"){
        
        $productType = $prdctforroll;
        $bagtype = $bagtyp; 
        $consta = 5.325;
        $Weights = $Width/10 * $length/10 * $thickness/1000;
        $Weightsper1000 =  $Weights/1000 ;
        //Final Calculation
        $weightperrolls = round($Weightsper1000/ $consta, 2);
        $numberofrolls = round($jobcarditem['qnt']/$weightperrolls, 0);
        $qnt = $numberofrolls;
    }


    


    $pdf->SetFont('Arial','B',10);

    $pdf->Cell(10.6,6,'id',1,0,'C');
    

    


   

    

    



    //$pdf->Cell(25.09,6,'Quantity Type',1,0,'C');

    $pdf->Cell(45.6,6,'Bag Type',1,0,'C');

    $pdf->SetFont('Arial','B',10);

    $pdf->Cell(38.6,6,'Material',1,0,'C');

   
    $pdf->Cell(37.6,6,'Colour',1,0,'C');

    $pdf->Cell(25.6,6,'Quantity',1,0,'C');

    $pdf->Cell(25.6,6,'Unit',1,0,'C');



    $pdf->Cell(30.6,6,'Width',1,0,'C');

    $pdf->Cell(30.6,6,'Length',1,0,'C');

    $pdf->Cell(30.6,6,'thickness',1,0,'C');

    $pdf->Ln();

    $pdf->SetFont('Arial','B',10);

    $pdf->Cell(10.6,5,$jobcarditem['id'],1,0,'C');



//     if($jobcarditem->id > 0){

//         DB::table('jobcarditems')
//            ->where('id', $jobcarditem->id )
//            ->update(['stateId' => 63]); 

// $jobcardId = DB::table('jobcarditems')
//            ->where('id',$jobcarditem->id )
//            ->value('jobCardId');

//            DB::table('job_cards')
//            ->where('id', $jobcardId  )
//            ->update(['stateId' => 63]); 

        



        
//     }

$printed = DB::table('jobcarditems')
              ->where('id',$jobcarditem['id'] )
              ->value('stateId');







if ($jobcarditem['id'] > 0 && $printed == 61) {
    
    DB::table('jobcarditems')
           ->where('id', $jobcarditem['id'] )
           ->update(['stateId' => 63]); 

    // $jobcardId = DB::table('jobcarditems')
    //             ->where('id',$jobcarditem->id )
    //             ->value('jobCardId');
  
    // DB::table('job_cards')
    //    ->where('id', $jobcardId  )
    //    ->update(['stateId' => 63]); 


}
    

   
   
   
   
   

   

    ///dd('whyyyyyyyyyyyyyyyyyy');

   
   
    //$pdf->Cell(25.09,5,$qntId,1);

    $pdf->Cell(45.6,5,$bagtype,1,0,'C');

   
    

    $pdf->SetFont('Arial','B',12);

    $pdf->Cell(38.6,5,$material,1,0,'C');

    $pdf->Cell(37.6,5,$color,1,0,'C');

    $pdf->Cell(25.6,5,$qnt,1,0,'C');

    $pdf->Cell(25.6,5,$unit,1,0,'C');


   

    $pdf->Cell(30.6,5,round($porduct['totalWidth'],0).' mm',1,0,'C');

    $pdf->Cell(30.6,5,$length.' mm',1,0,'C');

    $pdf->Cell(30.6,5,$thickness.' mic',1,0,'C');

  




  
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
    
    $barcodeVal = JobCardItemRpt::uniqidRealVal();

    
    $pdf->Ln();
        
    if ( $tmpProcessType['name'] == 'Printing' ) { 

                   
        $pdf->Ln();
        $pdf->Ln();
             
        $pdf->Ln();
        $pdf->Ln();
             
        $pdf->Ln();
        $pdf->Ln();
             
        $pdf->Ln();
        $pdf->Ln();

        $pdf->Ln();
  
        }

// Fetch the records from the database
$pruct = DB::table('jobcarditems')->where('jobCardId', $jobCardId)->where('processId', 84)->get();



// Check if the process type is 'Extruding' and if $pruct is not empty
if ($tmpProcessType['name'] == 'Extruding' && !$pruct->isEmpty()) {
    $pdf->Ln();

    $pdf->SetTextColor(255, 0, 0);

    // Add the cell with the red text
    $pdf->Cell(275, 16, 'PLEASE NOTE THIS ROLL HAS TO BE TREATED FOR PRINTING !!!!!!!!!!! ', 1, 0, 'C');

    // Reset text color to black or your default color if needed
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln();
}

$pdf->Ln();


  

    if ( $product['gussetWidth'] > 0  ){

        $Totalgusset = $Width;

        $width = round($product['product_Width'] ,0);

        $productgusst = $product['gussetWidth'];

        $widthm = $width - $productgusst;
        

        $sidegusset =   $productgusst/2;

        $RGUSEET = round($productgusst ,0);

        $pdf->SetFont('Arial','',15);
        $pdf->Cell(111,9,'GUSSET WIDTH',1,0,'C');
        $pdf->Ln();
        $pdf->SetFont('Arial','B',15);
        $pdf->Cell(111,9,'<<         '.$width.' mm         >>',1,0,'C');
        $pdf->Ln();
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(25,9,''.$sidegusset.' mm',1,0,'C');
        $pdf->SetFont('Arial','B',15);
        $pdf->Cell(61,9,''.$widthm.' mm',1,0,'C');
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(25,9,''.$sidegusset.' mm',1,0,'C');


        

        

       
        $pdf->Cell(10,15,'',0); 
        $pdf->SetFont('Arial','',13);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(20,9,'Width',1,0,'C');
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(32,12,$width.' mm',1,0,'C');
      
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(20,9,'Gusset',1,0,'C');
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(32,12,$RGUSEET.' mm',1,0,'C');
     
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(24,9,'Total Width',1,0,'C');
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(28,12,$Width.' mm',1,0,'C');
      
        

        

      


      
        
        $pdf->Ln();
        $pdf->Ln();
      
       

    }
    


    if ($tmpProcessType['name'] == "Bagging"){

        $productInpackets = DB::table('porducts')
                              ->where('id',$jobcard['productId'])
                              ->value('unitTypeId');
       
 
          if($productInpackets == $jobcarditem['unitId'] ){

            $Totalnumofbales = $total /$balepack;


          }
          else{ 

            $Totalnumofbales = $qnt ;

          }

        
    }
    else{
        $Totalnumofbales = 0;

    }
   

    // echo "<pre>";
    // print_r($productType );
    // exit;

    $tmpbagTyp = $typesKeys[$porduct['bagType']]; 
    $bagtyp = ''.$tmpbagTyp['name'];

    $productTy = $typesKeys[$porduct['productType']]; 
    $productTy = $productTy['name'];

    $productforrolls = $productKeys[$jobcard['productId']];
    $prdctforroll = ''.$productforrolls['name'];

    if ($tmpbagTyp['name'] ==  "Centre Fold" || "Rolls" && $productTy == "finished-Product" && $tmpProcessType['name'] != "Bagging"){
        //  echo "<pre>";
        //   print_r( $tmpbagTyp->name);
        //   exit;
        
        $productType = $prdctforroll;
        $consta = 5.325;
        $Weights = $Width/10 * $length/10 * $thickness/1000;
        $Weightsper1000 =  $Weights/1000 ;
        //Final Calculation
        $weightperrolls = round($Weightsper1000/ $consta, 2);
        $numberofrolls = round($jobcarditem['qnt']/$weightperrolls, 0);
        $weightperroll =  ''.$weightperrolls.' kg ';
        $qnt = $numberofrolls;


        $pdf->SetFont('Arial','',7);
        $pdf->Cell(17,9,'Product',1,);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(100,9, $productType,1,0,'C');
        $pdf->SetFont('Arial','',7);
        $pdf->Cell(17,9,'Customer',1,0,'C');
        $pdf->SetFont('Arial','B',10,);
        $pdf->Cell(80,9,$customerId,1,0,'C');
        $pdf->SetFont('Arial','',7);
        $pdf->Cell(25,9,'Total Weight:',1,0,'C');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(37,9,$total,1,'C');
        $pdf->Ln();
        $pdf->SetFont('Arial','',7);
        $pdf->Cell(37,9,'Testing Weight',1,);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(80,9,$testingweight." g",1,0,'C');
        $pdf->SetFont('Arial','',7);
        $pdf->Cell(37,9,'Weight Per Roll',1,0,'C');
        $pdf->SetFont('Arial','B',10,);
        $pdf->Cell(60,9,$weightperroll,1,0,'C');
        $pdf->SetFont('Arial','',7);
        $pdf->Cell(25,9,'Number of Rolls:',1,0,'C');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(37,9,$numberofrolls,1,0,'C');

    }
    else{
       
        $weightperroll = 0.000;
        $numberofrolls = 0.000;
        $pdf->SetFont('Arial','',7);
        $pdf->Cell(17,9,'Product',1,);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(100,9, $productType,1,0,'C');
        $pdf->SetFont('Arial','',7);
        $pdf->Cell(17,9,'Customer',1,0,'C');
        $pdf->SetFont('Arial','B',10,);
        $pdf->Cell(80,9,$customerId,1,0,'C');
        $pdf->SetFont('Arial','',7);
        $pdf->Cell(25,9,'Total Units / Kgs',1,0,'C');
        $pdf->SetFont('Arial','B',10);
        $totalunits = $jobcarditem['outstanding'] *  $type_value;
        $pdf->Cell(37,9,$totalunits,1,0,'C');
        $pdf->Ln();
        $pdf->SetFont('Arial','',7);
        $pdf->Cell(20,9,'Testing Weight',1,);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,9,$testingweight." g",1,0,'C');
        $pdf->SetFont('Arial','',7);
        $pdf->Cell(17,9,'No of Bales',1,);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,9,$Totalnumofbales.' Bales of '.$valuepack ,1,0,'C');
    }

   
   

    // if ($weightperrolls > 0 ){
      
       

    // }
    // else{
 
    // }


    
    // $pdf->SetFont('Arial','',7);
    // $pdf->Cell(17,9,'Product',1,);
    // $pdf->SetFont('Arial','B',10);
    // $pdf->Cell(100,9, $productType,1,0,'C');
    // $pdf->SetFont('Arial','',7);
    // $pdf->Cell(17,9,'Customer',1,0,'C');
    // $pdf->SetFont('Arial','B',10,);
    // $pdf->Cell(80,9,$customerId,1,0,'C');
    // $pdf->SetFont('Arial','',7);
    // $pdf->Cell(25,9,'Total:',1,0,'C');
    // $pdf->SetFont('Arial','B',10);
    // $pdf->Cell(37,9,$total,1,'C');
    // $pdf->Ln();
    // $pdf->SetFont('Arial','',7);
    // $pdf->Cell(37,9,'Testing Weight',1,);
    // $pdf->SetFont('Arial','B',10);
    // $pdf->Cell(80,9,$testingweight." g",1,0,'C');
    // $pdf->SetFont('Arial','',7);
    // $pdf->Cell(37,9,'Weight Per Roll',1,0,'C');
    // $pdf->SetFont('Arial','B',10,);
    // $pdf->Cell(60,9,$weightperroll,1,0,'C');
    // $pdf->SetFont('Arial','',7);
    // $pdf->Cell(25,9,'Number of Rolls:',1,0,'C');
    // $pdf->SetFont('Arial','B',10);
    // $pdf->Cell(37,9,$numberofrolls,1,0,'C');
   
    
    $pdf->Ln();
    $pdf->Ln();
    
    $pdf->SetFont('Arial','',12);
    // Packaging List ....

    $packageList = DB::table('packages')->where('productId', $productId)->get(['outerPackagePerProductId','minWeight','avgWeight','maxWeight','unitTypeId']);
    if(count($packageList)!=0 ){
    $pdf->Cell(276,10,'Packaging List',1,0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',7.5);
    $id = ''.$jobcarditemId;
    $headerPackingProduct = array( 'Outer Packaging', 'Min Weight', 'Avg Weight', 'Max Weight', 'Unit Type');
    $packagingProductPersist = new JobCardItemRpt();
    //$dataPP = $packagingProductPersist;
    //$packageArray = $packagingProductPersist->fpdfObjectsByProductId($productId);
    // echo "<pre>";
    // print_r($packageList);
    $packageArray = array ();
    $sizesList = array(55.2, 55.2, 55.2, 55.2, 55.2);
    $pdf->BasicTableWithSizes($headerPackingProduct, $packageArray ,$sizesList);

    //$pdf->Cell(50,4, $recipeList->count(),1);
    $pdf->SetFont('Arial','',8);
    foreach($packageList as $package)
	{
        $i=0;
        foreach($package as $val) 
        {
          if ($i==4)  
          {
            $pdf->SetFont('Arial','B',12);
            $unitType = $typesKeys[$val]; 
            $val = ''.$unitType->name;
            
          }
          else if ($i==0)  
          {
            $pdf->SetFont('Arial','',9);
            $tmpProduct = $productKeys[$val]; 
            $val = ''.$tmpProduct->name;
            
            
          }
          else if ($i==2)  
          {
            $pdf->SetFont('Arial','B',12);
            
          }
          else if ($i==3)  
          {
            $pdf->SetFont('Arial','',12);
          }
          $pdf->Cell(55.2,6,$val,1);
          $i = $i + 1;
        }
        $pdf->Ln();
    } 
    
    $pdf->Ln();
    $pdf->Ln();

   }
  else if (count($packageList)==0 && $tmpProcessType['name'] == 'Bagging' ) { 
    
    

    $pdf->Cell(276,10,'Packaging List',1,0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',7.5);
    $id = ''.$jobcarditemId;
    $headerPackingProduct = array( 'Min Weight', 'Avg Weight', 'Max Weight', 'Unit Type');
    $packagingProductPersist = new JobCardItemRpt();

    $packageArray = array ();
    $sizesList = array(69, 69, 69, 69, 69);
    $pdf->BasicTableWithSizes($headerPackingProduct, $packageArray ,$sizesList);
    $productunit = DB::table('porducts')->where('id', $productId)->get(['minWeight','avgWorkingWeight','maxWeight','unitTypeId']);
    $productPack = DB::table('porducts')->where('id', $productId)->get(['minWeightPerProduct','avgWeightPerProduct','maxWeightPerProduct','unitPackId']);
    
    $pdf->SetFont('Arial','',8);
    foreach($productunit as $product)
	{
        $i=0;
        foreach($product as $val) 
        {
            if ($i==3)  
            {
              $pdf->SetFont('Arial','B',12);
              $unitType = $typesKeys[$val]; 
              $val = ''.$unitType['name'];
              
            }
          $pdf->Cell(69,6,$val,1);
          $i = $i + 1;
        }
        $pdf->Ln();
    } 
    foreach($productPack as $productpack)
	{
        $i=0;
        foreach($productpack as $val) 
        {
            if ($i==3)  
            {
              $pdf->SetFont('Arial','B',12);
              $unitPackId = $typesKeys[$val]; 
              $val = ''.$unitPackId['name'];
              
            }
          $pdf->Cell(69,6,$val,1);
          $i = $i + 1;
        }
        $pdf->Ln();
    } 
    
    $pdf->Ln();
    $pdf->Ln();

}else if (count($packageList)==0 && $tmpProcessType['name'] != 'Extruding' ) { 
    
    
    $product = DB::table('job_cards')->where('id', $jobCardId)->value('productId');
    $image_path = DB::table('porducts')->where('id', $product)->value('image_path');

    //$image_path =($porduct['image_path']);

    //dd($image_path);

    if (!$image_path){

        $image_path = 'images/zIEWVpW2ZM.jpg' ;


    }


   // dd($image_path);


    //$pdf->Image(storage_path('app/public/' . $image_path), 100, 60, 60, 50);

    try {
        // Attempt to add the image to the PDF
        $pdf->Image(storage_path('app/public/' . $image_path), 100, 60, 60, 50);
    } catch (Exception $e) {
        // Handle the exception
        // You can log the error or show a message
        $pdf->SetTextColor(255, 0, 0); // Set text color to red for the error message
        $pdf->Cell(0, 10, 'Image format not supported change format to png | jpeg: ' . $e->getMessage(), 0, 1, 'C');
        $pdf->SetTextColor(0, 0, 0); // Reset text color to default
    }
    
   
    $pdf->SetFont('Arial','',7.5);
    $id = ''.$jobcarditemId;
  
    $packagingProductPersist = new JobCardItemRpt();



}

    if ($rollGenerate == 'yes') {
        
        $pdf->Cell(25,4,'Weight per Roll (kg):',1);
        $pdf->Cell(20,4,$rollWeight,1,'R');
        $pdf->Cell(25,4,'Number of Rolls:',1);
        $pdf->Cell(20,4,$noRolls,1,'R');
        $pdf->Cell(25,4,'Length per roll (meters):',1);
        $pdf->Cell(20,4,$lengthPerRoll,1,'R');
        $pdf->Cell(16,4,'Last roll length:',1);
        $pdf->Cell(20,4,$lastRollLength,1,'R');  
   
     
    }

//dd('hoyoo');
    

        if( $jobcarditem['other'] != null  ){


            $uppercaseString = strtoupper($jobcarditem['other']);

            $pdf->SetFillColor(255, 192, 192);
    
    
           // Calculate the width of the text
           $textWidth = 276; // Set your desired width
           $text = ' PLEASE READ NOTICE !! | ' .  $uppercaseString;
           
           // Create a multi-cell with the specified fill color
           $pdf->MultiCell($textWidth, 8, $text, 1, 'C', true);
           

            $pdf->SetFillColor(255, 255, 255); 
    
    
        }
    
        $pdf->Ln();
        $pdf->Ln();
    
    $pdf->SetFont('Arial','',12);
    $pdf->Text(30, 15, $code);
    $pdf->Cell(276,10,'Production',1,0,'C');
    $pdf->SetFont('Arial','',7.5);
    $pdf->Ln();
    //$pdf->SetXY(0,0);
    
    // Column headings

    //$pdf->AddPage();
    $header1 = array('Date', 'Operator', 'Job Card No.', 'Roll No.', "Kg's", 'Width', 'Length', 'Qty P/Roll', 'Mic', 'Qty/Roll', 'Bale Size', 'Scrap - KG');
    $pdf->newBasicTable($header1, $pdf);
    
    //$pdf->AddPage();
    //$pdf->FancyTable($header,$data);
    $pdf->Output();
}

}



?>