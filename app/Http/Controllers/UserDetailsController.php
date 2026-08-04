<?php
namespace App\Http\Controllers;
use App\Models\UserDetails;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Customerorder;
use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Library\UniqueCode;
use DB;
use Auth;

class UserDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['usersdetails'] = UserDetails::orderBy('id','asc')->paginate(20);
        return view('userdetails.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $usertypes=DB::table('types')->where('groupType','user')->get();
        $userTypeId = $request->userTypeId;
        $userTypeName = '';
        

        $userTypeObjList = array();
        foreach ($usertypes as $usertype) {
            if ($userTypeId == $usertype->id) {
                $userTypeName = $usertype->name;  
            }
        }

        $usersWithoutDetails = DB::select("
        SELECT *
        FROM users
        WHERE id NOT IN (
            SELECT userId
            FROM user_details
        )
    ");

                // echo "<pre>";
                // print_r($usersWithoutDetails);
                // exit;

        //$users
        View::share('usersWithoutDetails',$usersWithoutDetails);
        View::share('userTypeObjList',$userTypeObjList);
        
        return view('userdetails.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([ 'name' => 'required',]);

        $usertypes=DB::table('types')->where('groupType','user')->get();
        $userTypeId = $request->userTypeId;
        $userTypeName = '';
        

        $userTypeObjList = array();
        foreach ($usertypes as $usertype) {
            if ($userTypeId == $usertype->id) {
                $userTypeName = $usertype->name;  
            }
        }

        
       

        $orderId = -9;
        $order = null;
        $userTypeName = trim($userTypeName);
        // echo "<pre>";
        // print_r($userTypeObjList);
        // exit;
        
        if ($userTypeName == 'customer') {
            $userTypeObjList = DB::table('customers')->get();

            // $barcode = UniqueCode::uniqidRealVal();
            // $order = new Orders;
            // $order->reference = $request->userTypeById.' '.now().' '.$barcode;
            // $order->date = now();
            // $order->other = ' ';
            // $order->customerId = $request->userTypeById;
            // $order->totalValue = 0;
            // $order->stateId = 0;
            // $order->save();
            // $orderId = $order->id;

            $barcode = UniqueCode::uniqidRealVal();
            $ordercustomer = new Customerorder;
            $ordercustomer->reference = $request->userTypeById.' '.now().' '.$barcode;
            $ordercustomer->date = now();
            $ordercustomer->other = ' ';
            $ordercustomer->customerId = $request->userTypeById;
            $ordercustomer->totalValue = 0;
            $ordercustomer->stateId = 0;
            $ordercustomer->userId = Auth::id();
            $ordercustomer->save();
            $orderId = $ordercustomer->id;

           
    
            
        }

        $userDetail = new UserDetails;
        $userDetail->name = $request->name;
        $userDetail->surname = $request->surname;
        $userDetail->userId = Auth::id();
        $userDetail->userTypeById = $request->userTypeById;
        $userDetail->cellPhone = $request->cellPhone;
        $userDetail->telephone = $request->telephone;
        $userDetail->userTypeId = $request->userTypeId;
        $userDetail->orderId = $orderId;
        $userDetail->emailAddress = $request->emailAddress;
        $userDetail->userPosition= $request->userPosition;
        $userDetail->userName= $request->userName;
        $userDetail->securityLevel = $request->securityLevel;
        $userDetail->other = $request->other;
        $userDetail->save();



        return redirect()->route('userdetails.index')
                         ->with('success','A new User has been created successfully.');
            
    }

    /**
    * Display the specified resource.
    *
    * @param  \App\Models\UserDetails  $userDetail
    * @return \Illuminate\Http\Response
    */
    public function show(Request $request, UserDetails $userdetail)
    {
        // echo "<pre>";
        // print_r($userdetail);
        // exit;

        $usertypes=DB::table('types')->where('groupType','user')->get();

        $userTypeId = $userdetail->userTypeId;
        $userTypeName = '';

        $userTypeObjList = array();
        foreach ($usertypes as $usertype) {
            if ($userTypeId == $usertype->id) {
                $userTypeName = $usertype->name;  
            }
        }

        $userTypeName = trim($userTypeName);
        if ($userTypeName == 'customer') {
            $userTypeObjList = DB::table('customers')->get();
        }
        else if ($userTypeName == 'employee') {
            $userTypeObjList = DB::table('employees')->get();
        }
        else if ($userTypeName == 'controller') {
            $userTypeObjList = DB::table('employees')->get();
        }
        else if ($userTypeName == 'administrator') {
            $userTypeObjList = DB::table('employees')->get();
        }
        else if ($userTypeName == 'Supplier') {
            $userTypeObjList = array(['id' => '-9', 'name' => 'None']); 
        }
       
        View::share('userTypeObjList',$userTypeObjList);

        return view('userdetails.show',compact('userdetail'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserDetails  $userDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, UserDetails $userdetail)
    {
        $usertypes=DB::table('types')->where('groupType','user')->get();

        $userTypeId = $userdetail->userTypeId;
        $userTypeName = '';

        $userTypeObjList = array();
        foreach ($usertypes as $usertype) {
            if ($userTypeId == $usertype->id) {
                $userTypeName = $usertype->name;  
            }
        }

        $userTypeName = trim($userTypeName);
        if ($userTypeName == 'customer') {
            $userTypeObjList = DB::table('customers')->get();
        }
        else if ($userTypeName == 'employee') {
            $userTypeObjList = DB::table('users')->get();
        }
        else if ($userTypeName == 'controller') {
            $userTypeObjList = DB::table('users')->get();
        }
        else if ($userTypeName == 'administrator') {
            $userTypeObjList = DB::table('users')->get();
        }
        else if ($userTypeName == 'Supplier') {
            $userTypeList = array(['id' => '-9', 'name' => 'None']); 
        }
       
        View::share('userTypeObjList',$userTypeObjList);

        $action = $request->action;

        if ($action == 'Delete') {
            $userdetail->delete();
            return redirect()->route('userdetails.index')
                   ->with('success','A User has been deleted successfully');
        }
        else if ($action == 'View') {
            return view('userdetails.show',compact('userdetail'));  
        }
        //  echo "<pre>";
        //  print_r(''.$action.' User : '.$userdetail);
        //  exit;

        return view('userdetails.edit',compact('userdetail'));
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
            'name' => 'required',
            ]);
            $userDetail = UserDetails::find($id);
            $userDetail->name = $request->name;
            $userDetail->userId = $request->userId;
            $userDetail->userTypeById= $request->userTypeById;
            $userDetail->surname = $request->surname;
            $userDetail->cellPhone = $request->cellPhone;
            $userDetail->telephone= $request->telephone;
            $userDetail->userTypeId= $request->userTypeId;
            $userDetail->emailAddress = $request->emailAddress;
            $userDetail->userPosition= $request->userPosition;
            $userDetail->userName= $request->userName;
            $userDetail->securityLevel = $request->securityLevel;
            $userDetail->other = $request->other;
            $userDetail->save();

            //echo "<pre>";
            //print_r($userDetail);
            //exit;

            return redirect()->route('userdetails.index')
            ->with('success',' User has been updated successfully.');
    }

   
    
    public function getUserTypeList(Request $request){
    
      $userType = $request->userType;
    
      $userTypeList = null;
      if ($userType == 'customer') {
          $userTypeList = DB::table('customers')
                              ->get(['id','name']);
      }
      else if ($userType == 'Supplier') {
          $userTypeList = array(['id' => '-9', 'name' => 'None']); 
      }
      else if ($userType == 'employee') {
        $userTypeList = DB::table('users')
                            ->get(['id','name']);
      }
      else if ($userType == 'controller') {
        $userTypeList = DB::table('users')
                            ->get(['id','name']);
      }
      else if ($userType == 'administrator') {
        $userTypeList = DB::table('employees')
                            ->get(['id','name']);
      }
      
      // Fetch all records
      $response['data'] = $userTypeList;
    
      return response()->json($response);
    }


/**
* Remove the specified resource from storage.
*
* @param  \App\Models\UserDetails  $userDetails
* @return \Illuminate\Http\Response
*/
public function destroy(UserDetails $userdetail)
{
    // echo "<pre>";
    // print_r($userinfo);
    // exit;

  $userdetail->delete();
  return redirect()->route('userdetails.index')
                   ->with('success','A User has been deleted successfully');
}

}
