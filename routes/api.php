<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JWTController;

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
    return response()->json([ 'valid' => auth()->check() ]);
});


// Route::post('users','UsersController@index');
// Route::post('login','UsersController@login');
// Route::post('user_detail','UsersController@detail');
// Route::post('simpan_user','UsersController@store');
// Route::post('submit_pinjaman','UsersController@store');

Route::group(['middleware' => 'api'], function($router) {
    Route::post('/register', [JWTController::class, 'register']);
    Route::post('/login', [JWTController::class, 'login']); 
    Route::post('/logout', [JWTController::class, 'logout']);
    Route::post('/refresh', [JWTController::class, 'refresh']);
    Route::get('/profile', [JWTController::class, 'profile']);
	Route::post('users','UsersController@index');
	Route::post('pinjaman','PinjamanController@index');
	Route::post('submit_pinjaman','PinjamanController@submit_pinjaman');
	Route::post('upd_status','PinjamanController@upd_status_pinjaman');
	Route::post('pinjaman_detail','PinjamanController@detail');
	Route::post('submit_cicilan','PinjamanController@submit_cicilan');
	Route::post('master_data','MasterController@index');
	Route::post('upd_setting','MasterController@upd_setting');
	Route::post('get_dashboard','MasterController@get_dashboard');
});

