<?php

namespace App\Providers;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

          $url = env('APP_URL');


          // In a bootstrap file
        $ip = gethostbyname(gethostname());
         putenv("MQTT_BROKER_HOST=$ip");

  
        //    $hostname = gethostname();
      
              
        //   $serverIp = gethostbyname($hostname);
      
      
        //  try {

        //     $response = Http::get($url.'/qryrestip/stor?ipaddress='.$serverIp);

        //      if ($response->successful()) {
                
        //          Log::info('successful ip response saved on the cloud : ' );
                
        //      } else {
                
        //          Log::info('Unsuccessful ip response not saved on the cloud: ' );
        //      }


        //  } catch (\Exception $e) {
            
        //      Log::info('HTTP request failed: ' . $e->getMessage());
            
        //  }


        //________________________________________________________________________________________________________________________________________
        View::share('key' , 'value');
        Schema::defaultStringLength(191);

       // SELECT USERS.* FROM USERS lEFT JOIN USER_DETAILS ON USERS.id<>user_details.userId;    
        $users = DB::table('users')->leftjoin('user_details', 'user_details.userId', '<>', 'users.id')
                                   ->where('users.remember_token','<>',null)->distinct()
        
                                   ->get(['users.id','users.name']);

    

        View::share('users',$users);

        $user = DB::table('users')->get();
        View::share('user',$user);

//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
        


        $user= DB::table('users')->get();

        $userKeys = array();
        foreach ($user as $user) {
            $userKeys[$user->id] = $user;
        }
        View::share('user',$userKeys);



        //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
        


        $operators= DB::table('users')->get();

        $operatorKeys = array();
        foreach ($operators as $operator) {
            $operatorKeys[$operator->id] = $operator;
        }
        View::share('operators',$operatorKeys);




    //-----------------------------------------------------------------------------------------------------------------------------    

       
            $weights=DB::table('types')->where('groupType','weightStateId')->get();
        
            $weightsKeys = array();
            
        foreach ( $weights as  $weight) {
            $weightsKeys[$weight->id] = $weight;
        }
    
        View::share('weights',   $weightsKeys );
     //-----------------------------------------------------------------------------------------------------   

        $employees = DB::table('employees')->get();

        $employeKeys = array();
        foreach ($employees as $employee) {
            $employeKeys[$employee->id] = $employee;
        }
        View::share('employees',$employeKeys);

 ////////////////////////////////////////////////////////////////////////////////////

                $productTypeW = DB::table('types')
                ->where('name' ,'=', 'Work in progress')
                ->value('id');
    
                
    
                $productws=DB::table('porducts')
                               ->where('productType',$productTypeW)
                               ->get();

                // echo "<pre>";
                // print_r($productws);
                // exit;

               
    
    
                $productWKeys = array();
                foreach ($productws as $productw) {
                    $productWKeys[$productw->id] = $productw;
                }
                View::share('productws',$productWKeys);
    
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $servicetypes=DB::table('types')->where('groupType','serviceType')->get();

    $servicetypesKeys = array();
    foreach ($servicetypes as $servicetype) {
        $servicetypesKeyss[$servicetype->id] = $servicetype;
    }

    View::share('servicetypes',$servicetypesKeys);

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $vtypes=DB::table('types')->where('groupType','vehicleType')->get();

    $vtypesKeys = array();
    foreach ($vtypes as $vtype) {
        $vtypesKeys[$vtype->id] = $vtype;
    }

    View::share('vtypes',$vtypesKeys);

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $jobtypes=DB::table('types')->where('groupType','jobType')->get();
 
    $jobtypesKeys = array();
        foreach ($jobtypes as $jobtype) {
            $jobtypesKeys[$jobtype->id] = $jobtype;
        }
    
        View::share('jobtypes',$jobtypesKeys );

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $daytypes=DB::table('types')->where('groupType','day')->get();
    //vehicle_maintanances
    $daytypesKeys = array();
        foreach ($daytypes as $daytype) {
            $daytypesKeys[$daytype->id] = $daytype;
        }
    
        View::share('days', $daytypesKeys);

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $vehicletypes=DB::table('vehicles')->get();
    
    $vehicletypeKeys = array();
        foreach ($vehicletypes as $vehicletype) {
            $vehicletypeKeys[$vehicletype->id] = $vehicletype;
        }
    
        View::share('vehicletypes',$vehicletypeKeys);

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $servicetypes=DB::table('types')->where('groupType','serviceType')->get();

    $servicetypesKeys = array();
    foreach ($servicetypes as $servicetype) {
        $servicetypesKeys[$servicetype->id] = $servicetype;
    }

    View::share('servicetypes',$servicetypesKeys);

    ////////////////////////////////////////////////////////////////////////////////////

            $productType = DB::table('types')
            ->where('name' ,'=', 'finished-Product')
            ->value('id');

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                $vehicles = DB::table('types')
                             ->where('groupType','vehicle')
                             ->get();

                             $vehicleKeys = array();
                             foreach ($vehicles as $vehicle) {
                                $vehicleKeys[$vehicle->id] = $vehicle;
                             }
                             View::share('vehicles',$vehicleKeys);
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    

    $drivers = DB::table('employees')
    ->where('jobId','=',123)
    ->get();

    $driversKeys = array();
    foreach ($drivers as $driver) {
        $driversKeys[$driver->id] = $driver;
    }
    View::share('drivers',$driversKeys);

