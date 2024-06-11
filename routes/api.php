<?php

use App\Http\Controllers\API\MongodbControllers\BookCheckController;
use App\Http\Controllers\API\MongodbControllers\BookController;
use App\Http\Controllers\API\MongodbControllers\CreatorController;
use App\Http\Controllers\API\MongodbControllers\PublisherController;
use App\Http\Controllers\API\MongodbControllers\ReportController;
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
//SQL User
Route::post('/v1/user/login', 'API\UserController@login');
Route::post('/v1/user/auth', 'API\UserController@authenticate');

//MongoDB User
Route::post('/v2/user/login', 'API\UserController@login');
Route::post('/v2/user/auth', 'API\UserController@authenticate');


//Route::get('/v1/book/find', 'API\BookController@find');
//Route::get('/v1/book/check', 'API\BookController@checkBookK24');

//SQL Check
Route::get('/v1/book/check', 'API\BookCheckController@check');
Route::post('/v1/book/check_ketabir_ershad', 'API\BookCheckController@check_ketabir_and_ershad');
Route::get('/v1/book/exist', 'API\BookCheckController@exist');
Route::put('/v1/book/crawler-ketabir-with-circulation/{id}', 'API\CrawlerKetabirController@crawler_ketabir_with_circulation');

//MongoDB Check
//Route::get('/v2/book/check', [BookCheckController::class,'check']);//TODO must implement later
/*Route::post('/v2/book/check_ketabir_ershad', [BookCheckController::class,'check_ketabir_and_ershad']);//TODO must implement later*/
Route::get('/v2/book/exist', [BookCheckController::class,'exist']);
//Route::put('/v2/book/crawler-ketabir-with-circulation/{id}', 'API\CrawlerKetabirController@crawler_ketabir_with_circulation');//TODO must implement later



//Route::group
//(
//    ['middleware' => ['jwt.auth']],
//    function()
//    {
//        Route::post('/v1/book/find', 'API\BookController@find');
//        Route::post('/v1/', 'API\BookController@detail');
//    }
//);
Route::group(['middleware' => ['ChToken']], function () {

    //SQL Webs
    Route::post('/web/v1/publisher/detail', 'API\PublisherController@detail');
    Route::post('/web/v1/creator/detail', 'API\CreatorController@detail');
    Route::post('/web/v1/book/find', 'API\BookController@find');
    Route::post('/web/v1/book/detail', 'API\BookController@detail');

    //MongoDB Webs
    Route::post('/web/v2/publisher/detail', [PublisherController::class,'detail']);
    Route::post('/web/v2/creator/detail', [CreatorController::class,'detail']);
    Route::post('/web/v2/book/find', [BookController::class,'find']);
    Route::post('/web/v2/book/detail', [BookController::class,'detail']);
});

