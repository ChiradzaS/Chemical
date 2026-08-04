<?php
namespace App\Http\Controllers;
use App\Models\Porduct;
use App\Models\Jobcarditem;
use App\Models\Productionitem;
use App\Models\Order_item;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Stock;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use App\Barcode\Barcode;
use DB;
use Auth;


class PorductController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
public function index(Request $request,Porduct $porduct)
{

//   try {
//     $disk = Storage::disk('ftp'); 

   
//     $localFilePath = storage_path('app/Laravel/backup.zip'); 
//     $remoteFilePath = 'public_html/backup.zip'; 

    
//     if ($disk->put($remoteFilePath, file_get_contents($localFilePath))) {
//         echo "File uploaded successfully to FTP server.";
//     } else {
//         echo "File upload failed.";
//     }
    
// } catch (\Exception $e) {

//     echo "An error occurred: " . $e->getMessage();

// }


// try {
//   $disk = Storage::disk('ftp'); // FTP disk configuration

//   // Specify the local directory where backups are stored
//   $localDirectory = storage_path('app/Laravel');

//   // Check if the directory exists
//   if (!is_dir($localDirectory)) {
//       throw new \Exception("Local directory '{$localDirectory}' does not exist.");
//   }

//   // Use glob to find all files in the directory
//   $files = glob($localDirectory . '/*');
//   if (!$files) {
//       throw new \Exception("No files found in the directory '{$localDirectory}'.");
//   }

//   // Get the last modified file
//   $lastFile = array_reduce(
//       $files,
//       function ($latestFile, $currentFile) {
//           return filemtime($currentFile) > filemtime($latestFile) ? $currentFile : $latestFile;
//       }
//   );

//   $fileName = basename($lastFile); // Extract the file name
//   $remoteFilePath = 'public_html/localDatabaseBackup' . $fileName; // Target FTP path

//   // Upload the last modified file to FTP
//   if ($disk->put($remoteFilePath, file_get_contents($lastFile))) {
//       echo "File '{$fileName}' uploaded successfully to FTP server.";
//   } else {
//       echo "File upload failed.";
//   }
// } catch (\Exception $e) {
//   echo "An error occurred: " . $e->getMessage();
// }




 

  //Porduct::where('id','>',0)->update(['packagingLevel' => null]);

DB::table('porducts')->update(['lableId' => 1]);

  $value = $request->input('productType');

  $action = $request->get('action');


  if( $value == 'work-In-Progress' || $action <> null && trim($action, ' ') == 'querywp'   ){


    
  $productId = $request->get('productId');
  $color = $request->get('color');
  $bagTypes = $request->get('bagType');
  $materialTypeId= $request->get('materialTypeId');

  $id = DB::table('types')
          ->where('name' ,'=', $value)
          ->value('id');


  
$productComp = '<>';
if ($productId > 0) {
   $productComp = '=';
}



$colorComp = '<>';
if ($color > 0) {
   
     
     $colorComp = '=';
}

$bagTypeComp = '<>';
if ($bagTypes > 0) {
  $bagTypeComp = '=';
}


$materialComp = '<>';
if ($materialTypeId > 0) {
 
  $materialComp = '=';
}        

  $data['porducts'] = Porduct:: 
                                where('id',''.$productComp,$productId)
                                ->where('color', ''.$colorComp,$color)
                                ->where('bagType',''.$bagTypeComp,$bagTypes)
                                ->where('materialTypeId', ''.$materialComp,$materialTypeId)    
                                ->where('productType',101)                               
                                ->orderBy('id','desc')->paginate(500);


    return  view('porducts.wp ', $data ,['state'=>$value,'productId'=> -9,'color' => -9,'bagType' => -9, 'materialTypeId' => -9 ]);                               

  }


  if ($value == 'finished-Product' || $action <> null && trim($action, ' ') == 'query' ) {

    
  $productId = $request->get('productId');
  $color = $request->get('color');
  $bagTypes = $request->get('bagType');
  $materialTypeId= $request->get('materialTypeId');

  $id = DB::table('types')
          ->where('name' ,'=', $value)
          ->value('id');


  
$productComp = '<>';
if ($productId > 0) {
   $productComp = '=';
}



$colorComp = '<>';
if ($color > 0) {
   
     
     $colorComp = '=';
}

$bagTypeComp = '<>';
if ($bagTypes > 0) {
  $bagTypeComp = '=';
}


$materialComp = '<>';
if ($materialTypeId > 0) {
 
  $materialComp = '=';
}        

  $data['porducts'] = Porduct:: 
                                where('id',''.$productComp,$productId)
                                ->where('color', ''.$colorComp,$color)
                                ->where('bagType',''.$bagTypeComp,$bagTypes)
                                ->where('materialTypeId', ''.$materialComp,$materialTypeId)    
                                ->where('productType',100)                               
                                ->orderBy('id','desc')->paginate(500);


    return  view('porducts.index ', $data ,['state'=>$value,'productId'=> -9,'color' => -9,'bagType' => -9, 'materialTypeId' => -9 ]);                               
 
  }

 






}

