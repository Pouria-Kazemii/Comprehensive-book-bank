<?php

namespace App\Exports;

use App\Models\BookirBook;
use App\Models\BookirPartner;
use App\Models\BookirPublisher;
use App\Models\BookirSubject;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class NewBookEveryYearExport implements FromCollection, WithHeadings
{
    public function __construct($yearStart, $monthStart, $yearEnd, $monthEnd)
    {
        $this->yearStart = $yearStart;
        $this->yearEnd = $yearEnd;
        $this->monthStart = $monthStart;
        $this->monthEnd = $monthEnd;
    }
    public function collection()
    {
        $yearStart = (isset($this->yearStart) && $this->yearStart != 0) ? BookirBook::toGregorian($this->yearStart . '-' . $this->monthStart . '-01', '-', '-') : "";
        $yearEnd = (isset($this->yearEnd) && $this->yearEnd != 0) ? BookirBook::toGregorian($this->yearEnd . '-' . $this->monthEnd . '-30', '-', '-') : "";
        $data = array();
        // DB::enableQueryLog();
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        // $report = DB::table('bookir_book')->where('bookir_book.xpublishdate', '>=', $yearStart)->where('bookir_book.xpublishdate', '<=', $yearEnd)->where('xprintnumber',1);
        $report = DB::table('bookir_book')->select('bookir_book.xid', 'bookir_book.xsiteid as partners', 'bookir_book.xdocid as publisher','bookir_book.xdoctype as subjects', 'bookir_book.xpageurl2', 'bookir_book.xname', 'bookir_book.xpagecount', 'bookir_book.xformat', 'bookir_book.xcirculation', 'bookir_book.xisbn', 'bookir_book.xisbn2', 'bookir_book.xisbn3', 'bookir_book.xpublishdate', 'bookir_book.xcoverprice', 'bookir_book.xdiocode', 'bookir_book.xlang', 'bookir_book.is_translate', 'bookir_book.xpublishplace', 'bookir_book.check_circulation')->where('bookir_book.xpublishdate', '>=', $yearStart)->where('bookir_book.xpublishdate', '<=', $yearEnd)->where('xprintnumber', 1)->groupBy('bookir_book.xpageurl2')->get();

        //SELECT xcreatorname FROM `bookir_partner` where xid IN (SELECT xcreatorid FROM `bookir_partnerrule` where xbookid IN (SELECT xid FROM `bookir_book` where xpublishdate >= '2023-03-21' and xprintnumber =1)) 

        /* $report = $report->Join('bookir_partnerrule', 'bookir_book.xid', '=', 'bookir_partnerrule.xbookid')
       ->Join('bookir_partner', 'bookir_partnerrule.xcreatorid', '=', 'bookir_partner.xid')
       ->select('bookir_partner.xcreatorname as creatoers', 'bookir_book.xpageurl2','bookir_book.xname','bookir_book.xpagecount','bookir_book.xformat','bookir_book.xcirculation','bookir_book.xisbn','bookir_book.xisbn2','bookir_book.xisbn3','bookir_book.xpublishdate','bookir_book.xcoverprice','bookir_book.xdiocode','bookir_book.xlang','bookir_book.xpublishplace','bookir_book.check_circulation')
       ->groupBy('bookir_book.xpageurl2')->get(); */

        foreach ($report as $report_row) {
            $report_row->xpublishdate =  BookirBook::convertMiladi2Shamsi_with_slash($report_row->xpublishdate);
            $report_row->is_translate =  ($report_row->is_translate == 1) ? 'تالیف' : 'ترجمه';
            $publisher = BookirPublisher::select('xpublishername')
                ->Join('bi_book_bi_publisher', 'bi_book_bi_publisher.bi_publisher_xid', '=', 'bookir_publisher.xid')
                ->where('bi_book_bi_publisher.bi_book_xid', $report_row->xid)->first();

            if (isset($publisher->xpublishername) and !empty($publisher->xpublishername)) {
                $report_row->publisher = $publisher->xpublishername;
            }

            $partners =  BookirPartner::select('xcreatorname')
                ->Join('bookir_partnerrule', 'bookir_partnerrule.xcreatorid', '=', 'bookir_partner.xid')
                ->where('bookir_partnerrule.xbookid', $report_row->xid)->get();
            $creator_names  = $partners->pluck('xcreatorname')->all();

            $report_row->partners = implode(",", $creator_names);

            $subjects = BookirSubject::select('xsubject')
                ->Join('bi_book_bi_subject', 'bi_book_bi_subject.bi_subject_xid', '=', 'bookir_subject.xid')
                ->where('bi_book_bi_subject.bi_book_xid', $report_row->xid)->get();
            $subjects_title  = $subjects->pluck('xsubject')->all();

            $report_row->subjects = implode(",", $subjects_title);

            //    dd($report_row);
        }


        // $queries = DB::getQueryLog();
        // dd($queries);
        return $report;
    }

    public function headings(): array
    {
        return ["آیدی جدول", "پدیدآوررندگان", "انتشارات","موضوع", "لینک خانه کتاب", "نام کتاب", "تعداد صفحات", "فرمت", "تیراژ", "شابک 13 رقمی", "شابک 10 رقمی", "شابک 13 رقمی عدی", "تاریخ انتشار", "قیمت", "کد دیویی", "زبان", "تالیف یا ترجمه", "مکان انتشار", "وضعیت اظلاعات"];
    }
}
