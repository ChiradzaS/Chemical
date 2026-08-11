<?php
namespace App\Http\Controllers;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use DB;
use Auth;

class TypeController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
public function index(Request $request)
{

  $url = env('APP_URL');
  $maxRetries = 3; 
  $retryDelay = 2; 
  
    $action = $request->get('action');

    if( $action <> null && trim($action, ' ') == 'query'){

        $searchTerm = $request->input('searchInput');

        $customerIdComp = '<>';
        if ( $searchTerm <> null) {
         
          $customerIdComp = 'Like';
        } 

        $data['types'] = Type::where('name',''.$customerIdComp,'%'.$searchTerm.'%')->get();

                                      return view('types.index', $data);
        }

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
              try {
                  // Make the HTTP request
                  $response = Http::timeout(10) // Set a timeout of 10 seconds
                                    ->retry(3, 1000) // Retry 3 times with a 1-second delay
                                    ->get($url.'/qrytype/index');

                  // Check if the request was successful
                  if ($response->successful() ){

                    $data = json_decode($response);

                    return  view('types.index ', ['data' => $data] );
            
                  } else {
                      // Throw an exception if the request fails
                      return view('errorpage', [
                        'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
                    ]);
            
                  }
              } catch (Exception $e) {
                  // Log the error
                  Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
                  return view('errorpage', [
                    'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
                ]);

                  if ($attempt === $maxRetries) {
                    return view('errorpage', [
                        'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
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
public function create()
{
return view('types.create');
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
        'name' => 'required',
        'groupType' => 'required',
        // decimals allowed: 0.500 is a 500 ml container size
        'value' => 'nullable|string|max:50',
    ]);

    $type = new Type();
    $type->name = $request->name;
    $type->groupType = $request->groupType;

    // Use request value, otherwise default to 1
    $type->value = $request->filled('value') ? $this->normaliseValue($request->value) : 1;

    // description was previously copied from `value`, which left every sized
    // row described as a bare number — the name is the sensible fallback
    $type->description = $request->filled('description') ? $request->description : $type->name;
    $type->level = null;
    $type->parentKey = null;
    $type->topValue = null;
    $type->childType = null;
    $type->start_time = null;
    $type->end_time = null;
    $type->userId = Auth::id();
    $type->label = null;

    $type->save();

    return redirect()
        ->route('types.index')
        ->with('success', 'Type created successfully.');
}

/**
* A comma decimal ("0,500") is a common typo and parses as NaN downstream,
* so it is corrected here. Non-numeric values pass through untouched, since
* this column holds plain text for some group types.
*/
private function normaliseValue($value)
{
    $trimmed = trim((string) $value);

    if ($trimmed === '') {
        return $trimmed;
    }

    $candidate = str_replace(',', '.', $trimmed);

    return is_numeric($candidate) ? $candidate : $trimmed;
}

/**
* Display the specified resource.
*
* @param  \App\type  $type
* @return \Illuminate\Http\Response
*/
public function show(Type $type)
{
return view('types.show',compact('type'));
} 
/**
* Show the form for editing the specified resource.
*
* @param  \App\Type  $type
* @return \Illuminate\Http\Response
*/
public function edit(Type $type)
{
return view('types.edit',compact('type'));
}
/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\type  $type
* @return \Illuminate\Http\Response
*/
public function update(Request $request, $id)
{
    /* Only name and groupType are genuinely required. The rest were marked
       required but are left blank on most rows, so every edit was failing
       validation before it reached the database. */
    $request->validate([
    'name' => 'required',
    'groupType' => 'required',
    'description' => 'nullable|string',
    'value' => 'nullable|string|max:50',
    'level' => 'nullable|integer',
    'parentKey' => 'nullable|integer',
    'topValue' => 'nullable|integer',
    'childType' => 'nullable|string|max:100',
    'label' => 'nullable|string|max:100',
    ]);

    $value = $this->normaliseValue($request->value);

    $data = [
   
        'id' => $id,
        'name' => $request->name,
        'description' => $request->description,
        'value' => $value,
        'level' => $request->level,
        'parentKey' => $request->parentKey,
        'groupType' => $request->groupType,
        'topValue' => $request->topValue,
        'childType' => $request->childType,
        'userId' =>  Auth::id(),
        'lable' => $request->label,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
    
      ];
    
    
      
      $url = env('APP_URL');
      $maxRetries = 3; 
      $retryDelay = 2;

      for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            // Make the HTTP request
            $response = Http::timeout(10) // Set a timeout of 10 seconds
                              ->retry(3, 1000) // Retry 3 times with a 1-second delay
                              ->get($url.'/qrytype/update',$data);

            // Check if the request was successful
            if ($response->successful() ){

        $orderId = $response->json($response); 

        $type = Type::find($id);
        $type->name         = $request->input('name');
        $type->description  = $request->input('description');
        $type->value        = $value;
        $type->level        = $request->input('level') ;
        $type->parentKey    = $request->input('parentKey');
        $type->groupType    = $request->input('groupType');
        $type->topValue     = $request->input('topValue') ?? 0;
        $type->childType    = $request->input('childType');
        // userId comes from the session — it is not a form field, and reading
        // it from the request wrote null on every update
        $type->userId       = Auth::id();
        $type->start_time   = $request->input('start_time');
        $type->end_time     = $request->input('end_time');
        // the form field is `label`; reading `lable` always came back null
        $type->label        = $request->input('label');
        $type->save();
        
        return redirect()->route('types.index')->with('success','Update successfully');
      
            } else {
                // Throw an exception if the request fails
                return view('errorpage', [
                  'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
              ]);
      
            }
        } catch (Exception $e) {
            // Log the error
            Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
            return view('errorpage', [
              'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
          ]);

            if ($attempt === $maxRetries) {
              return view('errorpage', [
                  'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
              ]);
          }
      
            // Wait before retrying
            sleep($retryDelay);
        }
      }


    return redirect()->route('types.index')
    ->with('success','A new Type has been updated successfully.');
    }
/**
* Remove the specified resource from storage.
*
* @param  \App\Type  $type
* @return \Illuminate\Http\Response
*/
public function destroy(Type $type)
{
$type->delete();
return redirect()->route('types.index')
->with('success','A Type has been deleted successfully');
}

public function clone(Request $request)
{
    $product = $request->productid;

    $grouptype = $request->input('productId');

    $type  = Type::select('*')->where('groupType', $product)->get();

    $response['data'] = $type ;

    return response()->json($response);
}


 
public function actionview(Request $request) {
 
    $id =  $request->job;
  
    
    $url = env('APP_URL1');
  $maxRetries = 3; 
  $retryDelay = 2; 

  for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Make the HTTP request
        $response = Http::timeout(10) // Set a timeout of 10 seconds
                          ->retry(3, 1000) // Retry 3 times with a 1-second delay
                          ->get($url.'/qryjobcards/show?id='.$id);

        // Check if the request was successful
        if ($response->successful() ){
  
          $jsonResponse = json_decode( $response, true);
    
  
     $product = $jsonResponse['product'] ?? null;
     $jobcarditem = $jsonResponse['jobcarditems'] ?? null;
     $jobcard = $jsonResponse['jobcard'] ?? null;

      return view('job_cards.edit',['product' => $product,  'jobcarditems' =>  $jobcarditem   ,  'job_card' =>  $jobcard ]);
  
        } else {
            // Throw an exception if the request fails
            return view('errorpage', [
              'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
          ]);
  
        }
    } catch (Exception $e) {
        // Log the error
        Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
        return view('errorpage', [
          'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
      ]);

        if ($attempt === $maxRetries) {
          return view('errorpage', [
              'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
          ]);
      }
  
        // Wait before retrying
        sleep($retryDelay);
    }
  }
  
  }
   
   public function actionupdate(Request $request) {
   
     $id =  $request->job;
  
     
     $url = env('APP_URL1');
     $maxRetries = 3; 
     $retryDelay = 2; 

     for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
      try {
          // Make the HTTP request
          $response = Http::timeout(10) // Set a timeout of 10 seconds
                            ->retry(3, 1000) // Retry 3 times with a 1-second delay
                            ->get($url.'/qryjobcards/show?id='.$id);

          // Check if the request was successful
          if ($response->successful() ){
    
            $jsonResponse = json_decode( $response, true);
     
  
            $product = $jsonResponse['product'] ?? null;
            $jobcarditem = $jsonResponse['jobcarditems'] ?? null;
            $jobcard = $jsonResponse['jobcard'] ?? null;

             return view('job_cards.edit',['product' => $product,  'jobcarditems' =>  $jobcarditem   ,  'job_card' =>  $jobcard ]);
    
          } else {
              // Throw an exception if the request fails
              return view('errorpage', [
                'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
            ]);
    
          }
      } catch (Exception $e) {
          // Log the error
          Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
          return view('errorpage', [
            'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
        ]);

          if ($attempt === $maxRetries) {
            return view('errorpage', [
                'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
            ]);
        }
    
          // Wait before retrying
          sleep($retryDelay);
      }
    }
   
   }
   
   public function actiondelete(Request $request) {
   
     $id =  $request->job;
     
     $url = env('APP_URL1');
     $maxRetries = 3; 
     $retryDelay = 2; 

     for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
      try {
          // Construct service URL with proper URL encoding
          $service_url = $url . '/qryjobcards/destroy?'.http_build_query(['id' => $id]);
          
          // Initialize cURL
          $curl = curl_init($service_url);
          
          // Set comprehensive cURL options
          curl_setopt_array($curl, [
              CURLOPT_RETURNTRANSFER => true,  // Return transfer as string
              CURLOPT_TIMEOUT => 10,           // Timeout after 10 seconds
              CURLOPT_FAILONERROR => false,    // Don't fail on HTTP errors to check response
              CURLOPT_SSL_VERIFYPEER => true,  // Verify SSL certificate
              CURLOPT_SSL_VERIFYHOST => 2,     // Verify host name in SSL certificate
              CURLOPT_HTTPHEADER => [
                  'Accept: application/json',
                  'Content-Type: application/json'
              ]
          ]);
          
          // Execute cURL request
          $curl_response = curl_exec($curl);
          
          // Check for cURL errors
          if ($curl_response === false) {
              throw new Exception(curl_error($curl));
          }
          
          // Get HTTP status code
          $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
          
          // Close cURL resource
          curl_close($curl);
          
          // Validate HTTP status
          if ($http_status !== 200) {
              throw new Exception("HTTP Error: {$http_status}");
          }
          
          // Check for successful deletion
          if ($curl_response == '1' ) {

            return redirect()->route('job_cards.index')
            ->with('success','Order successfully been deleted');
        
        
          } else {
              // Throw exception for unsuccessful deletion
              throw new Exception('Deletion failed: Unexpected response');
          }
      } catch (Exception $e) {
          // Log the error
          Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
          return view('errorpage', [
            'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
        ]);
          
          // Last attempt
          if ($attempt === $maxRetries) {
              // Log final failure
              Log::error('Order item destroy failed after ' . $maxRetries . ' attempts', [
                  'order_item_id' => $id
              ]);
              
              // Redirect with error message
              return redirect()->route('order_items.index')
                  ->with('error', 'Failed to delete order item. Please try again later.');
          }
          
          // Wait before retrying
          sleep($retryDelay);
      }
    }
   
   
   }

}