<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class ChartController extends Controller
{
    public function index(Request $request)
    {
        $firstDate = ( isset($request['firstDate']) and !empty($request['firstDate']) ) ? intval($request->input('firstDate')): 1385 ;
        $lastDate = ( isset($request['lastDate']) and !empty($request['lastDate']) ) ? intval($request->input('lastDate')): 1403 ;
        $dateForCreators_totalPrice = ( isset($request['dfc_price']) and !empty($request['dfc_price']) ) ? intval($request->input('dfc_price')): 1403 ;
        $dateForCreators_totalCirculation = ( isset($request['dfc_circulation']) and !empty($request['dfc_circulation']) ) ? intval($request->input('dfc_circulation')): 1403  ;
        $dateForPublishers_totalPrice = ( isset($request['dfp_price']) and !empty($request['dfp_price']) ) ? intval($request->input('dfp_price')): 1403 ;
        $dateForPublishers_totalCirculation = ( isset($request['dfp_circulation']) and !empty($request['dfp_circulation']) ) ? intval($request->input('dfp_circulation')): 1403 ;

        $firstDateCache = Cache::get('first_date');
        $lastDateCache = Cache::get('last_date');
        $tenPastDayCache = Cache::get('ten_past_day');
        $dfc_price_year  = Cache::get('dfc_price_year');
        $dfc_circulation_year = Cache::get('dfc_circulation_year');
        $dfp_price_year = Cache::get('dfp_price_year');
        $dfp_circulation_year = Cache::get('dfp_circulation_year');

        if ($dfp_circulation_year != $dateForPublishers_totalCirculation){
            Cache::forget('dfp_circulation');
            Cache::forget('dfp_circulation_year');
            $dfp_circulation = $this->getTopPublishersAccordingToValue($dateForPublishers_totalCirculation , 'total_page');
            Cache::put('dfp_circulation',$dfp_circulation,60);
            Cache::put('dfp_circulation_year',$dateForPublishers_totalCirculation,60);
        }elseif (!$dfp_circulation_year){
            $dfp_circulation = $this->getTopPublishersAccordingToValue($dateForPublishers_totalCirculation , 'total_page');
            Cache::put('dfp_circulation',$dfp_circulation,60);
            Cache::put('dfp_circulation_year',$dateForPublishers_totalCirculation,60);
        }

        if ($dfp_price_year != $dateForPublishers_totalPrice){
            Cache::forget('dfp_price');
            Cache::forget('dfp_price_year');
            $dfp_price = $this->getTopPublishersAccordingToValue($dateForPublishers_totalPrice , 'total_price');
            Cache::put('dfp_price' , $dfp_price ,60);
            Cache::put('dfp_price_year', $dateForPublishers_totalPrice , 60);
        }elseif (!$dfp_price_year){
            $dfp_price = $this->getTopPublishersAccordingToValue($dateForPublishers_totalPrice , 'total_price');
            Cache::put('dfp_price' , $dfp_price ,60);
            Cache::put('dfp_price_year', $dateForPublishers_totalPrice , 60);
        }

        if ($dfc_circulation_year != $dateForCreators_totalCirculation ){
            Cache::forget('dfc_circulation');
            Cache::forget('dfc_circulation_year');
            $dfc_circulation = $this->getTopCreatorsAccordingToValue($dateForCreators_totalCirculation , 'total_page');
            Cache::put('dfc_circulation' , $dfc_circulation,60);
            Cache::put('dfc_circulation_year' , $dateForCreators_totalCirculation , 60);
        }elseif (!$dfc_circulation_year){
            $dfc_circulation = $this->getTopCreatorsAccordingToValue($dateForCreators_totalCirculation , 'total_page');
            Cache::put('dfc_circulation' , $dfc_circulation,60);
            Cache::put('dfc_circulation_year' , $dateForCreators_totalCirculation , 60);
        }

        if ($dfc_price_year != $dateForCreators_totalPrice){
            Cache::forget('dfc_price');
            Cache::forget('dfc_price_year');
            $dfc_price = $this->getTopCreatorsAccordingToValue($dateForCreators_totalPrice,'total_price');
            Cache::put('dfc_price' , $dfc_price,60);
            Cache::put('dfc_price_year' , $dateForCreators_totalPrice , 60);
        }elseif(!$dfc_price_year){
            $dfc_price = $this->getTopCreatorsAccordingToValue($dateForCreators_totalPrice,'total_price');
            Cache::put('dfc_price' , $dfc_price,60);
            Cache::put('dfc_price_year' , $dateForCreators_totalPrice , 60);
        }

        if (!$tenPastDayCache) {
            $solarHijriDate = [];
            $tenPastDay = DB::select(
                    DB::raw("
                SELECT DATE(FROM_UNIXTIME(xregdate)) AS ForDate, COUNT(*) AS BookCount
                FROM bookir_book
                WHERE 1
                GROUP BY DATE(FROM_UNIXTIME(xregdate))
                ORDER BY ForDate DESC
                LIMIT 0, 10
                ")
            );
            foreach ($tenPastDay as $day){
                $solarHijriDate [] = [
                    'ForDate' => Jalalian::fromFormat('Y-m-d',$day->ForDate)->format('m-d'),
                    'BookCount' => $day->BookCount
                ];
            }
            Cache::put('ten_past_day', $solarHijriDate, 60);
        }

        if ($firstDateCache != $firstDate or $lastDateCache != $lastDate){
            Cache::forget('date_range');
            Cache::forget('first_date');
            Cache::forget('last_date');
            $dateRange = $this->getBooksCountByYear($firstDate,$lastDate);
            Cache::put('date_range',$dateRange ,60);
            Cache::put('first_date',$firstDate,60);
            Cache::put('last_date',$lastDate,60);
        }elseif(!$firstDateCache or !$lastDateCache){
            $dateRange = $this->getBooksCountByYear($firstDate,$lastDate);
            Cache::put('date_range',$dateRange ,60);
            Cache::put('first_date',$firstDate,60);
            Cache::put('last_date',$lastDate,60);
        }

        return response([
            'msg' => 'success',
            'data' =>[
                'data for ten past date' => Cache::get('ten_past_day') ,

                'first date for rangeData' => Cache::get('first_date'),
                'last date for rangeData' => Cache::get('last_date'),
                'data for this 2 range of year' => Cache::get('date_range'),

                'year for creators_total price' => Cache::get('dfc_price_year') ,
                'data for creators_total price' => Cache::get('dfc_price') ,

                'year for creators_total circulation' => Cache::get('dfc_circulation_year'),
                'data for creators_total circulation' => Cache::get('dfc_circulation'),

                'year for publishers_total price' => Cache::get('dfp_price_year'),
                'data for publishers_total price' => Cache::get('dfp_price'),

                'year for publishers_total circulation' => Cache::get('dfp_circulation_year'),
                'date for publishers_total circulation' => Cache::get('dfp_circulation')
            ],
            'status' => 200
        ],200);
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
                            'total_price' => ['$sum' => '$xtotal_price'],
                            'total_circulation' => ['$sum' => '$xtotal_page']
                        ]
                    ],
                    [
                        '$sort' => ['total_price' => 1] // Sort by year
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
}
