<?php
namespace App\Exports;

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
        $report = DB::table('bookDigi')->select('recordNumber','title','nasher','tedadSafe','shabak','ghatechap')->where('title','!=',NULL)->where('bookDigi.has_permit',  $status)->get();
        return $report;
    }

    public function headings(): array
    {
        return ["آیدی کتاب در دیجیکالا", "عنوان کتاب","ناشر","تعداد صفحه","شابک","قطع"];
    }
}
