<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class RestIpController extends Controller
{

    public function index(Request $request)
    {

        $serverIp =  DB::table('local_host_info')
                       ->where('id', 1)
                       ->value('ipaddress');


        return response()->json( $serverIp);

    }

    public function store(Request $request)
    {


        $ipAddress = $request->input('ipaddress');

           DB::table('local_host_info')
              ->where('id', 1)
              ->update(['ipaddress' => $ipAddress]);

            
    }
    
}
