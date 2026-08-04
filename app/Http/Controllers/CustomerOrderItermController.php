<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Porduct;
use App\Models\Customerorderitem;
use DB;
use Auth;

class CustomerOrderItermController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $orderId = $request->get('orderId');  
        $data['customerorderitems'] = Customerorderitem::where('ordersId',$orderId)->orderBy('id','asc')->paginate(15);

        return view('customerorders.index', $data,['orderId'=> $orderId]);
      }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('customerorders.create');
        }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $customerorderitem = new Customerorderitem;
        $customerorderitem->ordersId = $request->ordersId;
        $customerorderitem->quantity = $request->quantity;
        $customerorderitem->other = 'none';
        $customerorderitem->unitId = $request->unitId;
        $customerorderitem->price = $request->price;
        $customerorderitem->productId = $request->productId;
        $customerorderitem->userId = Auth::id();
        $customerorderitem->stateId = 1;
        $customerorderitem->price = $request->price;
        $customerorderitem->save();
        return redirect()->route('customerorders.index',['orderId' => $customerorderitem->ordersId ])->with('success','A new order iterm Has Been created successfully');
    
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getProductbyidForOrderItem(Request $request){

        $productid = $request->productid;
      
        $porduct = Porduct::select('*')->where('id', $productid)->get();
        // echo "<pre>";
        //      print_r($porduct);
        //      exit;
        // Fetch all records
        $response['data'] =  $porduct;
      
        return response()->json($response);
      }
}
