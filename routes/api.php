<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserRestController;

use App\Models\Type;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::get('/usersrest/show', 'UserRestController@show');

//Route::get('usersrest',['as'=>'usersrest.index','uses'=>'UserRestController@index']);
//Route::get('usersrest/show/{id}',['as'=>'usersrest.show','uses'=>'UserRestController@show']);
//Route::get('usersrestshow',['as'=>'usersrest.show','uses'=>'UserRestController@show']);
//Route::post('usersrest/create',['as'=>'usersrest.store','uses'=>'UserRestController@store']);
//Route::get('usersrest/edit/{id}',['as'=>'usersrest.edit','uses'=>'UserRestController@edit']);
//Route::patch('usersrest/{id}',['as'=>'usersrest.update','uses'=>'UserRestController@update']);
Route::get('usersrest/destroy',['as'=>'usersrest.destroy','uses'=>'UserRestController@destroy']);

Route::get('restdestroy',['as'=>'usersrest.destroy','uses'=>'UserRestController@destroy']);
//Route::resource('usersrest', UserRestController::class);

Route::get('jobcardrest/qryjobcard',['as'=>'qryjobcard','uses'=>'App\Http\Controllers\JobCardRestController@qryJobCard']);
Route::get('userrest/qryuser', 'UserRestController@qryUser');
Route::get('queryrest/qrymachinery', 'QryRestController@qryMachinery');
Route::get('queryrest/qrytype', 'QryRestController@qryType');
Route::resource('userrest', App\Http\Controllers\UserRestController::class);
Route::resource('jobcardrest', App\Http\Controllers\JobCardRestController::class);
Route::resource('queryrest', App\Http\Controllers\QryRestController::class);
Route::resource('orderrest', App\Http\Controllers\OrderRestController::class);

Route::get('actionorders.actiondelete/{order}', [OrdersController::class, 'actiondelete'])->name('actionorders.actiondelete');
Route::get('actionorders.actionupdate/{order}', [OrdersController::class, 'actionupdate'])->name('actionorders.actionupdate');
Route::get('actionorders.actionview/{order}',   [OrdersController::class, 'actionview'])->name('actionorders.actionview');
Route::get('actionorders.actiondel/{order}', [OrdersController::class, 'actiondel'])->name('actionorders.actiondel');


Route::prefix('actionorders')->group(function () {

    Route::get('/actionview', ['as'=>'actionview','uses'=>'App\Http\Controllers\OrdersController@actionview']);
    Route::get('/actionupdate',['as'=>'actionupdate','uses'=>'App\Http\Controllers\OrdersController@actionupdate']);
    Route::get('/actiondelete', ['as'=>'actiondelete','uses'=>'App\Http\Controllers\OrdersController@actiondelete']);


});

Route::prefix('actionorderitems')->group(function () {

    Route::get('/actionview', ['as'=>'actionview','uses'=>'App\Http\Controllers\OrdersController@actionview']);
    Route::get('/actionupdate',['as'=>'actionupdate','uses'=>'App\Http\Controllers\OrdersController@actionupdate']);
    Route::get('/actiondelete', ['as'=>'actiondelete','uses'=>'App\Http\Controllers\OrdersController@actiondelete']);


});


Route::prefix('qryorders')->group(function () {

    Route::get('/index', ['as'=>'index','uses'=>'App\Http\Controllers\RestOrdersController@index']);
    Route::post('/store',['as'=>'store','uses'=>'App\Http\Controllers\RestOrdersController@store']);
    Route::get('/show', ['as'=>'show','uses'=>'App\Http\Controllers\RestOrdersController@show']);
    Route::get('/update',  ['as'=>'update','uses'=>'App\Http\Controllers\RestOrdersController@update']);
    Route::get('/destroy',  ['as'=>'destroy','uses'=>'App\Http\Controllers\RestOrdersController@destroy']);
    Route::get('/pdforders',  ['as'=>'pdforders','uses'=>'App\Http\Controllers\RestOrdersController@pdforders']);
    Route::get('/pdfoderitems',  ['as'=>'pdfoderitems','uses'=>'App\Http\Controllers\RestOrdersController@pdfoderitems']);

});


