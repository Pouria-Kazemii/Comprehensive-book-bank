<?php
namespace App\Exports;

use App\Models\BookirBook;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class TopAuthorExport implements FromCollection
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
        if (!empty($diocode) AND $diocode != 0) {
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
        $report = $report->Join('bookir_partnerrule', 'bookir_book.xid', '=', 'bookir_partnerrule.xbookid')
            ->Join('bookir_partner', 'bookir_partnerrule.xcreatorid', '=', 'bookir_partner.xid')
            ->select('bookir_partner.xcreatorname as creator_name', DB::raw("SUM(bookir_book.xcirculation) as sum_circulation"))
            ->groupBy('xcreatorid')
            ->orderBy('Sum_circulation', 'desc')
            ->skip(0)->take($this->limit)->get();

        // $queries = DB::getQueryLog();
        // dd($queries);
        return $report;
    }
}
