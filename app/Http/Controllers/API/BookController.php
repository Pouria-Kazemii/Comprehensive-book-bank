<?php

namespace App\Http\Controllers\Api;

use App\Helpers\BookMasterData;
use App\Http\Controllers\Controller;
use App\Models\BiBookBiPublisher;
use App\Models\BiBookBiSubject;
use App\Models\BookDigi;
use App\Models\BookirBook;
use App\Models\BookirPartner;
use App\Models\BookirPartnerrule;
use App\Models\BookirPublisher;
use App\Models\BookirSubject;
use App\Models\BookK24;
use App\Models\TblBookMaster;
use App\Models\TblBookMasterCategory;
use App\Models\TblBookMasterPerson;
use App\Models\TblBookMasterPublisher;
use App\Models\TblCategory;
use App\Models\TblPerson;
use App\Models\TblPublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    // list books
    public function find(Request $request)
    {
        return $this->lists($request);
    }

    // list books by publisher
    public function findByPublisher(Request $request)
    {
        $bookId = $request["bookId"];
        $wherePublisher = "";

        // get publisher
        $publishers = BiBookBiPublisher::where('bi_book_xid', '=', $bookId)->get();
        if($publishers != null and count($publishers) > 0)
        {
            foreach ($publishers as $publisher)
            {
                $wherePublisher = "bi_publisher_xid='".$publisher->bi_publisher_xid."' or ";
            }
            $wherePublisher = rtrim($wherePublisher, " or ");
        }

        return $this->lists($request, false, $wherePublisher);
    }

    // list books by creator
    public function findByCreator(Request $request)
    {
        $bookId = $request["bookId"];
        $whereCreator = "";

        // get creator
        $creators = BookirPartnerrule::where('xbookid', '=', $bookId)->get();
        if($creators != null and count($creators) > 0)
        {
            foreach ($creators as $creator)
            {
                $whereCreator = "xcreatorid='".$creator->xcreatorid."' or ";
            }
            $whereCreator = rtrim($whereCreator, " or ");
        }

        return $this->lists($request, false, "", $whereCreator);
    }

    // list books by ver
    public function findByVer(Request $request)
    {
        $bookId = $request["bookId"];
        $whereVer = "xid='$bookId' or xparent='$bookId'";

        return $this->lists($request, false, "", "", $whereVer);
    }

    // list
    public function lists(Request $request, $defaultWhere = true, $wherePublisher = "", $whereCreator = "", $whereVer = "")
    {
        $name = (isset($request["name"])) ? $request["name"] : "";
        $isbn = (isset($request["isbn"])) ? $request["isbn"] : "";
        $currentPageNumber = (isset($request["currentPageNumber"])) ? $request["currentPageNumber"] : 0;
        $data = null;
        $status = 404;
        $pageRows = 50;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // read books
        $books = BookirBook::orderBy('xpublishdate', 'desc');
        if($defaultWhere) $books->where('xparent', '=', '-1');
        if($name != "") $books->where('xname', 'like', "%$name%");
        if($isbn != "") $books->where('xisbn', '=', $isbn);
        if($wherePublisher != "") $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where $wherePublisher)");
        if($whereCreator != "") $books->whereRaw("xid In (Select xbookid From bookir_partnerrule Where $whereCreator)");
        if($whereVer != "") $books->whereRaw($whereVer);
        $books = $books->skip($offset)->take($pageRows)->get();
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $publisherNames = "";

                $bookPublishers = DB::table('bi_book_bi_publisher')
                    ->where('bi_book_xid', '=', $book->xid)
                    ->join('bookir_publisher', 'bi_book_bi_publisher.bi_publisher_xid', '=', 'bookir_publisher.xid')
                    ->select('bookir_publisher.xpublishername as name')
                    ->get();
                if($bookPublishers != null and count($bookPublishers) > 0)
                {
                    foreach ($bookPublishers as $bookPublisher)
                    {
                        $publisherNames .= $bookPublisher->name." - ";
                    }
                    $publisherNames = rtrim($publisherNames, " - ");
                }

                //
                $data[] =
                    [
                        "id" => $book->xid,
                        "name" => $book->xname,
                        "publisher" => $publisherNames,
                        "language" => $book->xlang,
                        "publishDate" => BookirBook::getShamsiYear($book->xpublishdate),
                        "printNumber" => $book->xprintnumber,
                        "format" => $book->xformat,
                        "pageCount" => $book->xpagecount,
                        "isbn" => $book->xisbn,
                        "price" => $book->xcoverprice
                    ];
            }

            $status = 200;
        }

        //
        $books = BookirBook::orderBy('xpublishdate', 'desc');
        if($defaultWhere) $books->where('xparent', '=', '-1');
        if($name != "") $books->where('xname', 'like', "%$name%");
        if($isbn != "") $books->where('xisbn', '=', $isbn);
        if($wherePublisher != "") $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where $wherePublisher)");
        if($whereCreator != "") $books->whereRaw("xid In (Select xbookid From bookir_partnerrule Where $whereCreator)");
        if($whereVer != "") $books->whereRaw($whereVer);
        $totalRows = $books->count();
        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
            $status
        );
    }

    // detail book
    public function detail(Request $request)
    {
        $bookId = $request["bookId"];
        $dataMaster = null;
        $yearPrintCountData = null;
        $publisherPrintCountData = null;
        $status = 404;

        // read books
        $book = BookirBook::where('xid', '=', $bookId)->where('xparent', '=', '-1')->first();
        if($book != null and $book->xid > 0)
        {
            $bookPublishers = DB::table('bi_book_bi_publisher')
                ->where('bi_book_xid', '=', $book->xid)
                ->join('bookir_publisher', 'bi_book_bi_publisher.bi_publisher_xid', '=', 'bookir_publisher.xid')
                ->select('bookir_publisher.xpublishername as name')
                ->get();

            $bookSubjects = DB::table('bi_book_bi_subject')
                ->where('bi_book_xid', '=', $book->xid)
                ->join('bookir_subject', 'bi_book_bi_subject.bi_subject_xid', '=', 'bookir_subject.xid')
                ->select('bookir_subject.xsubject as title')
                ->get();

            $bookPartnerRules = DB::table('bookir_partnerrule')
                ->where('xbookid', '=', $book->xid)
                ->join('bookir_partner', 'bookir_partnerrule.xcreatorid', '=', 'bookir_partner.xid')
                ->join('bookir_rules', 'bookir_partnerrule.xroleid', '=', 'bookir_rules.xid')
                ->select('bookir_partner.xcreatorname as name', 'bookir_rules.xrole as role')
                ->get();

            //
            $dataMaster =
                [
                    "isbn" => $book->xisbn,
                    "name" => $book->xname,
                    "dioCode" => $book->xdiocode,
                    "publisher" => $bookPublishers,
                    "subject" => $bookSubjects,
                    "creator" => $bookPartnerRules,
                    "image" => $book->ximgeurl,
                ];
        }

        // read books for year printCount
        $books = BookirBook::where('xid', '=', $bookId)->orwhere('xparent', '=', $bookId)->get();
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $year = BookirBook::getShamsiYear($book->xpublishdate);
                $printCount = $book->xpagecount;

                $yearPrintCountData[$year] = ["year" => $year, "printCount" => (isset($yearPrintCountData[$year])) ? $printCount + $yearPrintCountData[$year]["printCount"] : $printCount];
            }

            $yearPrintCountData = ["label" => array_column($yearPrintCountData, 'year'), "value" => array_column($yearPrintCountData, 'printCount')];
        }

        // read books for publisher PrintCount
        $books = DB::table('bookir_book')
            ->where('bookir_book.xid', '=', $bookId)->orwhere('bookir_book.xparent', '=', $bookId)
            ->join('bi_book_bi_publisher', 'bi_book_bi_publisher.bi_book_xid', '=', 'bookir_book.xid')
            ->join('bookir_publisher', 'bookir_publisher.xid', '=', 'bi_book_bi_publisher.bi_publisher_xid')
            ->select('bookir_publisher.xpublishername as name', DB::raw('SUM(bookir_book.xpagecount) as printCount'))
            ->groupBy('bookir_publisher.xid')
            ->get();
        if($books != null and count($books) > 0)
        {
            $totalPrintCount = 0;
            foreach ($books as $book)
            {
                $totalPrintCount += $book->printCount;
            }

            foreach ($books as $book)
            {
                $publisherName = $book->name;
                $percentPrintCount = ($book->printCount > 0 and $totalPrintCount > 0) ? round(($book->printCount / $totalPrintCount) * 100, 2) : 0;

                $publisherPrintCountData[] = ["name" => $publisherName, "percentPrintCount" => $percentPrintCount];
            }

            $publisherPrintCountData = ["label" => array_column($publisherPrintCountData, 'name'), "value" => array_column($publisherPrintCountData, 'percentPrintCount')];
        }

        //
        if($dataMaster != null) $status = 200;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["master" => $dataMaster, "yearPrintCount" => $yearPrintCountData, "publisherPrintCount" => $publisherPrintCountData]
            ],
            $status
        );
    }
}
