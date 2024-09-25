<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookDioCachedData;
use App\Models\MongoDBModels\DioSubject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DioChartsController extends Controller
{
    public function index(Request $request) : JsonResponse
    {
        $start = microtime('true');
        $year = getYearNow();
        $id = (int)$request->input('id');
        $startYear = (isset($request['startYear']) and !empty($request['startYear'])) ? intval($request->input('startYear')) : $year - 10;
        $endYear = (isset($request['endYear']) and !empty($request['endYear'])) ? intval($request->input('endYear')) : $year;
        $topYear = (isset($request['topYear']) and !empty($request['topYear'])) ? intval($request->input('topYear')) : $year;

        $totalPriceChart = [];
        $totalCirculationChart = [];
        $totalCountChart = [];
        $totalPageChart = [];
        $averageChart = [];
        $paragraphChart = [];
        $tonChart = [];

        $priceBottomBox = 0;
        $circulationBottomBox = 0;
        $countBottomBox = 0;
        $tonBottomBox = 0;
        $paragraphBottomBox = 0;
        $pagesBottomBox = 0;

        $totalPriceTopDonate = [];
        $totalCirculationTopDonate = [];
        $totalCountTopDonate = [];
        $totalPageTopDonate = [];
        $paragraphTopDonate = [];
        $tonTopDonate = [];

        $totalPriceBottomDonate = [];
        $totalCirculationBottomDonate = [];
        $totalCountBottomDonate = [];
        $totalPageBottomDonate = [];
        $paragraphBottomDonate = [];
        $tonBottomDonate = [];


        $topBoxData = BookDioCachedData::where('year', 0)->where('dio_subject_id', $id)->first();

        $bottomBoxAndChartData =BookDioCachedData::where('dio_subject_id' , $id)
            ->where('year', '<=' , $endYear)
            ->where('year','>=' , $startYear)
            ->get();

        $donateIds = DioSubject::where('parent_id' , $id)->pluck('id_by_law');


        foreach ($donateIds as $donateId){
            $topDonateValue = BookDioCachedData::where('year',0)->where('dio_subject_id',$donateId)->first();
            $totalCirculationTopDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $topDonateValue->total_circulation];
            $totalPriceTopDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $topDonateValue->total_price];
            $totalCountTopDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $topDonateValue->count];
            $totalPageTopDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $topDonateValue->total_page];
            $tonTopDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $topDonateValue->paragraph * 25 / 100];
            $paragraphTopDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $topDonateValue->paragraph];

            $botDonateValue = BookDioCachedData::where('dio_subject_id' , $donateId)
                ->where('year', '<=' , $endYear)
                ->where('year','>=' , $startYear)
                ->get();
            $totalCirculationBottomDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $botDonateValue->sum('total_circulation')];
            $totalPriceBottomDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $botDonateValue->sum('total_price')];
            $totalCountBottomDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $botDonateValue->sum('count')];
            $totalPageBottomDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $botDonateValue->sum('total_page')];
            $tonBottomDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $botDonateValue->sum('paragraph') * 25 / 100];
            $paragraphBottomDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $botDonateValue->sum('paragraph')];
        }

        foreach ($bottomBoxAndChartData as $item){
            $totalPageChart[] = $item->year;
            $totalPageChart [0] [] = $item->total_pages ?? 0;
            $totalPageChart [1] [] = $item->first_cover_total_pages ?? 0;
            if ($item->total_pages != null){
                $pagesBottomBox += $item->total_pages;
            }

            $totalCirculationChart[] = $item->year;
            $totalCirculationChart [0] [] = $item->total_circulation ?? 0;
            $totalCirculationChart [1] [] = $item->first_cover_total_circulation ?? 0;
            if ($item->total_circulation != null){
                $circulationBottomBox += $item->total_circulation;
            }

            $totalCountChart[] = $item->year;
            $totalCountChart [0] [] = $item->count ?? 0;
            $totalCountChart [1] [] = $item->first_cover_count ?? 0;
            if ($item->count != null){
                $countBottomBox += $item->count;
            }

            $totalPriceChart[] = $item->year;
            $totalPriceChart [0] [] = $item->total_price ?? 0;
            $totalPriceChart [1] [] = $item->first_cover_total_price ?? 0;
            if ($item->total_price != null){
                $priceBottomBox += $item->total_price;
            }

            $averageChart[] = $item->year;
            $averageChart [0] [] = $item->average ?? 0;
            $averageChart [1] [] = $item->first_cover_average ?? 0;

            $paragraphChart[] = $item->year;
            $paragraphChart[0][] = round($item->paragraph) ?? 0;
            $paragraphChart[1][] = round($item->first_cover_paragraph) ?? 0;
            $tonChart[] = $item->year;
            $tonChart[0][] = round($item->paragraph * 25 /1000) ?? 0;
            $tonChart[1][] = round($item->first_cover_paragraph * 25 /1000) ?? 0;
            if ($item->paragraph != null) {
                $paragraphBottomBox += $item->paragraph;
                $tonBottomBox += $item->paragraph * 25 / 1000;
            }
        }

        $topBox =[
            [
                'title_fa' => "مجموع صفحات چاپ شده از ابتدا تا کنون",
                'title_en' => "total_pages_all_time",
                'value' => convertToPersianNumbers($topBoxData->total_pages)
            ],
            [
                'title_fa' => "مجموع تیراژ از ابتدا تا کنون",
                'title_en' => "total_circulation_all_time",
                'value' => convertToPersianNumbers($topBoxData->total_circulation)
            ],
            [
                'title_fa' => "جمع مالی از ابتدا تا کنون",
                'title_en' => "total_price_all_time",
                'value' => convertToPersianNumbers($topBoxData->total_price)
            ],
            [
                'title_fa' => "مجموع وزن کاغذ مصرفی از ابتدا تا کنون(بر حسب کیلو - کاغذ ۷۰ گرمی)",
                'title_en' => "kilo_all_time",
                'value' => convertToPersianNumbers(round($topBoxData->paragraph * 25))
            ],
            [
                'title_fa' => "جمع بند کاغذ مصرفی از ابتدا تا کنون",
                'title_en' => "total_count_all_time",
                'value' => convertToPersianNumbers(round($topBoxData->paragraph))
            ],
            [
                'title_fa' => "جمع تعداد کتاب ها از ابتدا تا کنون",
                'title_en' => "total_count_all_time",
                'value' => convertToPersianNumbers($topBoxData->count)
            ],
        ];

        $bottomBox = [
            [
                'title_fa' => "مجموع صفحات چاپ شده از سال $startYear تا سال $endYear",
                'title_en' => "sum_total_pages",
                'value' => convertToPersianNumbers($pagesBottomBox)
            ],
            [
                'title_fa' => "مجموع تیراژ از سال $startYear تا سال $endYear",
                'title_en' => "sum_circulation_range",
                'value' => convertToPersianNumbers($circulationBottomBox)
            ],
            [
                'title_fa' => "جمع مالی از سال $startYear تا سال $endYear",
                'title_en' => "sum_total_price",
                'value' => convertToPersianNumbers($priceBottomBox)
            ],
            [
                'title_fa' => "مجموع وزن کاغذ مصرفی از سال $startYear تا سال $endYear (بر حسب تن - کاغذ ۷۰ گرمی)",
                'title_en' => "sum_ton_range",
                'value' => convertToPersianNumbers(round($tonBottomBox))
            ],
            [
                'title_fa' => "مجموع بند کاغذ مصرفی از سال $startYear تا سال $endYear",
                'title_en' => "sum_paragraph-range",
                'value' => convertToPersianNumbers(round($paragraphBottomBox))
            ],
            [
                'title_fa' => "مجموع تعداد کتاب از سال $startYear تا سال $endYear",
                'title_en' => "sum_count_range",
                'value' => convertToPersianNumbers($countBottomBox)
            ],
        ];

        $charts = [
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                "title_fa" => "نمودار میانگین قیمت از سال $startYear تا سال $endYear",
                "title_en"=> "data_for_average_books_price",
                'data' => $averageChart
            ],
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                "title_fa" => " نمودار بند کاغذ مصرفی از سال $startYear تا سال $endYear",
                "title_en"=> "data_for_paragraph_of_books",
                'data' => $paragraphChart
            ],
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                "title_fa" => "نمودار وزن کاغذ مصرفی از سال $startYear تا سال $endYear(بر حسب تن - کاغذ ۷۰ گرمی)",
                "title_en"=> "data_for_ton_of_books",
                'data' => $tonChart
            ],
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                "title_fa" => "نمودار مجموع تعداد کتاب  از سال $startYear تا سال $endYear",
                "title_en"=> "data_for_count_books",
                'data' => $totalCountChart
            ],
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                "title_fa" => "نمودار جمع مالی  از سال $startYear تا سال $endYear",
                "title_en"=> "data_for_price_books",
                'data' => $totalPriceChart
            ],
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                "title_fa" => "نمودار مجموع صفحات چاپ شده از سال $startYear تا سال $endYear",
                "title_en"=> "data_for_page_books",
                'data' => $totalPageChart
            ],
            [
                'stackLabels' =>['مقادیر کلی','چاپ اول'],
                "title_fa" => "نمودار مجموع تیراژ  از سال $startYear تا سال $endYear",
                "title_en"=> "data_for_circulation_books",
                'data' => $totalCirculationChart
            ],
        ];

        $bottomDonate = [
            [
                "title_fa" => " نمودار بند کاغذ مصرفی از سال $startYear تا سال $endYear",
                "title_en"=> "data_for_paragraph_of_books",
                'data' => $paragraphBottomDonate
            ],
            [
                "title_fa" => "نمودار وزن کاغذ مصرفی از سال $startYear تا سال $endYear(بر حسب تن - کاغذ ۷۰ گرمی)",
                "title_en"=> "data_for_ton_of_books",
                'data' => $tonBottomDonate
            ],
            [
                "title_fa" => "نمودار مجموع تعداد کتاب  از سال $startYear تا سال $endYear",
                "title_en"=> "data_for_count_books",
                'data' => $totalCountBottomDonate
            ],
            [
                "title_fa" => "نمودار جمع مالی  از سال $startYear تا سال $endYear",
                "title_en"=> "data_for_price_books",
                'data' => $totalPriceBottomDonate
            ],
            [
                "title_fa" => "نمودار مجموع صفحات چاپ شده از سال $startYear تا سال $endYear",
                "title_en"=> "data_for_page_books",
                'data' => $totalPageBottomDonate
            ],
            [
                "title_fa" => "نمودار مجموع تیراژ  از سال $startYear تا سال $endYear",
                "title_en"=> "data_for_circulation_books",
                'data' => $totalCirculationBottomDonate
            ],
        ];


         $topDonate = [
             [
                 'title_fa' => "نمودار مجموع صفحات چاپ شده از ابتدا تا کنون",
                 'title_en' => "total_pages_all_time_donate",
                 'value' => $totalPageTopDonate
             ],
             [
                 'title_fa' => "نمودار مجموع تیراژ از ابتدا تا کنون",
                 'title_en' => "total_circulation_all_time_donate",
                 'value' => $totalCirculationTopDonate
             ],
             [
                 'title_fa' => "نمودار جمع مالی از ابتدا تا کنون",
                 'title_en' => "total_price_all_time_donate",
                 'value' => $totalPriceTopDonate
             ],
             [
                 'title_fa' => "نمودار مجموع وزن کاغذ مصرفی از ابتدا تا کنون(بر حسب کیلو - کاغذ ۷۰ گرمی)",
                 'title_en' => "kilo_all_time_donate",
                 'value' => $tonTopDonate
             ],
             [
                 'title_fa' => "نمودار جمع بند کاغذ مصرفی از ابتدا تا کنون",
                 'title_en' => "total_count_all_time_donate",
                 'value' => $paragraphTopDonate
             ],
             [
                 'title_fa' => "نمودار جمع تعداد کتاب ها از ابتدا تا کنون",
                 'title_en' => "total_count_all_time_donate",
                 'value' => $totalCountTopDonate
             ],
         ];

        $end = microtime(true);
        $elapsedTime = $end - $start;
         return response()->json([
            'msg' => 'success',
            'data' => [
                'top_box' => $topBox,
                'top_donate' => $topDonate,
                'bottom_box' => $bottomBox,
                'bottom_donate' => $bottomDonate,
                'charts' => $charts,
//                'top' => $top
            ] ,
             'status' => 200 ,
             'time' => $elapsedTime,
         ]);

    }

    public function all(Request $request) :JsonResponse
    {
        $id =(int)$request->input('id');

        if ($id == 0){
            $dioSubjects = DioSubject::where('level' , 0)->get();
            $data = $this->getDioChild($dioSubjects);
        } else {
            $dioSubjects = DioSubject::where('parent_id', $id)->get();
            $data = $this->getDioChild($dioSubjects);
        }
        return response()->json([
            'msg' => 'success',
            'data' => [
                'level' => $dioSubjects[0]->level,
                'list' => $data
            ],
            'status' => 200
        ]);
    }

    private function getDioChild($dioSubjects) : array
    {
        $data = [];
        foreach ($dioSubjects as $dioSubject) {
            $data [] = [
                'id' => $dioSubject->id_by_law,
                'value' => $dioSubject->title,
                'has_child' => $dioSubject->has_child
            ];
        }
        return $data;
    }
}
