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

Route::get('/', 'LocationController@index');
Route::get('/GetNearestLocation', 'LocationController@GetNearestLocation');
Route::post('/CalculateCashback', 'LocationController@CalculateCashback');
Route::post('/CreateNewLocation', 'LocationController@CreateNewLocation');
Route::post('/register', 'Auth\ApiAuthController@register');

//incomplete, not working
Route::post('/login', 'Auth\ApiAuthController@login');