/**
* Show the form for creating a new resource.
*
* @return \Illuminate\Http\Response
*/
public function create(Request $request)
{ 
  $search = $request->get('search');




return view('porducts.create');

}
/**
* Store a newly created resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function store(Request $request)
{
  $request->validate([
    'name' => ['required', 'string', 'unique:porducts'],

], [
    'name.unique' => 'A product with the same name already exist in the system.',
]);

$porduct = new Porduct;
$porduct->name = $request->name;
$porduct->description = $request->description;
$porduct->unitTypeId = $request->unitTypeId;
$porduct->WeightPerProduct = $request->WeightPerProduct;
$porduct->color = $request->color;
$porduct->product_length = $request->product_length;
$porduct->product_Width = $request->product_Width;
$porduct->gussetWidth = $request->gussetWidth;
$porduct->totalWidth = $request->totalWidth;
$porduct->thickness = $request->thickness;
$porduct->label = $request->label;
$porduct->user = $request->user;
$porduct->otherInfo = $request->otherInfo;
$porduct->printId = $request->printId;
$porduct->materialTypeId = $request->materialTypeId;
$porduct->defaultSellingPricePerKg = $request->defaultSellingPricePerKg;
$porduct->actualtSellingPricePerKg = $request->actualtSellingPricePerKg;
$porduct->defaultSellingPice = $request->defaultSellingPice;
$porduct->actualSellingPrice = $request->actualSellingPrice;
$porduct->invDescription = $request->invDescription;
$porduct->code = $request->code;
$porduct->barcode = $request->barcode;
$porduct->unitPackId = $request->unitPackId;
$porduct->costPrice = $request->costPrice;
$porduct->costPricePerKg = $request->costPricePerKg;
$porduct->costDefaultPricePerKg = $request->costDefaultPricePerKg;
$porduct->minWeight = $request->minWeight;
$porduct->maxWeight = $request->maxWeight;
$porduct->avgWorkingWeight = $request->avgWorkingWeight;
$porduct->percentMaxWeight = $request->percentMaxWeight;
$porduct->percentMinWeight = $request->percentMinWeight;
$porduct->perecentAvgWeight = $request->perecentAvgWeight;
$porduct->weightPerProductionUnitTypeId = $request->weightPerProductionUnitTypeId;
$porduct->weightPerProductionType = $request->weightPerProductionType;
$porduct->avgWeightPerProduct = $request->avgWeightPerProduct;
$porduct->minWeightPerProduct = $request->minWeightPerProduct;
$porduct->maxWeightPerProduct = $request->maxWeightPerProduct;
$porduct->perecentAvgWeight = $request->perecentAvgWeight;
$porduct->percentMinWeightPerProduct = $request->percentMinWeightPerProduct;
$porduct->percentMaxWeightPerProduct = $request->percentMaxWeightPerProduct;
$porduct->percentAvgWeightPerProduct = $request->percentAvgWeightPerProduct;
$porduct->tms = $request->tms;
$porduct->purchasing = $request->purchasing;
$porduct->invoicing = $request->invoicing;
$porduct->bagType = $request->bagType;
$porduct->productType = $request->productType;
$porduct->workInProgressId = $request->workInProgressId;
$porduct->userId = Auth::id();
$porduct->lableMicron = $request->lableMicron;
$porduct->lable_text  = $request->input('label_text');

//dd($request->input('label_text'));


if($porduct->lableMicron==null){

  $porduct->lableMicron = 1;
  
}else{
  $porduct->lableMicron = 0;

}

$porduct->lableDate = $request->lableDate;

if($porduct->lableDate==null){

  $porduct->lableDate = 1;
  
}else{

  $porduct->lableDate =0;

}

$porduct->lableWeight = $request->lableWeight;

if($porduct->lableWeight ==null){

  $porduct->lableWeight=1;
  
}else{

  $porduct->lableWeight=0;

}



$porduct->lableId = $request->lableId;

if($porduct->lableId ==null){

  $porduct->lableId=1;
  
}else{

  $porduct->lableId=0;

}

$image = $request->file('image');
        
if ($image) {
  
  $filename = Str::random(10) . '.' . $image->getClientOriginalExtension();
  $path = $image->storeAs('images', $filename, 'public');

} else {
  
  $filename = null;
  $path = null;
}

$porduct->image_path = $path;


$porduct->save();

$stock = new Stock;
$stock->productId = $porduct->id;
$stock->userId = Auth::id();
$stock->qnt = 0;
$stock->prvqnt = 0;
$stock->lostTransId= 0;
$stock->save();

$state = DB::table('types')
            ->where('id' ,'=', $porduct->productType)
            ->value('name');



// $model = Porduct::query()
//     ->where('bagType', '=', 56)
//     ->update(['productType' => 101]);
return redirect()->route('porducts.index',['productType'=> $state])
->with('success','Product has been created successfully.');
}
/**
* Display the specified resource.
*
* @param  \App\porduct  $porduct
* @return \Illuminate\Http\Response
*/
public function show(Porduct $porduct)
{

  
return view('porducts.show',compact('porduct'));
} 
/**
* Show the form for editing the specified resource.
*
* @param  \App\porduct  $porduct
* @return \Illuminate\Http\Response
*/
public function edit(Porduct $porduct)
{
return view('porducts.edit',compact('porduct'));
}
/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\porduct  $porduct
* @return \Illuminate\Http\Response
*/
public function update(Request $request, $id)
{
$request->validate([
'name' => 'required',

]);
$porduct = Porduct::find($id);
$porduct->name = $request->name;
$porduct->description = $request->description;
$porduct->unitTypeId = $request->unitTypeId;
$porduct->WeightPerProduct = $request->WeightPerProduct;
$porduct->color = $request->color;
$porduct->product_length = $request->product_length;
$porduct->product_Width = $request->product_Width;
$porduct->gussetWidth = $request->gussetWidth;
$porduct->totalWidth = $request->totalWidth;
$porduct->thickness = $request->thickness;
$porduct->label = $request->label;
$porduct->user = $request->user;
$porduct->otherInfo = $request->otherInfo;
$porduct->printId = $request->printId;
$porduct->materialTypeId = $request->materialTypeId;
$porduct->defaultSellingPricePerKg = $request->defaultSellingPricePerKg;
$porduct->actualtSellingPricePerKg = $request->actualtSellingPricePerKg;
$porduct->defaultSellingPice = $request->defaultSellingPice;
$porduct->actualSellingPrice = $request->actualSellingPrice;
$porduct->invDescription = $request->invDescription;
$porduct->code = $request->code;
$porduct->unitPackId = $request->unitPackId;
$porduct->barcode = $request->barcode;
$porduct->costPrice = $request->costPrice;
$porduct->costPricePerKg = $request->costPricePerKg;
$porduct->costDefaultPricePerKg = $request->costDefaultPricePerKg;
$porduct->minWeight = $request->minWeight;
$porduct->maxWeight = $request->maxWeight;
$porduct->avgWorkingWeight = $request->avgWorkingWeight;
$porduct->percentMaxWeight = $request->percentMaxWeight;
$porduct->percentMinWeight = $request->percentMinWeight;
$porduct->perecentAvgWeight = $request->perecentAvgWeight;
$porduct->weightPerProductionUnitTypeId = $request->weightPerProductionUnitTypeId;
$porduct->weightPerProductionType = $request->weightPerProductionType;
$porduct->avgWeightPerProduct = $request->avgWeightPerProduct;
$porduct->perecentAvgWeight = $request->perecentAvgWeight;
$porduct->maxWeightPerProduct = $request->maxWeightPerProduct;
$porduct->lable_text  = $request->input('label_text');

$porduct->minWeightPerProduct = $request->minWeightPerProduct;
$porduct->percentMinWeightPerProduct = $request->percentMinWeightPerProduct;
$porduct->percentMaxWeightPerProduct = $request->percentMaxWeightPerProduct;
$porduct->percentAvgWeightPerProduct= $request->percentAvgWeightPerProduct;
$porduct->purchasing = $request->purchasing;
$porduct->invoicing = $request->invoicing;
$porduct->bagType = $request->bagType;
$porduct->productType = $request->productType;
$porduct->workInProgressId = $request->workInProgressId;

// Porduct::query()->update([
//   'lableMicron' => 0,
//   'lableDate' => 0,
//   'lableWeight' => 0,
// ]);

$porduct->lableMicron = $request->lableMicron;
if($porduct->lableMicron==null){

  $porduct->lableMicron = 1;
  
}else{
  $porduct->lableMicron = 0;

}

$porduct->lableDate = $request->lableDate;

if($porduct->lableDate==null){

  $porduct->lableDate = 1;
  
}else{

  $porduct->lableDate =0;

}

$porduct->lableWeight = $request->lableWeight;

if($porduct->lableWeight ==null){

  $porduct->lableWeight=1;
  
}else{

  $porduct->lableWeight=0;

}


$porduct->lableId = $request->lableId;

if($porduct->lableId ==null){

  $porduct->lableId=1;
  
}else{

  $porduct->lableId=0;

}

$image = $request->file('image');
        
if ($image) {
  
  $filename = Str::random(10) . '.' . $image->getClientOriginalExtension();
  $path = $image->storeAs('images', $filename, 'public');

} else {
  
  $filename = null;
  $path = null;
}

$porduct->image_path = $path;



$porduct->save();

// $stock = new Stock;
// $stock->productId = $porduct->id;
// $stock->userId = Auth::id();
// $stock->qnt = 0;
// $stock->prvqnt =0;
// $stock->lostTransId= 0;
// $stock->save();

$state = DB::table('types')
          ->where('id' ,'=', $porduct->productType)
          ->value('name');

   


return redirect()->route('porducts.index',['productType' => $state])
->with('success','Product Has Been updated successfully');
}
/**
* Remove the specified resource from storage.
*
* @param  \App\Porduct  $porduct
* @return \Illuminate\Http\Response
*/
public function destroy(Porduct $porduct,Request $request)
{


 
  $porduct = Porduct::find($porduct->id);

  //dd(''.$product);
  
//   foreach($products as $product){
//     $id = $product->id;

//   }

   

// if ($products) {


//     $existsjobcarditemTable = Jobcarditem::where('productId', $id)->get();
//     $existsproductionitemTable = Productionitem::where('productId', $id)->get();
 
//      if(count($existsjobcarditemTable) > 0 || count($existsproductionitemTable) > 0 ){

//         return redirect()->route('porducts.index', ['productType' => 'finished-Product'])
//             ->with('error', 'Product already in use cannot be deleted');
//     } else {
//         $porduct->delete();
//         return redirect()->route('porducts.index', ['productType' => 'finished-Product'])
//             ->with('success', 'Product has been deleted successfully');
//     }
// }

if (!$porduct) {
    return redirect()->route('porducts.index', ['productType' => 'finished-Product'])
        ->with('error', 'Product not found');
}

$id = $porduct->id;


$existsJobcarditemTable = Jobcarditem::where('productId', $id)->get();
$existsProductionitemTable = Productionitem::where('productId', $id)->get();
$existsOrderItemTable = Order_item::where('productId', $id)->get();

if (count($existsJobcarditemTable) > 0 || count($existsProductionitemTable) > 0 || count($existsOrderItemTable) > 0) {
    return redirect()->route('porducts.index', ['productType' => 'finished-Product'])
        ->with('error', ' Sorry Product Already In Use Cannot Be Deleted');
} else {
    $porduct->delete();
    return redirect()->route('porducts.index', ['productType' => 'finished-Product'])
        ->with('success', 'Product has been deleted successfully');
}
    

}



