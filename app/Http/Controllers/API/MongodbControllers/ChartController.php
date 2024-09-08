<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrDaily;
use App\Models\MongoDBModels\BPA_Yearly;
use App\Models\MongoDBModels\BTB_Yearly;
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
        $startYear = ( isset($request['startYear']) and !empty($request['startYear']) ) ? intval($request->input('startYear')): $year-10;
        $endYear = ( isset($request['endYear']) and !empty($request['endYear']) ) ? intval($request->input('endYear')): $year;
        $topYear = ( isset($request['topYear']) and !empty($request['topYear']) ) ? intval($request->input('topYear')): $year-10;
        $dataForRangeCount = [];
        $dataForRangePrice = [];
        $dataForRangeCirculation = [];
        $dataForRangeAverage = [];
        $dataForRangePage = [];
        $dataForRangeParagraph = [];
        $sumCountRange = 0;
        $sumPriceRange = 0;
        $sumPageRange = 0;
        $sumCirculationRange = 0;
        $sumParagraphRange = 0;
        $sumAverageRange = 0;
        $countForAverage = 0 ;
        $dataForCreatorPrice = [];
        $dataForCreatorCirculation = [];
        $dataForPublisherPrice = [];
        $dataForPublisherCirculation = [];
        $allTimesAverage = 0;
        $allTimesPrice = 0 ;
        $allTimesCount = 0 ;
        $allTimesCirculation = 0;
        $allTimesPage = 0;
        $allTimesParagraph = 0;

        $allTimesAverageData = BPA_Yearly::all();
        foreach ($allTimesAverageData as $item){
            $allTimesAverage += (int)$item->average;
        }
        $allTimesPage += BTPa_Yearly::sum('total_pages');
        $allTimesPrice += BTP_Yearly::sum('price');
        $allTimesCount += BTC_Yearly::sum('count');
        $allTimesCirculation += BTCi_Yearly::sum('circulation');
        $allTimesParagraph += BTB_Yearly::sum('paragraph');

        // Fetch or compute cache values
