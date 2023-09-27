<?php
namespace App\Exports;

use App\Models\BookDigi;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ContradictionsDigiExport implements FromCollection,WithHeadings
{
    public function __construct($status)
    {
        $this->status = $status;
    }
    public function collection()
    {
        $status = $this->status;
        $data = array();
        // DB::enableQueryLog();
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $report = BookDigi::select('recordNumber','title','nasher','tedadSafe','shabak','ghatechap','check_status','cat','has_permit','images')->where('title','!=',NULL)->where('has_permit',  $status)->get();
        foreach($report as $key=>$item){
            $report[$key]->cat = '';
            if($item->check_status == 2){
                if((isset($item->saleNashr) and $item->saleNashr != null and !empty($item->saleNashr))){
                    $georgianCarbonDate=\Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $item->saleNashr)->toCarbon();
                    if(strtotime($georgianCarbonDate) > strtotime('2022-03-21 00:00:00')){
                        $report[$key]->cat = '*';
                    }
                }
            }
            if($item->check_status == 1){
                $report[$key]->check_status = 'کتاب در خانه کتاب وجود دارد';
            }elseif($item->check_status == 2){
                $report[$key]->check_status = 'کتاب در خانه کتاب وجود ندارد';
            }elseif($item->check_status == 3){
                $report[$key]->check_status = 'جستجو نشده به دلیل محدودیت سال انتشار';
            }elseif($item->check_status == 4){
                $report[$key]->check_status = 'کتاب شابک ندارد';
            }

            
            $report[$key]->images = '';
            if($item->has_permit == 2){
                if((isset($item->saleNashr) and $item->saleNashr != null and !empty($item->saleNashr))){
                    $georgianCarbonDate=\Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $item->saleNashr)->toCarbon();
                    if(strtotime($georgianCarbonDate) < strtotime('2018-03-21 00:00:00')){
                        $report[$key]->images = '**';
                    }
                }
            }
           
            if($item->has_permit == 1){
                $report[$key]->has_permit = 'کتاب در اداره کتاب وجود دارد';
            }elseif($item->has_permit == 2){
                $report[$key]->has_permit = 'کتاب در اداره کتاب وجود ندارد';
            }elseif($item->has_permit == 3){
                $report[$key]->has_permit = 'جستجو نشده به دلیل محدودیت سال انتشار';
            }elseif($item->has_permit == 4){
                $report[$key]->has_permit = 'کتاب شابک ندارد';
            }

            $report[$key]->recordNumber = 'https://www.digikala.com/product/dkp-'.$item->recordNumber.'/';
        }
       
        return $report;
    }

    public function headings(): array
    {
        return ["آیدی کتاب در دیجیکالا", "عنوان کتاب","ناشر","تعداد صفحه","شابک","قطع"];
    }
}
