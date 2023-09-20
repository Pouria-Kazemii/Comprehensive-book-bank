<?php
namespace App\Exports;

use App\Models\Book30book;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class Contradictions30bookExport implements FromCollection,WithHeadings
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
        $report = Book30book::select('recordNumber','title','nasher','saleNashr','tedadSafe','shabak','tarjome','price')->where('title','!=',NULL)->where('has_permit',  $status)->get();
        foreach($report as $key=>$item){
            if($item->tarjome == 1 ){
                $report[$key]->tarjome = 'ترجمه';
            }else{
                $report[$key]->tarjome = 'تالیف';
            }
            $report[$key]->recordNumber = 'https://www.30book.com/book/'.$item->recordNumber;
        }
        return $report;
    }

    public function headings(): array
    {
        return ["لینک کتاب در 30book", "عنوان کتاب","ناشر","تاریخ انتشار","تعداد صفحه","شابک","تالیف یا ترجمه","قیمت"];
    }
}
