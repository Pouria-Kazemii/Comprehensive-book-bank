<?php
namespace App\Exports;

use App\Models\BookirBook;
use App\Models\BookTaaghche;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ContradictionsTaaghcheExport implements FromCollection,WithHeadings
{
    public function __construct($status)
    {
        $this->status = $status;
    }
    public function collection()
    {
        $status = $this->status;
        $data = array();
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $report = BookTaaghche::select('recordNumber','title','nasher','saleNashr','tedadSafe','shabak','translate','lang','fileSize','price','check_status','tags','has_permit','images')->where('title','!=',NULL)->whereIN('has_permit',  $status)->whereIN('check_status',$status)->get();
        foreach($report as $key=>$item){
            if($item->translate == 1 ){
                $report[$key]->translate = 'ترجمه';
            }else{
                $report[$key]->translate = 'تالیف';
            }
            if($item->fileSize != NULL AND $item->price !=NULL ){
                $report[$key]->fileSize = 'چاپی و الکترونیکی';
            }elseif($item->fileSize != NULL AND $item->price == 0){
                $report[$key]->fileSize = 'الکترونیکی';
            }elseif($item->fileSize == NULL AND $item->price > 0){
                $report[$key]->fileSize = 'چاپی';
            }else{
                $report[$key]->fileSize = 'ناموجود';
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

            $report[$key]->tags = '';
            if($item->check_status == 2){
                if((isset($item->saleNashr) and $item->saleNashr != null and !empty($item->saleNashr))){
                    $georgianCarbonDate=\Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $item->saleNashr)->toCarbon();
                    if($georgianCarbonDate > date('2022-03-21 00:00:00')){
                        $report[$key]->tags = '*';
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

            $report[$key]->images = '';
            if($item->has_permit == 2){
                if((isset($item->saleNashr) and $item->saleNashr != null and !empty($item->saleNashr))){
                    $georgianCarbonDate=\Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $item->saleNashr)->toCarbon();
                    if($georgianCarbonDate < date('2018-03-21 00:00:00')){
                        $report[$key]->images = '**';
                    }
                }
            }
            // if(($item->has_permit == 2 OR $item->check_status == 2) and (isset($report[$key]->saleNashr) and $report[$key]->saleNashr != null and !empty($report[$key]->saleNashr))){
            //     $georgianCarbonDate=\Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $report[$key]->saleNashr)->toCarbon();
            //     if ($georgianCarbonDate < date('2022-03-21 00:00:00') OR $georgianCarbonDate > date('2018-03-21 00:00:00') ) {
            //         $report[$key]->images = '('.$item->saleNashr.' )جستجو نشده به دلیل محدودیت سال انتشار';
            //     }else{
            //         $report[$key]->images = '';
            //     }
            // }else{
            //     $report[$key]->images = '';
            // }

           

            
            $report[$key]->recordNumber = 'https://taaghche.com/book/'.$item->recordNumber;
        }
        return $report;
    }

    public function headings(): array
    {
        return ["لینک کتاب در طاقچه", "عنوان کتاب","ناشر","تاریخ انتشار","تعداد صفحه","شابک","تالیف یا ترجمه","زبان","چاپی یا الکترونیکی","قیمت","وضعیت در خانه کتاب","راهنمای خانه کتاب","وضعیت در اداره کتاب","راهنمای ادراه کتاب"];
    }
}
