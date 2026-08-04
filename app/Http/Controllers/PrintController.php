<?php
namespace App\Http\Controllers;

//require ('C:\xampp\htdocs\LaravelCRUD\app\Library\PDF.php');
require (base_path().'\App\Library\JobCardItemRpt.php');
require (base_path().'\App\Library\JobCardsRpt.php');
require (base_path().'\App\Library\ProductionRpt.php');
require (base_path().'\App\Library\InvoiceRpt.php');
//include_once 'C:\xampp\htdocs\LaravelCRUD\app\Barcode\barcode.php';
include_once base_path().'\App\Library\JobCardItemRpt.php';
include_once base_path().'\App\Library\ChemicalJobCardItemRpt.php';
include_once base_path().'\App\Library\JobCardItemsRpt.php';
include_once base_path().'\App\Library\JobCardsRpt.php';
include_once base_path().'\App\Library\ProductionRpt.php';
include_once base_path().'\App\Library\ProductionRptList.php';
include_once base_path().'\App\Library\AllJobCardList.php';
include_once base_path().'\App\Library\InvoiceRpt.php';
include_once base_path().'\App\Library\OrderListRpt.php';
include_once base_path().'\App\Library\InvoiceDelivery.php';
include_once base_path().'\App\Library\Delivery.php';
include_once base_path().'\App\Library\ReactDelivery.php';
include_once base_path().'\App\Library\ClockingList.php';
include_once base_path().'\App\Library\ChemicalDocumentPdf.php';
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Jobcarditem;
use App\Models\DocumentAudit;
use App\Models\Clocking;
use Illuminate\Support\Facades\View;
use JobCardItemRpt;
use JobCardItemsRpt;
use JobCardsRpt;
use InvoiceRpt;
use OrderListRpt;
use ProductionRpt;
use ProductionRptList;
use AllJobCardList;
use InvoiceDelivery;
use ReactDelivery;
use Delivery;
use ClockingList;
use ChemicalJobCardItemRpt;
use ChemicalDocumentPdf;


