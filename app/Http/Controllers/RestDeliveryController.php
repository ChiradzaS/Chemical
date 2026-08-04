<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\Order_item;
use App\Models\Porduct;
use App\Models\Type;
use App\Models\TbDeliveryItem;
use App\Models\TbDelivery;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use DB;
use Auth;
use Carbon\Carbon;

class RestDeliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

       


                        $dataString = $request->query('data');
                        $listString = $request->query('list');

             if($dataString){



                    $deliver = json_decode(urldecode($dataString), true);
               
                    $deliveryData      = $deliver['delivery'] ?? null;
                   $deliveryItemsData       = $deliver['items'] ?? [];

                  // Log::info('1'.$deliveryItemsData);

                if ($deliveryData) {
                    // === Create Delivery ===
                            $delivery = new TbDelivery;
                            $delivery->reference                = $deliveryData['reference'] ?? null;
                            $delivery->customerId               = $deliveryData['customerId'] ?? null;
                            $delivery->vehicleReg               = $deliveryData['vehicleId'] ?? null;
                            $delivery->driver                   = $deliveryData['driverId'] ?? null;
                            $delivery->invoiceNo                = $deliveryData['invoiceNo'] ?? null;
                            $delivery->addressId                = $deliveryData['address'] ?? null;
                            $delivery->save();

                    // === Create deliver Items ===
                    foreach ($deliveryItemsData as $item) {


                            $deliveryitem = new TbDeliveryItem;
                            $deliveryitem->productId = $item['productId'] ?? null;
                            $deliveryitem->deliveryId = $delivery->id;
                            $deliveryitem->quantity = $item['quantity'] ?? null;

                            
                            $deliveryitem->unitId = Porduct::where('id', $item['productId'])
                                                            ->value('unitpackId');

                            $deliveryitem->save();

                                    
                    }

                    return response()->json([
                        'status' => 'JobCard + items created'
                    ]);
                }

            }


           if($listString){


                 



        $response = TbDelivery::join('tb_delivery_items', 'tb_deliveries.id', '=', 'tb_delivery_items.deliveryId')

                            ->select(

                                    'tb_deliveries.id              as tb_deliveries_id',
                                    'tb_deliveries.reference       as tb_deliveries_reference',                       
                                    'tb_deliveries.customerId      as tb_deliveries_customerId',
                                    'tb_deliveries.vehicleReg      as tb_deliveries_vehicleReg',
                                    'tb_deliveries.driver          as tb_deliveries_driver',
                                    'tb_deliveries.created_at      as tb_deliveries_created_at',
                                    'tb_deliveries.invoiceNo       as tb_deliveries_invoiceNo',
                                    'tb_deliveries.addressId       as tb_deliveries_addressId',
                                                        
                                    'tb_delivery_items.productId   as tb_delivery_items_productId',
                                    'tb_delivery_items.unitId      as tb_delivery_items_unitId',
                                    'tb_delivery_items.deliveryId  as tb_delivery_items_deliveryId',
                                    'tb_delivery_items.quantity    as tb_delivery_items_quantity'
                     

                                )
                                ->groupBy(

                                    'tb_deliveries.id', 
                                    'tb_deliveries.reference',                      
                                    'tb_deliveries.customerId', 
                                    'tb_deliveries.vehicleReg',
                                    'tb_deliveries.driver',
                                    'tb_deliveries.invoiceNo', 
                                    'tb_deliveries.addressId',
                                    'tb_deliveries.created_at',
                               
                                    'tb_delivery_items.productId', 
                                    'tb_delivery_items.unitId', 
                                    'tb_delivery_items.deliveryId',
                                    'tb_delivery_items.quantity'

                                )

                              
                                // ->where('tb_deliveries.created_at', '>=', $threeMonthsAgo)
                                // ->whereNull('job_cards.jobcardType')
                                // ->where('job_cards.stateId','<>', 45)
                                 //->take(100) // or ->limit(30)
                                ->orderBy('tb_deliveries.created_at', 'desc')
                                ->get();

        
 
                                return response()->json($response);


                                }


        $productId = $request->productId;
        $statusId = $request->statusId;
        

        if ($statusId == 3) {

            // Update the customerproduct table
             DB::table('customer_prices')
                ->where('id', $productId)
                ->update(['stateId' => 3]);
                
            // Return success message with updated record
            $updatedRecord = DB::table('customer_prices')
                                ->where('id', $productId)
                                ->first();

            $data = 1;

            return response()->json($data);

        }
        
        // If statusId is not 2, just return the original data
        DB::table('customer_prices')
             ->where('id', $productId)
             ->update(['stateId' => 2]);

           $data = 1;
            
        return response()->json($data);
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

       

              $dataString = $request->query('data');

              $delive = json_decode(urldecode($dataString), true);
               
                $deliveryData      = $deliver['delivery'] ?? null;
                $deliveryItemsData       = $deliver['items'] ?? [];

                if ($deliveryData) {
                    // === Create Delivery ===
                    $delivery = new TbDelivery;
                    $delivery->reference           = $deliveryData['reference'] ?? null;
                    $delivery->customerId          = $deliveryData['customerId'] ?? null;
                    $delivery->vehicleReg          = $deliveryData['vehicleReg'] ?? null;
                    $delivery->driver              = $deliveryData['driver'] ?? null;
                    $delivery->invoiceNo           = $deliveryData['invoiceNo'] ?? null;
                    $delivery->addressId           = $deliveryData['addressId'] ?? null;
                    $delivery->save();

                    // === Create deliver Items ===
                    foreach ($reactitems as $item) {


                    $deliveryitem = new TbDeliveryItem;
                    $deliveryitem->productId                = $deliveryItemsData['productId'] ?? null;
                    $deliveryitem->unitId                   = $deliveryItemsData['unitId'] ?? null;
                    $deliveryitem->deliveryId               = $delivery->id;
                    $deliveryitem->unitId                   = $deliveryItemsData['unitId'] ?? null;
                    $deliveryitem->quantity                 = $deliveryItemsData['quantity'] ?? null;
                    $deliveryitem->save();
                                    
                    }

                    return response()->json([
                        'status' => 'JobCard + items created'
                    ]);
                }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

              

        $orderitemId = $request->query('orderitemId'); 
        $orderId = $request->query('orderId');
        $quantity = $request->query('quantity');
        $id = $request->query('orderitemdeduct');

    


        if ($quantity){

            $jobcarditem = DB::table('order_items')->where('id', $id)->first();

            if ($jobcarditem) {
               
                $newQuantity = $jobcarditem->quantity - $quantity;
            
               
                DB::table('order_items')
                    ->where('id', $id)
                    ->update([
                        'quantity' => $newQuantity,
                        'stateId' => 44,
                    ]);

                    $response =  $id;

                    return response()->json([$response]);

            }


            





        }

        
        if ($orderitemId)
        {

            $Id         = DB::table('order_items')->where('id',$orderitemId)->pluck('ordersId');
            $orderinfo  = DB::table('orders')->where('id',$Id)->get();
            $orderitems = DB::table('order_items')->where('id', $orderitemId )->get();

            

            return response()->json([

                        'orderinfo' => $orderinfo,
                        'orderitems' => $orderitems

                    ]);

        }



        if ($orderId) {

            $orderinfo  = DB::table('orders')->where('id',$orderId)->get();
            $orderitems = DB::table('order_items')->where('ordersId',$orderId)->get();

            return response()->json([

                'orderinfo' => $orderinfo,
                'orderitems' => $orderitems

            ]);

       
       
       
        }



        
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





public function delete(Request $request)
{
    try {
        // Get the delivery ID from query parameter
        $deliveryId = $request->query('data');
        
        // If it's JSON encoded, decode it
        if (json_decode($deliveryId)) {
            $deliveryId = json_decode(urldecode($deliveryId), true);
        }
        
        Log::info('Deleting delivery with ID: ' . $deliveryId);
        
        // Find and delete the delivery from the database
        $delivery = TbDelivery::findOrFail($deliveryId);
        $delivery->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Delivery deleted successfully',
            'id' => $deliveryId
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error deleting delivery: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to delete delivery',
            'error' => $e->getMessage()
        ], 500);
    }
}



}
