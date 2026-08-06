<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Type;
use App\Http\Controllers\CompanyCRUDController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProdctController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\PorductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\Order_itemController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MachineryController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\JobCardController;
use App\Http\Controllers\JobcarditemController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\DeleteController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProductionitemController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\UserDetailsController;
use App\Http\Controllers\CustomAuthController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\CustomerAllocationController;
use App\Http\Controllers\ValidateController;
use App\Http\Controllers\CustomerOrderItermController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\EmployeeproductionController;
use App\Http\Controllers\EmployeeItemsController;
use App\Http\Controllers\CustomerorderController;
use App\Http\Controllers\CustomerorderitemController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\listController;
use App\Http\Controllers\AllocationController;
use App\Http\Controllers\UserRestController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ProductpricingController;
use App\Http\Controllers\FormulaController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\PickingslipController;
use App\Http\Controllers\PumpTransController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehiclemaintanaceController;
use App\Http\Controllers\JobScheduleController;
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\UsersxController;
use App\Http\Controllers\OnlineNewProducts;
use App\Http\Controllers\TerminalController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\Pricing1Controller;
use App\Http\Controllers\PriceUpdateController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ReactjobCardController;
use App\Http\Controllers\ChemicalProductController;
use App\Http\Controllers\ChemicalJobCardController;
use App\Http\Controllers\ChemicalDeliveryController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\OrderController;








/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('view');
    //return view('login');
});






//=======================================================================================================REACT WEB ROUTES =====================================================================================================================================

Route::get('/react-app', function () {
    return view('react-app'); // This route will load your React application
})->name('react.app');


Route::get('/jobcardReport', function () {
    return view('jobcardReport'); // This route will load your React application
})->name('jobcardReport.app');


Route::get('/reactjob', function () {

    return view('reactjob'); 

})->name('reactjob');


Route::get('/reactjoblist', function () {

    return view('reactjoblist'); 

})->name('reactjoblist');




Route::get('/reactorderlist', function () {

    return view('reactorderlist'); 

})->name('reactorderlist');


Route::get('/about', function () {

    return view('about'); 

})->name('about');




Route::get('/reactcreateorder', function () {

    return view('reactcreateorder'); 

})->name('reactcreateorder');




Route::get('/reactdeliveries', function () {

    return view('reactdeliveries'); 

})->name('reactdeliveries');




Route::get('/reactcreatedelivery', function () {

    return view('reactcreatedelivery'); 

})->name('reactcreatedelivery');


Route::get('/reactdeliverylist', function () {

    return view('reactdeliverylist'); 

})->name('reactdeliverylist');



Route::get('/chemicalproductlist', function () {

    return view('chemicalproductlist'); 

})->name('chemicalproductlist');




Route::get('/productchemicals', function () {
    return view('productchemicals');
});




Route::get('/chemicaljobcard', function () {
    return view('chemicaljobcard');
});



Route::get('/chemicaljobcardlist', function () {
    return view('chemicaljobcardlist');
});


Route::get('/chemicaldeliveries', function () {

    return view('chemicaldeliveries'); 

})->name('chemicaldeliveries');




Route::get('/chemicalcreatedelivery', function () {

    return view('chemicalcreatedelivery'); 

})->name('chemicalcreatedelivery');


Route::get('/chemicaldeliverylist', function () {

    return view('chemicaldeliverylist'); 

})->name('chemicaldeliverylist');



Route::get('/reactstockadjustment', function () {

    return view('reactstockadjustment'); 

})->name('reactstockadjustment');



Route::get('/chemicalorder', function () {

    return view('chemicalorder'); 

})->name('chemicalorder');



Route::get('/chemicalorderlist', function () {

    return view('chemicalorderlist'); 

})->name('chemicalorderlist');




Route::get('/chemicalmaterial', function () {

    return view('chemicalmaterial'); 

})->name('chemicalmaterial');



















// Route::get('/print/invoice',  [PrintController::class, 'printInvoice']);
// Route::get('/print/delivery', [PrintController::class, 'printDelivery']);
// Route::get('/print/both',     [PrintController::class, 'printBoth']);

Route::get('/print/invoice', [PrintController::class, 'printInvoice'])->name('printInvoice');
Route::get('/print/delivery', [PrintController::class, 'printDelivery'])->name('printDelivery');
Route::get('/print/both', [PrintController::class, 'printBoth'])->name('printBoth');

//=======================================================================================================REACT WEB ROUTES =====================================================================================================================================

// ── Blade views ───────────────────────────────────────────────────────────────
Route::get('/chemicaldeliverylist',       [ChemicalDeliveryController::class, 'listView']);
Route::get('/chemicaldeliveries/create',  [ChemicalDeliveryController::class, 'createView']);

// ── API (GET-only, encoded query params) ──────────────────────────────────────
Route::get('/chemicaldeliveries/store',   [ChemicalDeliveryController::class, 'store']);
Route::get('/chemicaldeliveries/index',   [ChemicalDeliveryController::class, 'index']);
Route::get('/chemicaldeliveries/show',    [ChemicalDeliveryController::class, 'show']);
Route::get('/chemicaldeliveries/destroy', [ChemicalDeliveryController::class, 'destroy']);


Route::get('/settings', function () {
    return view('settings'); // This route will load your React application
})->name('settings');

Route::get('view', function () {
    return view('view');
});

Route::get('customerMenu', function () {
    return view('customerMenu');
});

Route::get('employeelogin', function () {
    return view('employeelogin');
});

Route::get('employeeMenu', function () {
    return view('employeeMenu');
});

