<?php

namespace App\Exports;

use App\Models\BookKetabrah;
use App\Models\WebSiteBookLinksDefects;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ContradictionsKetabrahExport implements FromCollection, WithHeadings
{
    public function __construct($excel_type, $status, $excel_id, $saveInWebsiteBooklinksDefects = 0)
    {
        $this->excel_type = $excel_type;
        $this->status = $status;
        $this->excel_id = $excel_id;
        $this->saveInWebsiteBooklinksDefects = $saveInWebsiteBooklinksDefects;
    }
    public function collection()
    {
        $excel_type = $this->excel_type;
        $status = $this->status;
        $excel_id = $this->excel_id;
        $saveInWebsiteBooklinksDefects = $this->saveInWebsiteBooklinksDefects;
        $data = array();
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        if ($excel_type == 'unallowed') {
            $report = BookKetabrah::select('recordNumber', 'title', 'nasher', 'saleNashr', 'tedadSafe', 'shabak', 'translate', 'lang', 'price', 'check_status',  'tags','has_permit', 'images', 'id', 'unallowed')->where('title', '!=', NULL)->whereIN('unallowed',  $status)->get();
            foreach ($report as $key => $item) {
                $report[$key]->translate = ($item->translate == 1) ? 'ترجمه': 'تالیف';

                if ($item->fileSize != NULL and $item->price != NULL) {
                    $report[$key]->fileSize = 'چاپی و الکترونیکی';
                } elseif ($item->fileSize != NULL and $item->price == 0) {
                    $report[$key]->fileSize = 'الکترونیکی';
                } elseif ($item->fileSize == NULL and $item->price > 0) {
                    $report[$key]->fileSize = 'چاپی';
                } else {
                    $report[$key]->fileSize = 'ناموجود';
                }
                $report[$key]->main_recordNumber =  $item->recordNumber;
                $report[$key]->recordNumber = 'https://www.ketabrah.ir/book_name/book/'.$item->recordNumber;
            }
            if ($saveInWebsiteBooklinksDefects == 1) {
                foreach ($report as $key => $item) {
                    $bugId = siteBookLinkDefects($report[$key]->check_status, $report[$key]->has_permit);
                    $report[$key]->recordNumber = 'https://www.ketabrah.ir/book_name/book/'.$item->recordNumber;
                    WebSiteBookLinksDefects::create(array('siteName' => 'shahreketabonline', 'book_links' => $item->recordNumber, 'bookId' => $item->main_recordNumber, 'bugId' => $bugId, 'old_check_status' => $item->check_status, 'old_has_permit' => $item->has_permit, 'old_unallowed' => $item->unallowed, 'excelId' => $excel_id));
                }
            }
        } elseif (($excel_type == 'withoutIsbn') or ($excel_type == 'withIsbn')) {
            $report = BookKetabrah::select('recordNumber', 'title', 'nasher', 'saleNashr', 'tedadSafe', 'shabak', 'translate', 'lang', 'price', 'check_status', 'tags', 'has_permit', 'images')->where('title', '!=', NULL)->whereIN('has_permit',  $status)->whereIN('check_status', $status)->get();
            foreach ($report as $key => $item) {
                $report[$key]->translate = ($item->translate == 1) ? 'ترجمه': 'تالیف';

                if ($item->fileSize != NULL and $item->price != NULL) {
                    $report[$key]->fileSize = 'چاپی و الکترونیکی';
                } elseif ($item->fileSize != NULL and $item->price == 0) {
                    $report[$key]->fileSize = 'الکترونیکی';
                } elseif ($item->fileSize == NULL and $item->price > 0) {
                    $report[$key]->fileSize = 'چاپی';
                } else {
                    $report[$key]->fileSize = 'ناموجود';
                }

                $report[$key]->tags = '';
                if ($item->check_status == 2) {
                    if ((isset($item->saleNashr) and $item->saleNashr != null and !empty($item->saleNashr))) {
                        $georgianCarbonDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $item->saleNashr)->toCarbon();
                        if (strtotime($georgianCarbonDate) > strtotime('2022-03-21 00:00:00')) {
                            $report[$key]->tags = '*';
                        }
                    }
                }
                if ($item->check_status == 1) {
                    $report[$key]->check_status = 'کتاب در خانه کتاب وجود دارد';
                } elseif ($item->check_status == 2) {
                    $report[$key]->check_status = 'کتاب در خانه کتاب وجود ندارد';
                } elseif ($item->check_status == 3) {
                    $report[$key]->check_status = 'جستجو نشده به دلیل محدودیت سال انتشار';
                } elseif ($item->check_status == 4) {
                    $report[$key]->check_status = 'کتاب شابک ندارد';
                }


                $report[$key]->images = '';
                if ($item->has_permit == 2) {
                    if ((isset($item->saleNashr) and $item->saleNashr != null and !empty($item->saleNashr))) {
                        $georgianCarbonDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $item->saleNashr)->toCarbon();
                        if (strtotime($georgianCarbonDate) < strtotime('2024-03-29 00:00:00')) {
                            $report[$key]->images = '**';
                        }
                    }
                }

                if ($item->has_permit == 1) {
                    $report[$key]->has_permit = 'کتاب در اداره کتاب وجود دارد';
                } elseif ($item->has_permit == 2) {
                    $report[$key]->has_permit = 'کتاب در اداره کتاب وجود ندارد';
                } elseif ($item->has_permit == 3) {
                    $report[$key]->has_permit = 'جستجو نشده به دلیل محدودیت سال انتشار';
                } elseif ($item->has_permit == 4) {
                    $report[$key]->has_permit = 'کتاب شابک ندارد';
                }


                // if(($item->has_permit == 2 OR $item->check_status == 2) and (isset($report[$key]->saleNashr) and $report[$key]->saleNashr != null and !empty($report[$key]->saleNashr))){
                //     $georgianCarbonDate=\Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $report[$key]->saleNashr)->toCarbon();
                //     if ($georgianCarbonDate < date('2022-03-21 00:00:00') OR $georgianCarbonDate > date('2024-03-29 00:00:00') ) {
                //         $report[$key]->images = '('.$item->saleNashr.' )جستجو نشده به دلیل محدودیت سال انتشار';
                //     }else{
                //         $report[$key]->images = '';
                //     }
                // }else{
                //     $report[$key]->images = '';
                // }




                $report[$key]->recordNumber = 'https://www.ketabrah.ir/book_name/book/'.$item->recordNumber;
            }
        }
        return $report;
    }

    public function headings(): array
    {
        return ["لینک کتاب در کتابراه", "عنوان کتاب", "ناشر", "تاریخ انتشار", "تعداد صفحه", "شابک", "تالیف یا ترجمه", "زبان", "قیمت", "وضعیت در خانه کتاب", "راهنمای خانه کتاب", "وضعیت در اداره کتاب", "راهنمای اداره کتاب"];
    }
}
