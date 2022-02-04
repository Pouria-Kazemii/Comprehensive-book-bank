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

        return $this->lists($request, $wherePublisher);
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

        return $this->lists($request, "", $whereCreator);
    }

    // list
    public function lists(Request $request, $wherePublisher = "", $whereCreator = "")
    {
        $name = (isset($request["name"])) ? $request["name"] : "";
        $isbn = (isset($request["isbn"])) ? $request["isbn"] : "";
        $currentPageNumber = (isset($request["currentPageNumber"])) ? $request["currentPageNumber"] : 0;
        $data = null;
        $status = 404;
        $pageRows = 50;
        $totalPages = 0;
        $totalRows = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // read books
        $books = BookirBook::orderBy('xpublishdate', 'desc')->where('xparent', '=', '-1');
        if($name != "") $books->where('xname', 'like', "%$name%");
        if($isbn != "") $books->where('xisbn', '=', $isbn);
        if($wherePublisher != "") $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where $wherePublisher)");
        if($whereCreator != "") $books->whereRaw("xid In (Select xbookid From bookir_partnerrule Where $whereCreator)");
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
        $books = BookirBook::orderBy('xpublishdate', 'desc')->where('xparent', '=', '-1');
        if($name != "") $books->where('xname', 'like', "%$name%");
        if($isbn != "") $books->where('xisbn', '=', $isbn);
        if($wherePublisher != "") $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where $wherePublisher)");
        if($whereCreator != "") $books->whereRaw("xid In (Select xbookid From bookir_partnerrule Where $whereCreator)");
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
        $printData = null;
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
                    "publisher" => $bookPublishers,
                    "subject" => $bookSubjects,
                    "creator" => $bookPartnerRules,
                    "image" => $book->ximgeurl,
                ];
        }

        // read books for printCount
        $books = BookirBook::where('xid', '=', $bookId)->orwhere('xparent', '=', $bookId)->get();
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $year = BookirBook::getShamsiYear($book->xpublishdate);
                $printCount = $book->xpagecount;

                $printData[$year] = ["year" => $year, "printCount" => (isset($printData[$year])) ? $printCount + $printData[$year]["printCount"] : $printCount];
            }
        }

        $printData = ["label" => array_column($printData, 'year'), "value" => array_column($printData, 'printCount')];

        //
        if($dataMaster != null) $status = 200;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["master" => $dataMaster, "list" => null, "chart" => $printData]
            ],
            $status
        );
    }












    // dossier book
    public function dossier(Request $request)
    {
        $bookId = $request["bookId"];
        $dataMaster = null;
        $data = null;
        $chartData = null;
        $chartLabelData = null;
        $chartValueData = null;
        $publisherData = null;
        $totalPrintCount = 0;

        // read books
        $book = TblBookMaster::where('id', '=', $bookId)->first();
        if($book != null and $book->id > 0)
        {
            $dataMaster =
                [
                    "id" => $book->id,
                    "title" => $book->title,
                    "titleEn" => $book->title_en,
                    "publisher" => $book->publisher,
                    "author" => $book->author,
                    "translator" => $book->translator,
                    "language" => $book->language,
                    "category" => $book->category,
                    "weight" => $book->weight,
                    "bookCoverType" => $book->book_cover_type,
                    "paperType" => $book->paper_type,
                    "typePrinting" => $book->type_printing,
                    "editor" => $book->editor,
                    "firstYearPublication" => $book->first_year_publication,
                    "lastYearPublication" => $book->last_year_publication,
                    "printPeriodCount" => $book->print_period_count,
                    "bookSize" => $book->book_size,
                    "countPages" => $book->count_pages,
                    "printCount" => $book->print_count,
                    "printLocation" => $book->print_location,
                    "isbn" => $book->isbn,
                    "price" => $book->price,
                    "dioCode" => $book->dio_code,
                    "image" => $book->image,
                ];
        }

        // read books
        $books = BookK24::where('book_master_id', '=', $bookId)->get();
        if($books != null and count($books) > 0)
        {
            $dataTemp = null;

            foreach ($books as $book)
            {
                $dataTemp[] =
                    [
                        "title" => $book->title,
                        "titleEn" => '',
                        "publisher" => $book->nasher,
                        "author" => '',
                        "translator" => '',
                        "language" => $book->lang,
                        "category" => $book->cats,
                        "weight" => '',
                        "bookCoverType" => '',
                        "paperType" => '',
                        "typePrinting" => '',
                        "editor" => '',
                        "yearPublication" => $book->saleNashr,
                        "printPeriodCount" => $book->nobatChap,
                        "bookSize" => $book->ghatechap,
                        "countPages" => $book->tedadSafe,
                        "printCount" => $book->printCount,
                        "printLocation" => $book->printLocation,
                        "isbn" => $book->isbn,
                        "price" => $book->price,
                        "dioCode" => $book->DioCode,
                    ];

                if(isset($publisherData[md5($book->nasher)]))
                    $publisherData[md5($book->nasher)] = ["name" => $book->nasher, "printCount" => $book->printCount + $publisherData[md5($book->nasher)]["printCount"]];
                else
                    $publisherData[md5($book->nasher)] = ["name" => $book->nasher, "printCount" => $book->printCount];

                $totalPrintCount += $book->printCount;
            }

            $data[] = ["bookSource" => "ketab.ir", "books" => $dataTemp];
        }

        $data[] = ["bookSource" => "DigiKala.com", "books" => null];
        $data[] = ["bookSource" => "30Book.com", "books" => null];
        $data[] = ["bookSource" => "Gisoom.com", "books" => null];
        $data[] = ["bookSource" => "IranKetab.ir", "books" => null];

        // chart data
        if($publisherData != null and count($publisherData) > 0)
        {
            foreach ($publisherData as $publisher)
            {
                $chartLabelData[] = $publisher["name"];
                $chartValueData[] = round(($publisher["printCount"] / $totalPrintCount) * 100, 2);
            }
        }

        $chartData = ["label" => $chartLabelData, "value" => $chartValueData];

        //
        if($dataMaster != null and $data != null) $status = 200;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["master" => $dataMaster, "list" => $data, "chart" => $chartData]
            ],
            $status
        );
    }
}
