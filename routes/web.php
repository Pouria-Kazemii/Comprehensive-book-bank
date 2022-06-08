<?php

use App\Http\Controllers\API\BookController;
use App\Http\Controllers\API\InstagramController;
use App\Http\Controllers\API\CrawlerKetabirController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChangeDataController;
use App\Http\Controllers\ExcelController;

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
Route::get('/v1/book/update_temp_book_master_id_in_gissom/{limit}', [ChangeDataController::class, 'update_temp_book_master_id_in_gissom']);
Route::get('/v1/book/update_temp_book_master_id_in_digi/{limit}', [ChangeDataController::class, 'update_temp_book_master_id_in_digi']);
Route::get('/v1/book/update_temp_book_master_id_in_30book/{limit}', [ChangeDataController::class, 'update_temp_book_master_id_in_30book']);
Route::get('/v1/book/update_temp_book_master_id_in_iranketab/{limit}', [ChangeDataController::class, 'update_temp_book_master_id_in_iranketab']);
// Route::get('/v1/book/update_partner_master_id_in_iranketab/{limit}', [ChangeDataController::class, 'update_partner_master_id_in_iranketab']);
// Route::get('/dossier/{bookId}', [BookController::class, 'dossier']);
// Route::get('/consensus_similar_books_by_iranketab_entitle/{limit}', [ChangeDataController::class, 'consensus_similar_books_by_iranketab_entitle']);
// Route::get('/consensus_similar_books_by_iranketab_parentId/{limit}', [ChangeDataController::class, 'consensus_similar_books_by_iranketab_parentId']);
Route::get('/merge_parentid_tempparentid/{limit}', [ChangeDataController::class, 'merge_parentid_tempparentid']);
Route::get('/merge_parentid_tempparentid_desc/{limit}', [ChangeDataController::class, 'merge_parentid_tempparentid_desc']);
// Route::get('/update_tempparent_to_other_fields/{limit}', [ChangeDataController::class, 'update_tempparent_to_other_fields']);
// Route::get('/update_tempparent_to_other_fields_desc/{limit}', [ChangeDataController::class, 'update_tempparent_to_other_fields_desc']);
// Route::get('/check_old_xparent/{limit}', [ChangeDataController::class, 'check_old_xparent']);
// Route::get('/check_old_xparent/{from}/{limit}', [ChangeDataController::class, 'check_old_xparent']);
// Route::get('/check_old_xparent2/{from}/{limit}', [ChangeDataController::class, 'check_old_xparent2']);
Route::get('/test_insta_api', [InstagramController::class, 'test']);
Route::get('/publisher_list', [CrawlerKetabirController::class, 'publisher_list']);
Route::get('/export/{publisherId}/{limit}', [BookController::class, 'exportExcelBookFindByPublisherWeb']);



