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
Route::post('/v1/user/auth', 'API\UserController@authenticate');

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

Route::group(['middleware' => ['jwt.verify']], function () {


    Route::get('user', 'API\UserController@getAuthenticatedUser');
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
    Route::post('/v1/book/annual-activity-circulation', 'API\BookController@annualActivityByCirculation');
    Route::post('/v1/book/annual-activity-first-edition-circulation', 'API\BookController@annualActivityFirstEditionByCirculation');
    Route::post('/v1/book/best-selling', 'API\BookController@bestSelling');
    Route::post('/v1/book/best-selling-by-year', 'API\BookController@bestSellingByYear');
    Route::post('/v1/book/find-best-selling-by-publisher', 'API\BookController@findBestSellingBypublisher');

    Route::post('/v1/user/find', 'API\UserController@find');
    Route::post('/v1/user/save', 'API\UserController@store');
    Route::post('/v1/user/edit/{id}', 'API\UserController@update');
    Route::get('/v1/user/info/{id}', 'API\UserController@info');

    Route::post('/v1/creator/find', 'API\CreatorController@find');
    Route::post('/v1/creator/find/subject', 'API\CreatorController@findBySubject');
    Route::post('/v1/creator/find/publisher', 'API\CreatorController@findByPublisher');
    Route::post('/v1/creator/role', 'API\CreatorController@role');
    Route::post('/v1/creator/annual-activity-title', 'API\CreatorController@annualActivityByTitle');
    Route::post('/v1/creator/annual-activity-first-edition-title', 'API\CreatorController@annualActivityFirstEditionByTitle');
    Route::post('/v1/creator/annual-activity-circulation', 'API\CreatorController@annualActivityByCirculation');
    Route::post('/v1/creator/annual-activity-first-edition-circulation', 'API\CreatorController@annualActivityFirstEditionByCirculation');
    Route::post('/v1/creator/detail', 'API\CreatorController@detail');
    Route::post('/v1/creator/search', 'API\CreatorController@search');
    Route::post('/v1/creator/find/creator', 'API\CreatorController@findByCreator');
    Route::post('/v1/creator/most-active-by-year', 'API\CreatorController@mostActiveByYear');
    Route::post('/v1/creator/most-active', 'API\CreatorController@mostActive');
    Route::post('/v1/creator/most-active-by-first-edition-books', 'API\CreatorController@mostActiveByFirstEditionBooks');
    Route::post('/v1/creator/most-active-by-first-edition-books-by-year', 'API\CreatorController@mostActiveByFirstEditionBooksByYear');

    Route::post('/v1/subject/find', 'API\SubjectController@find');
    Route::post('/v1/subject/search', 'API\SubjectController@search');
    Route::post('/v1/subject/searchForSelectComponent', 'API\SubjectController@searchForSelectComponent');

    Route::post('/v1/publisher/search', 'API\PublisherController@search');
    Route::post('/v1/publisher/find', 'API\PublisherController@find');
    Route::post('/v1/publisher/find/subject', 'API\PublisherController@findBySubject');
    Route::post('/v1/publisher/find/creator', 'API\PublisherController@findByCreator');
    Route::post('/v1/publisher/detail', 'API\PublisherController@detail');
    Route::post('/v1/publisher/annual-activity-title', 'API\PublisherController@annualActivityByTitle');
    Route::post('/v1/publisher/annual-activity-first-edition-title', 'API\PublisherController@annualActivityFirstEditionByTitle');
    Route::post('/v1/publisher/annual-activity-circulation', 'API\PublisherController@annualActivityByCirculation');
    Route::post('/v1/publisher/annual-activity-first-edition-circulation', 'API\PublisherController@annualActivityFirstEditionByCirculation');
    Route::post('/v1/publisher/translate-authorship', 'API\PublisherController@translateAuthorship');
    Route::post('/v1/publisher/statistic-subject', 'API\PublisherController@statisticSubject');
    Route::post('/v1/publisher/publisher-role', 'API\PublisherController@publisherRole');
    Route::post('/v1/publisher/most-active-by-year', 'API\PublisherController@mostActiveByYear');
    Route::post('/v1/publisher/most-active', 'API\PublisherController@mostActive');
    Route::post('/v1/publisher/most-active-by-first-edition-books', 'API\PublisherController@mostActiveByFirstEditionBooks');
    Route::post('/v1/publisher/most-active-by-first-edition-books-by-year', 'API\PublisherController@mostActiveByFirstEditionBooksByYear');


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


    Route::post('/v1/role/search', 'API\RoleController@search');
    Route::post('/v1/bookLanguage/list/', 'API\BookLanguageController@list');
    Route::get('/v1/bookFormat/list/', 'API\BookFormatController@list');
    Route::get('/v1/bookCover/list/', 'API\BookCoverController@list');
});