class PrintController extends Controller
{

public function index(Request $request)
{
  $prnQry       = $request->get('prntReport');
  $jsonData     = $request->input('data');
  $jsonDat      = $request->input('dat');
  $jsonDelivery = $request->input('delivery');
 // $responses = json_decode(urldecode($jsonDat), true);
  $responsesArray = json_decode(urldecode($jsonData), true);
  $tt = null;


  if(!empty($jsonDelivery)){
        $tt = null;
    
  
      $deliveryId = $request->get('deliveryId');
      $tt = new ReactDelivery();
      return $tt->deliveryreact( $request ,['deliveryId' =>$deliveryId], $jsonDat);




  }  

  else if (!empty($jsonDat)) {

    $tt = null;
    
  
      $orderId = $request->get('orderId');
      $tt = new Delivery();
      return $tt->deliveryinvoice( $request ,['orderId' =>$orderId], $jsonDat);
    
  
  }else if (!empty($jsonData)) {

  $tt = null;
 

    $orderId = $request->get('orderId');
    $tt = new InvoiceDelivery();
    return $tt->delivery( $request ,['orderId' =>$orderId], $jsonData);
  

}else if ($prnQry == 'JOB_CARDS') {
    
    $tt = new JobCardsRpt();
    return $tt->myMethod( $request );

    $document = new DocumentAudit();
    $document->docId =  0;
    $document->docType = 'jobcards List'; 
    $document->stateId  = 63;
    $document->other = 0;
    $document->userId = 1;
    $document->action = 'Printed';
    $document->save(); 

  }
  else if ($prnQry == 'ALL_JOBCARDS') {
    
    $tt = new AllJobCardList();
    ///return $tt->myMethod( $request );

   
    $document = new DocumentAudit();
    $document->docId =  0;
    $document->docType = 'jobcards List'; 
    $document->stateId  = 63;
    $document->other = 0;
    $document->userId = 1;
    $document->action = 'Printed';
    $document->save(); 
   
  
  }
  else if ($prnQry == 'PRODUCTION_LIST') {
    
    $tt = new ProductionRptList();
    //return $tt->myMethod( $request );
  
  }  else if ($prnQry == 'ClockingList') {

    $clockings = Clocking::all(); 

    if ($clockings->count() > 0) {
        
      $tt = new ClockingList();

    } else {
       
        return view('importFile')->with("Please upload the clocking data from .csv to be able to process and print");
    }
    
   
    //return $tt->myMethod( $request );
  
  }
  else if ($prnQry == 'PRODUCTION_BY_PRODUCTIONID') {

    $productionId = $request->get('productionId');
    $other = $request->get('other');
    $stateId = $request->get('stateId');

    // echo "<pre>";
    // print_r($state);
    // exit;

    $document = new DocumentAudit();
    $document->docId =  $productionId;
    $document->docType = 'production&items'; 
    $document->stateId  = 63 ;
    $document->other = $other;
    $document->userId = 1;
    $document->action = 'Printed';
    $document->save(); 


    $tt = new ProductionRpt();
    return $tt->production( $request ,['productionId' =>  $productionId]);
  
  }
  else if ($prnQry == 'JOBCARD_ITEM_BY_JOBCARDID') {

    $productionId = $request->get('jobCardId');
    $tt = new ProductionRpt();
    return $tt->production( $request ,['productionId' =>  $productionId]);
  
  }
  else if ($prnQry == 'INVOICE_BY_INVOICEID') {

    $invoiceId = $request->get('invoiceId');
    $tt = new InvoiceRpt();
    return $tt->invoice( $request ,['invoiceId' =>  $invoiceId]);
  
  }
  else if ($prnQry == 'ODERS_LIST') {

    $orderId = $request->get('orderId');
    $tt = new OrderListRpt();
    return $tt->order( $request ,['orderId' =>$orderId]);
  
  }
  else if ($prnQry == 'JOBCARD_WITH_ITEMS') {

    $jobcardId = $request->get('jobCardId');
    $tt = new JobCardItemsRpt();
    return $tt->jobcard( $request ,['jobCardId' =>  $jobcardId]);

    $id = $request->get('jobCardId');
    $stateId = $request->get('stateId');
    $other = $request->get('other');
   
    

    $document = new DocumentAudit();
    $document->docId =  $id;
    $document->docType = 'Jobcard&items'; 
    $document->stateId  = 63 ;
    $document->other = $other;
    $document->userId = 1;
    $document->action = 'Printed';
    $document->save(); 
  
  }  else if ($prnQry == 'DELIVERY/INVOICE') {

   
    $orderId = $request->get('orderId');
    $tt = new InvoiceDelivery();
    return $tt->order( $request ,['orderId' =>$orderId], $jsonData);
  
  }

  return $tt->myMethod($request);
}

// public function index(Request $request)
// {
  
//   $tt = new ProductionRptList();
//   return $tt->myMethod( $request );
  
// }

//  public function index(Request $request)
//  {
  
//    $productionId = $request->get('productionId');
//    $tt = new ProductionRpt();
//    return $tt->production( $request ,['productionId' =>  $productionId]);
  
//  }

public function create(Request $request)
{
 
  $jobcarditemId = $request->get('jobcarditemId');
  $process = $request->get('Process');
  $other = $request->get('other');
  $state = $request->get('stateId');
  

  
  $tt = new JobCardItemRpt();
  return $tt->generate( $request ,['jobcarditemId' =>  $jobcarditemId , 'Process'=> $process]);


  
}


public function chemicalcreate(Request $request)
{
    $jobcarditemId = $request->get('jobcarditemId');
    $process       = $request->get('Process');
    $other         = $request->get('other');
    $state         = $request->get('stateId');

    return ChemicalJobCardItemRpt::printJobCard($request);
}



public function delivery(Request $request)
{


  Log::info('ready to print delivery notes ');


  
  $deliveryId = $request->get('id');

  $tt = new ReactDelivery();

  return $tt->deliveryreact( $request ,['deliveryId' => $deliveryId] );


}


public function printDelivery(Request $request)
{
    $deliveryId = $request->get('id');

    return ChemicalDocumentPdf::printDelivery($request);
}

public function printInvoice(Request $request)
{
    $invoiceId = $request->get('id');

    return ChemicalDocumentPdf::printInvoice($request);
}

public function printBoth(Request $request)
{
    return ChemicalDocumentPdf::printBoth($request);
}

   
 



  
}