//Route::group(['middleware' => ['jwt.verify']], function () {

    //SQL User
    Route::post('/v1/user/find', 'API\UserController@find');
    Route::post('/v1/user/save', 'API\UserController@store');
    Route::post('/v1/user/edit/{id}', 'API\UserController@update');
    Route::get('/v1/user/info/{id}', 'API\UserController@info');


    //MongoDB User
    Route::post('/v2/user/find', 'API\UserController@find');
    Route::post('/v2/user/save', 'API\UserController@store');
    Route::post('/v2/user/edit/{id}', 'API\UserController@update');
    Route::get('/v2/user/info/{id}', 'API\UserController@info');



    Route::get('user', 'API\UserController@getAuthenticatedUser');



    //SQL Books
    Route::post('/v1/book/save', 'API\BookController@store');
    Route::post('/v1/book/update/{id}', 'API\BookController@update');
    Route::post('/v1/book/find', 'API\BookController@find');
    Route::post('/v1/book/find-isbn', 'API\BookController@findIsbn');
    Route::post('/v1/book/find/publisher', 'API\BookController@findByPublisher');
    Route::post('/v1/export-excel-book/find/publisher', 'API\BookController@exportExcelBookFindByPublisher');
    Route::post('/v1/book/find/creator', 'API\BookController@findByCreator');
    Route::post('/v1/export-excel-book/find/creator', 'API\BookController@exportExcelBookFindByCreator');
    Route::post('/v1/book/find/ver', 'API\BookController@findByVer');
    Route::post('/v1/book/find/subject', 'API\BookController@findBySubject');
    Route::post('/v1/book/detail', 'API\BookController@detail');
    Route::get('/v1/book/detail_with_crawler_info/{isbn}', 'API\BookController@detailWithCrawlerInfo');
    Route::get('/v1/book/info/{id}', 'API\BookController@info');
    Route::post('/v1/book/dossier', 'API\BookController@dossier');
    Route::post('/v1/book/market', 'API\BookController@market');
    Route::post('/v1/book/search/dio', 'API\BookController@searchDio');
    Route::post('/v1/book/find/creator-publisher', 'API\BookController@findByCreatorOfPublisher');
    Route::post('/v1/book/find/shared-creators', 'API\BookController@findBySharedCreators');
    Route::post('/v1/book/advance-search', 'API\BookController@advanceSearch');
    Route::post('/v1/book/merge-book-dossier', 'API\BookController@mergeBookDossier');
    Route::post('/v1/book/separate-from-book-dossier', 'API\BookController@separateFromBookDossier');
    Route::post('/v1/book/annual-activity-circulation', 'API\BookController@annualActivityByCirculation');//not exist
    Route::post('/v1/book/annual-activity-first-edition-circulation', 'API\BookController@annualActivityFirstEditionByCirculation');//not exist
    Route::post('/v1/book/best-selling', 'API\BookController@bestSelling');//not exist
    Route::post('/v1/book/best-selling-by-year', 'API\BookController@bestSellingByYear');//not exist
    Route::post('/v1/book/find-best-selling-by-publisher', 'API\BookController@findBestSellingBypublisher');//not exist


    //MongoDB Books
    Route::post('/v2/book/find' , [BookController::class , 'find']);
    Route::post('/v2/book/find-isbn', [BookController::class ,'findIsbn']);
    Route::post('/v2/book/find/publisher', [BookController::class , 'findByPublisher']);
    Route::post('/v2/export-excel-book/find/publisher', [ BookController::class, 'exportExcelBookFindByPublisher']);
    Route::post('/v2/book/find/creator', [BookController::class ,'findByCreator']);
    Route::post('/v2/export-excel-book/find/creator', [BookController::class ,'exportExcelBookFindByCreator']);
    Route::post('/v2/book/find/ver', [BookController::class ,'findByVer']);
    Route::post('/v2/book/find/subject', [BookController::class,'findBySubject']);
    Route::post('/v2/book/detail', [BookController::class,'detail']);
    Route::get('/v2/book/info/{id}', [BookController::class,'info']);
    Route::post('/v2/book/search/dio', [BookController::class,'searchDio']);
    Route::post('/v2/book/find/creator-publisher', [BookController::class,'findByCreatorOfPublisher']);
    Route::post('/v2/book/find/shared-creators', [BookController::class,'findBySharedCreators']);
    Route::post('/v2/book/advance-search', [BookController::class,'advanceSearch']);
    //TODO : Must implement later
    Route::post('/v2/book/merge-book-dossier', [BookController::class,'mergeBookDossier']);
    Route::post('/v2/book/save', 'API\BookController@store');
    Route::post('/v2/book/update/{id}', 'API\BookController@update');
    Route::get('/v2/book/detail_with_crawler_info/{isbn}', 'API\BookController@detailWithCrawlerInfo');
    Route::post('/v2/book/dossier', 'API\BookController@dossier');

    //SQL Creators
    Route::post('/v1/creator/find', 'API\CreatorController@find');
    Route::post('/v1/creator/find/subject', 'API\CreatorController@findBySubject');
    Route::post('/v1/creator/find/publisher', 'API\CreatorController@findByPublisher');
    Route::post('/v1/creator/role', 'API\CreatorController@role');
    Route::post('/v1/creator/annual-activity', 'API\CreatorController@annualActivity');
    Route::post('/v1/creator/annual-activity-title', 'API\CreatorController@annualActivityByTitle');//not exist
    Route::post('/v1/creator/annual-activity-first-edition-title', 'API\CreatorController@annualActivityFirstEditionByTitle');//not exist
    Route::post('/v1/creator/annual-activity-circulation', 'API\CreatorController@annualActivityByCirculation');//not exist
    Route::post('/v1/creator/annual-activity-first-edition-circulation', 'API\CreatorController@annualActivityFirstEditionByCirculation');//not exist
    Route::post('/v1/creator/detail', 'API\CreatorController@detail');
    Route::post('/v1/creator/search', 'API\CreatorController@search');
    Route::post('/v1/creator/find/creator', 'API\CreatorController@findByCreator');
    Route::post('/v1/creator/most-active-by-year', 'API\CreatorController@mostActiveByYear');//not exist
    Route::post('/v1/creator/most-active', 'API\CreatorController@mostActive');//not exist
    Route::post('/v1/creator/most-active-by-first-edition-books', 'API\CreatorController@mostActiveByFirstEditionBooks');//not exist
    Route::post('/v1/creator/most-active-by-first-edition-books-by-year', 'API\CreatorController@mostActiveByFirstEditionBooksByYear');//not exist

    //MongoDB Creators
    Route::post('/v2/creator/find', [CreatorController::class,'find']);
    Route::post('/v2/creator/find/subject', [CreatorController::class ,'findBySubject']);
    Route::post('/v2/creator/find/publisher', [CreatorController::class , 'findByPublisher']);
    Route::post('/v2/creator/find/creator', [CreatorController::class,'findByCreator']);
    Route::post('/v2/creator/role', 'API\CreatorController@role');
    Route::post('/v2/creator/search', [CreatorController::class ,'search']);
    Route::post('/v2/creator/detail', [CreatorController::class,'detail']);
    Route::post('/v2/creator/annual-activity', [CreatorController::class,'annualActivity']);

    //SQL Subjects
    Route::post('/v1/subject/find', 'API\SubjectController@find');
    Route::post('/v1/subject/search', 'API\SubjectController@search');
    Route::post('/v1/subject/searchForSelectComponent', 'API\SubjectController@searchForSelectComponent');

    //MongoDB Subjects
    //TODO : using SQL until make collection for subject
    Route::post('/v2/subject/find', 'API\SubjectController@find');
    Route::post('/v2/subject/search', 'API\SubjectController@search');
    Route::post('/v2/subject/searchForSelectComponent', 'API\SubjectController@searchForSelectComponent');

    //SQL Publisher
    Route::post('/v1/publisher/search', 'API\PublisherController@search');
    Route::post('/v1/publisher/find', 'API\PublisherController@find');
    Route::post('/v1/publisher/find/subject', 'API\PublisherController@findBySubject');
    Route::post('/v1/publisher/find/creator', 'API\PublisherController@findByCreator');
    Route::post('/v1/publisher/detail', 'API\PublisherController@detail');
    Route::post('/v1/publisher/annual-activity-title', 'API\PublisherController@annualActivityByTitle');
    Route::post('/v1/publisher/annual-activity-first-edition-title', 'API\PublisherController@annualActivityFirstEditionByTitle');//not exist
    Route::post('/v1/publisher/annual-activity-circulation', 'API\PublisherController@annualActivityByCirculation');
    Route::post('/v1/publisher/annual-activity-first-edition-circulation', 'API\PublisherController@annualActivityFirstEditionByCirculation');//not exist
    Route::post('/v1/publisher/translate-authorship', 'API\PublisherController@translateAuthorship');
    Route::post('/v1/publisher/statistic-subject', 'API\PublisherController@statisticSubject');
    Route::post('/v1/publisher/publisher-role', 'API\PublisherController@publisherRole');
    Route::post('/v1/publisher/most-active-by-year', 'API\PublisherController@mostActiveByYear');//not exist
    Route::post('/v1/publisher/most-active', 'API\PublisherController@mostActive');//not exist
    Route::post('/v1/publisher/most-active-by-first-edition-books', 'API\PublisherController@mostActiveByFirstEditionBooks');//not exist
    Route::post('/v1/publisher/most-active-by-first-edition-books-by-year', 'API\PublisherController@mostActiveByFirstEditionBooksByYear');//not exist

    //MongoDB Publisher
    Route::post('/v2/publisher/find', [PublisherController::class,'find']);
    Route::post('/v2/publisher/find/subject', [PublisherController::class , 'findBySubject']);
    Route::post('/v2/publisher/find/creator',[PublisherController::class ,'findByCreator']);
    Route::post('/v2/publisher/search', [PublisherController::class ,'search']);
    Route::post('/v2/publisher/detail', [PublisherController::class,'detail']);
    Route::post('/v2/publisher/annual-activity-title', [PublisherController::class,'annualActivityByTitle']);
    Route::post('/v2/publisher/annual-activity-circulation', [PublisherController::class,'annualActivityByCirculation']);
    Route::post('/v2/publisher/translate-authorship', [PublisherController::class,'translateAuthorship']);
    Route::post('/v2/publisher/statistic-subject', [PublisherController::class ,'statisticSubject']);
    Route::post('/v2/publisher/publisher-role', [PublisherController::class ,'publisherRole']);

    //SQL Report
    Route::post('/v1/report/publisher', 'API\ReportController@publisher');
    Route::post('/v1/report/publisher-dio', 'API\ReportController@publisherDio');
    Route::post('/v1/report/publisher-book', 'API\ReportController@publisherBook');
    Route::post('/v1/report/publisher-subject', 'API\ReportController@publisherSubject');
    Route::post('/v1/report/publisher-subject-aggregation', 'API\ReportController@publisherSubjectAggregation');
    Route::post('/v1/report/subject-aggregation', 'API\ReportController@subjectAggregation');
    Route::post('/v1/report/subject', 'API\ReportController@subject');
    Route::post('/v1/report/creator-subject', 'API\ReportController@creatorSubject');
    Route::post('/v1/report/creator-publisher', 'API\ReportController@creatorPublisher');
    Route::post('/v1/report/creator-aggregation', 'API\ReportController@creatorAggregation');
    Route::post('/v1/report/dio', 'API\ReportController@dio');

    //MongoDB Report
    Route::post('/v2/report/publisher', [ReportController::class ,'publisher']);
    Route::post('/v2/report/publisher-dio', [ReportController::class,'publisherDio']);
    Route::post('/v2/report/publisher-book', [ReportController::class ,'publisherBook']);
    Route::post('/v2/report/publisher-subject', [ReportController::class,'publisherSubject']);
    Route::post('/v2/report/publisher-subject-aggregation', [ReportController::class,'publisherSubjectAggregation']);
    Route::post('/v2/report/subject-aggregation', [ReportController::class,'subjectAggregation']);
    Route::post('/v2/report/subject', [ReportController::class ,'subject']);
    Route::post('/v2/report/creator-subject', [ReportController::class ,'creatorSubject']);
    Route::post('/v2/report/creator-publisher', [ReportController::class,'creatorPublisher']);
    Route::post('/v2/report/creator-aggregation', [ReportController::class ,'creatorAggregation']);
    Route::post('/v2/report/dio', [ReportController::class ,'dio']);

    //SQL Role , Languages , Formats and Covers
    Route::post('/v1/role/search', 'API\RoleController@search');
    Route::post('/v1/bookLanguage/list/', 'API\BookLanguageController@list');
    Route::get('/v1/bookFormat/list/', 'API\BookFormatController@list');
    Route::get('/v1/bookCover/list/', 'API\BookCoverController@list');

    //MongoDB Role , Languages , Formats and Covers (using sql tables)
    Route::post('/v2/role/search', 'API\RoleController@search');
    Route::post('/v2/bookLanguage/list/', 'API\BookLanguageController@list');
    Route::get('/v2/bookFormat/list/', 'API\BookFormatController@list');
    Route::get('/v2/bookCover/list/', 'API\BookCoverController@list');


    //TODO must implement later
    Route::post('/v1/import/importErshadBooks/', 'API\ImportController@importErshadBooks');
    Route::post('/v1/import/importUnallowableBooks/', 'API\ImportController@importUnallowableBooks');

//});
