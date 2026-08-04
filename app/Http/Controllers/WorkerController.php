<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\Workers;
 
class WorkerController extends Controller
{
   public function index2(){
     return view('workers');
   }
 
   public function getUsers(){
 
     $workers = Workers::orderby('id','asc')->select('*')->get(); 
      
     // Fetch all records
     $response['data'] = $workers;
 
     return response()->json($response);
   }
 
   public function getUserbyid(Request $request){
 
      $userid = $request->userid;
 
      $workers = Workers::select('*')->where('id', $userid)->get();
 
      // Fetch all records
      $response['data'] = $workers;
 
      return response()->json($response);
   }
}