Route::get('supplierMenu', function () {
    return view('supplierMenu');
});

Route::get('controllerMenu', function () {
    return view('controllerMenu');
});

Route::get('InventoryMenu', function () {
    return view('InventoryMenu');
});


// In routes/web.php
Route::get('/errorpage', function () {
    return view('errorpage');
})->name('errorpage');


 
Route::get('/file-import',[UsersxController::class,
        'importView'])->name('import-view'); 
        
Route::post('/import',[UsersxController::class,
        'import'])->name('import'); 

Route::get('/export-users',[UsersxController::class,
        'exportUsers'])->name('export-users');

Route::get('/list',[UsersxController::class,
        'list'])->name('list');


Route::get('/settings/function1', [SettingsController::class, 'function1']);
Route::get('/settings/function2', [SettingsController::class, 'function2']);
Route::get('/settings/function3', [SettingsController::class, 'function3']);
Route::get('/settings/function4', [SettingsController::class, 'function4']);
Route::get('/settings/function5', [SettingsController::class, 'function5']);
Route::get('/settings/function6', [SettingsController::class, 'function6']);
Route::get('/settings/function7', [SettingsController::class, 'function7']);
// Route::get('statuses',  [SettingsController::class, 'statuses']);

Route::get('/settings/statuses', [SettingsController::class,'statuses'])->name('statuses');

Route::get('/settings/function1', [SettingsController::class,'function1'])->name('function1');




Route::resource('terminals', TerminalController::class);
Route::resource('vehiclemaintanances', TerminalController::class);
Route::resource('workspaces', WorkspaceController::class);
Route::resource('productions', ProductionController::class);
Route::resource('vehicles', VehicleController::class);
Route::post('/productions/production', [ProductionController::class,'production'])->name('production');
Route::post('/productions/productionitem', [ProductionController::class,'productionitem'])->name('productionitem');
Route::post('/productions/searchproduction',[ProductionController::class,'searchproduction'])->name('searchproduction');
Route::post('/productions/srchworkspace',[ProductionController::class,'srchworkspace'])->name('srchworkspace');
Route::post('/productions/changestate',[ProductionController::class,'changestate'])->name('changestate');
Route::post('/productions/changestat',[ProductionController::class,'changestat'])->name('changestat');
Route::post('/productions/complete',[ProductionController::class,'complete'])->name('complete');
Route::post('/setprices/getConstants',[PriceUpdateController::class,'getConstants'])->name('getConstants');
Route::resource('formulas',FormulaController::class);
Route::post('/productions/generate', [ProductionController::class, 'generate'])->name('generate');
Route::resource('productionitems', ProductionitemController::class);
Route::resource('prices', ProductpricingController::class);
Route::post('/prices/pricecheck', [ProductpricingController::class,'pricecheck'])->name('pricecheck');
Route::resource('recipes', RecipeController::class);
Route::resource('packages', PackageController::class);
Route::resource('job', JobController::class);
Route::resource('job_cards', JobCardController::class);
Route::post('/job_cards/clonejobcard', [JobCardController::class,'clonejobcard'])->name('clonejobcard');
Route::resource('audits', AuditController::class);
Route::resource('index',  PrintController::class);
Route::get('/chemicalcreate', [PrintController::class, 'chemicalcreate'])->name('chemicalcreate');
Route::get('/delivery', [PrintController::class, 'delivery'])->name('delivery');
Route::resource('item',  PrintController::class);
Route::resource('jobcarditems',  JobcarditemController::class);
Route::resource('machinery', MachineryController::class);
Route::resource('userdetails', UserDetailsController::class);
Route::resource('users', UserController::class);
Route::resource('printRpt',  PrintController::class);
Route::resource('employees', EmployeeController::class);
Route::resource('companies', CompanyCRUDController::class);
Route::resource('stocks', StockController::class);
Route::resource('order', OrdersController::class);
Route::post('/orders/checkitems', [OrdersController::class,'checkitems'])->name('checkitems');
Route::resource('ordercustomers', CustomerorderController::class);
Route::resource('order_items', Order_itemController::class);
Route::resource('customerorderitems', CustomerorderitemController::class);
Route::resource('customers', CustomerController::class);
Route::resource('invoices', InvoicesController::class);
Route::resource('invoice_items', InvoiceItemController::class);
Route::resource('porducts', PorductController::class);
Route::resource('types',TypeController::class);
Route::post('/types/clone', [TypeController::class, 'clone'])->name('clone');
Route::resource('pdf',PdfController::class);
Route::get('ajax', [WorkerController::class, 'index2']);
Route::get('/getUsers', [WorkerController::class, 'getUsers']);
Route::post('/getUserbyid', [WorkerController::class, 'getUserbyid']);
Route::post('/order_items/getProductbyid', [Order_itemController::class, 'getProductbyid'])->name('getProductbyid');
Route::post('/pricings/getTypeValue', [Pricing1Controller::class, 'getTypeValue'])->name('getTypeValue'); //saveprice
Route::post('/pricings/saveprice', [Pricing1Controller::class, 'saveprice'])->name('saveprice');
Route::post('/pricings/deleteprice', [Pricing1Controller::class, 'deleteprice'])->name('deleteprice');
Route::post('/order_items/generateAllocation', [Order_itemController::class, 'generateAllocation'])->name('generateAllocation');
Route::post('/order_items/delivernote', [DeliveryController::class, 'delivernote'])->name('delivernote');
Route::post('/order_items/delivernote1', [DeliveryController::class, 'delivernote1'])->name('delivernote1');
Route::post('/order_items/deliver', [DeliveryController::class, 'deliver'])->name('deliver');
Route::post('/order_items/invoice', [DeliveryController::class, 'invoice'])->name('invoice');
Route::post('/order_items/orderinvoice', [DeliveryController::class, 'orderinvoice'])->name('orderinvoice');
Route::post('/collect', [DeliveryController::class, 'collect'])->name('collect');
Route::post('/deliveries/index2', [DeliveryController::class],'index2')->name('deliveries.index2');
Route::post('/order_items/updateAllocation', [Order_itemController::class, 'updateAllocation'])->name('updateAllocation');
Route::post('/porducts/srchProduct', [PorductController::class, 'srchProduct'])->name('srchProduct');
Route::post('/porducts/generateProduct', [PorductController::class, 'generateProduct'])->name('generateProduct');
Route::post('porducts/checkname', [PorductController::class, 'checkname'])->name('checkname');
Route::post('/userdetails/getUserTypeList', [UserDetailsController::class, 'getUserTypeList'])->name('getUserTypeList');
Route::get('delete', [DeleteController::class,'index']);
Route::get('dashboard', [CustomAuthController::class, 'dashboard']); 
Route::get('login', [CustomAuthController::class, 'index'])->name('login');
Route::post('custom-login', [CustomAuthController::class, 'customLogin'])->name('login.custom'); 
Route::post('customLoginproduction', [CustomAuthController::class, 'customLoginproduction'])->name('customLoginproduction'); 
Route::get('registration', [CustomAuthController::class, 'registration'])->name('register-user');
Route::post('custom-registration', [CustomAuthController::class, 'customRegistration'])->name('register.custom'); 
Route::get('signout', [CustomAuthController::class, 'signOut'])->name('signout');
Route::get('send-email', [EmailController::class, 'index']);
Route::resource('validate',ValidateController::class);
Route::resource('customerorders',CustomerOrderItermController::class);
Route::resource('productionperemployees', EmployeeproductionController::class);
//Route::resource('barcodes', BarcodeController::class);
Route::resource('allocateproductions',AllocatiopController::class);
Route::post('/barcodes/getScannedProductionid', [BarcodeController::class, 'getScannedProductionid'])->name('getScannedProductionid');


