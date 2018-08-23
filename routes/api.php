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

Route::get('ok', function () { die('::'); });

/* ----------------------------backend------------------------------------------*/
Route::post('backend/auth/login','api\backend\AuthController@Login')->middleware('cors');
Route::middleware(['cors', 'Api'])->group(function () {

    /***************************--artists--***************************/

    Route::post('backend/artists/gets', 'api\backend\ArtistsController@gets');

    Route::post('backend/artists/create', 'api\backend\ArtistsController@create');

    Route::post('backend/artists/store', 'api\backend\ArtistsController@store');

    Route::post('backend/artists/edit', 'api\backend\ArtistsController@edit');

    Route::post('backend/artists/update', 'api\backend\ArtistsController@update');

    Route::post('backend/artists/destroy', 'api\backend\ArtistsController@destroy');

    /***************************!--artists--***************************/

    /***************************--musics--***************************/

    Route::post('backend/musics/create', 'api\backend\MusicsController@create');

    Route::post('backend/musics/gets', 'api\backend\MusicsController@gets');

    Route::post('backend/musics/store', 'api\backend\MusicsController@store');

    Route::post('backend/musics/edit', 'api\backend\MusicsController@edit');

    Route::post('backend/musics/update', 'api\backend\MusicsController@update');

    Route::post('backend/musics/destroy', 'api\backend\MusicsController@destroy');

    Route::post('backend/musics/sort', 'api\backend\MusicsController@sort');


    /***************************!--musics--***************************/

    /***************************--invoices--***************************/
    
    Route::post('backend/invoices/gets', 'api\backend\InvoicesController@gets');

    
    /***************************!--invoices--***************************/
});
/* ----------------------------!backend------------------------------------------*/

/* ----------------------------!frontend------------------------------------------*/


Route::middleware(['cors'])->group(function () {
    /***************************--artists--***************************/
    Route::post('frontend/auth/add', 'api\frontend\AuthController@add');
    
    Route::post('frontend/artists/first', 'api\frontend\ArtistsController@first');

    /***************************!--artists--***************************/
 
    /***************************--payment--***************************/

    Route::post('frontend/payment/purchase', 'api\frontend\PaymentController@purchase');
    
    Route::post('frontend/payment/checkout', 'api\frontend\PaymentController@checkout');

    Route::any('frontend/payment/paypal_success', 'api\frontend\PaymentController@paypal_success')->name("api.payment.paypal_success");

    Route::any('frontend/payment/paypal_cancel', 'api\frontend\PaymentController@paypal_cancel')->name("api.payment.paypal_cancel");

    Route::any('frontend/payment/ccavenue_success', 'api\frontend\PaymentController@ccavenue_success')->name("api.payment.ccavenue_success");

    Route::any('frontend/payment/ccavenue_cancel', 'api\frontend\PaymentController@ccavenue_cancel')->name("api.payment.ccavenue_cancel");

    Route::get('frontend/payment/ccavenue_submit', 'api\frontend\PaymentController@ccavenue_submit')->name("api.payment.ccavenue_submit");

    /***************************!--payment--***************************/ 

    /***************************--downloads--***************************/ 

    Route::post('frontend/downloads/add', 'api\frontend\DownloadsController@add');

    Route::post('frontend/downloads/check', 'api\frontend\DownloadsController@check');

    Route::any('frontend/downloads/file', 'api\frontend\DownloadsController@file');

    /***************************!--downloads--***************************/ 


});

/* ----------------------------!frontend------------------------------------------*/