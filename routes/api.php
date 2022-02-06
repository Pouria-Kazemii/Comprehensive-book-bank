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
//Route::get('/v1/book/check', 'API\BookCheckController@check');

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
Route::post('/v1/book/find/publisher', 'API\BookController@findByPublisher');
Route::post('/v1/book/find/creator', 'API\BookController@findByCreator');
Route::post('/v1/book/find/ver', 'API\BookController@findByVer');
Route::post('/v1/book/find/subject', 'API\BookController@findBySubject');
Route::post('/v1/book/detail', 'API\BookController@detail');
Route::post('/v1/book/market', 'API\BookController@market');

Route::post('/v1/creator/find', 'API\CreatorController@find');
Route::post('/v1/creator/find/subject', 'API\CreatorController@findBySubject');
Route::post('/v1/creator/find/publisher', 'API\CreatorController@findByPublisher');
Route::post('/v1/creator/role', 'API\CreatorController@role');
Route::post('/v1/creator/annual-activity', 'API\CreatorController@annualActivity');
Route::post('/v1/creator/detail', 'API\CreatorController@detail');

Route::post('/v1/subject/find', 'API\SubjectController@find');

Route::post('/v1/publisher/find', 'API\PublisherController@find');
Route::post('/v1/publisher/find/subject', 'API\PublisherController@findBySubject');
Route::post('/v1/publisher/find/creator', 'API\PublisherController@findByCreator');
Route::post('/v1/publisher/detail', 'API\PublisherController@detail');
Route::post('/v1/publisher/annual-activity-title', 'API\PublisherController@annualActivityByTitle');
Route::post('/v1/publisher/annual-activity-circulation', 'API\PublisherController@annualActivityByCirculation');
Route::post('/v1/publisher/translate-authorship', 'API\PublisherController@translateAuthorship');
Route::post('/v1/publisher/statistic-subject', 'API\PublisherController@statisticSubject');
