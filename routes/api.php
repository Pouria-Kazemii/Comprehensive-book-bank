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
Route::get('/v1/book/check', 'API\BookCheckController@check');

//Route::group
//(
//    ['middleware' => ['jwt.auth']],
//    function()
//    {
//        Route::post('/v1/book/find', 'API\BookController@find');
//        Route::post('/v1/book/detail', 'API\BookController@detail');
//    }
//);
//Route::post('/v1/book/find', 'API\BookController@find');
Route::any('/v1/book/find', 'API\BookController@find');
Route::post('/v1/book/find/publisher', 'API\BookController@findByPublisher');
Route::post('/v1/book/find/creator', 'API\BookController@findByCreator');
Route::post('/v1/book/find/ver', 'API\BookController@findByVer');

Route::post('/v1/book/dossier', 'API\BookController@dossier');
Route::post('/v1/book/detail', 'API\BookController@detail');

Route::post('/v1/author/find', 'API\AuthorController@find');
Route::post('/v1/author/detail', 'API\AuthorController@detail');

Route::post('/v1/subject/find', 'API\SubjectController@find');
Route::post('/v1/subject/detail', 'API\SubjectController@detail');

Route::post('/v1/publisher/find', 'API\PublisherController@find');
Route::post('/v1/publisher/detail', 'API\PublisherController@detail');
