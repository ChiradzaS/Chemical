<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Excel;


class ExcelImportController extends Controller
{

    public function index(Request $request){

        //dd('wangu');
        return view('excel-import');

    }
    public function import(Request $request)
    {
        
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
 
        
        $file = $request->file('file');
 
        
        Excel::import(new YourImportClass, $file);
 
        return redirect()->back()->with('success', 'Excel file imported successfully!');
    }

}
