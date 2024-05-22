<?php
namespace App\Exports;

use App\Models\BookDigi;
use App\Models\WebSiteBookLinksDefects;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class WebsiteBookLinkDigiExport implements FromCollection,WithHeadings
{
    public function __construct($excel_id)
    {
        $this->excel_id = $excel_id;
    }
    public function collection()
    {
        $excel_id = $this->excel_id;
        $data = array();
        // DB::enableQueryLog();
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $report = WebSiteBookLinksDefects::select('book_links','old_check_status','old_has_permit','old_unallowed','new_check_status','new_has_permit','new_unallowed','result','id')->where('excelId',$this->excel_id)->get();
        foreach($report as $key=>$item){
            $array = explode("/",$item->book_links);
            $report[$key]->book_links = substr($item->book_links,0,mb_strpos($item->book_links,$array[5]));
            $report[$key]->old_check_status = checkStatusTitle($item->old_check_status);
            $report[$key]->new_check_status = checkStatusTitle($item->new_check_status);
            $report[$key]->old_has_permit = hasPermitTitle($item->old_has_permit);
            $report[$key]->new_has_permit = hasPermitTitle($item->new_has_permit);
            $report[$key]->old_unallowed = unallowedTitle($item->old_unallowed);
            $report[$key]->new_unallowed = unallowedTitle($item->new_unallowed);
            // $bugId = siteBookLinkDefects($report[$key]->new_check_status,$report[$key]->new_has_permit); 
        }
        return $report;
    }

    public function headings(): array
    {
                // 'book_links',           'old_check_status',            'old_has_permit',           'old_unallowed',                'new_check_status',           'new_has_permit',           'new_unallowed',        'result'
        return ["آیدی کتاب در دیجیکالا", "وضعیت پیشین در خانه کتاب","وضعیت پیشین در اداره کتاب","وضعیت پیشین کتاب غیر مجاز","وضعیت فعلی در خانه کتاب","وضعیت فعلی در اداره کتاب","وضعیت فعلی کتاب غیر مجاز","نتیجه"];
    }
}
