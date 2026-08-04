<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;

use App\Models\Company;

use App\Models\Porduct;

use App\Models\Customer;

use App\Models\Order_item;

use App\Models\Orders;

use App\Models\Productpricing;

use App\Models\Type;

use App\Models\Users;

use App\Models\Customerorderitem;

use Illuminate\Support\Facades\Log;

use DB;

use Auth;



class OnlineOrdersController extends Controller

{

    /**

     * Display a listing of the resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function index(Request $request)

    {
        



        $orders = Orders::where('userId',auth()->user()->id)

                        ->orderBy('created_at', 'desc')

                        ->get();





      $mostOrderedItems = Order_item::select('order_items.productId', 'order_items.unitId', 'porducts.defaultSellingPice as default_selling_price', DB::raw('COUNT(*) as total'))

                                    ->join('orders', 'order_items.ordersId', '=', 'orders.id')

                                    ->leftJoin('porducts', 'order_items.productId', '=', 'porducts.id')

                                    ->where('orders.userId',auth()->user()->id)

                                    ->groupBy('order_items.productId', 'order_items.unitId', 'default_selling_price')

                                    ->orderByDesc('total')

                                    ->take(5)

                                    ->get();

                    

                



                        

    foreach ($mostOrderedItems as &$item) {

        $productId = $item['productId'];

        $unitId = $item['unitId'];

      

    

       

        $productPricing = ProductPricing::where('productId', $productId)->where('active','=','1')->value('price');

    

        

        if ($productPricing) {



            $item['final_selling_price'] = $productPricing;

       



        } else {



            $item['final_selling_price'] = $item['default_selling_price'];



        }



        $item['id'] =   $item['productId'];

        $item['productId'] =  Porduct::where('id', $productId )->value('name');

        $item['unitId'] =  Type::where('id',  $unitId )->value('name');

      



    }



    
         dd($mostOrderedItems);


        return view('onlineorders.index', ['orders' => $orders, 'favourates' => $mostOrderedItems]);



        

    }



    /**

     * Show the form for creating a new resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function create()

    {

        //

    }



    /**

     * Store a newly created resource in storage.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function store(Request $request)

    {

        //

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



    

    public function fetch(Request $request){



        Log::info('ndirikupinda ');

        dd('Reached get_suggestions method');

        return response()->json($response);

      }





    

    public function getProductbyidForOrderItem(Request $request){



        $products = Porduct::all();

        $productData = $products->map(function ($product) {

            return ["productName" => $product->name,

                    "product" => $product->id,



        ];

        });



        $charactr = $request->productname;

        Log::info( '----------'.$charactr);

    

        $query = request()->query('query', '');

        Log::info( $query);

        $suggestions = $productData->filter(function ($product) use ($charactr ) {

            return strpos(strtolower($product['productName']), strtolower( $charactr )) !== false;

        })->values();



        //Log::info($suggestion);

    

        return response()->json($suggestions);



      }





      public function get_suggestions(Request $request){


        $orderItemId = $request->id ;

        $orderItem = Order_item::find($orderItemId);
    
        if ($orderItem) {

            $orderItem->delete();

            $response = $orderItemId;
            
             return response()->json($response);

        } 


      

      }











    public function order (Request $request)

    {

        $user = auth()->user()->id;





        $user = Users::where('id', $user)->value('company');



        if($user){









        $orderitems = $request->input('productsArray');



        Log::info($orderitems);



        if(!$request->input('productsArray')) {

            $response = [

                'error' => 'The productsArray is null.',

            ];

        

            return response()->json($response, 400); 

        }





      



       





        $order = new Orders;

        $order->reference = 'online';

        $order->date = now();

        $order->other = 'now';

        $order->customerId = $user;

        $order->totalValue = 0.00;

        $order->datePlaced = now();

        $order->dueDate= now();

        $order->stateId = 61;

        $order->orderBy = 1;

        $order->userId = auth()->user()->id ;

        $order->save();



        foreach ( $orderitems as $orderitem){



            $id = $orderitem['productId'];

            





            $pack = Porduct::where('id',$id)->value('unitPackId');

            $qnt = $orderitem['quantity'] ;

            $price = $orderitem['pricePerBale'] ;







        $orderitem = new Order_item;

        $orderitem->ordersId = $order->id;

        $orderitem->customerId = $order->customerId;

        $orderitem->quantity =   $qnt;

        $orderitem->other = 'online';

        $orderitem->unitId =   $pack;

        $orderitem->price =   $price;

        $orderitem->openningQNT = $qnt;

        //$orderitem->dueDate = today();

        $orderitem->reference = 'online';

        $orderitem->productId =   $id ;

        $orderitem->stateId = 61;

        $orderitem->orderBy = 1;

        $orderitem->totalPrice = $request->totalPrice;

        $orderitem->userId = auth()->user()->id ;

        $orderitem->save();



        }

        

        // Process the $productsArray as needed



        return response()->json(['message' => 'Data receivedpppppppp successfully']);





           }else{



            $response = 500;





            return response()->json($response); 



    }





    }





    public function getProductbyidForOrderIte(Request $request){



  



        $id = $request->productname;

        $product = Porduct::find($id);



        if ($product) {





            $pack = $product->unitPackId;

            $packet = $product->unitTypeId;

            $colour = $product->color;

            $material = $product->materialTypeId;

            $length = $product->product_length;

            $width = $product->product_Width;

            $price = $product->defaultSellingPice; 

            $gusset = $product->gussetWidth; 

            $bagtype = $product->bagType;

           

        }







        $pack= Type::where('id', $pack)->value('name');

        $packet = Type::where('id',  $packet)->value('name');

        $colour = Type::where('id',  $colour )->value('name');

        $material = Type::where('id', $material)->value('name');

        $bagtype = Type::where('id',  $bagtype)->value('name');





        $product_price = Productpricing::where('productId',$id)->where('active','=',1)->value('price');



        





        if($product_price){



            $price = $product_price;



        }else{



            $price = $price;





        }









        $suggestions = [

            'pack' => $pack,

            'packet' => $packet,

            'colour' => $colour,

            'material' => $material,

            'length' =>  $length,

            'width' =>  $width ,

            'price' => $price,

            'gusset' => $price,

            'bagtype' => $bagtype,

            'id' => $id,

        ];



        





        return response()->json($suggestions);



      }



      public function deleteorderitem(Request $request) {

        
        $responsed = $request->productname;



        return response()->json($response);

    }







      



      public function getProduct(Request $request) {

        $query = $request->query('query', '');

        $quantity = $request->query('quantity', '');

    

        $product = Product::where('name', $query)->first();

    

        if ($product) {

            $product_name = $product->name;

            $product_id = $product->id;

    

            if ($product->costPrice !== null) {

                $product_price = (float) $product->costPrice;

    

                try {

                    $quantity = (int) $quantity;

                    $total_cost = $product_price * $quantity;

    

                    return response()->json([

                        "product_name" => $product_name,

                        "product_price" => $product_price,

                        "total_cost" => $total_cost,

                        "quantity" => $quantity,

                        "productId" => $product_id,

                    ]);

                } catch (Exception $e) {

                    return response()->json([

                        "product_name" => $product_name,

                        "product_price" => 0,

                        "total_cost" => 0,

                        "quantity" => 0,

                        "productId" => 0,

                    ]);

                }

            } else {

                return response()->json([

                    "product_name" => $product_name,

                    "product_price" => 0,

                    "total_cost" => 0,

                    "quantity" => 0,

                    "productId" => 0,

                ]);

            }

        } else {

            return response()->json([

                "product_name" => $product_name,

                "product_price" => 0,

                "total_cost" => 0,

                "quantity" => 0,

                "productId" => 0,

            ]);

        }

    }

    







 

  

  

    

   

    public function getSuggestion() {

        $products = Porduct::all();

        $productData = $products->map(function ($product) {

            return ["productName" => $product->name];

        });

    

        $query = request()->query('query', '');

        $suggestion = $productData->filter(function ($product) use ($query) {

            return strpos(strtolower($product['productName']), strtolower($query)) !== false;

        })->values();

    

        return response()->json($suggestion);

    }

    



    public function getProductbyidFor(Request $request){



        $productid = $request->productid;





     //Log::info('ndirikupinda '.$productid );



        $newPricing = ProductPricing::where('productId', $productid)

            ->where('active', '=', '1')

            ->value('price');

        

        $unit = Porduct::where('id', $productid)->value('unitPackId');

        $old = Porduct::where('id', $productid)->value('defaultSellingPice'); // Corrected variable name

        

        if ($newPricing !== null) {

            $values = [

                "unitTypeId" => $unit,

                "price"      => $newPricing,

            ];

        } else {

            $values = [

                "unitTypeId" => $unit,

                "price"      => $old,

            ];

        }

        

        $response['data'] = $values;

        

        return response()->json($response);

        

        

        

    }



}