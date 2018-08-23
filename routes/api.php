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

/* ----------------------------backend------------------------------------------*/
Route::post('backend/auth/login','api\backend\AuthController@Login')->middleware('cors');
Route::group(['prefix' => 'backend','middleware' =>'cors'], function ()  {

    /***************************--artists--***************************/
    Route::post('artists/gets', 'api\backend\ArtistsController@gets');

    Route::post('artists/create', 'api\backend\ArtistsController@create');

    Route::post('artists/store', 'api\backend\ArtistsController@store');

    Route::post('artists/edit', 'api\backend\ArtistsController@edit');

    Route::post('artists/update', 'api\backend\ArtistsController@update');

    Route::post('artists/destroy', 'api\backend\ArtistsController@destroy');

    /***************************!--artists--***************************/

    /***************************--musics--***************************/

    Route::post('musics/create', 'api\backend\MusicsController@create');

    Route::post('musics/gets', 'api\backend\MusicsController@gets');

    Route::post('musics/store', 'api\backend\MusicsController@store');

    Route::post('musics/edit', 'api\backend\MusicsController@edit');

    Route::post('musics/update', 'api\backend\MusicsController@update');

    Route::post('musics/destroy', 'api\backend\MusicsController@destroy');

    Route::post('musics/sort', 'api\backend\MusicsController@sort');


    /***************************!--musics--***************************/

    /***************************--invoices--***************************/
    
    Route::post('invoices/gets', 'api\backend\InvoicesController@gets');

    
    /***************************!--invoices--***************************/
});
/* ----------------------------!backend------------------------------------------*/
/* ----------------------------!frontend------------------------------------------*/
Route::group(['prefix' => 'frontend','middleware' => ['cors']], function ()  {
    /***************************--artists--***************************/
    Route::post('auth/add', 'api\frontend\AuthController@add');
    
    Route::post('artists/first', 'api\frontend\ArtistsController@first');

    /***************************!--artists--***************************/
 
    /***************************--payment--***************************/

    Route::post('payment/purchase', 'api\frontend\PaymentController@purchase');
    
    Route::post('payment/checkout', 'api\frontend\PaymentController@checkout');

    Route::any('payment/paypal_success', 'api\frontend\PaymentController@paypal_success')->name("api.payment.paypal_success");

    Route::any('payment/paypal_cancel', 'api\frontend\PaymentController@paypal_cancel')->name("api.payment.paypal_cancel");

    Route::any('payment/ccavenue_success', 'api\frontend\PaymentController@ccavenue_success')->name("api.payment.ccavenue_success");

    Route::any('payment/ccavenue_cancel', 'api\frontend\PaymentController@ccavenue_cancel')->name("api.payment.ccavenue_cancel");

    Route::get('payment/ccavenue_submit', 'api\frontend\PaymentController@ccavenue_submit')->name("api.payment.ccavenue_submit");

    /***************************!--payment--***************************/ 

    /***************************--downloads--***************************/ 

    Route::post('downloads/add', 'api\frontend\DownloadsController@add');

    Route::post('downloads/check', 'api\frontend\DownloadsController@check');

    Route::any('downloads/file', 'api\frontend\DownloadsController@file');

    /***************************!--downloads--***************************/ 
    Route::get('demo', function(Request $request){
        echo "sdfsada";
    });

});
/* ----------------------------!frontend------------------------------------------*/