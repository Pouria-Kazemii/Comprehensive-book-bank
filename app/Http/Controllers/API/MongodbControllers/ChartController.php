<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
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
        Debugbar::startMeasure('controller_start', 'Controller method start');

        $firstDate = ( isset($request['firstDate']) and !empty($request['firstDate']) ) ? intval($request->input('firstDate')): 1385 ;
        $lastDate = ( isset($request['lastDate']) and !empty($request['lastDate']) ) ? intval($request->input('lastDate')): 1403 ;
        $dateForCreators_totalPrice = ( isset($request['dfc_price']) and !empty($request['dfc_price']) ) ? intval($request->input('dfc_price')): 1403 ;
        $dateForCreators_totalCirculation = ( isset($request['dfc_circulation']) and !empty($request['dfc_circulation']) ) ? intval($request->input('dfc_circulation')): 1403  ;
        $dateForPublishers_totalPrice = ( isset($request['dfp_price']) and !empty($request['dfp_price']) ) ? intval($request->input('dfp_price')): 1403 ;
        $dateForPublishers_totalCirculation = ( isset($request['dfp_circulation']) and !empty($request['dfp_circulation']) ) ? intval($request->input('dfp_circulation')): 1403 ;

        $cacheTTL = 600; // Cache for 10 minutes

        // Cache keys
        $cacheKeys = [
            'dfp_circulation' => "dfp_circulation_{$dateForPublishers_totalCirculation}",
            'dfp_price' => "dfp_price_{$dateForPublishers_totalPrice}",
            'dfc_circulation' => "dfc_circulation_{$dateForCreators_totalCirculation}",
            'dfc_price' => "dfc_price_{$dateForCreators_totalPrice}",
            'ten_past_day' => 'ten_past_day',
            'date_range' => "date_range_{$firstDate}_{$lastDate}",
        ];

        // Fetch or compute cache values
        $dfp_circulation = Cache::remember($cacheKeys['dfp_circulation'], $cacheTTL, function() use ($dateForPublishers_totalCirculation) {
            return $this->getTopPublishersAccordingToValue($dateForPublishers_totalCirculation, 'total_page');
        });

        $dfp_price = Cache::remember($cacheKeys['dfp_price'], $cacheTTL, function() use ($dateForPublishers_totalPrice) {
            return $this->getTopPublishersAccordingToValue($dateForPublishers_totalPrice, 'total_price');
        });

        $dfc_circulation = Cache::remember($cacheKeys['dfc_circulation'], $cacheTTL, function() use ($dateForCreators_totalCirculation) {
            return $this->getTopCreatorsAccordingToValue($dateForCreators_totalCirculation, 'total_page');
        });

        $dfc_price = Cache::remember($cacheKeys['dfc_price'], $cacheTTL, function() use ($dateForCreators_totalPrice) {
            return $this->getTopCreatorsAccordingToValue($dateForCreators_totalPrice, 'total_price');
        });

        $tenPastDay = Cache::remember($cacheKeys['ten_past_day'], $cacheTTL, function() {
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
            foreach ($tenPastDay as $day) {
                $solarHijriDate[] = [
                    'ForDate' => Jalalian::fromFormat('Y-m-d', $day->ForDate)->format('m-d'),
                    'BookCount' => $day->BookCount
                ];
            }
            return $solarHijriDate;
        });

        $dateRange = Cache::remember($cacheKeys['date_range'], $cacheTTL, function() use ($firstDate, $lastDate) {
            return $this->getBooksCountByYear($firstDate, $lastDate);
        });


        Debugbar::stopMeasure('controller_start');
        Debugbar::addMessage('Controller method completed', 'info');

        $debugData = Debugbar::getData();

        Log::info('Debug Data:', $debugData);


        return response([
            'msg' => 'success',
            'debug' => $debugData,
            'data' => [
                'data for ten past date' => $tenPastDay,
                'first date for rangeData' => $firstDate,
                'last date for rangeData' => $lastDate,
                'data for this 2 range of year' => $dateRange,
                'year for creators_total price' => $dateForCreators_totalPrice,
                'data for creators_total price' => $dfc_price,
                'year for creators_total circulation' => $dateForCreators_totalCirculation,
                'data for creators_total circulation' => $dfc_circulation,
                'year for publishers_total price' => $dateForPublishers_totalPrice,
                'data for publishers_total price' => $dfp_price,
                'year for publishers_total circulation' => $dateForPublishers_totalCirculation,
                'date for publishers_total circulation' => $dfp_circulation
            ],
            'status' => 200
        ], 200);
    }

    public function noCache(Request $request)
    {
        Debugbar::startMeasure('controller_start', 'Controller method start');

        $data = [];
        $firstDate = (isset($request['firstDate']) and !empty($request['firstDate'])) ? intval($request->input('firstDate')) : 1385;
        $lastDate = (isset($request['lastDate']) and !empty($request['lastDate'])) ? intval($request->input('lastDate')) : 1403;
        $dateForCreators_totalPrice = (isset($request['dfc_price']) and !empty($request['dfc_price'])) ? intval($request->input('dfc_price')) : 1403;
        $dateForCreators_totalCirculation = (isset($request['dfc_circulation']) and !empty($request['dfc_circulation'])) ? intval($request->input('dfc_circulation')) : 1403;
        $dateForPublishers_totalPrice = (isset($request['dfp_price']) and !empty($request['dfp_price'])) ? intval($request->input('dfp_price')) : 1403;
        $dateForPublishers_totalCirculation = (isset($request['dfp_circulation']) and !empty($request['dfp_circulation'])) ? intval($request->input('dfp_circulation')) : 1403;
        $array = [];
        if (!Cache::get('ten_past_day')) {
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
            foreach ($tenPastDay as $day) {
                $array = [
                    'ForDate' => Jalalian::fromFormat('Y-m-d', $day->ForDate)->format('m-d'),
                    'BookCount' => $day->BookCount
                ];
            }
            Cache::put('ten_past_day', $array, 300);
        }

        foreach ($this->getBooksCountByYear($firstDate, $lastDate) as $book) {
            $data [] = $book;
        };

        foreach ($this->getTopCreatorsAccordingToValue($dateForCreators_totalCirculation, 'total_page') as $book) {
            $data [] = $book;
        };
        foreach ($this->getTopCreatorsAccordingToValue($dateForCreators_totalPrice, 'total_price') as $book) {
            $data [] = $book;
        }

        foreach ($this->getTopPublishersAccordingToValue($dateForPublishers_totalCirculation, 'total_page') as $book) {
            $data [] = $book;
        }

        foreach ($this->getTopPublishersAccordingToValue($dateForPublishers_totalPrice, 'total_price') as $book) {
            $data [] = $book;
        }


        Debugbar::stopMeasure('controller_start');
        Debugbar::addMessage('Controller method completed', 'info');

        $debugData = Debugbar::getData();

        Log::info('Debug Data:', $debugData);


        return response([
            'msg' => 'success',
            'debug' => $debugData,
            'data' => [$data,Cache::get('ten_past_day')],
            'status' => 200
        ], 200);
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
