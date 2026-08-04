<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Porduct;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use DB;

class Plasticmaterial extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    $category = $request->query('category');
    $fromDate = $request->query('from_date');
    $toDate = $request->query('to_date');

    if($category){
    
    // Log::info('Category: ' . $category);
    // Log::info('Frommmmmmm: ' . $fromDate);
    // Log::info('Tooooooo: ' . $toDate);
    
    // Set default dates if from_date is null (first day of current month to end of current month)
    if (is_null($fromDate)) {
        $fromDate = now()->startOfMonth()->toDateString();
        $toDate = $toDate ?? now()->endOfMonth()->toDateString();
    }
    
    // Set default to_date if still null
    if (is_null($toDate)) {
        $toDate = now()->toDateString();
    }



    // Log::info('final Frommmmmmm: ' . $fromDate);
    // Log::info('final Tooooooo: ' . $toDate);
    
    // Query jobccaarditems table
    $jobItems = DB::table('jobcarditems')
        ->select(
            DB::raw('SUM(CASE WHEN processId = 23 THEN qnt ELSE 0 END) as production_sum'),
            DB::raw('SUM(CASE WHEN processId = 24 THEN qnt ELSE 0 END) as bags_sum'),
            //DB::raw('SUM(CASE WHEN processId = 23 THEN outstanding ELSE 0 END) as production_sum_production'),
           // DB::raw('SUM(CASE WHEN processId = 24 THEN outstanding ELSE 0 END) as bags_sum_production')
        )
                ->where('materialId', $category)
                 ->whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate])
                ->first();
    
    //    Log::info($jobItems);
    // Get the orginal types query
    $types = DB::table('types')
       //->where('id', $category)
        ->where('groupType', '=', 'material')
        ->get();

       Log::info('data coming in from the query ' . $types);

    
    // Prepare response
    $response = [
        'from_date' => $fromDate,
        'to_date' => $toDate,
        'production_sum' => $jobItems->production_sum ?? 0,
        'bags_sum' => $jobItems->bags_sum ?? 0,
        // 'production_sum_production' => $jobItems->production_sum_production ?? 0,
        // 'bags_sum_production' => $jobItems->bags_sum_production ?? 0,
        'types' => $types
    ];


    return response()->json($response);

    }


        $response = DB::table('types')
                       ->where('groupType', '=', 'material')
                       ->get();
    
    return response()->json($response);
}

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        //
    }


        public function getProductdetails(Request $request)
    {
        // Get the 'data' parameter from the URL's query string.
        $productId = $request->query('data');

        // Check if a product ID was provided
        if (!$productId) {
            return response()->json([
                'success' => false,
                'message' => 'Product ID is missinnnnnnnng.'
            ], 400); // 400 Bad Request
        }

        // Retrieve the product from the database using the product ID.
        // I have corrected the typo Porduct to Product in the comments.
        $product = Porduct::select('*')->where('id', $productId)->get();

        // Check if the product was found and return a JSON response.
        if ($product->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], 404); // 404 Not Found
        }

        $response['data'] = $product;

   

        // Your existing logic for session data can be added here if needed.
        // $packagingLevelData = $request->session()->get('packagingLevel');

        return response()->json($response);
    }

}
