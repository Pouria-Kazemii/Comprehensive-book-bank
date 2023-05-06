<?php
namespace App\Exports;

use App\Models\BookirBook;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ParentBookExport implements FromCollection, WithHeadings
{
    public function __construct($startDate, $endDate, $dio)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->dio = $dio;
    }
    public function collection()
    {
        //  DB::enableQueryLog();
        $start = $this->startDate;
        $end = $this->endDate;
        $collection = collect([]);
        for ($start; $start <= $end; $start++) {
            unset($result);
            $yearStart = (isset($start) && $start != 0) ? BookirBook::toGregorian($start . '-01-01', '-', '-') : "";
            $yearEnd = (isset($start) && $start != 0) ? BookirBook::toGregorian($start . '-12-29', '-', '-') : "";
            $diocode = $this->dio;
            $data = array();
            // DB::enableQueryLog();
            $report = BookirBook::select('count(xid) as allcount')->where('xpublishdate', '>=', $yearStart)->where('xpublishdate', '<=', $yearEnd)->where('xparent', '-1');
            if (!empty($diocode) and $diocode != 0) {
                $diocode_arr = explode(',', $diocode);
                $report = $report->where(function ($query) use ($diocode_arr) {
                    foreach ($diocode_arr as $key => $doi_item) {
                        if ($key == 0) {
                            $query->where('xdiocode', 'LIKE', "$doi_item%");
                        } else {
                            $query->orwhere('xdiocode', 'LIKE', "$doi_item%");

                        }
                    }
                });
            }
            $result['start'] = $start;
            $result['count'] = ($report->count() > 0) ? $report->count() : 0;
            $collection->push($result);

        }
        // return $collection;
        dd($collection);
        // $queries = DB::getQueryLog();
        // dd($result);

    }

    public function headings(): array
    {
        return ["سال", "تعداد عنوان کتاب"];
    }
}
