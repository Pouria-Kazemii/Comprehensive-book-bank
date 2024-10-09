<?php

namespace App\Exports\ChartsExports;

use App\Models\MongoDBModels\PublisherCacheData;

class PublisherExport extends Export
{
     public string $publisherId;
     public int $startYear;
     public int $endYear;

     public function __construct($publisherId,$startYear,$endYear)
     {
         $currentYear = getYearNow();
         $this->publisherId = $publisherId;
         $startYear != 0 ? $this->startYear = $startYear : $this->startYear = $currentYear-10;
         $endYear != 0 ? $this->endYear = $endYear : $this->endYear = $currentYear;
     }

    public function initial()
    {
        $allTime = PublisherCacheData::where('publisher_id', $this->publisherId)->where('year', 0)->first();

        $totalData = PublisherCacheData::where('publisher_id', $this->publisherId)->where('year', '<=', $this->endYear)->where('year', '>=', $this->startYear)->get();

        $price = $this->getAttribute($totalData ,'year','total_price',true,true );
        $dataPrice = $price['arrData'];
        $sumPriceRange = $price['intData'];

        $average = $this->getAttribute($totalData,'year','average');
        $dataAverage = $average['arrData'];

        $count =$this->getAttribute($totalData ,'year','count',true,true );
        $dataCount = $count['arrData'];
        $sumCountRange = $count['intData'];

        $paragraph = $this->getAttribute($totalData ,'year','paragraph',true,true );
        $dataParagraph = $paragraph['arrData'];
        $sumParagraphRange = $paragraph['intData'];

        $circulation = $this->getAttribute($totalData ,'year','total_circulation',true,true );
        $dataCirculation = $circulation['arrData'];
        $sumCirculationRange = $circulation['intData'];

        $pages = $this->getAttribute($totalData ,'year','total_pages',true,true );
        $dataPages = $pages['arrData'];
        $sumPagesRange = $pages['intData'];

        $startYear = convertToPersianNumbersPure($this->startYear);
        $endYear = convertToPersianNumbersPure($this->endYear);

        $box = [
            [
                'title_fa' => 'مجموع کتاب ها از ابتدا تا کنون',
                'value' => convertToPersianNumbers($allTime->count)

            ],
            [
                'title_fa' => 'مجموع تیراژ از ابتدا تا کنون',
                'value' => convertToPersianNumbers($allTime->total_circulation)
            ],
            [
                'title_fa' => 'جمع مالی از ابتدا تا کنون',
                'value' => convertToPersianNumbers($allTime->total_price)
            ] ,
            [
                'title_fa' => 'مجموع بند کاغذ مصرفی از ابتدا تا کنون',
                'value' => convertToPersianNumbers(round($allTime->paragraph))
            ],
            [
                'title_fa' => 'مجموع صفحات چاپ شده از ابتدا تا کنون',
                'value' => convertToPersianNumbers($allTime->total_pages)
            ],
            [
                'title_fa' => "مجموع تعداد کتاب از سال $startYear تا سال  $endYear",
                'value' => convertToPersianNumbers($sumCountRange)
            ],
            [
                'title_fa' => "مجموع تیراژ از سال $startYear تا سال $endYear",
                'value' => convertToPersianNumbers($sumCirculationRange)

            ],
            [
                'title_fa' => "جمع مالی از سال $startYear تا سال $endYear",
                'value' => convertToPersianNumbers($sumPriceRange)
            ],
            [
                'title_fa' => "مجموع بند کاغذ مصرفی از سال $startYear تا سال $endYear",
                'value' => convertToPersianNumbers(round($sumParagraphRange))
            ],
            [
                'title_fa' => "مجموع صفحات چاپ شده از سال $startYear تا سال $endYear",
                'value' => convertToPersianNumbers($sumPagesRange)
            ],
        ];

        $charts = [
            [
                'label'=> "نمودار میانگین قیمت از سال $startYear تا سال $endYear",
                'data' => $dataAverage
            ],
            [
                'label'=> "نمودار تعداد کتاب از سال $startYear تا سال $endYear",
                'data' => $dataCount,
            ],
            [
                'label' =>"نمودار مجموع تیراژ از سال $startYear تا سال $endYear",
                'data' => $dataCirculation
            ],
            [
                'label'=> "نمودار جمع مالی از سال $startYear تا سال $endYear" ,
                'data' => $dataPrice
            ],
            [
                'label' => "نمودار بند کاغذ مصرفی از سال $startYear تا سال $endYear",
                'data' => $dataParagraph
            ],
            [
                'label' => "نمودار مجموع صفحات از سال $startYear تا سال $endYear",
                'data' => $dataPages,
            ],
        ];

        $this->allData = [
            'charts' => $charts,
            'boxes' => $box
        ];
    }
}
