<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobCard;
use Auth;

class JobCardRestController extends Controller
{
    //
    public function qryJobCard(Request $request)
    {
        $tmpId = $request->get('id');
        $jobCard = JobCard::find($tmpId);
        if (!$jobCard) {
            return response()->json(['error' => 'JobCard not found in database.'], 404);
        }
        return response()->json($jobCard);  
    } 


}
