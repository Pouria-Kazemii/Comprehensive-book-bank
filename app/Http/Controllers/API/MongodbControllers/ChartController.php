<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrDaily;
use App\Models\MongoDBModels\BPA_Yearly;
use App\Models\MongoDBModels\BTC_Yearly;
use App\Models\MongoDBModels\BTCi_Yearly;
use App\Models\MongoDBModels\BTP_Yearly;
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

        // Fetch or compute cache values
        $dfp_circulation =TCP_Yearly::where('year', $dateForPublishers_totalCirculation)->first();

        $dfp_price   = TPP_Yearly::where('year' , $dateForPublishers_totalPrice)->first();

        $dfc_circulation = TCC_Yearly::where('year' , $dateForCreators_totalCirculation)->first();

        $dfc_price = TPC_Yearly::where('year' , $dateForCreators_totalPrice)->first();

        $dataForTenPastDayBookInserted = $this->getLastTenDayBooks();

        $dateRangeCount = BTC_Yearly::where('year', '<=' , $lastDateForCount)->where('year','>=' , $firstDateForCount)->get();
        foreach ($dateRangeCount as $item) {
            $dataForRangeCount [] =[
                'year' => $item->year,
                'count' => $item->count
            ];
        }
        $dateRangePrice = BTP_Yearly::where('year' , '<=' , $lastDateForPrice)->where('year', '>=' ,$firstDateForPrice)->get();
        foreach ($dateRangePrice as $item){
            $dataForRangePrice [] =[
                'year' => $item->year,
                'price' => $item->price
            ];
        }

        $dateRangeCirculation = BTCi_Yearly::where('year' , '<=' , $lastDateForCirculation)->where('year' , '>=' ,$firstDateForCirculation)->get();
        foreach ($dateRangeCirculation as $item){
            $dataForRangeCirculation [] =[
                'year' => $item->year,
                'circulation' => $item->circulation
            ];
        }

        $dateRangeAverage = BPA_Yearly::where('year','<=' , $lastDateForAverage)->where('year' , '>=' , $firstDateForAverage)->get();
        foreach ($dateRangeAverage as $item){
            $dataForRangeAverage [] =[
                'year' => $item->year,
                'average' => $item->average
            ];
        }

        $end = microtime(true);
        $elapsedTime = $end - $start;
        return response([
            'msg' => 'success',
            'data' => [
                'data_for_ten_past_new_books' => $dataForTenPastDayBookInserted,

                'start_year_for_average_books_price' => $firstDateForAverage,
                'end_year_for_average_books_price' => $lastDateForAverage,
                'data_for_average_books_price' => $dataForRangeAverage,

                'start_year_for_count_books' => $firstDateForCount,
                'end_year_for_count_books' => $lastDateForCount,
                'data_for_count_books' => $dataForRangeCount,

                'start_year_for_price_books' => $firstDateForPrice,
                'end_year_for_price_books' => $lastDateForPrice,
                'data_for_price_books' => $dataForRangePrice,

                'start_year_for_circulation_books' => $firstDateForCirculation,
                'end_year_for_circulation_books' => $lastDateForCirculation,
                'data_for_circulation_books' => $dataForRangeCirculation,

                'year_for_creators_total_price' => $dateForCreators_totalPrice,
                'data-for_creators_total_price' => $dfc_price->creators,

                'year_for_creators_total_circulation' => $dateForCreators_totalCirculation,
                'data_for_creators_total_circulation' => $dfc_circulation->creators,

                'year_for_publishers_total_price' => $dateForPublishers_totalPrice,
                'data_for_publishers_total_price' => $dfp_price->publishers,

                'year_for_publishers_total_circulation' => $dateForPublishers_totalCirculation,
                'date_for_publishers_total_circulation' => $dfp_circulation->publishers
            ],
            'status' => 200 ,
            'time' => $elapsedTime,
        ], 200);
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