///////////////////////////////////////////////////////////////////////////////////////////////////////////
            $grouptypes = DB::table('types')->distinct()->pluck('groupType');


            View::share('grouptypes',$grouptypes);
            //   echo "<pre>";
            //   print_r($grouptype);
            //   exit;

            $products=DB::table('porducts')
           -> where('productType', '!=', '101')
            ->orderBy('product_Width', 'desc') 
           
            ->get();
           


            $productKeys = array();
            foreach ($products as $product) {
                $productKeys[$product->id] = $product;
            }
            View::share('products',$productKeys);


////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $porducts=DB::table('porducts')
                    //->where('productType',$productType )
                    ->orderBy('product_Width', 'desc') 
                    ->get();



        $porductKeys = array();
        foreach ($porducts as $porduct) {
            $porductKeys[$porduct->id] = $porduct;
        }
        View::share('porducts',$porductKeys);

        ////////////////////////////////////////////////////////////////////////////////////



        /////////////////////////////////////////////////////////////////////////////////////////////

            //  $packs=DB::table('types')->where('groupType','packagingLevel')->get();

            //  foreach ($packs as $pack) {
            //     $packsKeys [$pack->id] = $pack;
            //  }
            //  View::share('packs',$packsKeys);






        ////////////////////////////////////////////////////////////////////////////////////////////

        // $jobtypes=DB::table('types')->where('groupType','jobType')->get();

        // foreach ($jobtypes as $jobtypes) {
        //     $jobtypeKeys[$jobtypes->id] = $jobtypes;
        // }

        //     //   echo "<pre>";
        //     //   print_r($jobtypeKeys);
        //     //   exit;

        // View::share('jobtypes',$jobtypeKeys);



        ////DOCtypes////////////////////////////////////////////////////////////////////////////////////////
        
        $doctypes=DB::table('types')->where('groupType','DocType')->get();

        foreach ($doctypes as $doctype) {
            $doctypeKeys[$doctype->id] = $doctype;
        }
        View::share('doctypes',$doctypeKeys);

        //------------------------------------------------------------------------------------

        //allocationtype
        $allocationtypes=DB::table('types')->where('groupType','allocation')->get();

        $allocationtypeKeys = array();
        foreach ($allocationtypes as $allocationtype) {
            $allocationtypeKeys[$allocationtype->id] = $allocationtype;
        }
        View::share('allocationtypes', $allocationtypeKeys);

        //unittype
        $unittypes=DB::table('types')->where('groupType','unit') ->orderBy('value', 'desc') ->get();

        $unittypeKeys = array();
        foreach ($unittypes as $unittype) {
            $unittypeKeys[$unittype->id] = $unittype;
        }

        View::share('unittypes',$unittypeKeys);

        $statetypes=DB::table('types')->where('groupType','stateId')->get();

        $statetypeKeys = array();
        foreach ($statetypes as $statetype) {
            $statetypeKeys[$statetype->id] = $statetype;
        }

        View::share('$statetypes',$statetypeKeys);

        //usertype
        $usertypes=DB::table('types')->where('groupType','user')->get();

        $usertypeKeys = array();
        foreach ($usertypes as $usertype) {
            $usertypeKeys[$usertype->id] = $usertype;
        }
        View::share('usertypes',$usertypeKeys);

        $fomulartypes=DB::table('types')->where('groupType','formulaType')->get();

        $fomulartypesKeys = array();
        foreach ($fomulartypes as $fomulartype) {
            $fomulartypesKeys[$fomulartype->id] = $fomulartype;
        }
        View::share('fomulartypes',$fomulartypesKeys);
       
       $customertypes=DB::table('types')->where('groupType','customer')->get();

       $customertypeKeys = array();
       foreach ($customertypes as $customertype) {
           $customertypeKeys[$customertype->id] = $customertype;
       }

       View::share('customertypes',$customertypeKeys);



       $bagtypes=DB::table('types')->where('groupType','bagType')->get();

       $bagtypeKeys = array();
       foreach ($bagtypes as $bagtype) {
           $bagtypeKeys[$bagtype->id] = $bagtype;
       }

       View::share('bagtypes',$bagtypeKeys);


       $machinetypes=DB::table('machineries')->get();

       $machinetypeKeys = array();
       foreach ($machinetypes as $machinetype) {
           $machinetypeKeys[$machinetype->id] = $machinetype;
       }
       View::share('machinetypes',$machinetypeKeys);

       $users=DB::table('users')->get();

       $userKeys = array();
       foreach ($users as $user) {
        $userKeys[$user->id] = $user;
       }
       View::share('users',$userKeys);


     
       $machines=DB::table('types')->where('groupType','machine')->get();

       $machinetypeKeys = array();
       foreach ( $machines as  $machine) {
           $machineKeys[$machine->id] = $machine;
       }
       View::share('machines',$machineKeys);

       $productTypes=DB::table('types')->where('groupType','productType')->get();

       $productTypeKeys = array();
       foreach ( $productTypes as  $productType) {
        $productTypeKeys[$productType->id] = $productType;
       }
       View::share('productTypes',$productTypeKeys);




       //Status types
       $statustypes=DB::table('types')->where('groupType','stateId')->get();

       $statustypeKeys = array();
       foreach ($statustypes as $statustype) {
           $statustypeKeys[$statustype->id] = $statustype;
       }
       View::share('statustypes',$statustypeKeys);



       $usertypes=DB::table('types')->where('groupType','user')->get();
       
       $userTypeObjList = array();
       foreach ($usertypes as $usertype) {
        $userTypeObjList[$usertype->id] = $usertype;
           
       }

       View::share('userTypeObjList', $userTypeObjList);

       /////////////////////////////////////////////////////////////////////////////////////////////////////

       $fueltypes=DB::table('types')->where('groupType','fuelType')->orderby('name','asc')->get();

       $fueltypeKeys = array();
       foreach ($fueltypes as $fueltype) {
         $fueltypeKeys[$fueltype->id] = $fueltype;
       }

       View::share('fuels', $fueltypeKeys);
       ////////////////////////////////////////////////////////////////////////////////////////////////////




       //Material Type
       $materialtypes=DB::table('types')->where('groupType','material')->get();

       $materialtypeKeys = array();
       foreach ($materialtypes as $materialtype) {
           $materialtypeKeys[$materialtype->id] = $materialtype;
       }
       View::share('materialtypes',$materialtypeKeys);

       //colourtype
       $colourtypes=DB::table('types')->where('groupType','colour')->get();

       $colourtypeKeys = array();
       foreach ($colourtypes as $colourtype) {
           $colourtypeKeys[$colourtype->id] = $colourtype;
       }
       View::share('colourtypes',$colourtypeKeys);

       $shifttypes=DB::table('types')->where('groupType','shift')->get();

       $shifttypeKeys = array();
       foreach ($shifttypes as $shifttype) {
           $shifttypeKeys[$shifttype->id] = $shifttype;
       }

       View::share('shifttypes',$shifttypeKeys);


        $types=DB::table('types')->get();
        View::share('types',$types);


        //customertype
        $customers=DB::table('customers')->orderBy('name','asc')->get();

        $customerKeys = array();
        foreach ($customers as $customer) {
            $customerKeys[$customer->id] = $customer;
        }
        View::share('customers',$customerKeys);

        $orders=DB::table('orders')->get();

        $orderKeys = array();
        foreach ($orders as $order) {
            $orderKeys[$order->id] = $orders;
        }
        View::share('orders',$orderKeys);

        //---------------------------------------------------------------------------------------------------------------------------

        $customOrder = ['Extruding', 'Printing', 'Bagging'];

            $processtypes = DB::table('types')
                ->where('groupType', 'processes')
                ->orderByRaw("FIELD(name, '" . implode("','", $customOrder) . "')")
                ->get();

        $processtypesKeys = array();
        foreach ($processtypes as $processtype) {
            $processtypesKeys[$processtype->id] = $processtype;
        }

        View::share('processtypes',$processtypesKeys);

        //////////////////////////////////////////////////////////////////////////////////////////

        $workinprogress=DB::table('porducts')->where('productType',101)->orderBy('product_Width','desc')->get();

        $WorkInprogressKeys = array();
        foreach ($workinprogress as $workinprogres) {
            $WorkInprogressKeys[$workinprogres->id] =$workinprogres;
        }

        View::share('wproducts',$WorkInprogressKeys);


        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $vattypes=DB::table('types')->where('groupType','VatType')->get();

        $vattypesKeys = array();
        foreach ($vattypes as $vattype) {
            $vattypesKeys[$vattype->id] = $vattype;
        }

        View::share('vattypes',$vattypesKeys);

       /////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $jobcardlists=DB::table('job_cards')->get();

        $jobcardKeys = array();
        foreach ($jobcardlists as $jobcardlist) {
            $jobcardKeys[$jobcardlist->id] = $jobcardlist;
        }


        View::share('jobcardlists', $jobcardKeys);

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pumptypes=DB::table('types')->where('groupType','pump')->get();

        $pumptypeKeys = array();
        foreach ($pumptypes as $pumptype) {
            $pumptypeKeys[$pumptype->id] = $pumptype;
        }
 
  
  
        View::share('pumps',$pumptypeKeys);