// --------------------------------------------the new resouce controller ------------------------------------------------route

Route::resource('allocateproductions', 'App\Http\Controllers\ProductionAllocationController');
Route::resource('pricings','App\Http\Controllers\Pricing1Controller');
Route::resource('setprices','App\Http\Controllers\PriceUpdateController');
Route::resource('recycles','App\Http\Controllers\RecycleController');
Route::resource('barcodes','App\Http\Controllers\BarcodeController');
// Route::resource('about','App\Http\Controllers\AboutUsController');

// --------------------------------------------the new resouce controller ------------------------------------------------route


Route::resource('vehicletrans',PumpTransController::class);
Route::resource('allocation',AllocationController::class);
Route::resource('deliveries',DeliveryController::class);
Route::resource('pickingslip',PickingslipController::class);
Route::resource('schedules',JobScheduleController::class);
Route::resource('onlineproducts',OnlineNewProducts::class);

Route::resource('reactjobcards',ReactjobCardController::class);
//Route::get('/reactjobcards/getProductdetails', [ReactjobCardController::class, 'getProductdetails'])->name('getProductdetails');

Route::get('reactjobcards/getProductdetails',['as'=>'getProductdetails','uses'=>'App\Http\Controllers\ReactjobCardController@getProductdetails']);


//Route::get('/orders/new', 'OrdersController@new')->name('new');
//Route::get('/orders/new', 'ResourceController@new');
Route::get('orders/new',[OrdersController::class,'new'])->name('new');
Route::resource('customerallocation',CustomerAllocationController::class);
Route::post('/customerallocation/allocatecustomer',[CustomerAllocationController::class,'allocatecustomer'])->name('allocatecustomer');
Route::post('/schedules/setschedule',[JobScheduleController::class,'setschedule'])->name('setschedule');
Route::post('pickingslip/getorderitem',[PickingslipController::class, 'getorderitem'])->name('getorderitem');
Route::post('/pickingslip/changestatepickingslip', [PickingslipController::class, 'changestatepickingslip'])->name('changestatepickingslip');
Route::post('/pickingslip/deliverslip', [PickingslipController::class, 'deliverslip'])->name('deliverslip');
//Route::post('/allocation/generateAllocation',[AllocationController::class,'generateAllocation'])->name('generateAllocation');
Route::resource('lists',listController::class);
Route::post('/customerorders/getProductbyidForOrderItem', [CustomerOrderItermController::class,'getProductbyidForOrderItem'])->name('getProductbyidForOrderItem');

