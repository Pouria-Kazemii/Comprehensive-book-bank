<?php
namespace App\Exports;

use App\Models\BookBarkhatBook;
use App\Models\WebSiteBookLinksDefects;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ContradictionsBarkhatExport implements FromCollection,WithHeadings
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
        $report = BookBarkhatBook::select('recordNumber','title','nasher','tedadSafe','shabak','ghatechap','check_status','cats','has_permit','images','id')->where('title','!=',NULL)->whereIN('has_permit',  $status)->get();
        foreach($report as $key=>$item){
            $report[$key]->cats = '';
            if($item->check_status == 2){
                if((isset($item->saleNashr) and $item->saleNashr != null and !empty($item->saleNashr))){
                    $georgianCarbonDate=\Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $item->saleNashr)->toCarbon();
                    if(strtotime($georgianCarbonDate) > strtotime('2022-10-25 00:00:00')){
                        $report[$key]->cats = '*';
                    }
                }
            }
            $report[$key]->main_check_status = $item->check_status;
            $report[$key]->check_status = checkStatusTitle($item->check_status);
            
            $report[$key]->images = '';
            if($item->has_permit == 2){
                if((isset($item->saleNashr) and $item->saleNashr != null and !empty($item->saleNashr))){
                    $georgianCarbonDate=\Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $item->saleNashr)->toCarbon();
                    if(strtotime($georgianCarbonDate) < strtotime('2018-10-25 00:00:00')){
                        $report[$key]->images = '**';
                    }
                }
            }

            $report[$key]->main_has_permit = $item->has_permit;
            $report[$key]->has_permit = hasPermitTitle($item->has_permit);

            $bugId = siteBookLinkDefects($report[$key]->check_status,$report[$key]->has_permit);
            $report[$key]->main_recordNumber = 'bk_'.$item->recordNumber;
            $report[$key]->recordNumber = 'https://barkhatbook.com/product/bk_'.$item->recordNumber.'/'.$item->title.'/';
            // WebSiteBookLinksDefects::create(array('siteName'=>'barkhatbook','book_links'=>$item->recordNumber,'recordNumber'=>$item->main_recordNumber,'bookId'=>$item->id,'bugId'=>$bugId,'old_check_status'=>$item->main_check_status,'old_has_permit'=>$item->main_has_permit,'excelId'=>$excel_id));
        }
       
        return $report;
    }

    public function headings(): array
    {
        return ["لینک کتاب برخط بوک", "عنوان کتاب","ناشر","تعداد صفحه","شابک","قطع","وضعیت در خانه کتاب","راهنمای خانه کتاب","وضعیت در اداره کتاب","راهنمای اداره کتاب"];
    }
}
