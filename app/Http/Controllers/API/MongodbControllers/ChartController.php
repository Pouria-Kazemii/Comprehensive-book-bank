<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrDaily;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;

class ChartController extends Controller
{
    public function index(Request $request)
    {
        $start = microtime(true);
        $year = getYearNow();
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

        // Fetch or compute cache values
        $dfp_circulation = $this->getTopPublishersAccordingToValue($dateForPublishers_totalCirculation, 'total_page');

        $dfp_price   = $this->getTopPublishersAccordingToValue($dateForPublishers_totalPrice, 'total_price');

        $dfc_circulation = $this->getTopCreatorsAccordingToValue($dateForCreators_totalCirculation, 'total_page');

        $dfc_price = $this->getTopCreatorsAccordingToValue($dateForCreators_totalPrice, 'total_price');

        $dataForTenPastDayBookInserted = $this->getLastTenDayBooks();
        $dateRangeCount = $this->getBooksCountByYear($firstDateForCount, $lastDateForCount);
        $dateRangePrice = $this->getBooksPriceByYear($firstDateForPrice , $lastDateForPrice);
        $dateRangeCirculation = $this->getBooksCirculationByYear($firstDateForCirculation , $lastDateForCirculation);
        $dateRangeAverage = $this->getAverageBookPrice($firstDateForAverage,$lastDateForAverage);

        $end = microtime(true);
        $elapsedTime = $end - $start;
        return response([
            'msg' => 'success',
            'data' => [
                'data_for_ten_past_new_books' => $dataForTenPastDayBookInserted,
                'start_year_for_average_books' => $firstDateForAverage,
                'end_year_for_average_books' => $lastDateForAverage,
                'data_for_average_books' => $dateRangeAverage,
                'start_year_for_count_books' => $firstDateForCount,
                'end_year_for_count_books' => $lastDateForCount,
                'data_for_count_books' => $dateRangeCount,
                'start_year_for_price_books' => $firstDateForPrice,
                'end_year_for_price_books' => $lastDateForPrice,
                'data_for_price_books' => $dateRangePrice,
                'start_year_for_circulation_books' => $firstDateForCirculation,
                'end_year_for_circulation_books' => $lastDateForCirculation,
                'data_for_circulation_books' => $dateRangeCirculation,
                'year_for_creators_total_price' => $dateForCreators_totalPrice,
                'data-for_creators_total_price' => $dfc_price,
                'year_for_creators_total_circulation' => $dateForCreators_totalCirculation,
                'data_for_creators_total_circulation' => $dfc_circulation,
                'year_for_publishers_total_price' => $dateForPublishers_totalPrice,
                'data_for_publishers_total_price' => $dfp_price,
                'year_for_publishers_total_circulation' => $dateForPublishers_totalCirculation,
                'date_for_publishers_total_circulation' => $dfp_circulation
            ],
            'status' => 200 ,
            'time' => $elapsedTime,
        ], 200);
    }
    private function getBooksPriceByYear($startYear , $endYear)
    {
        return BookIrBook2 ::raw(function ($collection) use($startYear , $endYear) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'xpublishdate_shamsi' => [
                            '$gte' => (int)$startYear,
                            '$lte' => (int)$endYear
                        ]
                        ,
                        'xtotal_price' => [
                            '$ne' => 0 // Ensure xcoverprice is not equal to 0
                        ]
                    ]
                ]
                ,
                [
                    '$group' => [
                        '_id' => '$xpublishdate_shamsi',
                        'total_price' => ['$sum' => '$xtotal_price'],
                    ]
                ],
                [
                    '$sort' => ['_id' => 1] // Sort by year
                ]
            ]);
        });
    }
    private function getBooksCirculationByYear($startYear , $endYear)
    {
        return BookIrBook2 ::raw(function ($collection) use($startYear , $endYear) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'xpublishdate_shamsi' => [
                            '$gte' => (int)$startYear,
                            '$lte' => (int)$endYear
                        ]
                        ,
                        'xtotal_page' => [
                            '$ne' => 0 // Ensure xcoverprice is not equal to 0
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$xpublishdate_shamsi',
                        'total_circulation' => ['$sum' => '$xtotal_page']
                    ]
                ],
                [
                    '$sort' => ['_id' => 1] // Sort by year
                ]
            ]);
        });
    }
    private function getBooksCountByYear($startYear, $endYear)
    {
        return BookIrBook2 ::raw(function ($collection) use($startYear , $endYear) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'xpublishdate_shamsi' => [
                                '$gte' => (int)$startYear,
                                '$lte' => (int)$endYear
                            ]
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => '$xpublishdate_shamsi',
                            'count' => ['$sum' => 1],
                        ]
                    ],
                    [
                        '$sort' => ['_id' => 1] // Sort by year
                    ]
                ]);
            });
    }

    private function getTopCreatorsAccordingToValue(int $year ,string $value)
    {
        return BookIrBook2::raw(function($collection) use ($year , $value) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'xpublishdate_shamsi' => $year
                        ,
                        'xcoverprice' => [
                            '$ne' => 0 // Ensure xcoverprice is not equal to 0
                        ]
                    ]
                ],
                [
                    '$unwind' => '$partners'
                ],
                [
                    '$group' => [
                        '_id' => [
                            'id' => '$partners.xcreator_id',
                            'name' => '$partners.xcreatorname'
                        ],
                        $value => ['$sum' => '$x'.$value]
                    ]
                ],
                [
                    '$sort' => [$value => -1] // Sort by total_price in descending order
                ],
                [
                    '$limit' => 30 // Limit to top 30 creators
                ]
            ]);
        });
    }

    private function getTopPublishersAccordingToValue(int $year ,string $value)
    {
        return BookIrBook2::raw(function($collection) use ($year , $value) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'xpublishdate_shamsi' => $year
                        ,
                        'xcoverprice' => [
                            '$ne' => 0 // Ensure xcoverprice is not equal to 0
                        ]
                    ]
                ],
                [
                    '$unwind' => '$publisher'
                ],
                [
                    '$group' => [
                        '_id' => [
                            'id' => '$publisher.xpublisher_id',
                            'name' => '$publisher.xpublishername'
                        ],
                        $value => ['$sum' => '$x'.$value]
                    ]
                ],
                [
                    '$sort' => [$value => -1] // Sort by total_price in descending order
                ],
                [
                    '$limit' => 30 // Limit to top 30 creators
                ]
            ]);
        });
    }

    private function getAverageBookPrice($startYear ,$endYear)
    {
        $response = [];
        $data = BookIrBook2::raw(function ($collection) use($startYear , $endYear){
            return $collection->aggregate([
                [
                    '$match' => [
                        'xpublishdate_shamsi' => [
                            '$gte' => (int)$startYear,
                            '$lte' => (int)$endYear
                        ]
                        ,
                        'xcoverprice' => [
                            '$ne' => 0 // Ensure xcoverprice is not equal to 0
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$xpublishdate_shamsi',
                        'count' => ['$sum' => 1],
                        'price' => ['$sum' => '$xcoverprice'],
                    ]
                ],
                [
                    '$sort' => ['_id' => 1] // Sort by year
                ]
            ]);
        });

        foreach ($data as $value){
            $response [] = [
                '_id' => $value['_id'] ,
                'average' => priceFormat(round($value['price']/$value['count']))
            ];
        }

        return $response;
    }

    private function getLastTenDayBooks()
    {
        $response = [];
        $data = BookIrDaily::orderBy('_id' , -1)->take(10)->get();
        foreach ($data as $value){
            $response [] = [
                'day' => $value->day,
                'month' => $value->month,
                'year' => $value->year,
                'count' => $value->count
            ];
        }
        return $response;
    }
}