Route::prefix('qryallocations')->group(function () {

    Route::get('/index', ['as'=>'index','uses'=>'App\Http\Controllers\RestAllocationController@index']);
    Route::get('/store',['as'=>'store','uses'=>'App\Http\Controllers\RestAllocationController@store']);
    Route::get('/show', ['as'=>'show','uses'=>'App\Http\Controllers\RestAllocationController@show']);
    Route::get('/update',  ['as'=>'update','uses'=>'App\Http\Controllers\RestAllocationController@update']);
    Route::get('/destroy',  ['as'=>'destroy','uses'=>'App\Http\Controllers\RestAllocationController@destroy']);

});


Route::prefix('qryjobcards')->group(function () {

    Route::get('/index', ['as'=>'index','uses'=>'App\Http\Controllers\RestJobcardController@index']);
    Route::post('/store',['as'=>'store','uses'=>'App\Http\Controllers\RestJobcardController@store']);
    Route::get('/show', ['as'=>'show','uses'=>'App\Http\Controllers\RestJobcardController@show']);
    Route::put('/{id}/update',  ['as'=>'update','uses'=>'App\Http\Controllers\RestJobcardController@update']);
    Route::get('/destroy',  ['as'=>'destroy','uses'=>'App\Http\Controllers\RestJobcardController@destroy']);

});


Route::prefix('qryproducts')->group(function () {

    Route::get('/index', ['as'=>'index','uses'=>'App\Http\Controllers\RestProductsController@index']);
    Route::post('/store',['as'=>'store','uses'=>'App\Http\Controllers\RestProductsController@store']);
    Route::get('/show', ['as'=>'show','uses'=>'App\Http\Controllers\RestProductsController@show']);
    Route::put('/{id}/update',  ['as'=>'update','uses'=>'App\Http\Controllers\RestProductsController@update']);
    Route::get('/destroy',  ['as'=>'destroy','uses'=>'App\Http\Controllers\RestProductsController@destroy']);

});





Route::prefix('qryorderitems')->group(function () {

    Route::get('/index',  ['as'=>'index','uses'=>'App\Http\Controllers\RestOrderItemsController@index']);
    Route::post('/store', ['as'=>'store','uses'=>'App\Http\Controllers\RestOrderItemsController@store']);
    Route::get('/{id}/show', ['as'=>'store','uses'=>'App\Http\Controllers\RestOrderItemsController@show']);
    Route::get('/update', ['as'=>'update','uses'=>'App\Http\Controllers\RestOrderItemsController@update']);
    Route::get('/destroy', ['as'=>'destroy','uses'=>'App\Http\Controllers\RestOrderItemsController@destroy']);
    
});





Route::prefix('qryorderitems')->group(function () {

    Route::get('/index',  ['as'=>'index','uses'=>'App\Http\Controllers\RestOrderItemsController@index']);
    Route::post('/store', ['as'=>'store','uses'=>'App\Http\Controllers\RestOrderItemsController@store']);
    Route::get('/{id}/show', ['as'=>'store','uses'=>'App\Http\Controllers\RestOrderItemsController@show']);
    Route::get('/update', ['as'=>'update','uses'=>'App\Http\Controllers\RestOrderItemsController@update']);
    Route::get('/destroy', ['as'=>'destroy','uses'=>'App\Http\Controllers\RestOrderItemsController@destroy']);
    
});



Route::get('/constants', function (Request $request) {
    
    $unitValue = Type::where('grouptype', 'unit')
        ->where('id', $request->unitId)
        ->value('value');

    $virginConstant = Type::where('grouptype', 'constant')
        ->where('name', 'virgin_constant')
        ->value('value');

    $recycledConstant = Type::where('grouptype', 'constant')
        ->where('name', 'recycled_constant')
        ->value('value');

    $isVirgin = Type::where('grouptype', 'material')
        ->where('description', 'virgin')
        ->where('id', $request->materialType)
        ->exists();

    return response()->json([
        'unitValue' => $unitValue,
        'constantValue' => $isVirgin ? $virginConstant : $recycledConstant,
    ]);
});

