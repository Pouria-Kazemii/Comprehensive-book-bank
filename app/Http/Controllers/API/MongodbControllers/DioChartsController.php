<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookDioCachedData;
use App\Models\MongoDBModels\DioSubject;
use App\Models\MongoDBModels\TCC_Yearly;
use App\Models\MongoDBModels\TCP_Yearly;
use App\Models\MongoDBModels\TPC_Yearly;
use App\Models\MongoDBModels\TPP_Yearly;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DioChartsController extends Controller
{
    public function index(Request $request) : JsonResponse
    {
        $start = microtime('true');
        $year = getYearNow();
        (int)$request->input('id') == null ? $id = 0 : $id = (int)$request->input('id');
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


        $dataForCreatorPrice = [];
        $dataForCreatorCirculation = [];
        $dataForPublisherPrice = [];
        $dataForPublisherCirculation = [];

        if ($id != 0) {
            $topBoxData = BookDioCachedData::where('year', 0)->where('dio_subject_id', $id)->first();
            $topBoxTotalPrice = $topBoxData->total_price;
            $topBoxTotalCirculation = $topBoxData->total_circulation;
            $topBoxTotalPages = $topBoxData->total_pages;
            $topBoxTotalCount = $topBoxData->count;
            $topBoxParagraph = $topBoxData->paragraph;
            $topBoxTon = $topBoxData->paragraph * 25 / 1000;

            $bottomBoxAndChartData = BookDioCachedData::where('dio_subject_id', $id)
                ->where('year', '<=', $endYear)
                ->where('year', '>=', $startYear)
                ->get();


            $topChartData =BookDioCachedData::where('year', $topYear)->where('dio_subject_id' , $id)->first();
            foreach ($topChartData->top_circulation_publishers as $item){
                $dataForPublisherCirculation['label'][] = $item['publisher_name'];
                $dataForPublisherCirculation['value'][] = $item['total_page'];
            }

            foreach ($topChartData->top_price_publishers as $item){
                $dataForPublisherPrice ['label'] [] = $item['publisher_name'];
                $dataForPublisherPrice['value'] [] = $item['total_price'];
            }

           foreach ($topChartData->top_circulation_creators as $item){
                $dataForCreatorCirculation['label'][] = $item['creator_name'];
                $dataForCreatorCirculation['value'][] = $item['total_page'];
            }

           foreach ($topChartData->top_price_creators  as $item){
                $dataForCreatorPrice['label'] [] = $item['creator_name'];
                $dataForCreatorPrice['value'] [] = $item['total_price'];
           }
        } else {
            $topBoxData = BookDioCachedData::where('year', 0);
            $topBoxTotalPrice = $topBoxData->sum('total_price');
            $topBoxTotalCirculation = $topBoxData->sum('total_circulation');
            $topBoxTotalPages = $topBoxData->sum('total_pages');
            $topBoxTotalCount = $topBoxData->sum('count');
            $topBoxParagraph = $topBoxData->sum('paragraph');
            $topBoxTon = $topBoxParagraph * 20 / 1000;

            $bottomBoxAndChartData = BookDioCachedData::raw(function ($collection) use ($startYear, $endYear) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'year' => [
                                '$lte' => $endYear,
                                '$gte' => $startYear
                            ]
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => '$year',
                            'total_pages' => ['$sum' => '$total_pages'],
                            'first_cover_total_pages' => ['$sum' => '$first_cover_total_pages'],
                            'total_price' => ['$sum' => '$total_price'],
                            'first_cover_total_price' => ['$sum' => '$first_cover_total_price'],
                            'total_circulation' => ['$sum' => '$total_circulation'],
                            'first_cover_total_circulation' => ['$sum' => '$first_cover_total_circulation'],
                            'count' => ['$sum' => '$count'],
                            'first_cover_count' => ['$sum' => '$first_cover_count'],
                            'paragraph' => ['$sum' => '$paragraph'],
                            'first_cover_paragraph' => ['$sum' => '$first_cover_paragraph'],
                            'average' => ['$sum' => '$average'],
                            'first_cover_average' => ['$sum' => '$first_cover_average']
                        ]
                    ]
                ]);
            });


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
        }

        $donateIds = DioSubject::where('parent_id' , $id)->pluck('id_by_law');
        foreach ($donateIds as $donateId){
            $topDonateValue = BookDioCachedData::where('year',0)->where('dio_subject_id',$donateId)->first();
            $totalCirculationTopDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $topDonateValue->total_circulation];
            $totalPriceTopDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $topDonateValue->total_price];
            $totalCountTopDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $topDonateValue->count];
            $totalPageTopDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $topDonateValue->total_pages];
            $tonTopDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $topDonateValue->paragraph * 25 / 100];
            $paragraphTopDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $topDonateValue->paragraph];

            $botDonateValue = BookDioCachedData::where('dio_subject_id' , $donateId)
                ->where('year', '<=' , $endYear)
                ->where('year','>=' , $startYear)
                ->get();
            $totalCirculationBottomDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $botDonateValue->sum('total_circulation')];
            $totalPriceBottomDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $botDonateValue->sum('total_price')];
            $totalCountBottomDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $botDonateValue->sum('count')];
            $totalPageBottomDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $botDonateValue->sum('total_pages')];
            $tonBottomDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $botDonateValue->sum('paragraph') * 25 / 100];
            $paragraphBottomDonate [] = ["kind" => $topDonateValue->dio_subject_title , "share" => $botDonateValue->sum('paragraph')];
        }

        foreach ($bottomBoxAndChartData as $item){
            $id != 0 ? $totalPageChart['label'] [] = $item->year : $totalPageChart['label'] [] = $item->_id;
            $totalPageChart ['value'] [0] [] = $item->total_pages ?? 0;
            $totalPageChart ['value'] [1] [] = $item->first_cover_total_pages ?? 0;
            if ($item->total_pages != null){
                $pagesBottomBox += $item->total_pages;
            }

            $id != 0 ? $totalCirculationChart['label'] [] = $item->year: $totalCirculationChart['label'] [] = $item->_id;
            $totalCirculationChart ['value'] [0] [] = $item->total_circulation ?? 0;
            $totalCirculationChart ['value'] [1] [] = $item->first_cover_total_circulation ?? 0;
            if ($item->total_circulation != null){
                $circulationBottomBox += $item->total_circulation;
            }

            $id != 0 ? $totalCountChart['label'] [] = $item->year : $totalCountChart['label'] [] = $item->_id;
            $totalCountChart ['value'] [0] [] = $item->count ?? 0;
            $totalCountChart ['value'] [1] [] = $item->first_cover_count ?? 0;
            if ($item->count != null){
                $countBottomBox += $item->count;
            }

            $id != 0 ? $totalPriceChart['label'] [] = $item->year : $totalPriceChart['label'] [] = $item->_id;
            $totalPriceChart ['value'] [0] [] = $item->total_price ?? 0;
            $totalPriceChart ['value'] [1] [] = $item->first_cover_total_price ?? 0;
            if ($item->total_price != null){
                $priceBottomBox += $item->total_price;
            }

            $id != 0 ? $averageChart['label'] [] = $item->year :$averageChart['label'] [] = $item->_id ;
            $averageChart ['value'] [0] [] = $item->average ?? 0;
            $averageChart ['value'] [1] [] = $item->first_cover_average ?? 0;

            $id != 0 ? $paragraphChart['label'] [] = $item->year : $paragraphChart['label'] [] = $item->_id;
            $paragraphChart['value'][0][] = round($item->paragraph) ?? 0;
            $paragraphChart['value'][1][] = round($item->first_cover_paragraph) ?? 0;
            $id != 0 ? $tonChart['label'] [] = $item->year : $tonChart['label'] [] = $item->_id;
            $tonChart['value'][0][] = round($item->paragraph * 25 /1000) ?? 0;
            $tonChart['value'][1][] = round($item->first_cover_paragraph * 25 /1000) ?? 0;
            if ($item->paragraph != null) {
                $paragraphBottomBox += $item->paragraph;
                $tonBottomBox += $item->paragraph * 25 / 1000;
            }
        }

        $startYear = convertToPersianNumbersPure($startYear);
        $endYear = convertToPersianNumbersPure($endYear);
        $topYear = convertToPersianNumbersPure($topYear);

        $topBox =[
            [
                'title_fa' => "مجموع صفحات چاپ شده از ابتدا تا کنون",
                'title_en' => "total_pages_all_time",
                'value' => convertToPersianNumbers($topBoxTotalPages)
            ],
            [
                'title_fa' => "مجموع تیراژ از ابتدا تا کنون",
                'title_en' => "total_circulation_all_time",
                'value' => convertToPersianNumbers($topBoxTotalCirculation)
            ],
            [
                'title_fa' => "جمع مالی از ابتدا تا کنون",
                'title_en' => "total_price_all_time",
                'value' => convertToPersianNumbers($topBoxTotalPrice)
            ],
            [
                'title_fa' => "مجموع وزن کاغذ مصرفی از ابتدا تا کنون(بر حسب تن - کاغذ ۷۰ گرمی)",
                'title_en' => "ton_all_time",
                'value' => convertToPersianNumbers(round($topBoxTon))
            ],
            [
                'title_fa' => "جمع بند کاغذ مصرفی از ابتدا تا کنون",
                'title_en' => "total_count_all_time",
                'value' => convertToPersianNumbers(round($topBoxParagraph))
            ],
            [
                'title_fa' => "جمع تعداد کتاب ها از ابتدا تا کنون",
                'title_en' => "total_count_all_time",
                'value' => convertToPersianNumbers($topBoxTotalCount)
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

        $top=[
            [
                'title_fa' => "نمودار پدیدآورندگان برتر بر حسب جمع مالی در سال $topYear",
                'title_en' => 'data_for_creators_total_price',
                'data' => $dataForCreatorPrice
            ],
            [
                'title_fa' => "نمودار پدیدآورندگان برتر بر حسب مجموع تیراژ در سال $topYear",
                'title_en' => 'data_for_creators_total_circulation',
                'data' => $dataForCreatorCirculation
            ],
            [
                'title_fa' => "نمودار انتشارات برتر بر حسب جمع مالی در سال $topYear",
                'title_en' => 'data_for_publishers_total_price',
                'data' => $dataForPublisherPrice
            ],
            [
                'title_fa' => "نمودار انتشارات برتر بر حسب مجموع تیراژ در سال $topYear",
                'title_en' => 'date_for_publishers_total_circulation',
                'data' => $dataForPublisherCirculation
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
                'top' => $top
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
