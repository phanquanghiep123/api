<?php

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
Route::get('/bb.php', function() {
	die('TEST ok');
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ok', function() {
	die('TEST ok');
});

Route::get('/create_user', function () {
    $user = new App\User();
    $user->password = Hash::make('123456');
    $user->email = 'phanquanghiep123@gmail.com';
    $user->is_sys = 1;
    $user->save();
});
Route::any('downloads/file', 'api\frontend\DownloadsController@file');