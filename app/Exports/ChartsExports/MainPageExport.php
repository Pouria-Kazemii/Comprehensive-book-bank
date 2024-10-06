<?php

namespace App\Exports\ChartsExports;

use App\Models\MongoDBModels\BTB_Yearly;
use App\Models\MongoDBModels\BTC_Yearly;
use App\Models\MongoDBModels\BTCi_Yearly;
use App\Models\MongoDBModels\BTP_Yearly;
use App\Models\MongoDBModels\BTPa_Yearly;
use App\Models\MongoDBModels\TCC_Yearly;
use App\Models\MongoDBModels\TCP_Yearly;
use App\Models\MongoDBModels\TPC_Yearly;
use App\Models\MongoDBModels\TPP_Yearly;

class MainPageExport  extends Export
{
    public $startYear;
    public $endYear;
    public  $topYear;

    public function __construct($firstYear , $endYear ,$topYear)
    {
        $this->startYear = $firstYear;
        $this->endYear = $endYear;
        $this->topYear = $topYear;
    }

    public function initial()
    {
        $allTimesPage = BTPa_Yearly::sum('total_pages');
        $allTimesPrice = BTP_Yearly::sum('price');
        $allTimesCount = BTC_Yearly::sum('count');
        $allTimesCirculation = BTCi_Yearly::sum('circulation');
        $allTimesParagraph = BTB_Yearly::sum('paragraph');
        $allTimesTon = $allTimesParagraph * 25 / 1000;

        // Fetch or compute cache values
//        $dataForTenPastDayBookInserted = $this->getLastTenDayBooks();
        $dfp_circulation = TCP_Yearly::where('year', $this->topYear)->first();
        $dataForPublisherCirculation = $this->getAttribute($dfp_circulation->publishers, 'publisher_name', 'total_page')['arrData'];


        $dfp_price = TPP_Yearly::where('year', $this->topYear)->first();
        $dataForPublisherPrice = $this->getAttribute($dfp_price->publishers, 'publisher_name', 'total_price')['arrData'];

        $dfc_circulation = TCC_Yearly::where('year', $this->topYear)->first();
        $dataForCreatorCirculation = $this->getAttribute($dfc_circulation->creators, 'creator_name', 'total_page')['arrData'];

        $dfc_price = TPC_Yearly::where('year', $this->topYear)->first();
        $dataForCreatorPrice = $this->getAttribute($dfc_price->creators, 'creator_name', 'total_price')['arrData'];

        $dataRangePage = BTPa_Yearly::where('year', '<=', $this->endYear)->where('year', '>=', $this->startYear)->get();
        $drp = $this->getAttribute($dataRangePage, 'year', 'total_pages', true, true);
        $dataForRangePage = $drp['arrData'];
        $sumPageRange = $drp['intData'];

        $dataRangeParagraph = BTB_Yearly::where('year', '<=', $this->endYear)->where('year', '>=', $this->startYear)->get();
        $drpa = $this->getAttribute($dataRangeParagraph, 'year', 'paragraph', true, true);
        $dataForRangeParagraph = $drpa['arrData'];
        $sumParagraphRange = $drpa['intData'];


        $dateRangeCount = BTC_Yearly::where('year', '<=', $this->endYear)->where('year', '>=', $this->startYear)->get();
        $drc = $this->getAttribute($dateRangeCount, 'year', 'count', true, true);
        $dataForRangeCount = $drc['arrData'];
        $sumCountRange = $drc['intData'];

        $dateRangePrice = BTP_Yearly::where('year', '<=', $this->endYear)->where('year', '>=', $this->startYear)->get();
        $drpr = $this->getAttribute($dateRangePrice, 'year', 'price', true, true);
        $dataForRangePrice = $drpr['arrData'];
        $sumPriceRange = $drpr['intData'];

        $dateRangeCirculation = BTCi_Yearly::where('year', '<=', $this->endYear)->where('year', '>=', $this->startYear)->get();
        $drci = $this->getAttribute($dateRangeCirculation, 'year', 'circulation', true, true);
        $dataForRangeCirculation = $drci['arrData'];
        $sumCirculationRange = $drci['intData'];


        $startYear = convertToPersianNumbersPure($this->startYear);
        $endYear = convertToPersianNumbersPure($this->endYear);
        $topYear = convertToPersianNumbersPure($this->topYear);

        $box [] = [
            'title_fa' => "مجموع تعداد کتاب ها از ابتدا تا کنون",
            'value' => ($allTimesCount)
        ];
        $box [] =
            [
                'title_fa' => "مجموع تیراژ از ابتدا تا کنون",
                'value' => ($allTimesCirculation)
            ];
        $box [] =
            [
                'title_fa' => "جمع مالی کتاب ها از ابتدا تا کنون",

                'value' => ($allTimesPrice)
            ];
        $box [] =
            [
                'title_fa' => "مجموع وزن کاغذ مصرفی از ابتدا تا کنون(بر حسب تن - کاغذ ۷۰ گرمی)",
                'value' => (round($allTimesTon))
            ];
        $box [] =
            [
                'title_fa' => "مجموع بند کاغذ مصرفی از ابتدا تا کنون",
                'value' => (round($allTimesParagraph))
            ];
        $box [] =
            [
                'title_fa' => "مجموع صفحات چاپ شده از ابتدا تا کنون",
                'value' => ($allTimesPage)
            ];


        $box [] =
            [
                'title_fa' => "مجموع تعداد کتاب از سال $startYear تا سال $endYear",
                'value' => ($sumCountRange)
            ];
        $box [] =
            [
                'title_fa' => "مجموع تیراژ از سال $startYear تا سال $endYear",
                'value' => ($sumCirculationRange)
            ];
        $box [] =
            [
                'title_fa' => "جمع مالی از سال $startYear تا سال $endYear",
                'value' => ($sumPriceRange)
            ];
        $box [] =
            [
                'title_fa' => "مجموع بند کاغذ مصرفی از سال $startYear تا سال $endYear",
                'value' => (round($sumParagraphRange))
            ];
        $box [] =
            [
                'title_fa' => "مجموع صفحات چاپ شده از سال $startYear تا سال $endYear",
                'value' => ($sumPageRange)
            ];
        $charts = [
            [
                'label' => "نمودار مجموع تعداد کتاب  از سال $startYear تا سال $endYear",
                'data' => $dataForRangeCount
            ],
            [
                'label' => "نمودار مجموع تیراژ  از سال $startYear تا سال $endYear",
                'data' => $dataForRangeCirculation
            ],
            [
                'label' => "نمودار جمع مالی  از سال $startYear تا سال $endYear",
                'data' => $dataForRangePrice
            ],
            [
                'label' => " نمودار بند کاغذ مصرفی از سال $startYear تا سال $endYear",
                'data' => $dataForRangeParagraph
            ],
            [
                'label' => "نمودار مجموع صفحات چاپ شده از سال $startYear تا سال $endYear",
                'data' => $dataForRangePage
            ],
        ];

        $charts [] = [
            'label' => "نمودار پدیدآورندگان برتر بر حسب جمع مالی در سال $topYear",
            'data' => $dataForCreatorPrice
        ];
        $charts [] = [
            'label' => "نمودار پدیدآورندگان برتر بر حسب مجموع تیراژ در سال $topYear",
            'data' => $dataForCreatorCirculation
        ];
        $charts [] = [
            'label' => "نمودار انتشارات برتر بر حسب جمع مالی در سال $topYear",
            'data' => $dataForPublisherPrice
        ];
        $charts [] = [
            'label' => "نمودار انتشارات برتر بر حسب مجموع تیراژ در سال $topYear",
            'data' => $dataForPublisherCirculation
        ];

        $this->allData = [
            'charts' => $charts,
            'boxes' => $box
        ];
    }
}