Route::post('/customerorders/getProductbyidForOrderItem', [CustomerOrderItermController::class,'getProductbyidForOrderItem'])->name('getProductbyidForOrderItem');
//Route::resource('userrest', UserRestController::class);
//Route::resource('jobcardrest', App\Http\Controllers\JobCardRestController::class);
//Route::get('usersrest/usersrestshow',['as'=>'usersrest.show','uses'=>'UserRestController@show']);
//Route::get('usersrestshow', 'UserRestController@show');
//Route::get('usersrest/show',['as'=>'usersrest.show','uses'=>'UserRestController@show']);
//Route::post('/orderrest/store', [OrderRestController::class, 'store']);
Route::get('usersrest/destroy',['as'=>'usersrest.destroy','uses'=>'UserRestController@destroy']);
Route::get('userrest/qryUser',['as'=>'qryuser','uses'=>'App\Http\Controllers\UserRestController@qryUser']);
Route::get('jobcardrest/qryjobcard',['as'=>'qryjobcard','uses'=>'App\Http\Controllers\JobCardRestController@qryJobCard']);
Route::get('queryrest/qrymachinery',['as'=>'qrymachinery','uses'=>'App\Http\Controllers\QryRestController@qryMachinery']);
Route::get('queryrest/qrytype',['as'=>'qrytype','uses'=>'App\Http\Controllers\QryRestController@qryType']);
Route::get('queryrest/qryproduct',['as'=>'qryproduct','uses'=>'App\Http\Controllers\QryRestController@qryProduct']);
Route::get('queryrest/qryUsers',['as'=>'qryUsers','uses'=>'App\Http\Controllers\QryRestController@qryUsers']);
Route::get('queryrest/qryEdit',['as'=>'qryEdit','uses'=>'App\Http\Controllers\QryRestController@qryEdit']);
Route::get('queryrest/qryAllocateCustomer',['as'=>'qryAllocateCustomer','uses'=>'App\Http\Controllers\QryRestController@qryAllocateCustomer']);
//Route::get('queryrest/qryOrders',['as'=>'qryOrders','uses'=>'App\Http\Controllers\QryRestController@qryOrders']);
Route::get('queryrest/qrygetProductbyidFor',['as'=>'qrygetProductbyidFor','uses'=>'App\Http\Controllers\QryRestController@qrygetProductbyidFor']);
Route::get('queryrest/customerProducts',['as'=>'customerProducts','uses'=>'App\Http\Controllers\QryRestController@customerProducts']);
Route::get('queryrest/qrygetnewproducts',['as'=>'qrygetnewproducts','uses'=>'App\Http\Controllers\QryRestController@qrygetnewproducts']);
Route::get('queryrest/qrygetallorders',['as'=>'qrygetallorders','uses'=>'App\Http\Controllers\QryRestController@qrygetallorders']);
//Route::get('orderrest/store',['as'=>'store','uses'=>'App\Http\Controllers\OrderRestController@store']);
//Route::get('queryrest/store',['as'=>'store','uses'=>'App\Http\Controllers\QryRestController@store']);

Route::get('deliveries/display', ['as'=>'display','uses'=>'App\Http\Controllers\DeliveryController@display']);


//Route::get('queryrest/qrystore',['as'=>'qrystore','uses'=>'App\Http\Controllers\QryRestController@qrystore']);
Route::post('queryrest/qrystore', [
    'as' => 'qrystore',
    'uses' => 'App\Http\Controllers\QryRestController@qrystore'
]);

//----------------------------------------------------------------------------------------------------------------------------------------------------

Route::get('dashboard', [CustomAuthController::class, 'dashboard']); 
Route::get('login', [CustomAuthController::class, 'index'])->name('login');
Route::post('custom-login', [CustomAuthController::class, 'customLogin'])->name('login.custom'); 
Route::get('registration', [CustomAuthController::class, 'registration'])->name('register-user');
Route::post('custom-registration', [CustomAuthController::class, 'customRegistration'])->name('register.custom'); 
Route::get('signout', [CustomAuthController::class, 'signOut'])->name('signout');
Route::get('send-email', [EmailController::class, 'index']);
Route::get('validat', [CustomAuthController::class, 'validat'])->name('validat');
Route::post('make', [ValidationController::class, 'make'])->name('make');
Route::resource('validate', ValidationController::class);
Route::post('create', [ ValidationController::class, 'create']);
Route::resource('customerorders', CustomerOrderController::class);
Route::resource('calculators', PricingController::class);
Route::resource('onlineorders', OnlineOrdersController::class);
Route::post('/onlineorders/getProductbyidForOrderItem', [ DeliveryController::class,'getProductbyidForOrderItem'])->name('getProductbyidForOrderItem');
Route::post('/onlineorders/getProductbyidForOrderIte', [ DeliveryController::class,'getProductbyidForOrderIte'])->name('getProductbyidForOrderIte');
Route::post('/onlineorders/fetch',[ DeliveryController::class,'fetch'])->name('fetch');
Route::get('/onlineorders/getProduct', [ DeliveryController::class,'getProduct'])->name('getProduct');
Route::post('/onlineorders/get_suggestions', [ DeliveryController::class,'get_suggestions'])->name('get_suggestions');
Route::get('/onlineorders/getSuggestion', [ DeliveryController::class,' getSuggestion'])->name(' getSuggestion');
Route::post('/onlineorders/order', [ DeliveryController::class,'order'])->name('order');
Route::post('/onlineorders/getProductbyidFor', [ DeliveryController::class,'getProductbyidFor'])->name('getProductbyidFor');

Route::post('/onlineorders/changeOnlineProductstate', [ DeliveryController::class,'changeOnlineProductstate'])->name('changeOnlineProductstate');

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------

Route::get('actiontype.actiondelete/{type}', [TypeController::class, 'actiondelete'])->name('actiontype.actiondelete');
Route::get('actiontype.actionupdate/{type}', [TypeController::class, 'actionupdate'])->name('actiontype.actionupdate');
Route::get('actiontype.actionview/{type}',   [TypeController::class, 'actionview'])->name('actiontype.actionview');
Route::get('actiontype.actiondel/{type}',    [TypeController::class, 'actiondel'])->name('actionotype.actiondel');

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------

