<?php
namespace App\Http\Controllers;


//include_once 'App\Library\PDF.php';
//use 'app/Library/PDF.php';
require ('C:\xampp\htdocs\LaravelCRUD\app\Library\PDF.php');
include_once 'C:\xampp\htdocs\LaravelCRUD\app\Barcode\barcode.php';
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use PDF;

class TestController extends Controller
{

public function index(Request $request)
{
  
  $jobCardId = $request->get('jobCardId');
  Log::info("Test ------------------------------------------- : ".$jobCardId);
  $tt = new PDF();
  return $tt->myMethod( $request ,['jobCardId' =>  $jobCardId]);
  
}

public function create(Request $request)
{
  
  $jobcarditemId = $request->get('jobcarditemId');
  Log::info("Test ------------------------------------------- : ". $jobcarditemId);
  $tt = new PDF();
  return $tt->Method( $request ,['jobcarditemId' =>  $jobcarditemId]);
  
}


}
