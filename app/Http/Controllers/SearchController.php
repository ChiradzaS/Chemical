<?php

namespace App\Http\Controllers;

use App\Models\Porduct;
use Illuminate\Http\Request;
use Auth;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $searchTerm = $request->input('searchTerm');
        $products = Porduct::where('name', 'like', '%' . $searchTerm . '%')->get();

        return view('search.results', compact('products'));
    }
}