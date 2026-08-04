<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class Logmessage extends Controller
{
    public function logtest()
    {

Log::info(" Here is dummy log data");
        return view('view');
    }
}
