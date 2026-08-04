<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Log;
use DB;
use Exception;
//use App\Http\Controllers\CustomerallocationController;

class CustomerAllocationController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {



        $url = env('APP_URL');

        
        $maxRetries = 3; // Maximum number of retries
        $retryDelay = 2; // Delay between retries in seconds

        

  
        // $service_url = $url.'/qryallocations/index';
        // $curl = curl_init($service_url);
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // $curl_response = curl_exec($curl);

        // //dd($curl_response);         OLD METHOD
    
        
        // if ($curl_response === false) {
           
        //     if ($curl_response === false) {
        //         $info = curl_getinfo($curl);
        //         curl_close($curl);
        //         die('error occurred during curl exec. Additional info: ' . var_export($info));
        //     }
        // }
    
        // curl_close($curl);
    
        // // Parse the response and pass it to the view
        // $users = json_decode($curl_response);
        // //dd($users );
        // return view('customerallocation.index', ['users' => $users]);




        //---------------------------------------------------------------------------------------------------------------------------

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // Construct service URL
                $service_url = $url . '/qryallocations/index';
    
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
                    throw new Exception(curl_error($curl));
                }
    
                // Close cURL resource
                curl_close($curl);
    
                // Decode JSON response
                $users = json_decode($curl_response);
    
                // Validate response
                if (empty($users)) {
                    throw new Exception('Empty or invalid response');
                }
    
                // Successful request
                return view('customerallocation.index', ['users' => $users]);
    
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

    }
    


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        dd('hoyo');
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
        $userId = $id;

        $url = env('APP_URL');

              
        $maxRetries = 3; // Maximum number of retries
        $retryDelay = 2; // Delay between retries in seconds



        // $service_url = $url.'/qryallocations/update?user='.$userId;
        // $curl = curl_init($service_url);
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // $curl_response = curl_exec($curl);
    
       
        // if ($curl_response === false) {
    
            
        //     if ($curl_response === false) {
        //         $info = curl_getinfo($curl);
        //         curl_close($curl);
        //         die('error occurred during curl exec. Additional info: ' . var_export($info));
        //     }
        // }
    
        // curl_close($curl);
    
        // // Redirect to the desired route after successful execution
        // return redirect()->route('customerallocation.index');



        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // Construct service URL
                $service_url = $url.'/qryallocations/update?user='.$userId;
    
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
                    throw new Exception(curl_error($curl));
                }
    
                // Close cURL resource
                curl_close($curl);
    
                // Decode JSON response
                $users = json_decode($curl_response, true);
    
                // Validate response
                if (empty($users)) {
                    throw new Exception('Empty or invalid response');
                }
    
                // Successful request
                return redirect()->route('customerallocation.index');
    
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
     * @param  int  $user Users
     * @return \Illuminate\Http\Response
     */
    public function destroy( Users $user)
    {
       
        //
    }



    public function allocatecustomer(Request $request)
{

    $user = $request->user;
    $customer = $request->CustomerId;

    $url = env('APP_URL');

    $maxRetries = 3; // Maximum number of retries
    $retryDelay = 2; // Delay between retries in seconds



//     $service_url =  $url.'/qryallocations/store?user='.$user .'&customer='.$customer;
//     $curl = curl_init($service_url);
//     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//     $curl_response = curl_exec($curl);

//    // dd('wooooooooowowowow');

    
//     if ($curl_response === false) {

      
//         if ($curl_response === false) {
//             $info = curl_getinfo($curl);
//             curl_close($curl);
//             return response()->json(['error' => 'Failed to connect to both external and localhost servers.', 'additional_info' => $info], 500);
//         }
//     }

//     curl_close($curl);

//     // Parse the response or handle it accordingly
//     $response = 88;

//     return response()->json($response);



    
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            // Construct service URL
            $service_url = $url.'/qryallocations/store?user='.$user .'&customer='.$customer;

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
                throw new Exception(curl_error($curl));
            }

            // Close cURL resource
            curl_close($curl);

            // Decode JSON response
            $users = json_decode($curl_response, true);

            // Validate response
            if (empty($users)) {
                throw new Exception('Empty or invalid response');
            }

            // Successful request
            $response = 88;

            return response()->json($response);

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
}

}