//========================================================CHEMICALTYPES===============================================================================================

      




                $chemicalTypes = DB::table('types')
                    ->where('groupType', 'ChemicalType')
                    ->get()
                    ->keyBy('id');

                $chemicalCustomers = DB::table('types')
                    ->where('groupType', 'ChemicalCustomer')
                    ->get()
                    ->keyBy('id');

                $viscosity = DB::table('types')
                    ->where('groupType', 'viscosity')
                    ->get()
                    ->keyBy('id');

                $activeIngredients = DB::table('types')
                    ->where('groupType', 'activeIngredient')
                    ->get()
                    ->keyBy('id');

                $fragrances = DB::table('types')
                    ->where('groupType', 'fragrance')
                    ->get()
                    ->keyBy('id');

                $chemicalColours = DB::table('types')
                    ->where('groupType', 'chemicalColour')
                    ->get()
                    ->keyBy('id');

                $bottleTypes = DB::table('types')
                    ->where('groupType', 'bottleType')
                    ->get()
                    ->keyBy('id');

                    

                    $containerSizes = DB::table('types')
                                ->where('groupType', 'containerSize')
                                ->get()
                                ->keyBy('id');

                    $containerSizeKeys = array();

                    foreach ($containerSizes as $containerSize) {
                        $containerSizeKeys[$containerSize->id] = $containerSize;
                    }

                    View::share('containerSizes', $containerSizeKeys);


                $capTypes = DB::table('types')
                    ->where('groupType', 'capType')
                    ->get()
                    ->keyBy('id');

                $lableTypes = DB::table('types')
                    ->where('groupType', 'lablelType')
                    ->get()
                    ->keyBy('id');


                $chemicalProducts = DB::table('chemical_products')
                    ->get()
                    ->keyBy('id');
                   


                $chemicalprocesstypes = DB::table('types')
                        ->where('groupType', 'chemicalprocesses')
                        ->get();

                $chemicalprocesstypesKeys = array();
                    foreach ($chemicalprocesstypes as $chemicalprocesstype) {
                        $chemicalprocesstypesKeys[$chemicalprocesstype->id] = $chemicalprocesstype;
                    }




                $ChemicalMaterialTypes = DB::table('types')
                        ->where('groupType', 'ChemicalMaterialType')
                        ->get();
                    


                $ChemicalUnitTypes = DB::table('types')
                        ->where('groupType', 'ChemicalUnitType')
                        ->get();



                $suppliers = DB::table('suppliers')
                            ->where('is_active', 1)
                            ->orderBy('name')
                            ->get();
                                







                    

                View::share(compact(
                    'chemicalprocesstypes',
                    'chemicalProducts',
                    'chemicalTypes',
                    'chemicalCustomers',
                    'viscosity',
                    'activeIngredients',
                    'fragrances',
                    'chemicalColours',
                    'bottleTypes',
                    'containerSizes',
                    'suppliers',
                    'capTypes',
                    'lableTypes',
                    'ChemicalMaterialTypes',
                    'ChemicalUnitTypes'
                ));

                    

        
        
    }
}
