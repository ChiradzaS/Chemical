<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerPrice;
use Illuminate\Support\Facades\Log;
use Exception;

class OnlineNewProducts extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $url = env('APP_URL');

        
        $maxRetries = 3; // Maximum number of retries
        $retryDelay = 2;

        // $service_url = 'http://www.sailingpackaging.co.za/queryrest/qrygetnewproducts';
        // $curl = curl_init($service_url);
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // $curl_response = curl_exec($curl);
    
        
        // if ($curl_response === false) {
    
          
        //     if ($curl_response === false) {
        //         $info = curl_getinfo($curl);
        //         curl_close($curl);
        //         return response()->json(['error' => 'Failed to connect to both external and localhost servers.', 'additional_info' => $info], 500);
        //     }
        // }
        // curl_close($curl);
        // $data = json_decode($curl_response);

    
        // return view('onlineproducts.index', ['data' => $data ]);


        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // Construct service URL
                $service_url = $url.'/queryrest/qrygetnewproducts';
    
                // Initialize cURL
                $curl = curl_init($service_url);
                
                // Set cURL options
                curl_setopt_array($curl, [
                    CURLOPT_RETURNTRANSFER => true,  // Return transfer as string
                    CURLOPT_TIMEOUT => 10,           // Timeout after 10 seconds
                    CURLOPT_FAILONERROR => true,     // Fail on HTTP errors
                ]);
    
                // Execute cURL request
                $curl_response = curl_exec($curl);
    
                // Check for cURL errors
                if ($curl_response === false) {
                    return view('errorpage', [
                        'message' => 'Sorry, there was an error fetching allocations after ' . $maxRetries . ' attempts.'
                    ]);
                }
    
                curl_close($curl);
                $data = json_decode($curl_response);
        
            
                return view('onlineproducts.index', ['data' => $data ]);
    
            } catch (Exception $e) {
                // Log the error
                Log::error('Allocation fetch attempt ' . $attempt . ' failed: ' . $e->getMessage());
    
                // Last attempt
                if ($attempt === $maxRetries) {
                    return view('errorpage', [
                        'message' => 'Sorry, there was an error fetching allocations after ' . $maxRetries . ' attempts.'
                    ]);
                }
    
                // Wait before retrying
                sleep($retryDelay);
            }
        }
        //
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
}