Route::get('actionprod.actiondelete/{order}', [ProductionController::class, 'actiondelete'])->name('actionprod.actiondelete');
Route::get('actionprod.actionupdate/{order}', [ProductionController::class, 'actionupdate'])->name('actionprod.actionupdate');
Route::get('actionprod.actionview/{order}',   [ProductionController::class, 'actionview'])->name('actionprod.actionview');
Route::get('actionprod.actiondel/{order}',    [ProductionController::class, 'actiondel'])->name('actionoprod.actiondel');

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------


Route::get('actionproditems.actionupdate/{order}',   [ProductionitemController::class, 'actionupdate'])->name('actionproditems.actionupdate');
Route::get('actionproditems.actiondel/{order}',      [ProductionitemController::class, 'actiondel'])->name('actionproditems.actiondel');
Route::get('actionproditems.actionview/{order}',     [ProductionitemController::class, 'actionview'])->name('actionproditems.actionview');
Route::get('actionproditems.actiondelete/{order}',   [ProductionitemController::class, 'actiondelete'])->name('actionproditems.actiondelete');

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------


Route::get('actionorders.actiondelete/{order}', [OrdersController::class, 'actiondelete'])->name('actionorders.actiondelete');
Route::get('actionorders.actionupdate/{order}', [OrdersController::class, 'actionupdate'])->name('actionorders.actionupdate');
Route::get('actionorders.actionview/{order}',   [OrdersController::class, 'actionview'])->name('actionorders.actionview');
Route::get('actionorders.actiondel/{order}',    [OrdersController::class, 'actiondel'])->name('actionorders.actiondel');

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------


Route::get('actionorderitems.actionupdate/{order}',   [Order_itemController::class, 'actionupdate'])->name('actionorderitems.actionupdate');
Route::get('actionorderitems.actiondel/{order}', [Order_itemController::class, 'actiondel'])->name('actionorderitems.actiondel');
Route::get('actionorderitems.actionupdate1/{order}',   [OrdersController::class, 'actionupdate1'])->name('actionorderitems.actionupdate1');
Route::get('actionorderitems.actiondel1/{order}', [OrdersController::class, 'actiondel2'])->name('actionorderitems.actiondel2');

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------


Route::get('actionjobs.actionupdate/{job}',   [JobCardController::class, 'actionupdate'])->name('actionjobs.actionupdate');
Route::get('actionjobs.actiondelete/{job}', [JobCardController::class, 'actiondelete'])->name('actionjobs.actiondelete');
Route::get('actionjobs.actionupdate/{job}',   [JobCardController::class, 'actionupdate'])->name('actionjobs.actionupdate');
Route::get('actionjobs.actionview/{job}', [JobCardController::class, 'actionview'])->name('actionjobs.actionview');
Route::get('actionjobs.actionproduction/{job}', [JobCardController::class, 'actionproduction'])->name('actionjobs.actionproduction');

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------



Route::get('actionjobitems.actiondelete/{item}', [JobcarditemController::class, 'actiondelete'])->name('actionjobitems.actiondelete');
Route::get('actionjobitems.actionupdate/{item}',   [JobcarditemController::class, 'actionupdate'])->name('actionjobitems.actionupdate');
Route::get('actionjobitems.actionview/{item}', [JobcarditemController::class, 'actionview'])->name('actionjobitems.actionview');

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------


Route::prefix('actionorders')->group(function () {

Route::get('/actionview',   ['as'=>'actionview','uses'=>'App\Http\Controllers\OrdersController@actionview']);
Route::get('/actionupdate', ['as'=>'actionupdate','uses'=>'App\Http\Controllers\OrdersController@actionupdate']);
Route::get('/actiondelete', ['as'=>'actiondelete','uses'=>'App\Http\Controllers\OrdersController@actiondelete']);


});

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------


Route::prefix('actionorderitems')->group(function () {

    Route::get('/actionview',   ['as'=>'actionview','uses'=>'App\Http\Controllers\OrdersController@actionview']);
    Route::get('/actionupdate' ,['as'=>'actionupdate','uses'=>'App\Http\Controllers\OrdersController@actionupdate']);
    Route::get('/actiondelete', ['as'=>'actiondelete','uses'=>'App\Http\Controllers\OrdersController@actiondelete']);


});

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------



Route::prefix('qryorders')->group(function () {

    Route::get('/index',        ['as'=>'index','uses'=>'App\Http\Controllers\RestOrdersController@index']);
    Route::get('/store',        ['as'=>'store','uses'=>'App\Http\Controllers\RestOrdersController@store']);
    Route::get('/show',         ['as'=>'show','uses'=>'App\Http\Controllers\RestOrdersController@show']);
    Route::get('/update',       ['as'=>'update','uses'=>'App\Http\Controllers\RestOrdersController@update']);
    Route::get('/destroy',      ['as'=>'destroy','uses'=>'App\Http\Controllers\RestOrdersController@destroy']);
    Route::get('/showitem',     ['as'=>'showitem','uses'=>'App\Http\Controllers\RestOrdersController@showitem']);
    Route::get('/pdfoderitems', ['as'=>'pdfoderitems','uses'=>'App\Http\Controllers\RestOrdersController@pdfoderitems']);
    
    Route::get('/orderbyid', ['as'=>'orderbyid','uses'=>'App\Http\Controllers\RestOrdersController@orderbyid']);
    Route::get('/orderitembyid', ['as'=>'orderitembyid','uses'=>'App\Http\Controllers\RestOrdersController@orderitembyid']);
    Route::get('/orderitemfrst', ['as'=>'orderitemfrst','uses'=>'App\Http\Controllers\RestOrdersController@orderitemfrst']);


    Route::get('/production', ['as'=>'production','uses'=>'App\Http\Controllers\RestOrdersController@production']);

});

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------



