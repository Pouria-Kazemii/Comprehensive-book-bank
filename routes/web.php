<?php

use App\Http\Controllers\Api\BookController;
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
// Route::get('/v1/book/check_is_translate/{roleid}/{from}/{limit}/{order}', [ChangeDataController::class, 'check_is_translate']);
Route::get('/v1/book/update_book_master_id_in_gissom/{limit}', [ChangeDataController::class, 'update_book_master_id_in_gissom']);
Route::get('/v1/book/update_book_master_id_in_digi/{limit}', [ChangeDataController::class, 'update_book_master_id_in_digi']);
Route::get('/v1/book/update_book_master_id_in_30book/{limit}', [ChangeDataController::class, 'update_book_master_id_in_30book']);
Route::get('/v1/book/update_book_master_id_in_iranketab/{limit}', [ChangeDataController::class, 'update_book_master_id_in_iranketab']);
Route::get('/v1/book/update_partner_master_id_in_iranketab/{limit}', [ChangeDataController::class, 'update_partner_master_id_in_iranketab']);
Route::get('/dossier/{bookId}', [BookController::class, 'dossier']);
// Route::get('/consensus_similar_books_by_iranketab_entitle/{limit}', [ChangeDataController::class, 'consensus_similar_books_by_iranketab_entitle']);
Route::get('/consensus_similar_books_by_iranketab_parentId/{limit}', [ChangeDataController::class, 'consensus_similar_books_by_iranketab_parentId']);
Route::get('/merge_parentid_tempparentid/{limit}', [ChangeDataController::class, 'merge_parentid_tempparentid']);
// Route::get('/update_tempparent_to_other_fields/{limit}', [ChangeDataController::class, 'update_tempparent_to_other_fields']);
// Route::get('/update_tempparent_to_other_fields_desc/{limit}', [ChangeDataController::class, 'update_tempparent_to_other_fields_desc']);
Route::get('/check_old_xparent/{limit}', [ChangeDataController::class, 'check_old_xparent']);


