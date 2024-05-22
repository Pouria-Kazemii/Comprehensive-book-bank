<?php

namespace App\Exports;

use App\Models\BookGisoom;
use App\Models\WebSiteBookLinksDefects;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ContradictionsGisoomExport implements FromCollection, WithHeadings
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
            $report = BookGisoom::select('recordNumber', 'title', 'nasher', 'tedadSafe', 'saleNashr', 'shabak10', 'shabak13', 'ghatechap', 'check_status', 'catText', 'has_permit', 'image', 'unallowed')->where('title', '!=', NULL)->whereIN('unallowed',  $status)->get();

            if ($saveInWebsiteBooklinksDefects == 1) {
                foreach ($report as $key => $item) {
                    $bugId = siteBookLinkDefects($report[$key]->check_status, $report[$key]->has_permit);
                    WebSiteBookLinksDefects::create(array('siteName' => 'gisoom', 'book_links' => 'https://www.gisoom.com/book/' . $item->recordNumber . '/book_name', 'recordNumber' => $item->recordNumber, 'bookId' => $item->id, 'bugId' => $bugId, 'old_check_status' => $item->check_status, 'old_has_permit' => $item->has_permit, 'old_unallowed' => $item->unallowed, 'excelId' => $excel_id));
                }
            }
        } elseif (($excel_type == 'withoutIsbn') or ($excel_type == 'withIsbn')) {


            $report = BookGisoom::select('recordNumber', 'title', 'nasher', 'tedadSafe', 'saleNashr', 'shabak10', 'shabak13', 'ghatechap', 'check_status', 'catText', 'has_permit', 'image', 'unallowed')->where('title', '!=', NULL)->whereIN('has_permit',  $status)->whereIN('check_status', $status)->get();
            foreach ($report as $key => $item) {
                $report[$key]->catText = '';
                if ($item->check_status == 2) {
                    if ((isset($item->saleNashr) and $item->saleNashr != null and !empty($item->saleNashr))) {
                        $georgianCarbonDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', substr($item->saleNashr, 0, 4) . '/01/01')->toCarbon();
                        if (strtotime($georgianCarbonDate) > strtotime('2022-03-21 00:00:00')) {
                            $report[$key]->catText = '*';
                        }
                    }
                }
                $report[$key]->main_check_status = $item->check_status;
                $report[$key]->check_status = checkStatusTitle($item->check_status);

                $report[$key]->image = '';
                if ($item->has_permit == 2) {
                    if ((isset($item->saleNashr) and $item->saleNashr != null and !empty($item->saleNashr))) {
                        $georgianCarbonDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', substr($item->saleNashr, 0, 4) . '/01/01')->toCarbon();
                        if (strtotime($georgianCarbonDate) < strtotime('2018-03-21 00:00:00')) {
                            $report[$key]->image = '**';
                        }
                    }
                }
                $report[$key]->main_has_permit = $item->has_permit;
                $report[$key]->has_permit = hasPermitTitle($item->has_permit);
                $report[$key]->main_recordNumber =  $item->recordNumber;
                $report[$key]->recordNumber = 'https://www.gisoom.com/book/' . $item->recordNumber . '/book_name';
                
                if ($saveInWebsiteBooklinksDefects == 1) {
                    $bugId = siteBookLinkDefects($report[$key]->check_status, $report[$key]->has_permit);
                    WebSiteBookLinksDefects::create(array('siteName' => 'gisoom', 'book_links' => $item->recordNumber, 'recordNumber' => $item->main_recordNumber, 'bookId' => $item->id, 'bugId' => $bugId, 'old_check_status' => $item->main_check_status, 'old_has_permit' => $item->main_has_permit, 'old_unallowed' => $item->unallowed, 'excelId' => $excel_id));
                }
            }
        }
        return $report;
    }

    public function headings(): array
    {
        return ["لینک کتاب در گیسوم", "عنوان کتاب", "ناشر", "تعداد صفحه", "سال نشر", "شابک 10 رقمی", "شابک 13 رقمی", "قطع", "وضعیت در خانه کتاب", "راهنمای خانه کتاب", "وضعیت در اداره کتاب", "راهنمای اداره کتاب"];
    }
}