Route::prefix('qryallocations')->group(function () {

    Route::get('/index',   ['as'=>'index','uses'=>'App\Http\Controllers\RestAllocationController@index']);
    Route::get('/store',   ['as'=>'store','uses'=>'App\Http\Controllers\RestAllocationController@store']);
    Route::get('/show',    ['as'=>'show','uses'=>'App\Http\Controllers\RestAllocationController@show']);
    Route::get('/update',  ['as'=>'update','uses'=>'App\Http\Controllers\RestAllocationController@update']);
    Route::get('/destroy', ['as'=>'destroy','uses'=>'App\Http\Controllers\RestAllocationController@destroy']);

});

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------

Route::prefix('qrydeliveries')->group(function () {

    Route::get('/index',   ['as'=>'index','uses'=>'App\Http\Controllers\RestDeliveryController@index']);
    Route::get('/store',   ['as'=>'store','uses'=>'App\Http\Controllers\RestDeliveryController@store']);
    Route::get('/show',    ['as'=>'show','uses'=>'App\Http\Controllers\RestDeliveryController@show']);
    Route::get('/update',  ['as'=>'update','uses'=>'App\Http\Controllers\RestDeliveryController@update']);
    Route::get('/destroy', ['as'=>'destroy','uses'=>'App\Http\Controllers\RestDeliveryController@destroy']);
    Route::get('/delete',  ['as'=>'delete','uses'=>'App\Http\Controllers\RestDeliveryController@delete']);

});

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------


Route::prefix('qryjobcards')->group(function () {

    Route::get('/index',     ['as'=>'index','uses'=>'App\Http\Controllers\RestJobcardController@index']);
    Route::get('/store',     ['as'=>'store','uses'=>'App\Http\Controllers\RestJobcardController@store']);
    Route::get('/show',      ['as'=>'show','uses'=>'App\Http\Controllers\RestJobcardController@show']);
    Route::put('/{id}/update',  ['as'=>'update','uses'=>'App\Http\Controllers\RestJobcardController@update']);
    Route::get('/destroy',   ['as'=>'destroy','uses'=>'App\Http\Controllers\RestJobcardController@destroy']);
    Route::get('/productionj',   ['as'=>'productionj','uses'=>'App\Http\Controllers\RestJobcardController@productionj']);
    Route::get('/reactdelete',   ['as'=>'reactdelete','uses'=>'App\Http\Controllers\RestJobcardController@reactdelete']);

});


//-------------------------------------------------------------------------------------------------------------------------------------------------------------------




Route::prefix('qryjobcarditems')->group(function () {

    Route::get('/index',   ['as'=>'index','uses'=>'App\Http\Controllers\RestJobcarditemController@index']);
    Route::get('/store',   ['as'=>'store','uses'=>'App\Http\Controllers\RestJobcarditemController@store']);
    Route::get('/show',    ['as'=>'show','uses'=>'App\Http\Controllers\RestJobcarditemController@show']);
    Route::get('/update',  ['as'=>'update','uses'=>'App\Http\Controllers\RestJobcarditemController@update']);
    Route::get('/destroy', ['as'=>'destroy','uses'=>'App\Http\Controllers\RestJobcarditemController@destroy']);

});

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------



Route::prefix('qryproducts')->group(function () {

    Route::get('/index',    ['as'=>'index','uses'=>'App\Http\Controllers\RestProductsController@index']);
    Route::get('/store',    ['as'=>'store','uses'=>'App\Http\Controllers\RestProductsController@store']);
    Route::get('/show',     ['as'=>'show','uses'=>'App\Http\Controllers\RestProductsController@show']);
    Route::put('/update',   ['as'=>'update','uses'=>'App\Http\Controllers\RestProductsController@update']);
    Route::get('/destroy',  ['as'=>'destroy','uses'=>'App\Http\Controllers\RestProductsController@destroy']);

});

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------






Route::prefix('qryorderitems')->group(function () {

    Route::get('/index',   ['as'=>'index','uses'=>'App\Http\Controllers\RestOrderItemsController@index']);
    Route::get('/store',   ['as'=>'store','uses'=>'App\Http\Controllers\RestOrderItemsController@store']);
    Route::get('/show',    ['as'=>'show','uses'=>'App\Http\Controllers\RestOrderItemsController@show']);
    Route::get('/update',  ['as'=>'update','uses'=>'App\Http\Controllers\RestOrderItemsController@update']);
    Route::get('/destroy', ['as'=>'destroy','uses'=>'App\Http\Controllers\RestOrderItemsController@destroy']);
    Route::get('/changestate', ['as'=>'changestate','uses'=>'App\Http\Controllers\RestOrderItemsController@changestate']);
    
});

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------



Route::prefix('qryproduction')->group(function () {

    Route::get('/index',   ['as'=>'index',   'uses'=>'App\Http\Controllers\RestProductionController@index']);
    Route::get('/store',   ['as'=>'store',   'uses'=>'App\Http\Controllers\RestProductionController@store']);
    Route::get('/show',    ['as'=>'show',    'uses'=>'App\Http\Controllers\RestProductionController@show']);
    Route::get('/update',  ['as'=>'update',  'uses'=>'App\Http\Controllers\RestProductionController@update']);
    Route::get('/destroy', ['as'=>'destroy', 'uses'=>'App\Http\Controllers\RestProductionController@destroy']);
    Route::get('/newstore',['as'=>'newstore','uses'=>'App\Http\Controllers\RestProductionController@newstore']);
    
});



