<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;
use Hash;
use Session;
use App\Models\User;
use App\Models\Type;
use App\Models\Order_item;
use App\Models\UserDetails;
use Illuminate\Support\Facades\Auth;
use App\Library\UniqueCode;
use Illuminate\Support\Facades\View;
use Mail;
use App\Mail\SendMail;
use DB;




class CustomAuthController extends Controller
{

    public function index(Request $request)
    {
        $employeeId = $request->input('employeeId');
    
        // If employeeId is provided
        if ($employeeId) {
            $user = User::find($employeeId); // Using find for simplicity
    
            // Check if user exists
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
    
            // Prepare credentials for authentication
            $credentials = [
                'name' => $user->name,
                'password' => $user->other, // Assuming 'other' holds the password
            ];
    
            // Attempt authentication
            if (Auth::attempt($credentials)) {
                // Get user details
                $userDetails = DB::table('user_details')->where('userId', $user->id)->first();
    
                if (!$userDetails) {
                    return redirect("login")->withErrors('Please talk to admin to get your account authorized');
                }
    
                // Get user type
                $userType = $userDetails->userTypeId;
                $orderId = $userDetails->orderId;
    
                // Get user type name
                $userTypeName = DB::table('types')->where('id', $userType)->value('name');
    
                // Set session and redirect based on user type
                Session::put('menu', strtolower($userTypeName) . 'Menu');
    
                return view($userTypeName . 'Menu', compact('orderId'));
            } else {
                return redirect("login")->withErrors('Login details are not valid');
            }
        }
    
        return view('login');
    }
     

    public function customLoginproduction(Request $request ,User $user)
    {

            // Retrieve the employeeId from the request
            $employeeId = $request->input('employeeId');

            Log::info('loggggin in ');

            // Query the user based on employeeId
            $user = User::where('employeeId', $employeeId)->first();

            // Check if user exists
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Prepare credentials for authentication
            $credentials = [
                'name' => $user->name,
                'password' => $user->other,
            ];

        if (Auth::attempt($credentials)) {

            // echo "<pre>";
            // print_r('we here bb');
            // exit();

            $userTypeName = '';
            $userType = '';

            $data = $request->get('name');
            //     echo "<pre>";
            //   print_r($data);
            

            $ids=DB::table('users')->where('name',$data)->first('id');

            foreach ($ids as $id) {
             {
                    $userId = $id ;  
                }
            }

            //     echo "<pre>";
            //   print_r($userType );
            
            $usertypes=DB::table('user_details')->where('userId', $userId)->get();

            foreach ($usertypes as $usertype) {
                {
                    $userType = $usertype->userTypeId ; 
                    $orderId = $usertype->orderId ; 
            
                   }
               }

            //    $ordertypes=DB::table('user_details')->where('userId', $userId)->get('orderId');

            //    foreach ($ordertypes as $ordertype) {
            //        {
            //            $orderId = $ordertype->orderId ;  
               
            //           }
            //       }

            if($userType == null){
                return redirect("login")->withErrors('Please talk to adimn to get your account authorised');
            }

        
   
               $names=DB::table('types')->where('id',$userType)->get('name');

                  
          

               foreach ($names as $name) {
                {
                    $userTypeName  = $name->name ;  
                   }
               }

     
        
  
            if ( $userTypeName  == 'customer') {
                Session::put('menu', 'customerMenu');
                return view('customerMenu',['orderId' =>$orderId]);
            }
            else if ($userTypeName == 'admin') {
                Session::put('menu', 'view');
                $data = Type::where('groupType', '=','PackagingLevel')->value('value');
                $request->session()->put('packagingLevel', $data);
                return view('view');
            }
            else if ($userTypeName == 'controller') {
                Session::put('menu', 'controllerMenu');
                return view('controllerMenu');
            }
            else if ($userTypeName == 'employee') {
                Session::put('menu', 'employeeMenu');
                return view('employeeMenu');
            }
            else if ($userTypeName == 'Supplier') {
                Session::put('menu', 'supplierMenu');
                return view('supplierMenu');
            }
            else if ($userTypeName == 'dispatch') {
                Session::put('menu', 'deliveryMenu');
                return view('deliveryMenu');
            }
            else if ($userTypeName == 'vehiclecontroller') {
                Session::put('menu', 'vehicleMenu');
                return view('vehicleMenu');
            }

            return redirect()->intended('customerMenu')
            ->withSuccess('Signed in');
           
            

        
        // if()}{

        // };
            
            //Once checked then get userdetails if 
            // customer the bring customerMenu
            //
            //if user is not connected to userdetails
            //select customerMenu


           
        }else{

            echo "<pre>";
            print_r('Ask adim to get registred in the system ');
            exit();
        }
  
        return redirect("login")->withSuccess('Login details are not valid');
    }
      
