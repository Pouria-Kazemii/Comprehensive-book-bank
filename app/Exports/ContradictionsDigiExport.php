<?php

namespace App\Exports;

use App\Models\BookDigi;
use App\Models\WebSiteBookLinksDefects;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ContradictionsDigiExport implements FromCollection, WithHeadings
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
        // DB::enableQueryLog();
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        if ($excel_type == 'unallowed') {
            $report = BookDigi::select('pageUrl', 'title', 'nasher', 'tedadSafe','shabak', 'ghatechap', 'check_status', 'cats', 'has_permit', 'images', 'unallowed')->where('title', '!=', NULL)->whereIN('unallowed',  $status)->get();
            if ($saveInWebsiteBooklinksDefects == 1) {
                foreach ($report as $key => $item) {
                    $bugId = siteBookLinkDefects($report[$key]->check_status, $report[$key]->has_permit);
                    WebSiteBookLinksDefects::create(array('siteName' => 'digi', 'book_links' => $item->pageUrl, 'bookId' => $item->id, 'bugId' => $bugId, 'old_check_status' => $item->check_status, 'old_has_permit' => $item->has_permit, 'old_unallowed' => $item->unallowed, 'excelId' => $excel_id));
                }
            }
        } elseif (($excel_type == 'withoutIsbn') or ($excel_type == 'withIsbn')) {
            $report = BookDigi::select('recordNumber', 'title', 'nasher', 'tedadSafe', 'shabak', 'ghatechap', 'check_status', 'cat', 'has_permit', 'images')->where('title', '!=', NULL)->whereIN('has_permit',  $status)->whereIN('check_status', $status)->get();
            foreach ($report as $key => $item) {
                $report[$key]->cat = '';
                /*if ($item->check_status == 2) {
                    if ((isset($item->saleNashr) and $item->saleNashr != null and !empty($item->saleNashr))) {
                        $georgianCarbonDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $item->saleNashr)->toCarbon();
                        if (strtotime($georgianCarbonDate) > strtotime('2022-03-21 00:00:00')) {
                            $report[$key]->cat = '*';
                        }
                    }
                }*/
                $report[$key]->check_status = checkStatusTitle($item->check_status);

                $report[$key]->images = '';
                /*if ($item->has_permit == 2) {
                    if ((isset($item->saleNashr) and $item->saleNashr != null and !empty($item->saleNashr))) {
                        $georgianCarbonDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $item->saleNashr)->toCarbon();
                        if (strtotime($georgianCarbonDate) < strtotime('2024-03-29 00:00:00')) {
                            $report[$key]->images = '**';
                        }
                    }
                }*/

                $report[$key]->has_permit = hasPermitTitle($item->has_permit);

                $bugId = siteBookLinkDefects($report[$key]->check_status, $report[$key]->has_permit);
                $report[$key]->recordNumber = 'https://www.digikala.com/product/' . $item->recordNumber . '/';
                WebSiteBookLinksDefects::create(array('siteName' => 'digikala', 'book_links' => 'https://www.digikala.com/product/' . $item->recordNumber . '/', 'recordNumber' => $item->recordNumber, 'bookId' => $item->id, 'bugId' => $bugId, 'excelId' => $excel_id));
            }
        }
        return $report;
    }

    public function headings(): array
    {
        return ["آیدی کتاب در دیجیکالا", "عنوان کتاب", "ناشر", "تعداد صفحه", "شابک", "قطع", "وضعیت در خانه کتاب", "راهنمای خانه کتاب", "وضعیت در اداره کتاب", "راهنمای اداره کتاب"];
    }
}
