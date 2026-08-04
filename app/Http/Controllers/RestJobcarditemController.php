<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
namespace App\Http\Controllers;
use App\Models\Orders;
use App\Models\Jobcarditem;
use App\Models\Jobcard;
use App\Models\Porduct;
use App\Models\ChemicalJobcard;
use App\Models\ChemicalProduct;
use App\Models\Chemicaljobcarditem;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use DB;
use Auth;

class RestJobcarditemController extends Controller
{
    public function index(Request $request)
    {


        return response()->json($data);
    }




    public function store(Request $request)
    {



           return response()->json($responds);
    }




public function show(Request $request)
{
    $id = $request->input('id');

    $dashPos    = strpos($id, '-');
    $itemId     = $dashPos !== false ? substr($id, 0, $dashPos) : $id;
    $wpProduct  = $dashPos !== false ? (int) substr($id, $dashPos + 1) : 0;

    $item      = Chemicaljobcarditem::where('id', $itemId)->select('jobCardId', 'productId')->first();
    $jobcardId = $item->jobCardId;
    $productId = $item->productId;

    if ($dashPos !== false) {
        $jobcarditems = Chemicaljobcarditem::where('jobCardId', $jobcardId)->where('processId', 24)->get();
        $productId    = $jobcarditems->first()?->productId ?? $productId;
    } else {
        $jobcarditems = Chemicaljobcarditem::where('id', $itemId)->get();
    }

    $product = ChemicalProduct::where('id', $productId)->get();
    $jobcard = ChemicalJobcard::where('id', $jobcardId)->get();

    return response()->json([
        'product'      => $product,
        'jobcarditems' => $jobcarditems,
        'jobcard'      => $jobcard,
        'wpProduct'    => $wpProduct,
    ]);
}
       

      

    




    public function update(Request $request, $id)
    {
        $response = 1 ;
                
        return response()->json( $response);
    }




    public function destroy(Request $request)
    {

                
            return response()->json( $response);



    

    }
}
