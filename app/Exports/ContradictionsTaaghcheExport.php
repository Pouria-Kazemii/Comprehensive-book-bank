<?php
namespace App\Exports;

use App\Models\BookirBook;
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
        $report = DB::table('booktaaghche')->select('recordNumber','title','nasher','saleNashr','tedadSafe','shabak','translate','lang','fileSize','price')->where('title','!=',NULL)->where('booktaaghche.check_status',  $status)->get();
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
        }
        return $report;
    }

    public function headings(): array
    {
        return ["آیدی کتاب در طاقچه", "عنوان کتاب","ناشر","تاریخ انتشار","تعداد صفحه","شابک","تالیف یا ترجمه","زبان","چاپی یا الکترونیکی","قیمت"];
    }
}
