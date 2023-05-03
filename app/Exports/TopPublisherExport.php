<?php
namespace App\Exports;

use App\Models\BookirBook;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class TopPublisherExport implements FromCollection
{
    public function __construct($startDate, $endDate, $dio, $limit)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->dio = $dio;
        $this->limit = $limit;
    }
    public function collection()
    {
        $yearStart = (isset($this->startDate) && $this->startDate != 0) ? BookirBook::toGregorian($this->startDate . '-01-01', '-', '-') : "";
        $yearEnd = (isset($this->endDate) && $this->endDate != 0) ? BookirBook::toGregorian($this->endDate . '-12-29', '-', '-') : "";
        $diocode = $this->dio;
        $data = array();
        // DB::enableQueryLog();
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $report = DB::table('bookir_book')->where('bookir_book.xpublishdate', '>=', $yearStart)->where('bookir_book.xpublishdate', '<=', $yearEnd);
        if (!empty($diocode)) {
            $diocode_arr = explode(',', $diocode);
            // dd($diocode_arr);
            $report = $report->where(function ($query) use($diocode_arr) {
                foreach ($diocode_arr as $key => $doi_item) {
                    if ($key == 0) {
                        $query->where('xdiocode', 'LIKE', "$doi_item%");
                    } else {
                        $query->orwhere('xdiocode', 'LIKE', "$doi_item%");

                    }
                }
            });
        }
        $report = $report->Join('bi_book_bi_publisher', 'bookir_book.xid', '=', 'bi_book_bi_publisher.bi_book_xid')
            ->Join('bookir_publisher', 'bi_book_bi_publisher.bi_publisher_xid', '=', 'bookir_publisher.xid')
            ->select('bi_book_bi_publisher.bi_publisher_xid as publisher_id', 'bookir_publisher.xpublishername as publisher_name', 'bookir_publisher.xmanager as manager', DB::raw("SUM(bookir_book.xcirculation) as sum_circulation"))
            ->groupBy('bi_publisher_xid')
            ->orderBy('Sum_circulation', 'desc')
            ->skip(0)->take($this->limit)->get();

        // $queries = DB::getQueryLog();
        // dd($queries);
        return $report;
    }
}