//-------------------------------------------------------------------------------------------------------------------------------------------------------------------



Route::prefix('qryrecycle')->group(function () {

    Route::get('/index',   ['as'=>'index','uses'=>'App\Http\Controllers\RestfulRecycletController@index']);
    Route::get('/store',   ['as'=>'store','uses'=>'App\Http\Controllers\RestfulRecycletController@store']);

    
});



//-------------------------------------------------------------------------------------------------------------------------------------------------------------------



Route::prefix('qryoperatorproduction')->group(function () {

    Route::get('/index',     ['as'=>'index',  'uses'=>'App\Http\Controllers\RestProductionByOpertorController@index']);

    
});

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------



Route::prefix('qryproductionitems')->group(function () {

    Route::get('/index',  ['as'=>'index','uses'=>'App\Http\Controllers\RestProductionItemController@index']);
    Route::get('/store',  ['as'=>'store','uses'=>'App\Http\Controllers\RestProductionItemController@store']);
    Route::get('/item',   ['as'=>'item','uses'=>'App\Http\Controllers\RestProductionItemController@item']);
    Route::get('/show',   ['as'=>'show','uses'=>'App\Http\Controllers\RestProductionItemController@show']);
    Route::get('/update', ['as'=>'update','uses'=>'App\Http\Controllers\RestProductionItemController@update']);
    Route::get('/destroy',['as'=>'destroy','uses'=>'App\Http\Controllers\RestProductionItemController@destroy']);
    
});


//-------------------------------------------------------------------------------------------------------------------------------------------------------------------

Route::prefix('qryjobcarditempdf')->group(function () {

    Route::get('/qry1',['as'=>'qry1','uses'=>'App\Http\Controllers\RestJobcardRprtController@qry1']);
    Route::get('/qry2',['as'=>'qry2','uses'=>'App\Http\Controllers\RestJobcardRprtController@qry2']);
    Route::get('/qry3',['as'=>'qry3','uses'=>'App\Http\Controllers\RestJobcardRprtController@qry3']);
    Route::get('/qry4',['as'=>'qry4','uses'=>'App\Http\Controllers\RestJobcardRprtController@qry4']);
    Route::get('/qry5',['as'=>'qry5','uses'=>'App\Http\Controllers\RestJobcardRprtController@qry5']);
    Route::get('/qry6',['as'=>'qry6','uses'=>'App\Http\Controllers\RestJobcardRprtController@qry6']);
    Route::get('/qry7',['as'=>'qry7','uses'=>'App\Http\Controllers\RestJobcardRprtController@qry7']);
    Route::get('/qry8',['as'=>'qry8','uses'=>'App\Http\Controllers\RestJobcardRprtController@qry8']);
    
});


//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------

Route::prefix('qryorderlistpdf')->group(function () {

    Route::get('/qry1',['as'=>'qry1','uses'=>'App\Http\Controllers\RestOrderListRptController@qry1']);
    Route::get('/qry2',['as'=>'qry2','uses'=>'App\Http\Controllers\RestOrderListRptController@qry2']);
    Route::get('/qry3',['as'=>'qry3','uses'=>'App\Http\Controllers\RestOrderListRptController@qry3']);
    Route::get('/qry4',['as'=>'qry4','uses'=>'App\Http\Controllers\RestOrderListRptController@qry4']);
    Route::get('/qry5',['as'=>'qry5','uses'=>'App\Http\Controllers\RestOrderListRptController@qry5']);
    Route::get('/qry6',['as'=>'qry6','uses'=>'App\Http\Controllers\RestOrderListRptController@qry6']);
    Route::get('/qry7',['as'=>'qry7','uses'=>'App\Http\Controllers\RestOrderListRptController@qry7']);
    Route::get('/qry8',['as'=>'qry8','uses'=>'App\Http\Controllers\RestOrderListRptController@qry8']);
    
});


// __________________________________________________________________________________________________________________

Route::prefix('qrytype')->group(function () {

    Route::get('/index',   ['as'=>'index',  'uses'=>'App\Http\Controllers\RestTypesController@index']);
    
    Route::get('/store',    ['as'=>'store', 'uses'=>'App\Http\Controllers\RestTypesController@store']);

    Route::get('/update',   ['as'=>'update','uses'=>'App\Http\Controllers\RestTypesController@update']);

    Route::get('/clon',['as'=>'clon',       'uses'=>'App\Http\Controllers\RestTypesController@clon']);

    
});


//_________________________________________________________________________________________________________________________________________________________________________

Route::prefix('qrycustomer')->group(function () {

    Route::get('/index',   ['as'=>'index','uses'=>'App\Http\Controllers\RestCustomerController@index']);
    Route::get('/store',   ['as'=>'store','uses'=>'App\Http\Controllers\RestCustomerController@store']);
    Route::get('/show',    ['as'=>'show','uses'=>'App\Http\Controllers\RestCustomerController@show']);
    Route::get('/update',  ['as'=>'update','uses'=>'App\Http\Controllers\RestCustomerController@update']);
    Route::get('/destroy', ['as'=>'destroy','uses'=>'App\Http\Controllers\RestCustomerController@destroy']);

});

//______________________________________________________________________________________________________________________________________________


Route::prefix('qrymachine')->group(function () {

    Route::get('/index',['as'=>'index','uses'=>'App\Http\Controllers\RestMachineController@index']);

    
});

//________________________________________________________________________________________________________________________________________________________

