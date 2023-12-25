<?php
namespace App\Exports;

use App\Models\BookDigi;
use App\Models\BookKetabejam;
use App\Models\WebSiteBookLinksDefects;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ContradictionsKetabejamExport implements FromCollection,WithHeadings
{
    public function __construct($status,$excel_id)
    {
        $this->status = $status;
        $this->excel_id = $excel_id;
    }
    public function collection()
    {
        $status = $this->status;
        $excel_id = $this->excel_id;
        $data = array();
        // DB::enableQueryLog();
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $report = BookKetabejam::select('pageUrl','title','nasher','tedadSafe','saleNashr','shabak','ghatechap','check_status','cats','has_permit','images')->where('title','!=',NULL)->whereIN('has_permit',  $status)->whereIN('check_status',$status)->get();
        foreach($report as $key=>$item){
            $report[$key]->cats = '';
            if($item->check_status == 2){
                if((isset($item->saleNashr) and $item->saleNashr != NULL and !empty($item->saleNashr))){
                    $georgianCarbonDate=\Morilog\Jalali\Jalalian::fromFormat('Y/m/d', substr($item->saleNashr,0,4).'/01/01')->toCarbon();
                    if(strtotime($georgianCarbonDate) > strtotime('2022-03-21 00:00:00')){
                        $report[$key]->cats = '*';
                    }
                }
            }
            $report[$key]->check_status = checkStatusTitle($item->check_status);
           
            $report[$key]->images = '';
            if($item->has_permit == 2){
                if((isset($item->saleNashr) and $item->saleNashr != NULL and !empty($item->saleNashr))){
                    $georgianCarbonDate=\Morilog\Jalali\Jalalian::fromFormat('Y/m/d', substr($item->saleNashr,0,4).'/01/01')->toCarbon();
                    if(strtotime($georgianCarbonDate) < strtotime('2018-03-21 00:00:00')){
                        $report[$key]->images = '**';
                    }
                }
            }
           
            $report[$key]->has_permit = hasPermitTitle($item->has_permit);

            $bugId = siteBookLinkDefects($report[$key]->check_status,$report[$key]->has_permit);
            WebSiteBookLinksDefects::create(array('siteName'=>'ketabejam','book_links'=> $item->pageUrl,'bookId'=>$item->id,'bugId'=>$bugId,'excelId'=>$excel_id));
        }
       
        return $report;
    }

    public function headings(): array
    {
        return ["آیدی کتاب در کتاب جم", "عنوان کتاب","ناشر","تعداد صفحه","سال نشر","شابک","قطع","وضعیت در خانه کتاب","راهنمای خانه کتاب","وضعیت در اداره کتاب","راهنمای اداره کتاب"];
    }
}
