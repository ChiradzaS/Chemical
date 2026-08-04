<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Type; 
use Auth;

class ShowtController extends Controller
{
    function show()
    {
        $data=Type::all();
        return view('showt',['types'=>$data]);
    }
}