Route::prefix('qryterminal')->group(function () {

    Route::get('/index',   ['as'=>'index','uses'=>'App\Http\Controllers\RestCustomerController@index']);
  


});

//____________________________________________________________________________________________________________________________________________________________________________________

Route::prefix('qryrestip')->group(function () {

    Route::get('/index',   ['as'=>'index','uses'=>'App\Http\Controllers\RestIpController@index']);
    Route::get('/store',   ['as'=>'store','uses'=>'App\Http\Controllers\RestIpController@store']);


});

//____________________________________________________________________________________________________________________________________________________________________

Route::prefix('productionallocations')->group(function () {

    Route::get('/index',   ['as'=>'index','uses'=>'App\Http\Controllers\RestProductionController@index']);
    Route::get('/store',   ['as'=>'store','uses'=>'App\Http\Controllers\RestProductionController@store']);
    Route::get('/show',    ['as'=>'show','uses'=>'App\Http\Controllers\RestProductionController@show']);
    Route::get('/update',  ['as'=>'update','uses'=>'App\Http\Controllers\RestProductionController@update']);
    Route::get('/destroy', ['as'=>'destroy','uses'=>'App\Http\Controllers\RestProductionController@destroy']);
    Route::get('/newstore',['as'=>'newstore','uses'=>'App\Http\Controllers\RestProductionController@newstore']);
    
});

//__________________________________________________________________________________________________________________________________________________________________________________

Route::prefix('qryprices')->group(function () {

    Route::get('/store',   ['as'=>'store','uses'=>'App\Http\Controllers\RESTPricingController@store']);
    Route::get('/index',   ['as'=>'index','uses'=>'App\Http\Controllers\RESTPricingController@index']);
    Route::get('/destroy',   ['as'=>'destroy','uses'=>'App\Http\Controllers\RESTPricingController@destroy']);
  
});

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------


Route::prefix('qrycustomeronlineproducts')->group(function () {



  Route::get('/index',['as'=>'index','uses'=>'App\Http\Controllers\RestDeliveryController@index']);


});

//-----------------------------------------------------------------------------------------------------------------------------------------


Route::prefix('qryplasticmaterials')->group(function () {

    Route::get('/index',   ['as'=>'index','uses'=>'App\Http\Controllers\Plasticmaterial@index']);
    Route::get('/store',   ['as'=>'store','uses'=>'App\Http\Controllers\Plasticmaterial@store']);
    Route::get('/show',    ['as'=>'show','uses'=>'App\Http\Controllers\Plasticmaterial@show']);
    Route::get('/update',  ['as'=>'update','uses'=>'App\Http\Controllers\Plasticmaterial@update']);
    Route::get('/destroy', ['as'=>'destroy','uses'=>'App\Http\Controllers\Plasticmaterial@destroy']);
    Route::get('/getProductdetails', ['as'=>'getProductdetails','uses'=>'App\Http\Controllers\Plasticmaterial@getProductdetails']);

});
// REACT ROUTE



Route::get('/chemicalproducts/show',       [ChemicalProductController::class, 'show']);
Route::get('/chemicalproducts/store',      [ChemicalProductController::class, 'store']);
Route::get('/chemicalproducts/update',     [ChemicalProductController::class, 'update']);
Route::get('/chemicalproducts/index',      [ChemicalProductController::class, 'index']);
Route::get('/chemicalproducts/destroy',    [ChemicalProductController::class, 'destroy']);
Route::get('chemicalproducts/updatebatch', [ChemicalProductController::class, 'updateBatch']);

 

Route::get('/chemicaljobcard',            fn() => view('chemicaljobcard'));
Route::get('/chemicaljobcardlist',        fn() => view('chemicaljobcardlist'));
Route::get('/chemicaljobcards/store',     [ChemicalJobCardController::class, 'store']);
Route::get('/chemicaljobcards/index',     [ChemicalJobCardController::class, 'index']);
Route::get('/chemicaljobcards/destroy',   [ChemicalJobCardController::class, 'destroy']);




 


Route::get('/chemicalproducts/stocklist',  [StockAdjustmentController::class, 'productsList']);
Route::get('/stock_adjustment/',          [StockAdjustmentController::class, 'index']);
Route::get('/stock_adjustments/',         [StockAdjustmentController::class, 'store']);
Route::delete('/stock_adjustments/{id}/',  [StockAdjustmentController::class, 'destroy']);



use App\Http\Controllers\CompanyInfoController;



Route::get('/qrycompanyinfo/fetch',[CompanyInfoController::class, 'fetch']);
Route::get('/qrycompanyinfo/save', [CompanyInfoController::class, 'save']);


Route::get('ordercreate', [OrderController::class, 'createPage']);
Route::get('orderlist',   [OrderController::class, 'listPage']);

Route::get('orders/index',         [OrderController::class, 'index']);
Route::get('orders/show',          [OrderController::class, 'show']);
Route::get('orders/store',         [OrderController::class, 'store']);
Route::get('orders/completeitem',  [OrderController::class, 'completeItem']);
Route::get('orders/completeorder', [OrderController::class, 'completeOrder']);
Route::get('orders/destroy',       [OrderController::class, 'destroy']);



use App\Http\Controllers\RawMaterialController;

Route::get('/raw-materials',        [RawMaterialController::class, 'index']);
Route::get('/raw-materials/list',   [RawMaterialController::class, 'list']);
Route::get('/raw-materials/save',   [RawMaterialController::class, 'save']);
Route::get('/raw-materials/toggle', [RawMaterialController::class, 'toggle']);
Route::get('/raw-materials/lookup', [RawMaterialController::class, 'lookup']);