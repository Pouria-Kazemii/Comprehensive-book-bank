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
        // DB::enableQueryLog();
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $report = BookTaaghche::select('recordNumber','title','nasher','saleNashr','tedadSafe','shabak','translate','lang','fileSize','price')->where('saleNashr','<','1400/01/01')->where('title','!=',NULL)->where('has_permit',  $status)->get();
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
            $report[$key]->recordNumber = 'https://taaghche.com/book/'.$item->recordNumber;
        }
        return $report;
    }

    public function headings(): array
    {
        return ["لینک کتاب در طاقچه", "عنوان کتاب","ناشر","تاریخ انتشار","تعداد صفحه","شابک","تالیف یا ترجمه","زبان","چاپی یا الکترونیکی","قیمت"];
    }
}