//        $dataForTenPastDayBookInserted = $this->getLastTenDayBooks();

        $dfp_circulation =TCP_Yearly::where('year', $topYear)->first();
        foreach ($dfp_circulation->publishers as $item){
            $dataForPublisherCirculation['label'][] = $item['publisher_name'];
            $dataForPublisherCirculation['value'][] = $item['total_page'];
        }

        $dfp_price   = TPP_Yearly::where('year' , $topYear)->first();
        foreach ($dfp_price->publishers as $item){
            $dataForPublisherPrice ['label'] [] = $item['publisher_name'];
            $dataForPublisherPrice['value'] [] = $item['total_price'];
        }

        $dfc_circulation = TCC_Yearly::where('year' , $topYear)->first();
        foreach ($dfc_circulation->creators as $item){
            $dataForCreatorCirculation['label'][] = $item['creator_name'];
            $dataForCreatorCirculation['value'][] = $item['total_page'];
        }

        $dfc_price = TPC_Yearly::where('year' , $topYear)->first();
        foreach ($dfc_price->creators  as $item){
            $dataForCreatorPrice['label'] [] = $item['creator_name'];
            $dataForCreatorPrice['value'] [] = $item['total_price'];
        }

        $dataRangeParagraph = BTB_Yearly::where('year', '<=' , $endYear)->where('year','>=' , $startYear)->get();
        foreach ($dataRangeParagraph as $item){
            $dataForRangeParagraph['label'] [] = $item->year;
            $dataForRangeParagraph['value'] [] = round($item->paragraph);
            if ($item->paragraph != null){
                $sumParagraphRange += $item->paragraph;
            }
        }
        $dataRangePage = BTPa_Yearly::where('year', '<=' , $endYear)->where('year','>=' , $startYear)->get();
        foreach ($dataRangePage as $item) {
            $dataForRangePage ['label'] [] = $item->year;
            $dataForRangePage ['value'] [] = $item->total_pages;
            if ($item->total_pages != null){
                $sumPageRange += $item->total_pages;
            }
        }

        $dateRangeCount = BTC_Yearly::where('year', '<=' , $endYear)->where('year','>=' , $startYear)->get();
        foreach ($dateRangeCount as $item) {
            $dataForRangeCount ['label'] [] = $item->year;
            $dataForRangeCount ['value'] [] = $item->count;
            if ($item->count != null){
                $sumCountRange += $item->count;
            }
        }

        $dateRangePrice = BTP_Yearly::where('year' , '<=' , $endYear)->where('year', '>=' ,$startYear)->get();
        foreach ($dateRangePrice as $item){
            $dataForRangePrice ['label'] [] =$item->year;
            $dataForRangePrice ['value'] [] =$item->price;
                if($item->price != null){
                    $sumPriceRange += $item->price;
                }
        }

        $dateRangeCirculation = BTCi_Yearly::where('year' , '<=' , $endYear)->where('year' , '>=' ,$startYear)->get();
        foreach ($dateRangeCirculation as $item){
            $dataForRangeCirculation ['label'] [] = $item->year;
            $dataForRangeCirculation ['value'] [] = $item->circulation;
            if ($item->circulation != null){
                $sumCirculationRange += $item->circulation;
            }
        }

        $dateRangeAverage = BPA_Yearly::where('year','<=' , $endYear)->where('year' , '>=' , $startYear)->get();
        foreach ($dateRangeAverage as $item){
            $dataForRangeAverage ['label'] [] =$item->year;
            $dataForRangeAverage ['value'] [] =round($item->average);
            if ($item->average != null){
                $sumAverageRange += (int)$item->average;
                $countForAverage++;
            }
        }

        if ($sumAverageRange != 0) {
            $sumAverageRange = round($sumAverageRange / $countForAverage);
        }

        $firstBox = [
            [
                'title_fa' => "مجموع تعداد کتاب ها از ابتدا تا کنون",
                'title_en' => 'all_times_count',
                'value' => $allTimesCount
            ],
            [
                'title_fa' => "مجموع تیراژ از ابتدا تا کنون",
                'title_en' => 'all_times_circulation',
                'value' => $allTimesCirculation
            ],
            [
                'title_fa' => "مجموع صفحات چاپ شده از ابتدا تا کنون",
                'title_en' => 'all_times_page',
                'value' => $allTimesPage
            ],
            [
                'title_fa' => "جمع مالی کتاب ها از ابتدا تا کنون",
                'title_en' => 'all_times_price',
                'value' => $allTimesPrice
            ],
            [
                'title_fa' => "میانگین قیمت کتاب از ابتدا تا کنون",
                'title_en' => 'all_times_average',
                'value' => round($allTimesAverage)
            ],
            [
                'title_fa' => "مجموع بند کاغذ استفاده شده از ابتدا تا کنون",
                'title_en' => 'all_times_paragraph',
                'value' => round($allTimesParagraph)
            ],
        ];

        $secondBox = [
            [
                'title_fa' => "مجموع صفحات چاپ شده از سال $startYear تا سال $endYear",
                'title_en' => 'sum_total_pages',
                'value' => $sumPageRange
            ],
            [
                'title_fa' => "مجموع بند کاغذ استفاده شده از سال $startYear تا سال $endYear",
                'title_en' => 'sum_paragraph-range' ,
                'value' => round($sumParagraphRange)
            ],
            [
                'title_fa' => "مجموع تعداد کتاب از سال $startYear تا سال $endYear",
                'title_en' => 'sum_count_range',
                'value' => $sumCountRange
            ],
            [
                'title_fa' => "مجموع تیراژ از سال $startYear تا سال $endYear",
                'title_en' => 'sum_total_circulation',
                'value' => $sumCirculationRange
            ],
            [
                'title_fa' => "جمع مالی از سال $startYear تا سال $endYear",
                'title_en' => 'sum_total_price',
                'value' => $sumPriceRange
            ],
            [
                'title_fa' => "میانگین قیمت کتاب از سال $startYear تا سال $endYear",
                'title_en' => 'sum_average',
                'value' => round($sumAverageRange)
            ],
        ];
        $charts = [
//            [
//                'title_fa' => 'نمودار تعداد کتاب های 10 روز اخیر بانک جامع',
//                'title_en' => 'data_for_ten_past_new_books',
//                'data' => $dataForTenPastDayBookInserted
//            ],
            [
                'title_fa' => 'نمودار میانگین قیمت',
                'title_en' => 'data_for_average_books_price',
                'data' => $dataForRangeAverage
            ],
            [
                'title_fa' => ' نمودار بند کاغذ استفاده شده',
                'title_en' => 'data_for_paragraph_of_books',
                'data' => $dataForRangeParagraph
            ],
            [
                'title_fa' => 'نمودار مجموع تعداد کتاب',
                'title_en' => 'data_for_count_books',
                'data' => $dataForRangeCount
            ],
            [
                'title_fa' => 'نمودار جمع مالی',
                'title_en' => 'data_for_price_books',
                'data' => $dataForRangePrice
            ],
            [
                'title_fa' => 'نمودار مجموع صفحات چاپ شده',
                'title_en' => 'data_for_page_books',
                'data' => $dataForRangePage
            ],
            [
                'title_fa' => 'نمودار مجموع تیراژ',
                'title_en' => 'data_for_circulation_books',
                'data' => $dataForRangeCirculation
            ],
        ];
        $top=[
            [
                'title_fa' => 'نمودار پدیدآورندگان برتر بر حسب جمع مالی',
                'title_en' => 'data_for_creators_total_price',
                'data' => $dataForCreatorPrice
            ],
            [
                'title_fa' => 'نمودار پدیدآورندگان برتر بر حسب مجموع تیراژ',
                'title_en' => 'data_for_creators_total_circulation',
                'data' => $dataForCreatorCirculation
            ],
            [
                'title_fa' => 'نمودار انتشارات برتر بر حسب جمع مالی',
                'title_en' => 'data_for_publishers_total_price',
                'data' => $dataForPublisherPrice
            ],
            [
                'title_fa' => 'نمودار انتشارات برتر بر حسب مجموع تیراژ',
                'title_en' => 'date_for_publishers_total_circulation',
                'data' => $dataForPublisherCirculation
            ],
        ];
        $end = microtime(true);
        $elapsedTime = $end - $start;
        return response([
            'msg' => 'success',
            'data' => [
                'top_box' => $firstBox,
                'bottom_box' => $secondBox,
                'charts' => $charts,
                'top' => $top
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
        $dataParagraph = [];
        $sumPriceRange = 0;
        $sumAverageRange = 0;
        $sumCountRange = 0;
        $sumCirculationRange = 0;
        $sumParagraphRange = 0 ;
        $sumPagesRange = 0;
        $countForAverage = 0;

        $allTime = PublisherCacheData::where('publisher_id', $publisherId)->where('year', 0)->first();

        $dataTotalPrice = PublisherCacheData::where('publisher_id', $publisherId)->where('year', '<=', $endYear)->where('year', '>=', $startYear)->get();
        foreach ($dataTotalPrice as $item) {
            $dataPrice ['label'] [] = $item->year;
            $dataPrice ['value'] [0][] = $item->total_price;
            $dataPrice ['value'] [1][] = $item->first_cover_total_price;
            if ($item->total_price != null) {
                $sumPriceRange += $item->total_price;
            }

            if ($item->total_price != 0) {
                $dataAverage ['label'] [] = $item->year;
                $dataAverage ['value'] [0][] = round($item->average);
                $dataAverage ['value'] [1][] =round( $item->first_cover_average);
                if ($item->average != null) {
                    $sumAverageRange += $item->average;
                    $countForAverage++;
                }
            }


            $dataCount ['label'] [] = $item->year;
            $dataCount ['value'] [0][] = $item->count;
            $dataCount ['value'] [1][] = $item->first_cover_count;
            if ($item->count != null) {
                $sumCountRange += $item->count;
            }

            $dataParagraph['label'] [] = $item->year;
            $dataParagraph['value'][0][] = round($item->paragraph);
            if ($item->paragraph != null){
                $sumParagraphRange += $item->paragraph;
            }

            $dataCirculation['label'] [] = $item->year;
            $dataCirculation['value'] [0][]  = $item->total_circulation;
            $dataCirculation['value'] [1][] = $item->first_cover_total_circulation;
            if ($item->total_circulation != null) {
                $sumCirculationRange += $item->total_circulation;
            }


            $dataPages ['label'] [] = $item->year;
            $dataPages ['value'] [0][] = $item->total_pages;
            $dataPages ['value'] [1][] = $item->first_cover_total_pages;
            if ($item->total_pages != null) {
                $sumPagesRange = +$item->total_pages;
            }
        }
        if ($sumAverageRange != 0) {
            $sumAverageRange = round($sumAverageRange / $countForAverage);
        }

        $firstBox = [
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
                'title_fa' => 'مجموع بند کاغذ استفاده شده از ابتدا تا کنون',
                'title_en' => 'total_paragraph_all_time',
                'value' => round($allTime->paragraph)
            ]
        ];

        $secondBox = [
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
            ],
            [
                'title_fa' => "مجموع بند کاغذ استفاده شده از سال $startYear تا سال $endYear",
                'title_en' => 'sum_paragraph' ,
                'value' => round($sumParagraphRange)
            ]
        ];

        $charts = [
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                'title_fa' => 'نمودار مجموع صفحات',
                'title_en' => 'data_total_pages_range',
                'data' => $dataPages,
            ],
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                'title_fa' =>'نمودار مجموع تیراژ',
                'title_en' => 'data_total_circulation_range',
                'data' => $dataCirculation
            ],
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                'title_fa'=> 'نمودار جمع مالی' ,
                'title_en' => 'data_total_price_range',
                'data' => $dataPrice
            ],
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                'title_fa'=> 'نمودار تعداد کتاب',
                'title_en' => 'data_total_count_books_range',
                'data' => $dataCount,
            ],
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                'title_fa'=> 'نمودار میانگین قیمت',
                'title_en' => 'data_average_price_range',
                'data' => $dataAverage
            ],
            [
                'stackLabels' => ['مقادیرکلی'],
                'title_fa' => 'نمودار بند کاغذ استفاده شده',
                'title_en' => 'data_paragraph_range',
                'data' => $dataParagraph
            ]
        ];
        $end = microtime(true);
        $elapsedTime = $end - $start;
        return response([
            'msg' => 'success',
            'data' => [
                'top_box' => $firstBox ,
                'bottom_box' => $secondBox,
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
        $dataParagraph = [];
        $sumPriceRange = 0;
        $sumAverageRange = 0;
        $sumCountRange = 0;
        $sumCirculationRange = 0;
        $sumPagesRange = 0;
        $sumParagraphRange = 0;
        $countForAverage = 0;

        $allTime = CreatorCacheData::where('creator_id', $creatorId)->where('year', 0)->first();

        $dataTotalPrice = CreatorCacheData::where('creator_id', $creatorId)->where('year', '<=', $endYear)->where('year', '>=', $startYear)->get();
        foreach ($dataTotalPrice as $item) {
            $dataPrice ['label'] [] = $item->year;
            $dataPrice ['value'] [0][] = $item->total_price;
            $dataPrice ['value'] [1][] = $item->first_cover_total_price ;
            if ($item->total_price != null) {
                $sumPriceRange += $item->total_price;
            }

            if ($item->total_price != 0) {
                $dataAverage ['label'] [] = $item->year;
                $dataAverage ['value'] [0][] = round($item->average);
                $dataAverage ['value'] [1][] = round($item->first_cover_average);
                if ($item->average != null) {
                    $sumAverageRange += $item->average;
                    $countForAverage++;
                }
            }


            $dataCount ['label'] [] = $item->year;
            $dataCount ['value'] [0][] = $item->count;
            $dataCount ['value'] [1][] = $item->first_cover_count;
            if ($item->count != null) {
                $sumCountRange += $item->count;
            }

            $dataParagraph['label'] [] = $item->year;
            $dataParagraph['value'][0][] = round($item->paragraph);
            if ($item->paragraph != null){
                $sumParagraphRange+=$item->paragraph;
            }

            $dataCirculation['label'] [] = $item->year;
            $dataCirculation['value'] [0][] = $item->total_circulation;
            $dataCirculation['value'] [1][] = $item->first_cover_total_circulation;
            if ($item->total_circulation != null) {
                $sumCirculationRange += $item->total_circulation;
            }


            $dataPages ['label'] [] = $item->year;
            $dataPages ['value'] [0][] = $item->total_pages;
            $dataPages ['value'] [1][] = $item->first_cover_total_pages;
            if ($item->total_pages != null) {
                $sumPagesRange = +$item->total_pages;
            }
        }
        if ($sumAverageRange != 0) {
            $sumAverageRange = round($sumAverageRange / $countForAverage);
        }

        $firstBox = [
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
                'title_fa' => 'مجموع بند کاغذ استفاده شده از ابتدا تا کنون',
                'title_en' => 'total_paragraph_all_time',
                'value' => round($allTime->paragraph)
            ]
        ];

        $secondBox = [
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
            ],
            [
                'title_fa' => "مجموع بند کاغذ استفاده شده از سال $startYear تا سال $endYear",
                'title_en' => 'sum_paragraph' ,
                'value' => round($sumParagraphRange)
            ]
        ];

        $charts = [
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                'title_fa' => 'نمودار مجموع صفحات',
                'title_en' => 'data_total_pages_range',
                'data' => $dataPages,
            ],
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                'title_fa' =>'نمودار مجموع تیراژ',
                'title_en' => 'data_total_circulation_range',
                'data' => $dataCirculation
            ],
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                'title_fa'=> 'نمودار جمع مالی' ,
                'title_en' => 'data_total_price_range',
                'data' => $dataPrice
            ],
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                'title_fa'=> 'نمودار تعداد کتاب',
                'title_en' => 'data_total_count_books_range',
                'data' => $dataCount,
            ],
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                'title_fa'=> 'نمودار میانگین قیمت',
                'title_en' => 'data_average_price_range',
                'data' => $dataAverage
            ],
            [
                'stackLabels' => ['مقادیرکلی'],
                'title_fa' => 'نمودار بند کاغذ استفاده شده',
                'title_en' => 'data_paragraph_range',
                'data' => $dataParagraph
            ]
        ];
        $end = microtime(true);
        $elapsedTime = $end - $start;
        return response([
            'msg' => 'success',
            'data' => [
                'top_box' => $firstBox ,
                'bottom_box' => $secondBox,
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
