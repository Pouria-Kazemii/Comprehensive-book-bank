<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChangeDataController;

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

Route::get('/', function () {
    return view('welcome');
});
//Route::get('book', 'bookController@index');
// add by kiani
Route::get('/v1/book/check_is_translate/{roleid}/{from}/{limit}/{order}', [ChangeDataController::class, 'check_is_translate']);
Route::get('/v1/publisher/role', 'API\PublisherController@role');
