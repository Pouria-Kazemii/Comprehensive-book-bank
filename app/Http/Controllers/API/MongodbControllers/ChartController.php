<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrDaily;
use App\Models\MongoDBModels\BPA_Yearly;
use App\Models\MongoDBModels\BTC_Yearly;
use App\Models\MongoDBModels\BTCi_Yearly;
use App\Models\MongoDBModels\BTP_Yearly;
use App\Models\MongoDBModels\BTPa_Yearly;
use App\Models\MongoDBModels\CreatorCacheData;
use App\Models\MongoDBModels\PublisherCacheData;
use App\Models\MongoDBModels\TCC_Yearly;
use App\Models\MongoDBModels\TCP_Yearly;
use App\Models\MongoDBModels\TPC_Yearly;
use App\Models\MongoDBModels\TPP_Yearly;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    public function index(Request $request)
    {
        $start = microtime(true);
        $year = getYearNow();
        $firstDateForPage =( isset($request['firstDateForPage']) and !empty($request['firstDateForPage']) ) ? intval($request->input('firstDateForPage')): $year-10;
        $lastDateForPage = ( isset($request['lastDateForPage']) and !empty($request['lastDateForPage']) ) ? intval($request->input('lastDateForPage')): $year ;
        $firstDateForCount = ( isset($request['firstDateForCount']) and !empty($request['firstDateForCount']) ) ? intval($request->input('firstDateForCount')): $year-10;
        $lastDateForCount = ( isset($request['lastDateForCount']) and !empty($request['lastDateForCount']) ) ? intval($request->input('lastDateForCount')): $year ;
        $firstDateForPrice = ( isset($request['firstDateForPrice']) and !empty($request['firstDateForPrice']) ) ? intval($request->input('firstDateForPrice')): $year-10;
        $lastDateForPrice = ( isset($request['lastDateForPrice']) and !empty($request['lastDateForPrice']) ) ? intval($request->input('lastDateForPrice')): $year ;
        $firstDateForCirculation = ( isset($request['firstDateForCirculation']) and !empty($request['firstDateForCirculation']) ) ? intval($request->input('firstDateForCirculation')): $year-10;
        $lastDateForCirculation = ( isset($request['lastDateForCirculation']) and !empty($request['lastDateForCirculation']) ) ? intval($request->input('lastDateForCirculation')): $year ;
        $firstDateForAverage = ( isset($request['firstDateForAverage']) and !empty($request['firstDateForAverage']) ) ? intval($request->input('firstDateForAverage')): $year-10 ;
        $lastDateForAverage = ( isset($request['lastDateForAverage']) and !empty($request['lastDateForAverage']) ) ? intval($request->input('lastDateForAverage')): $year ;
        $dateForCreators_totalPrice = ( isset($request['dfc_price']) and !empty($request['dfc_price']) ) ? intval($request->input('dfc_price')): $year ;
        $dateForCreators_totalCirculation = ( isset($request['dfc_circulation']) and !empty($request['dfc_circulation']) ) ? intval($request->input('dfc_circulation')): $year  ;
        $dateForPublishers_totalPrice = ( isset($request['dfp_price']) and !empty($request['dfp_price']) ) ? intval($request->input('dfp_price')): $year ;
        $dateForPublishers_totalCirculation = ( isset($request['dfp_circulation']) and !empty($request['dfp_circulation']) ) ? intval($request->input('dfp_circulation')): $year ;

        $dataForRangeCount = [];
        $dataForRangePrice = [];
        $dataForRangeCirculation = [];
        $dataForRangeAverage = [];
        $dataForCreatorPrice = [];
        $dataForCreatorCirculation = [];
        $dataForPublisherPrice = [];
        $dataForPublisherCirculation = [];
        $dataForRangePage = [];

        // Fetch or compute cache values
        $dataForTenPastDayBookInserted = $this->getLastTenDayBooks();

        $dfp_circulation =TCP_Yearly::where('year', $dateForPublishers_totalCirculation)->first();
        foreach ($dfp_circulation->publishers as $item){
            $dataForPublisherCirculation['label'][] = $item['publisher_name'];
            $dataForPublisherCirculation['value'][] = $item['total_page'];
        }

        $dfp_price   = TPP_Yearly::where('year' , $dateForPublishers_totalPrice)->first();
        foreach ($dfp_price->publishers as $item){
            $dataForPublisherPrice ['label'] [] = $item['publisher_name'];
            $dataForPublisherPrice['value'] [] = $item['total_price'];
        }

        $dfc_circulation = TCC_Yearly::where('year' , $dateForCreators_totalCirculation)->first();
        foreach ($dfc_circulation->creators as $item){
            $dataForCreatorCirculation['label'][] = $item['creator_name'];
            $dataForCreatorCirculation['value'][] = $item['total_page'];
        }

        $dfc_price = TPC_Yearly::where('year' , $dateForCreators_totalPrice)->first();
        foreach ($dfc_price->creators  as $item){
            $dataForCreatorPrice['label'] [] = $item['creator_name'];
            $dataForCreatorPrice['value'] [] = $item['total_price'];
        }

        $dataRangePage = BTPa_Yearly::where('year', '<=' , $lastDateForPage)->where('year','>=' , $firstDateForPage)->get();
        foreach ($dataRangePage as $item) {
            $dataForRangePage ['label'] [] = $item->year;
            $dataForRangePage ['value'] [] = $item->total_pages;
        }

        $dateRangeCount = BTC_Yearly::where('year', '<=' , $lastDateForCount)->where('year','>=' , $firstDateForCount)->get();
        foreach ($dateRangeCount as $item) {
            $dataForRangeCount ['label'] [] = $item->year;
            $dataForRangeCount ['value'] [] = $item->count;
        }

        $dateRangePrice = BTP_Yearly::where('year' , '<=' , $lastDateForPrice)->where('year', '>=' ,$firstDateForPrice)->get();
        foreach ($dateRangePrice as $item){
            $dataForRangePrice ['label'] [] =$item->year;
            $dataForRangePrice ['value'] [] =$item->price;
        }

        $dateRangeCirculation = BTCi_Yearly::where('year' , '<=' , $lastDateForCirculation)->where('year' , '>=' ,$firstDateForCirculation)->get();
        foreach ($dateRangeCirculation as $item){
            $dataForRangeCirculation ['label'] [] = $item->year;
            $dataForRangeCirculation ['value'] [] = $item->circulation;
        }

        $dateRangeAverage = BPA_Yearly::where('year','<=' , $lastDateForAverage)->where('year' , '>=' , $firstDateForAverage)->get();
        foreach ($dateRangeAverage as $item){
            $dataForRangeAverage ['label'] [] =$item->year;
            $dataForRangeAverage ['value'] [] =$item->average;
        }

        $end = microtime(true);
        $elapsedTime = $end - $start;
        return response([
            'msg' => 'success',
            'data' => [
                'data_for_ten_past_new_books' => $dataForTenPastDayBookInserted,

                'data_for_average_books_price' => $dataForRangeAverage,

                'data_for_count_books' => $dataForRangeCount,

                'data_for_price_books' => $dataForRangePrice,

                'data_for_page_books' => $dataForRangePage,

                'data_for_circulation_books' => $dataForRangeCirculation,

                'data_for_creators_total_price' => $dataForCreatorPrice,

                'data_for_creators_total_circulation' => $dataForCreatorCirculation,

                'data_for_publishers_total_price' => $dataForPublisherPrice,

                'date_for_publishers_total_circulation' => $dataForPublisherCirculation
            ],
            'status' => 200 ,
            'time' => $elapsedTime,
        ], 200);
    }

    public function publisher(Request $request , string $publisherId)
    {
        $start = microtime(true);
        $year = getYearNow();
        $startYear = ( isset($request['startYear']) and !empty($request['startYear']) ) ? intval($request->input('startYear')): $year-10;
        $endYear = ( isset($request['endYear']) and !empty($request['endYear']) ) ? intval($request->input('endYear')): $year;

        $dataPrice = [];
        $dataCirculation = [];
        $dataCount = [];
        $dataAverage = [];
        $dataPages = [];
        $sumPriceRange = 0;
        $sumAverageRange = 0;
        $sumCountRange = 0;
        $sumCirculationRange = 0;
        $sumPagesRange = 0;
        $countForAverage = 0;

        $allTime = PublisherCacheData::where('publisher_id', $publisherId)->where('year', 0)->first();

        $dataTotalPrice = PublisherCacheData::where('publisher_id', $publisherId)->where('year', '<=', $endYear)->where('year', '>=', $startYear)->get();
        foreach ($dataTotalPrice as $item) {
            $dataPrice ['label'] [] = $item->year;
            $dataPrice ['value'] [] = [$item->total_price, $item->first_cover_toal_price];
            if ($item->total_price != null) {
                $sumPriceRange += $item->total_price;
            }

            if ($item->total_price != 0) {
                $dataAverage ['label'] [] = $item->year;
                $dataAverage ['value'] [] = [$item->average, $item->first_cover_average];
                if ($item->average != null) {
                    $sumAverageRange += $item->average;
                    $countForAverage++;
                }
            }


            $dataCount ['label'] [] = $item->year;
            $dataCount ['value'] [] = [$item->count, $item->first_cover_count];
            if ($item->count != null) {
                $sumCountRange += $item->count;
            }


            $dataCirculation['label'] [] = $item->year;
            $dataCirculation['value'] [] = [$item->total_circulation, $item->first_cover_total_circulation];
            if ($item->total_circulation != null) {
                $sumCirculationRange += $item->total_circulation;
            }


            $dataPages ['label'] [] = $item->year;
            $dataPages ['value'] [] = [$item->total_pages, $item->first_cover_total_pages];
            if ($item->total_pages != null) {
                $sumPagesRange = +$item->total_pages;
            }
        }
        if ($sumAverageRange != 0) {
            $sumAverageRange = $sumAverageRange / $countForAverage;
        }
        $box = [
            [
                'title_fa' => 'مجموع صفحات چاپ شده از ابتدا تا کنون',
                'title_en' => 'total_pages-all_times',
                'value' => $allTime->total_pages,
            ],
            [
                'title_fa' => 'مجموع تیراژ از ابتدا تا کنون',
                'title_en' => 'total_circulation_all_times',
                'value' => $allTime->total_circulation,
            ],
            [
                'title_fa' => 'جمع مالی از ابتدا تا کنون',
                'title_en' => 'total_price_all_times',
                'value' => $allTime->total_price,
            ] ,
            [
                'title_fa' => 'مجموع کتاب ها از ابتدا تا کنون',
                'title_en' => 'total_count_books_all_times',
                'value' => $allTime->count,

            ],
            [
                'title_fa' => 'میانگین قیمت از ابتدا تا کنون',
                'title_en' => 'average_price_all_times',
                'value' =>$allTime->total_price != 0 ? $allTime->average : 0,

            ],
            [
                'title_fa' => "مجموع صفحات چاپ شده از سال $startYear تا سال $endYear",
                'title_en' => 'sum_total_pages_range',
                'value' => $sumPagesRange,
            ],
            [
                'title_fa' => "مجموع تیراژ از سال $startYear تا سال $endYear",
                'title_en' => 'sum_total_circulation_range',
                'value' => $sumCirculationRange,

            ],
            [
                'title_fa' => "جمع مالی از سال $startYear تا سال $endYear",
                'title_en' => 'sum_total_price_range',
                'value' => $sumPriceRange,

            ],
            [
                'title_fa' => "مجموع تعداد کتاب از سال $startYear تا سال  $endYear",
                'title_en' => 'sum_count_range',
                'value' => $sumCountRange,
            ],
            [
                'title_fa' => "میانگین قیمت از سال $startYear تا سال $endYear",
                'title_en' => 'sum_average_range',
                'value' => $sumPriceRange !=0 ? $sumAverageRange :0,
            ]
        ];

        $charts = [
            [
                'title_fa' => 'نمودار مجموع صفحات',
                'data_total_pages_range' => $dataPages,
            ],
            [
                'title_fa' =>'نمودار مجموع تیراژ',
                'data_total_circulation_range' => $dataCirculation
            ],
            [
                'title_fa'=> 'نمودار جمع مالی' ,
                'data_total_price_range' => $dataPrice
            ],
            [
                'title_fa'=> 'نمودار تعداد کتاب',
                'data_total_count_books_range' => $dataCount,
            ],
            [
                'title_fa'=> 'نمودار میانگین قیمت',
                'data_average_price_range' => $dataAverage
            ]
        ];
        $end = microtime(true);
        $elapsedTime = $end - $start;
        return response([
            'msg' => 'success',
            'data' => [
                'box' => $box ,
                'charts' => $charts
            ]
            ,
            'status' => 200,
            'time' => $elapsedTime
        ], 200);
    }

    public function creator(Request $request , string $creatorId)
    {
        $start = microtime(true);
        $year = getYearNow();
        $startYear = (isset($request['startYear']) and !empty($request['startYear'])) ? intval($request->input('startYear')) : $year - 10;
        $endYear = (isset($request['endYear']) and !empty($request['endYear'])) ? intval($request->input('endYear')) : $year;

        $dataPrice = [];
        $dataCirculation = [];
        $dataCount = [];
        $dataAverage = [];
        $dataPages = [];
        $sumPriceRange = 0;
        $sumAverageRange = 0;
        $sumCountRange = 0;
        $sumCirculationRange = 0;
        $sumPagesRange = 0;
        $countForAverage = 0;

        $allTime = CreatorCacheData::where('creator_id', $creatorId)->where('year', 0)->first();

        $dataTotalPrice = CreatorCacheData::where('creator_id', $creatorId)->where('year', '<=', $endYear)->where('year', '>=', $startYear)->get();
        foreach ($dataTotalPrice as $item) {
            $dataPrice ['label'] [] = $item->year;
            $dataPrice ['value'] [] = [$item->total_price, $item->first_cover_toal_price];
            if ($item->total_price != null) {
                $sumPriceRange += $item->total_price;
            }

            if ($item->total_price != 0) {
                $dataAverage ['label'] [] = $item->year;
                $dataAverage ['value'] [] = [$item->average, $item->first_cover_average];
                if ($item->average != null) {
                    $sumAverageRange += $item->average;
                    $countForAverage++;
                }
            }


            $dataCount ['label'] [] = $item->year;
            $dataCount ['value'] [] = [$item->count, $item->first_cover_count];
            if ($item->count != null) {
                $sumCountRange += $item->count;
            }


            $dataCirculation['label'] [] = $item->year;
            $dataCirculation['value'] [] = [$item->total_circulation, $item->first_cover_total_circulation];
            if ($item->total_circulation != null) {
                $sumCirculationRange += $item->total_circulation;
            }


            $dataPages ['label'] [] = $item->year;
            $dataPages ['value'] [] = [$item->total_pages, $item->first_cover_total_pages];
            if ($item->total_pages != null) {
                $sumPagesRange = +$item->total_pages;
            }
        }
        if ($sumAverageRange != 0) {
            $sumAverageRange = $sumAverageRange / $countForAverage;
        }
        $box = [
            [
                'title_fa' => 'مجموع صفحات چاپ شده از ابتدا تا کنون',
                'title_en' => 'total_pages-all_times',
                'value' => $allTime->total_pages,
            ],
            [
                'title_fa' => 'مجموع تیراژ از ابتدا تا کنون',
                'title_en' => 'total_circulation_all_times',
                'value' => $allTime->total_circulation,
            ],
            [
                'title_fa' => 'جمع مالی از ابتدا تا کنون',
                'title_en' => 'total_price_all_times',
                'value' => $allTime->total_price,
            ] ,
            [
                'title_fa' => 'مجموع کتاب ها از ابتدا تا کنون',
                'title_en' => 'total_count_books_all_times',
                'value' => $allTime->count,

            ],
            [
                'title_fa' => 'میانگین قیمت از ابتدا تا کنون',
                'title_en' => 'average_price_all_times',
                'value' =>$allTime->total_price != 0 ? $allTime->average : 0,

            ],
            [
                'title_fa' => "مجموع صفحات چاپ شده از سال $startYear تا سال $endYear",
                'title_en' => 'sum_total_pages_range',
                'value' => $sumPagesRange,
            ],
            [
                'title_fa' => "مجموع تیراژ از سال $startYear تا سال $endYear",
                'title_en' => 'sum_total_circulation_range',
                'value' => $sumCirculationRange,

            ],
            [
                'title_fa' => "جمع مالی از سال $startYear تا سال $endYear",
                'title_en' => 'sum_total_price_range',
                'value' => $sumPriceRange,

            ],
            [
                'title_fa' => "مجموع تعداد کتاب از سال $startYear تا سال  $endYear",
                'title_en' => 'sum_count_range',
                'value' => $sumCountRange,
            ],
            [
                'title_fa' => "میانگین قیمت از سال $startYear تا سال $endYear",
                'title_en' => 'sum_average_range',
                'value' => $sumPriceRange !=0 ? $sumAverageRange :0,
            ]
        ];

        $charts = [
            [
                'title_fa' => 'نمودار مجموع صفحات',
                'data_total_pages_range' => $dataPages,
            ],
            [
                'title_fa' =>'نمودار مجموع تیراژ',
                'data_total_circulation_range' => $dataCirculation
            ],
            [
                'title_fa'=> 'نمودار جمع مالی' ,
                'data_total_price_range' => $dataPrice
            ],
            [
                'title_fa'=> 'نمودار تعداد کتاب',
                'data_total_count_books_range' => $dataCount,
            ],
            [
                'title_fa'=> 'نمودار میانگین قیمت',
                'data_average_price_range' => $dataAverage
            ]
        ];
        $end = microtime(true);
        $elapsedTime = $end - $start;
        return response([
            'msg' => 'success',
            'data' => [
                'box' => $box ,
                'charts' => $charts
            ]
            ,
            'status' => 200,
            'time' => $elapsedTime
        ], 200);
    }


    private function getLastTenDayBooks()
    {
        $response = [];
        $data = BookIrDaily::orderBy('_id' , -1)->take(10)->get();
        foreach ($data as $value){
            $response ['label'][] = $value->date;
            $response ['value'][] = $value->count;
        }
        return $response;
    }
}