public function srchProduct(Request $request){


  $gusset = $request->gusset;
  $width = $request->width;
  $totalWidth = $request->totalWidth;
  $materialType = $request->materialType;
  $colour = $request->colour ;
  $micron = $request->micron;
  $bagType= $request->bagType;

  if($gusset > 0 ){

  $porduct = Porduct::select('*')->where('materialTypeId', $materialType)
                                 ->where('color', $colour )
                                 ->where('totalWidth', $totalWidth)
                                 ->where('gussetWidth', $gusset)
                                 ->where('thickness', $micron)
                                 ->where('productType','=' , 101 )
                                 ->get();
  $response['data'] = $porduct;

}else{

  $porduct = Porduct::select('*')->where('materialTypeId', $materialType)
                                  ->where('color', $colour )
                                  ->where('totalWidth', $totalWidth)
                                  ->where('thickness', $micron)
                                  ->where('productType','=' , 101 )
                                  ->get();

$response['data'] = $porduct;

}

  

  return response()->json($response);
}


 public function generateProduct(Request $request){



  $gusset = $request->gusset;
  $width = $request->width;
  $totalWidth = $request->totalWidth;
  $materialType = $request->materialType;
  $colour = $request->colour ;
  $micron = $request->micron;
  $bagType= $request->bagType;
  $length= $request->length;

  if ($length != null){
    $length = $length;
  }else
  { $length = 300000;}



  $color = DB::table('types')
          ->where('id' ,'=',$colour )
          ->value('name');

          $bagtypename = DB::table('types')
          ->where('id' ,'=',$bagType )
          ->value('name');

          $materialname = DB::table('types')
          ->where('id' ,'=',$materialType )
          ->value('name');

          
  
 

  $name = round($width,0).'mm x ' .round($micron,0).'mic  '.$materialname.'/'.$color.' '.$bagtypename.' wp' ;

  if( $gusset > 0){

    $gussetsides = $gusset /2;

    $name = round($width,0).'mm ' .'('.$gussetsides .'mm x ' .$gussetsides.'mm)'.' x '.round($micron,0).'mic  '.$materialname.'/'.$color.' '.$bagtypename.'wp' ;

  }
  

  if ($exists = Porduct::where('name', $name)->exists()) {

    //Log::info('/////////////hamuna zvachose///////////////////');

    $response = 1;

    return response()->json($response);

}

$porduct = new Porduct;
$porduct->name = $name;
$porduct->description =  $name;
$porduct->unitTypeId = $request->unitTypeId;
$porduct->WeightPerProduct = 30;
$porduct->color = $colour;
$porduct->product_length = $length;
$porduct->product_Width = $width;
$porduct->gussetWidth = $gusset;
$porduct->totalWidth =  $totalWidth;
$porduct->thickness = $micron;
$porduct->label = $name;
$porduct->otherInfo = $name;
$porduct->materialTypeId = $materialType;
$porduct->defaultSellingPricePerKg = 20;
$porduct->actualtSellingPricePerKg = 20;
$porduct->defaultSellingPice = 20;
$porduct->actualSellingPrice = 20;
$porduct->invDescription = $name;
$porduct->unitPackId = 52;
$porduct->unitTypeId = 52;
$porduct->barcode = Barcode::uniqidReal();
$porduct->bagType = $bagType;
$porduct->productType = 101;
$porduct->userId = Auth::id();
$porduct->save();



$porduct = Porduct::select('*')->where('id' ,$porduct->id )
                               ->get();


$response['data'] = $porduct;



  

  return response()->json($response);
 }


 public function checkname(Request $request)
 {
     
  
  $name = $request->name;

  $exists = Porduct::where('name',  $name )->exists();

  if ($exists) {
      
    $response = 0;
  } else {
     
    $response = 1;
  }

  

     return response()->json($response);
 }




}
