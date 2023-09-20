<?php
namespace App\Exports;

use App\Models\BookIranketab;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ContradictionsIranketabExport implements FromCollection,WithHeadings
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
        $report = BookIranketab::select('recordNumber','title','nasher','saleNashr','tedadSafe','shabak','traslate','price')->where('title','!=',NULL)->where('has_permit',  $status)->get();
        foreach($report as $key=>$item){
            if($item->traslate == 1 ){
                $report[$key]->traslate = 'ترجمه';
            }else{
                $report[$key]->traslate = 'تالیف';
            }
            $report[$key]->recordNumber = 'https://www.iranketab.ir/book/'.$item->recordNumber;
        }
        return $report;
    }

    public function headings(): array
    {
        return ["لینک کتاب در ایران کتاب", "عنوان کتاب","ناشر","تاریخ انتشار","تعداد صفحه","شابک","تالیف یا ترجمه","قیمت"];
    }
}
