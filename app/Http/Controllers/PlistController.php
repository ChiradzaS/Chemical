<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Porduct; 

class PlistController extends Controller
{
    function show()
    {
        $data=Porduct::all();
    return view('product_list',['porducts'=>$data]);
        

    }
}