    public function customLogin(Request $request ,User $user)
    {
        //dd('iam');
       
        $request->validate([
            'name' => 'required',
            'password' => 'required',
        ]);

      
   
        $credentials = $request->only('name', 'password');

        // echo "<pre>";
        // print_r($credentials);
        // exit();

        if (Auth::attempt($credentials)) {

            // echo "<pre>";
            // print_r('we here bb');
            // exit();

            $userTypeName = '';
            $userType = '';

            $data = $request->get('name');
            //     echo "<pre>";
            //   print_r($data);
            

            $ids=DB::table('users')->where('name',$data)->first('id');

            foreach ($ids as $id) {
             {
                    $userId = $id ;  
                }
            }

            //     echo "<pre>";
            //   print_r($userType );
            
            $usertypes=DB::table('user_details')->where('userId', $userId)->get();

            foreach ($usertypes as $usertype) {
                {
                    $userType = $usertype->userTypeId ; 
                    $orderId = $usertype->orderId ; 
            
                   }
               }

            //    $ordertypes=DB::table('user_details')->where('userId', $userId)->get('orderId');

            //    foreach ($ordertypes as $ordertype) {
            //        {
            //            $orderId = $ordertype->orderId ;  
               
            //           }
            //       }

            if($userType == null){
                return redirect("login")->withErrors('Please talk to adimn to get your account authorised');
            }

        
   
               $names=DB::table('types')->where('id',$userType)->get('name');

                  
          

               foreach ($names as $name) {
                {
                    $userTypeName  = $name->name ;  
                   }
               }

     
        
  
            if ( $userTypeName  == 'customer') {
                Session::put('menu', 'customerMenu');
                return view('customerMenu',['orderId' =>$orderId]);
            }
            else if ($userTypeName == 'admin') {
                Session::put('menu', 'view');
                $data = Type::where('groupType', '=','PackagingLevel')->value('value');
                $request->session()->put('packagingLevel', $data);
                return view('view');
            }
            else if ($userTypeName == 'controller') {
                Session::put('menu', 'controllerMenu');
                return view('controllerMenu');
            }
            else if ($userTypeName == 'employee') {
                Session::put('menu', 'employeeMenu');
                return view('employeeMenu');
            }
            else if ($userTypeName == 'Supplier') {
                Session::put('menu', 'supplierMenu');
                return view('supplierMenu');
            }
            else if ($userTypeName == 'dispatch') {
                Session::put('menu', 'deliveryMenu');
                return view('deliveryMenu');
            }
            else if ($userTypeName == 'vehiclecontroller') {
                Session::put('menu', 'vehicleMenu');
                return view('vehicleMenu');
            }

            return redirect()->intended('customerMenu')
            ->withSuccess('Signed in');
           
            

        
        // if()}{

        // };
            
            //Once checked then get userdetails if 
            // customer the bring customerMenu
            //
            //if user is not connected to userdetails
            //select customerMenu


           
        }else{

            echo "<pre>";
            print_r('Ask adim to get registred in the system ');
            exit();
        }
  
        return redirect("login")->withSuccess('Login details are not valid');
    }

    public function registration()
    {
        return view('registration');
    }
      
    public function customRegistration(Request $request)
    {  
        // $request->validate([
        //     'name' => 'required',
        //     'email' => 'required|email|unique:users',
        //     'password' => 'required|min:6',
        // ]);
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);
           //dd('700000000000000000000000000000');
        $data = $request->all();
       $check = $this->create($data);
         
        return redirect("login")->withSuccess('You have signed-in');
    }

    public function create(array $data)
    {
      $uniqueVal = UniqueCode::uniqidRealVal();

    

      
      return User::create([

        'remember_token' => $uniqueVal,
        'name'  => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'other' => $data['password']
      ]);
      

    //   $user = new User();
    //   $user->remember_token = $uniqueVal;
    //   $user->name = $data['name'];
    //   $user->email = $data['email'];
    //   $user->password = $data['password'];
    
      $bSaved = $user->save();
      $validate = $user->id;

    //   echo "<pre>";
    //   print_r($validate);
     

      //Add validate function.

      $testMailData = [
        'title' => 'Welcome',
        'body' => 'Your log in link is : http://localhost/LaravelCRUD/validate?token='.$uniqueVal.'&validate='.$validate 
      ];

    //   echo "<pre>";
    //   print_r(   $testMailData);
    //   exit;

      Mail::to($user->email)->send(new SendMail($testMailData));

      dd('Success! Email has been sent successfully.');

      return $bSaved;

    }    
    
    public function dashboard()
    {
         /* Current Login User Details */
         $user = Auth::user();
         //var_dump($user);
        
         /* Current Login User ID */
         $userID = ''; 
         if (Auth::user() <> null) {
            $userID = Auth::user()->id;
         }

         //var_dump($userID);
        
         /* Current Login User Name */
         //$userName = Auth::user()->name; 
         //var_dump($userName);
        
        /* Current Login User Email */
        //$userEmail = Auth::user()->email; 
        //var_dump($userEmail);

        //echo "<pre>";
        //print_r(' User Id : '.$userID.' , User Name : '.$userName.' , User Email : '.$userEmail);
        //exit;

        if(Auth::check()){
            if ($userID == 2) {
                return view('customerMenu');
            }

            return view('view');
        }
  
        return redirect("login")->withSuccess('You are not allowed to access');
    }
    
    public function signOut() {
        Session::flush();
        Auth::logout();
  
        return Redirect('login');
    }

    public function home()
    {
        return view('view');
    } 
}