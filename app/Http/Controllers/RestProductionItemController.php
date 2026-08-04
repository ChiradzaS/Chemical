<?php


namespace App\Http\Controllers;

use App\Models\Jobcarditem;
use App\Models\Productionitem;
use App\Models\Production;
use App\Models\Workspace;
use App\Models\Stock;
use App\Models\StocksTrans;
use Illuminate\Http\Request;
use App\Models\Porduct;
use App\Models\Type;
use App\Models\DocumentAudit;
use App\Models\Oder;
use App\Library\SerialNo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use DB;
use Auth;
use Carbon\Carbon;
use DateTime;
use App\Jobs\StoreProductionItem;
use App\Http\Controllers\Controller;


class RestProductionItemController extends Controller
{


    public function item(Request $request)
    {

       $user =  $request->input('userId');
       $job =  $request->input('jobcarditemId');
 
       $todaysDate = Carbon::today();

       $response  = DB::table('productionitems')
                            
                            ->where('jobcarditemId',  $job)
                            ->whereDate('created_at', '=', $todaysDate)
                            ->orderBy('created_at','desc')
                            ->get();

                          

         return response()->json($response);


    }
    
    public function store(Request $request)
{

    $storeType =  $request->input('storeType');
    $productId = $request->input('productId');
    $weight_per_bale = 0 ;

    if($storeType){

        $existingCode = Productionitem::where('unique_code', $request->input('code'))->exists();

        if ($existingCode) {

            // Log that the item is already saved
            \Log::info('Production item already exists', [
                'unique_code' => $request->input('code'),
                'timestamp' => now()
            ]);

            return;

        }

        $processId = $request->input('processId');
        $quantity =  $request->input('qnt') ?? 0;

        // if ($processId == 924) {
        //     // Fetch only the 'kgs' column from the products table where productId matches
        //     $quantity =  $request->input('weight')?? 1;
        //     $weight_per_bale = Porduct::where('id', $productId)->value('maxWeightPerProduct') ?? 0;
        // }

        $currentHour = now()->hour;
        $currentDate = now();

        $currentDateTime = new DateTime();
        $currentTime = $currentDateTime->format('H:i');

        if ($currentTime >= '00:00' && $currentTime <= '06:00') {
            $currentDate->modify('-1 day');
        }

        $data = $request->all();
        $data['qnt'] = $quantity;
        $data['weight_per_bale'] = $weight_per_bale;
        $data['dateCreated'] = $currentDate;
        $data['SerialNo'] = SerialNo::generateSerialNumber();

        StoreProductionItem::dispatch($data);

        $productionitem['productionItemId'] =  1212;
        $productionitem['jobcardQuantity'] =  10000;
        $productionitem['barcode'] =  123456789;
        $productionitem['balestickers']    =  1;
        $productionitem['packetstickers']  =  1;

        $response = $productionitem;

        return response()->json($response);

    }


    $processId = $request->input('processId');

    $weight_per_bale = 0;

    $productionitem = new Productionitem;
    $productionitem->productionId         = $request->input('productionId');
    $productionitem->jobcarditemId        = $request->input('jobcarditemId');
    $productionitem->other                = 'none';
    $productionitem->productId            = $request->input('productId');
    $productionitem->userId               = $request->input('userId');
    $productionitem->qnt                  = $request->input('qnt');
    $productionitem->unitId               = $request->input('unitId');
    $productionitem->processId            = $request->input('processId');
    $productionitem->machineId            = $request->input('machineryId');
    $productionitem->tms                  = date('H:i:s');
    $productionitem->employeeId           = $request->input('userId');
    $productionitem->shiftId              = $request->input('shiftId');
    $productionitem->weight               = $request->input('weight');
    $productionitem->wpProduct            = $request->input('wpProduct');
    $productionitem->weightState          = $request->input('weightState');

    $productionitem->weight_per_bale      = $weight_per_bale;
    $productionitem->save();

    $productionitem['productionItemId']  =  $productionitem->id;
    $productionitem['jobcardQuantity']   =  10000;
    $productionitem['barcode']           =  123456789;
    $productionitem['balestickers']      =  1;
    $productionitem['packetstickers']    =  1;

    $response = $productionitem;

    return response()->json($response);
}

}
