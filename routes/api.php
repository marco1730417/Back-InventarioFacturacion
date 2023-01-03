<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
 */

Route::prefix('/user')->group( function () {
   
    Route::post('/login','App\Http\Controllers\LoginController@login');
    Route::post('/register', 'App\Http\Controllers\LoginController@register');
  //  Route::get('/get-users', 'App\Http\Controllers\UserController@getUsers');

    Route::middleware('auth:api')->get('/get-users', 'App\Http\Controllers\UserController@getUsers');
      
});
