<?php
namespace App\Http\Controllers;

use App\Models\Jobcarditem;
use App\Models\Productionitem;
use Illuminate\Http\Request;
use App\Models\Porduct;
use App\Models\Production;
use App\Models\DocumentAudit;
use Illuminate\Support\Facades\View;
use DB;
use Auth;


class ProdController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/

public function index()
{
  $data['productions'] = Production::orderBy('id','desc')->paginate(50);
  return view('productionperemployee.index', $data);
}



/**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create(Request $request)
  {



    $myButton= $request->get('myButton');
  

    $productionId = $request->get('productionId');
    

    $jobcarditem = new Jobcarditem();
    $productionitem = new Productionitem();
    $productionitem->productionId = $productionId;
    if ($myButton == "fetchData") {
        

        $fetchId = $request->get('fetchId');
        
        $jobcarditem = null;
        $jobcarditemInfo = $request->get('jobcarditemInfo');
        $jobcarditemInfo = trim($jobcarditemInfo);
        if ($fetchId == "jobcarditemId") {
           
           $jobcarditem = DB::table('jobcarditems')->where('id', $jobcarditemInfo)->first();
            
           
        } else if ($fetchId == "barcode") {
          
           $jobcarditem = DB::table('jobcarditems')->where('barcode', $jobcarditemInfo)->first();
        }

        
        if ($jobcarditem != null) {
         
           $product = DB::table('porducts')->where('id', $jobcarditem->productId)->first();
           $productionitem = new Productionitem();
      
           $productionitem->jobcarditemId = $jobcarditem->id;
          
           $productionitem->productId = $jobcarditem->productId; 
          
           $productionitem->unitId = $product->unitTypeId;
           
           $productionitem->productionId = $productionId;

           $productionitem->other = $request->other;

           $productionitem->qnt = $request->qnt;

           $productionitem->other = $request->other;

           $productionitem->userId = Auth::id();

           $productionitem->counttype = 0;


           $productionitem->weight = 0;

           //$productionitem->state = 44;

           

           ///$productionSum =  DB::table('productionitems')->where('jobcarditemId',$productionitem->jobcarditemId)->sum('qnt');

        

           

           //$jobcarditemSum =  DB::table('jobcarditems')->where('id',$productionitem->jobcarditemId)->sum('qnt');
  
    
   

           //$productionitem->outstanding =  $jobcarditemSum -  $productionSum ;

       
           

 
        }
        
        View::share('productionitem', $productionitem);
        View::share('jobcarditem', $jobcarditem);

        return view('productionitems.create',compact('productionitem'), compact('jobcarditem'));

    } else if ($myButton == "create") {
         //Must save data and go back to list if production quantity is no greater than jobcarditem quantity
         
         $fetchQnt = $request->get('qnt'); 

         $fetchitemQnt = $request->get('jobcarditemQnt'); 

         if( $fetchQnt > $fetchitemQnt){

            return redirect()->route('productionitems.create', ['productionId' => $productionitem->productionId])->with('success','Your quantity has exceded the limit');

         }

           
         else{  $productionitem = new Productionitem();
      
           $productionitem->jobcarditemId =  $request->jobcarditemId;
          
           $productionitem->productId = $request->productId; 
          
           $productionitem->unitId = $request->unitId;
           
           $productionitem->productionId = $productionId;

           $productionitem->other = $request->other;

           $productionitem->qnt = $request->qnt;

           $productionitem->other = $request->other;

           $productionitem->userId = Auth::id();

           $productionitem->counttype = $request->counttype;

           $productionitem->weight = $request->weight;

          

         $productionitem->save();
         //save data and go to production.edit
         return redirect()->route('productions.edit', $productionitem->productionId)->with('success','A new order iterm Has Been created successfully');
      }
    }

    View::share('productionitem', $productionitem);
    View::share('jobcarditem', $jobcarditem);
  
    return view('productionitems.create', compact('productionitem'), compact('jobcarditem'));
   
  }



/**
* Store a newly created resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function store(Request $request)
{

$productionitem = new Productionitem;
$productionitem->productionId = $request->productionId;
$productionitem->jobcarditemId = $request->jobcarditemId;
$productionitem->other = $request->other;
$productionitem->productId = $request->productId;
$productionitem->qnt = $request->qnt;
$productionitem->qntUnitId = $request->qntUnitId;
$productionitem->userId = Auth::id();
$productionitem->save();


    $document = new DocumentAudit();
    $document->docId = $production->id ;
    $document->docType = 'productionitem started'; 
    $document->stateId  = 61;
    $document->other = 0;
    $document->userId = Auth::id();
    $document->action = 'Started';
    $document->save();

return redirect()->route('productions.edit', $productionitem->productionId)->with('success','A new order iterm Has Been created successfully');
}





/**
* Display the specified resource.
*
* @param  \App\order_item  $order_item
* @return \Illuminate\Http\Response
*/
public function show(Request $request,Productionitem $productionitem)
{ 
return view('productionitems.show',compact('productionitem'));
//return redirect()->route('productions.edit',$productionitem->productionId);
}





/**
* Show the form for editing the specified resource.
*
* @param  \App\productionitem  $productionitem
* @return \Illuminate\Http\Response
*/
public function edit(Productionitem $productionitem)
{
return view('productionitems.edit',compact('productionitem'));
}




/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\productionitem  $productionitem
* @return \Illuminate\Http\Response
*/



public function update(Request $request, $id)
{

   $myButton= $request->get('myButton');

   if  ($myButton == "save"){

$productionitem = Productionitem::find($id);
$productionitem->jobcarditemId =  $request->jobcarditemId;      
$productionitem->productId = $request->productId; 
$productionitem->unitId = $request->unitId;
$productionitem->productionId = $request->productionId;
$productionitem->other = $request->other;
$productionitem->qnt = $request->qnt;
$productionitem->other = $request->other;
$productionitem->counttype = $request->counttype;
$productionitem->weight = $request->weight;
//$productionitem->state =44;
$productionitem->save();

return redirect()->route('productions.edit',$productionitem->productionId)
->with('success','A new order iterm Has Been updated successfully');

} 


}
/**
* Remove the specified resource from storage.
*
* @param  \App\order_item  $order_item
* @return \Illuminate\Http\Response
*/
public function destroy(Productionitem $productionitem)
{
$productionitem->delete();
return redirect()->route('productions.edit',$productionitem->productionId)
->with('success','A new order iterm Has Been deleted  successfully');
}


public function getProductbyid(Request $request){

   $productid = $request->productid;
 
   $porduct = Porduct::select('*')->where('id', $productid)->get();
  
   // Fetch all records
   $response['data'] = $porduct;
 
   return response()->json($response);
 }


}