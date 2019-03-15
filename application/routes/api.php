<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->post('/broadcasting/auth',function (Request $request){ 
    $pusher = new Pusher\Pusher(env('PUSHER_APP_KEY'),env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'));
    return $pusher->socket_auth($request->request->get('channel_name'),$request->request->get('socket_id'));
}); 





// Route::group(['prefix' => 'designRequest'], function()
// {
//     Route::post('/', 'Api\DesignRequestController@index');
//     Route::post('view', 'Api\DesignRequestController@view');
//     Route::post('store', 'Api\DesignRequestController@store');
//     Route::post('update', 'Api\DesignRequestController@update');
//     Route::post('setStatus', 'Api\DesignRequestController@setStatus');
//     Route::post('delete', 'Api\DesignRequestController@delete');
// });

Route::post('login', 'Api\AuthController@login');
Route::post('logout', 'Api\AuthController@logout');
Route::post('auth', 'Api\AuthController@auth');
Route::post('pusher', 'Api\AuthController@pusher');
Route::post('hasAccess', 'Api\AuthController@hasAccess');

Route::group(['prefix' => 'dashboard'], function()
{
    Route::post('/', 'Api\DashboardController@index');
});

Route::group(['prefix' => 'arModel'], function()
{
    Route::post('/', 'Api\ArModelController@index');
    Route::post('/model', 'Api\ArModelController@model');
});

Route::group(['prefix' => 'delivery'], function()
{
    Route::post('/user', 'Api\DeliveryController@user');
    Route::post('/userCourier', 'Api\DeliveryController@userCourier');
    Route::post('/collection', 'Api\DeliveryController@collection');
    
    Route::post('/', 'Api\DeliveryController@index');
    Route::post('/view', 'Api\DeliveryController@view');
    Route::post('/courier', 'Api\DeliveryController@courier');
    Route::post('/take', 'Api\DeliveryController@take');
    Route::post('/undoTake', 'Api\DeliveryController@undoTake');
    Route::post('/startSend', 'Api\DeliveryController@startSend');
    Route::post('/undoStartSend', 'Api\DeliveryController@undoStartSend');
    Route::post('/finish', 'Api\DeliveryController@finish');
    Route::post('/undoFinish', 'Api\DeliveryController@undoFinish');
    Route::post('/confirm', 'Api\DeliveryController@confirm');
    Route::post('/undoConfirm', 'Api\DeliveryController@undoConfirm');
});


Route::group(['prefix' => 'listRequest'], function()
{
    Route::post('/collection', 'Api\ListRequestController@collection');
    
    Route::post('/', 'Api\ListRequestController@index');
    Route::post('/view', 'Api\ListRequestController@view');
    Route::post('/store', 'Api\ListRequestController@view');
    Route::post('/update', 'Api\ListRequestController@view');
    Route::post('/delete', 'Api\ListRequestController@view');
});


Route::group(['prefix' => 'crm'], function()
{
    Route::post('/collection', 'Api\CrmController@collection');
    Route::post('/notifications', 'Api\CrmController@notifications');
    
    Route::post('/', 'Api\CrmController@index');
    Route::post('/schedule', 'Api\CrmController@schedule');
    Route::post('/create', 'Api\CrmController@create');
    Route::post('/next', 'Api\CrmController@next');
    Route::post('/reschedule', 'Api\CrmController@reschedule');
    Route::post('/checkIn', 'Api\CrmController@checkIn');
    Route::post('/checkOut', 'Api\CrmController@checkOut');
    Route::post('/sendFeedback', 'Api\CrmController@sendFeedback');
    Route::post('/waFeedback', 'Api\CrmController@waFeedback');
    
    Route::post('/sendFeedbackByEmail', 'Api\CrmController@sendFeedbackByEmail');
    Route::post('/sendFeedbackByWhatsapp', 'Api\CrmController@sendFeedbackByWhatsapp');
});

Route::group(['prefix' => 'company'], function()
{
    Route::post('/get', 'Api\CompanyController@get');
    Route::post('/getBrand', 'Api\CompanyController@getBrand');
    Route::post('/getAddress', 'Api\CompanyController@getAddress');
    Route::post('/getPic', 'Api\CompanyController@getPic');
    Route::post('/getDetail', 'Api\CompanyController@getDetail');
});

