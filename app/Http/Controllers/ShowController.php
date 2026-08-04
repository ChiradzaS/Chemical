<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use Auth;


class ShowController extends Controller
{
    function show()
    {
        $data=Company::all();
    return view('show',['companies'=>$data]);
        

    }
}


