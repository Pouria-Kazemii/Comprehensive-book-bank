<?php
namespace App\Exports;

use App\Models\BookirBook;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ContradictionsFidiboExport implements FromCollection,WithHeadings
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
        $report = DB::table('bookfidibo')->select('recordNumber','title','nasher','saleNashr','tedadSafe','shabak','translate','lang','fileSize')->where('title','!=',NULL)->where('bookfidibo.has_permit',  $status)->get();
        foreach($report as $key=>$item){
            if($item->translate == 1 ){
                $report[$key]->translate = 'ترجمه';
            }else{
                $report[$key]->translate = 'تالیف';
            }
            if($item->fileSize != NULL ){
                $report[$key]->fileSize = 'الکترونیکی';
            }else{
                $report[$key]->fileSize = 'چاپی';
            }
            $report[$key]->recordNumber = 'https://fidibo.com/book/'.$item->recordNumber;

        }
        return $report;
    }

    public function headings(): array
    {
        return ["لینک کتاب در فیدیبو", "عنوان کتاب","ناشر","تاریخ انتشار","تعداد صفحه","شابک","تالیف یا ترجمه","زبان","چاپی یا الکترونیکی"];
    }
}
