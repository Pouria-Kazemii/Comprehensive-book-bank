<?php

// use App\Http\Controllers\API\BookController;
use App\Http\Controllers\API\InstagramController;
use App\Http\Controllers\API\CrawlerKetabirController;
use App\Http\Controllers\API\ImportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChangeDataController;
use App\Http\Controllers\CronjobController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\TestController;

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
Route::get('/consensus_similar_books_by_iranketab_parentId_new/{limit}', [ChangeDataController::class, 'consensus_similar_books_by_iranketab_parentId_new']);
Route::get('/merge_parentid_tempparentid/{limit}', [ChangeDataController::class, 'merge_parentid_tempparentid']);
Route::get('/merge_parentid_tempparentid_desc/{limit}', [ChangeDataController::class, 'merge_parentid_tempparentid_desc']);
// Route::get('/update_tempparent_to_other_fields/{limit}', [ChangeDataController::class, 'update_tempparent_to_other_fields']);
// Route::get('/update_tempparent_to_other_fields_desc/{limit}', [ChangeDataController::class, 'update_tempparent_to_other_fields_desc']);
// Route::get('/check_old_xparent/{limit}', [ChangeDataController::class, 'check_old_xparent']);
// Route::get('/check_old_xparent/{from}/{limit}', [ChangeDataController::class, 'check_old_xparent']);
// Route::get('/check_old_xparent2/{from}/{limit}', [ChangeDataController::class, 'check_old_xparent2']);
Route::get('/test_insta_api', [InstagramController::class, 'test']);
Route::get('/publisher_list', [CrawlerKetabirController::class, 'publisher_list']);
// Route::get('/export/{publisherId}/{limit}', [BookController::class, 'exportExcelBookFindByPublisherWeb']);
Route::get('correct_isbns', [CronjobController::class, 'correct_isbns']);
// Route::get('correct_isbns_with_chunk', [CronjobController::class, 'correct_isbns_with_chunk']);
Route::get('fill_circulation_temp_table/{limit}', [CronjobController::class, 'fill_circulation_temp_table']);
Route::get('detail_with_crawler_info/{isbn}', [BookController::class, 'detailWithCrawlerInfo']);
Route::get('exportExcelParentBook/{startDate}/{endDate}/{dio}', [ExcelController::class, 'exportExcelParentBook']);
Route::get('exportExcelTopPublisher/{startDate}/{endDate}/{dio}/{limit}', [ExcelController::class, 'exportExcelTopPublisher']);
Route::get('exportExcelTopAuthor/{startDate}/{endDate}/{dio}/{limit}', [ExcelController::class, 'exportExcelTopAuthor']);
Route::get('exportExcelContradictionsFidibo/{status}', [ExcelController::class, 'exportExcelContradictionsFidibo']);
Route::get('exportExcelContradictionsTaaghche/{status}', [ExcelController::class, 'exportExcelContradictionsTaaghche']);
//digi
Route::get('exportExcelContradictionsDigi/{status}', [ExcelController::class, 'exportExcelContradictionsDigi']);
Route::get('exportExcelWebsiteBookLinkDefectsCheckResultDigi/{excel_id}/{excel_name}', [ExcelController::class, 'exportExcelWebsiteBookLinkDefectsCheckResultDigi']);
Route::get('importDigiExcel/{excel_type}/{excel_name}', [ImportController::class, 'importDigiExcel']);

Route::get('exportExcelContradictionsIranketab/{status}', [ExcelController::class, 'exportExcelContradictionsIranketab']);
Route::get('exportExcelContradictions30book/{status}', [ExcelController::class, 'exportExcelContradictions30book']);
Route::get('exportExcelContradictionsShahreKetabOnline/{status}', [ExcelController::class, 'exportExcelContradictionsShahreKetabOnline']);
Route::get('test_maja_api', [TestController::class, 'test_majma_api']);
Route::get('test_get_books_majma/{from_date}/{to_date}/{from}/{result_count}', [TestController::class, 'test_get_books_majma']);
Route::get('test_get_books_majma_second/{from_date}/{to_date}/{from}/{result_count}', [TestController::class, 'test_get_books_majma_second']);
Route::get('test_get_book_id_majma/{book_id}', [TestController::class, 'test_get_book_id_majma']);
Route::get('test_get_publishers_majma/{from}/{result_count}', [TestController::class, 'test_get_publishers_majma']);
Route::get('test_get_publisher_id_majma/{publisher_id}', [TestController::class, 'test_get_publisher_id_majma']);
Route::get('test_get_authors_majma/{from}/{result_count}', [TestController::class, 'test_get_authors_majma']);
Route::get('test_get_iranketab', [TestController::class, 'test_get_iranketab']);
Route::get('exportExcelContradictionsBarkhatBook/{status}/{excel_name}', [ExcelController::class, 'exportExcelContradictionsBarkhatBook']);
Route::get('exportExcelContradictionsKetabejam/{excel_type}/{status}/{excel_name}/{saveInWebsiteBooklinksDefects}', [ExcelController::class, 'exportExcelContradictionsKetabejam']);
Route::get('exportExcelContradictionsGisoom/{excel_type}/{status}/{excel_name}/{saveInWebsiteBooklinksDefects}', [ExcelController::class, 'exportExcelContradictionsGisoom']);
Route::get('NewBookEveryYearExport/{yearStart}/{monthStart}/{yearEnd}/{monthEnd}', [ExcelController::class, 'NewBookEveryYearExport']);
Route::get('/test_mongo/{year}/{skip}/{limit}' , [\App\Http\Controllers\API\MongodbControllers\BookController::class , 'getTotal']);
Route::get('/test/xdiocode' , [TestController::class , 'dio']);
//MongoDB Excel Download
Route::get('/exportExcelMainPageCharts/{startYear}/{endYear}/{topYear}', [ExcelController::class ,  'exportExcelWithCharts'] );
Route::get('/exportExcelPartner/{partnerId}/{startYear}/{endYear}' , [ExcelController::class , 'exportExcelPartner']);
Route::post('/exportExcelAdvanceSearch' , [ExcelController::class ,'exportExcelAdvanceSearch' ]);
Route::get('/exportExcelBookSearch/{textSearch}/{isbn}' , [ExcelController::class , 'exportExcelBookSearch']);
Route::get('/exportExcelPublisherBooks/{publisherId}',[ExcelController::class , 'exportExcelPublisherBooks']);
Route::get('/exportExcelCreatorBooks/{creatorId}' , [ExcelController::class , 'exportExcelCreatorBooks']);
Route::get('/exportExcelSubjectBooks/{subjectId}' , [ExcelController::class , 'exportExcelSubjectBooks']);
Route::get('/exportExcelDioCodeBooks/{diocode}/{startYear}/{endYear}/{translate}/{authorship}' , [ExcelController::class , 'exportExcelDioCode']);
Route::get('/exportExcelPublisherBooksWithYears/{publisherId}/{startYear}/{endYear}' , [ExcelController::class ,'exportExcelPublisherWithYear' ]);
Route::get('/exportExcelPublisherSubject/{publisherId}/{subjectTitle}/{startYear}/{endYear}' , [ExcelController::class , 'exportExcelPublisherSubject']);
Route::get('/exportExcelSubjectBooks/{subjectTitle}/{startYear}/{endYear}/{translate}/{authorship}' , [ExcelController::class , 'exportExcelSubjectBooksWithYear']);
Route::get('/exportExcelCreatorSubject/{creatorId}/{subjectTitle}/{startYear}/{endYear}',[ExcelController::class , 'exportExcelCreatorSubject']);
