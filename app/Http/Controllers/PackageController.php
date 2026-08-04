<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Porduct;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use DB;
use Auth;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $action = $request->get('action');

     if ($action <> null && trim($action, ' ') == 'query'){

        $productId = $request->get('productId');

  
                  $productComp = '<>';
                  if ($productId > 0) {
                    $productComp = '=';
                  }
  
                 
                  
  
                 
                  $data['porducts'] =Porduct::where('id',''.$productComp,$productId)
                                            ->orderBy('id','desc')->paginate(500);
  
       
      
  
      return view('packages.index ', $data);                              
  
       }
        $data['porducts'] = Porduct::orderBy('id','desc')->paginate(50);
        return  view('packages.index ', $data);
    }

        /**
* Show the form for editing the specified resource.
*
* @param  \App\package  $package
* @return \Illuminate\Http\Response
*/
public function create(Request $request)
{
    $productId = $request->get('productId');
    Log::info(" getting id from model ------------------------------------------- : ".$productId); 
    $packagesList=DB::table('packages')->where('productId', $productId)->get();
    Log::info(" package list ------------------------------------------- : ".$packagesList); 
    $porduct = Porduct::find($productId);
    Log::info(" - 1 Update Job Card ------------------------------------------- : ".$porduct); 

    View::share('porduct',$porduct);
    View::share('packages',$packagesList);
 
    return view('packages.create', ['productId' => $productId]);

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
            'barcode' => 'required',
            'outerPackagePerProductId' => 'required',
            ]);
        $package = new Package;
        $package->product = $request->product;
        $package->productId = $request->productId;
        $package->outerPackagePerProductId = $request->outerPackagePerProductId;
        $package->unitTypeId = $request->unitTypeId;
        $package->avgWeight = $request->avgWeight;
        $package->barcode = $request->barcode;
        $package->otherInfo = $request->otherInfo;
        $package->otherPackagingDetails = $request->otherPackagingDetails;
        $package->maxWeight = $request->maxWeight;
        $package->minWeight = $request->minWeight;
        $package->ratioToProduct = $request->ratioToProduct;
        $package->printLabel = $request->printLabel;
        $package->prnBarcode = $request->prnBarcode;
        $package->prnSerialNo = $request->prnSerialNo;
        $package->custBarcode = $request->custBarcode;
        $package->labelLine1 = $request->labelLine1;
        $package->labelLine2 = $request->labelLine2;
        $package->labelLine3 = $request->labelLine3;
        $package->labelLine4 = $request->labelLine4;
        $package->save();
        return redirect()->route('packages.create',['productId' => $package->productId])->with('success','Package has been created successfully.');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        return view('pakages.show',compact('package'));
    }

    /**
* Show the form for editing the specified resource.
*
* @param  \App\package  $package
* @return \Illuminate\Http\Response
*/
    public function edit(Package $package)
    {
        $packagesList=DB::table('packages')->where('productId', $package->productId)->get();
  
        $porduct = Porduct::find($package->productId);

        View::share('porduct',$porduct);
        View::share('packages',$packagesList);
        Log::info(" - Update Job Card ------------------------------------------- : ".$package->id); 
     
        return view('packages.edit',compact('package'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
 
        ]);

        $package = Package::find($id);
        $package->outerPackagePerProductId = $request->outerPackagePerProductId;
        $package->unitTypeId = $request->unitTypeId;
        $package->avgWeight = $request->avgWeight;
        $package->barcode = $request->barcode;
        $package->otherInfo = $request->otherInfo;
        $package->otherPackagingDetails = $request->otherPackagingDetails;
        $package->maxWeight = $request->maxWeight;
        $package->minWeight = $request->minWeight;
        $package->ratioToProduct = $request->ratioToProduct;
        $package->printLabel = $request->printLabel;
        $package->prnBarcode = $request->prnBarcode;
        $package->prnSerialNo = $request->prnSerialNo;
        $package->custBarcode = $request->custBarcode;
        $package->labelLine1 = $request->labelLine1;
        $package->labelLine2 = $request->labelLine2;
        $package->labelLine3 = $request->labelLine3;
        $package->labelLine4 = $request->labelLine4;
        $package->save();

        return redirect()->route('packages.create',['productId' => $package->productId])
        ->with('success','Package Has Been updated successfully');
    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->route('packages.create', ['productId' => $package->productId] )
        ->with('success','Package has been deleted successfully');
    }

    
}
