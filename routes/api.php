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

Route::post('/v1/user/login', 'API\UserController@login');

//Route::get('/v1/book/find', 'API\BookController@find');
//Route::get('/v1/book/check', 'API\BookController@checkBookK24');

//Route::group
//(
//    ['middleware' => ['jwt.auth']],
//    function()
//    {
//        Route::post('/v1/book/find', 'API\BookController@find');
//        Route::post('/v1/book/detail', 'API\BookController@detail');
//    }
//);
Route::post('/v1/book/find', 'API\BookController@find');
Route::post('/v1/book/detail', 'API\BookController@detail');
