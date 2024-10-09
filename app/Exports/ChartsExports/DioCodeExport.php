<?php

namespace App\Exports\ChartsExports;

use App\Models\MongoDBModels\BookDioCachedData;
use App\Models\MongoDBModels\DioSubject;
use App\Models\MongoDBModels\TCC_Yearly;
use App\Models\MongoDBModels\TCP_Yearly;
use App\Models\MongoDBModels\TPC_Yearly;
use App\Models\MongoDBModels\TPP_Yearly;

class DioCodeExport extends Export
{
    public int $id;
    public int $startYear;
    public int $endYear;
    public int $topYear;

    public function __construct($id,$startYear,$endYear,$topYear)
    {
        $currentYear = getYearNow();
        $this->id = $id;
        $startYear != 0 ? $this->startYear = $startYear : $this->startYear = $currentYear-10 ;
        $endYear != 0 ? $this->endYear = $endYear : $this->endYear = $currentYear;
        $topYear != 0 ? $this->topYear = $topYear:$this->topYear = $currentYear;
    }

    public function initial()
    {
        $totalCirculationTopDonate = [['subject' , 'circulation']];
        $totalPriceTopDonate= [['subject' , 'price']];
        $totalCountTopDonate= [['subject' , 'book_count']];
        $totalPageTopDonate= [['subject' , 'pages']];
        $paragraphTopDonate= [['subject' , 'paragraph']];
        $totalCirculationBottomDonate= [['subject' , 'circulation']];
        $totalPriceBottomDonate= [['subject' , 'price']];
        $totalCountBottomDonate= [['subject' , 'book_count']];
         $totalPageBottomDonate= [['subject' , 'pages']];
        $paragraphBottomDonate = [['subject' , 'paragraph']];

        if ($this->id != 0) {
            $topBoxData = BookDioCachedData::where('year', 0)->where('dio_subject_id', $this->id)->first();
            $topBoxTotalPrice = $topBoxData->total_price;
            $topBoxTotalCirculation = $topBoxData->total_circulation;
            $topBoxTotalPages = $topBoxData->total_pages;
            $topBoxTotalCount = $topBoxData->count;
            $topBoxParagraph = $topBoxData->paragraph;
            $topBoxTon = $topBoxData->paragraph * 25 / 1000;

            $bottomBoxAndChartData = BookDioCachedData::where('dio_subject_id', $this->id)
                ->where('year', '<=', $this->endYear)
                ->where('year', '>=', $this->startYear)
                ->get();


            $topChartData =BookDioCachedData::where('year', $this->topYear)->where('dio_subject_id' , $this->id)->first();
            $dataForPublisherCirculation = $this->getAttribute($topChartData->top_circulation_publishers , 'publisher_name' , 'total_page');
            $dataForPublisherPrice = $this->getAttribute($topChartData->top_price_publishers ,'publisher_name','total_price');
            $dataForCreatorCirculation = $this->getAttribute($topChartData->top_circulation_creators,'creator_name','total_page');
            $dataForCreatorPrice = $this->getAttribute($topChartData->top_price_creators,'creator_name','total_price');

        } else {
            $topBoxData = BookDioCachedData::where('year', 0)
                ->where(function ($query){
                    $query->where('dio_subject_id' , 1)
                        ->orWhere('dio_subject_id' , 2)
                        ->orWhere('dio_subject_id',3)
                        ->orWhere('dio_subject_id',4);
                })
                ->get();
            $topBoxTotalPrice = $topBoxData->sum('total_price');
            $topBoxTotalCirculation = $topBoxData->sum('total_circulation');
            $topBoxTotalPages = $topBoxData->sum('total_pages');
            $topBoxTotalCount = $topBoxData->sum('count');
            $topBoxParagraph = $topBoxData->sum('paragraph');
            $topBoxTon = $topBoxParagraph * 20 / 1000;

            $bottomBoxAndChartData = BookDioCachedData::raw(function ($collection) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'year' => [
                                '$lte' => $this->endYear,
                                '$gte' => $this->startYear
                            ],
                            '$or' => [
                                ['dio_subject_id' => 1],
                                ['dio_subject_id' => 2],
                                ['dio_subject_id' => 3],
                                ['dio_subject_id' => 4]
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
                    ],
                    ['$sort' => ['_id' => 1]]
                ]);
            });

            $dfp_circulation =TCP_Yearly::where('year', $this->topYear)->first();
            $dataForPublisherCirculation = $this->getAttribute($dfp_circulation->publishers,'publisher_name','total_page')['arrData'];

            $dfp_price   = TPP_Yearly::where('year' , $this->topYear)->first();
            $dataForPublisherPrice = $this->getAttribute($dfp_price->publishers , ' publisher_name' , 'total_price')['arrData'];

            $dfc_circulation = TCC_Yearly::where('year' , $this->topYear)->first();
            $dataForCreatorCirculation = $this->getAttribute($dfc_circulation->creators , 'creator_name' , 'total_page')['arrData'];

            $dfc_price = TPC_Yearly::where('year' , $this->topYear)->first();
            $dataForCreatorPrice = $this->getAttribute($dfc_price->creators,'creator_name' , 'total_price')['arrData'];
        }

        $donateIds = DioSubject::where('parent_id' , $this->id)->pluck('id_by_law');
        foreach ($donateIds as $donateId){
            $topDonateValue = BookDioCachedData::where('year',0)->where('dio_subject_id',$donateId)->first();
            $totalCirculationTopDonate [] = [$topDonateValue->dio_subject_title , $topDonateValue->total_circulation];
            $totalPriceTopDonate [] = [$topDonateValue->dio_subject_title , $topDonateValue->total_price];
            $totalCountTopDonate [] = [$topDonateValue->dio_subject_title , $topDonateValue->count];
            $totalPageTopDonate [] = [$topDonateValue->dio_subject_title ,$topDonateValue->total_pages];
            $paragraphTopDonate [] = [ $topDonateValue->dio_subject_title , $topDonateValue->paragraph];

            $botDonateValue = BookDioCachedData::where('dio_subject_id' , $donateId)
                ->where('year', '<=' , $this->endYear)
                ->where('year','>=' , $this->startYear)
                ->get();

            $totalCirculationBottomDonate [] = [ $topDonateValue->dio_subject_title ,  $botDonateValue->sum('total_circulation')];
            $totalPriceBottomDonate [] = [$topDonateValue->dio_subject_title , $botDonateValue->sum('total_price')];
            $totalCountBottomDonate [] = [$topDonateValue->dio_subject_title ,  $botDonateValue->sum('count')];
            $totalPageBottomDonate [] = [ $topDonateValue->dio_subject_title , $botDonateValue->sum('total_pages')];
            $paragraphBottomDonate [] = [$topDonateValue->dio_subject_title ,$botDonateValue->sum('paragraph')];
        }

            $totalPage = $this->getAttribute($bottomBoxAndChartData,$this->id != 0 ?'year' : '_id','total_pages',true,true);
            $totalPageChart = $totalPage['arrData'];
            $pagesBottomBox = $totalPage['intData'];

            $totalPage = $this->getAttribute($bottomBoxAndChartData,$this->id != 0 ?'year' : '_id','paragraph',true,true);
            $paragraphChart = $totalPage['arrData'];
            $paragraphBottomBox = $totalPage['intData'];

            $totalPage = $this->getAttribute($bottomBoxAndChartData,$this->id != 0 ?'year' : '_id','average',false,true);
            $averageChart = $totalPage['arrData'];

            $totalPage = $this->getAttribute($bottomBoxAndChartData,$this->id != 0 ?'year' : '_id','total_price',true,true);
            $totalPriceChart = $totalPage['arrData'];
            $priceBottomBox = $totalPage['intData'];

            $totalPage = $this->getAttribute($bottomBoxAndChartData,$this->id != 0 ?'year' : '_id','count',true,true);
            $totalCountChart = $totalPage['arrData'];
            $countBottomBox = $totalPage['intData'];

            $totalPage = $this->getAttribute($bottomBoxAndChartData,$this->id != 0 ?'year' : '_id','total_circulation',true,true);
            $totalCirculationChart = $totalPage['arrData'];
            $circulationBottomBox = $totalPage['intData'];

        $startYear = convertToPersianNumbersPure($this->startYear);
        $endYear = convertToPersianNumbersPure($this->endYear);
        $topYear = convertToPersianNumbersPure($this->topYear);

        $box =[
            [
                'title_fa' => "جمع تعداد کتاب ها از ابتدا تا کنون",
                'value' => convertToPersianNumbers($topBoxTotalCount)
            ],
            [
                'title_fa' => "مجموع تیراژ از ابتدا تا کنون",
                'value' => convertToPersianNumbers($topBoxTotalCirculation)
            ],
            [
                'title_fa' => "جمع مالی از ابتدا تا کنون",
                'value' => convertToPersianNumbers($topBoxTotalPrice)
            ],
            [
                'title_fa' => "مجموع وزن کاغذ مصرفی از ابتدا تا کنون(بر حسب تن - کاغذ ۷۰ گرمی)",
                'value' => convertToPersianNumbers(round($topBoxTon))
            ],
            [
                'title_fa' => "جمع بند کاغذ مصرفی از ابتدا تا کنون",
                'value' => convertToPersianNumbers(round($topBoxParagraph))
            ],
            [
                'title_fa' => "مجموع صفحات چاپ شده از ابتدا تا کنون",
                'value' => convertToPersianNumbers($topBoxTotalPages)
            ],[
                'title_fa' => "مجموع تعداد کتاب از سال $startYear تا سال $endYear",
                'value' => convertToPersianNumbers($countBottomBox)
            ],

            [
                'title_fa' => "مجموع تیراژ از سال $startYear تا سال $endYear",
                'value' => convertToPersianNumbers($circulationBottomBox)
            ],
            [
                'title_fa' => "جمع مالی از سال $startYear تا سال $endYear",
                'value' => convertToPersianNumbers($priceBottomBox)
            ],
            [
                'title_fa' => "مجموع بند کاغذ مصرفی از سال $startYear تا سال $endYear",
                'value' => convertToPersianNumbers(round($paragraphBottomBox))
            ],
            [
                'title_fa' => "مجموع صفحات چاپ شده از سال $startYear تا سال $endYear",
                'value' => convertToPersianNumbers($pagesBottomBox)
            ],
        ];

        $charts = [
            [
                "label" => "نمودار میانگین قیمت از سال $startYear تا سال $endYear",
                'data' => $averageChart
            ],
            [
                "label" => "نمودار مجموع تعداد کتاب  از سال $startYear تا سال $endYear",
                'data' => $totalCountChart
            ],
            [
                "label" => "نمودار مجموع تیراژ  از سال $startYear تا سال $endYear",
                'data' => $totalCirculationChart
            ],
            [
                "label" => "نمودار جمع مالی  از سال $startYear تا سال $endYear",
                'data' => $totalPriceChart
            ],
            [
                "label" => " نمودار بند کاغذ مصرفی از سال $startYear تا سال $endYear",
                'data' => $paragraphChart
            ],
            [
                "label" => "نمودار مجموع صفحات چاپ شده از سال $startYear تا سال $endYear",
                'data' => $totalPageChart
            ],
            [
                'label' => "نمودار پدیدآورندگان برتر بر حسب جمع مالی در سال $topYear",
                'data' => $dataForCreatorPrice
            ],
            [
                'label' => "نمودار پدیدآورندگان برتر بر حسب مجموع تیراژ در سال $topYear",
                'data' => $dataForCreatorCirculation
            ],
            [
                'label' => "نمودار انتشارات برتر بر حسب جمع مالی در سال $topYear",
                'data' => $dataForPublisherPrice
            ],
            [
                'label' => "نمودار انتشارات برتر بر حسب مجموع تیراژ در سال $topYear",
                'data' => $dataForPublisherCirculation
            ],
        ];

        $donates = [
            [
                "label" => "نمودار مجموع تعداد کتاب  از سال $startYear تا سال $endYear",
                'data' => $totalCountBottomDonate
            ],
            [
                "label" => "نمودار مجموع تیراژ  از سال $startYear تا سال $endYear",
                'data' => $totalCirculationBottomDonate
            ],
            [
                "label" => "نمودار جمع مالی  از سال $startYear تا سال $endYear",
                'data' => $totalPriceBottomDonate
            ],
            [
                "label" => " نمودار بند کاغذ مصرفی از سال $startYear تا سال $endYear",
                'data' => $paragraphBottomDonate
            ],
            [
                "label" => "نمودار مجموع صفحات چاپ شده از سال $startYear تا سال $endYear",
                'data' => $totalPageBottomDonate
            ],
            [
                'label' => "نمودار جمع تعداد کتاب ها از ابتدا تا کنون",
                'data' => $totalCountTopDonate
            ],
            [
                'label' => "نمودار مجموع تیراژ از ابتدا تا کنون",
                'data' => $totalCirculationTopDonate
            ],
            [
                'label' => "نمودار جمع مالی از ابتدا تا کنون",
                'data' => $totalPriceTopDonate
            ],
            [
                'label' => "نمودار جمع بند کاغذ مصرفی از ابتدا تا کنون",
                'data' => $paragraphTopDonate
            ],
            [
                'label' => "نمودار مجموع صفحات چاپ شده از ابتدا تا کنون",
                'data' => $totalPageTopDonate
            ],
        ];
        $this->allData = [
          'charts' => $charts,
          'boxes' => $box,
          'donates' => $donates
        ];
    }
}
