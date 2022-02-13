<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BiBookBiPublisher;
use App\Models\Book30book;
use App\Models\BookDigi;
use App\Models\BookGisoom;
use App\Models\BookirBook;
use App\Models\BookirPartnerrule;
use App\Models\BookirPublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    // find
    public function find(Request $request)
    {
        return $this->lists($request);
    }

    // find by publisher
    public function findByPublisher(Request $request)
    {
        $publisherId = $request["publisherId"];
        $bookId = $request["bookId"];
        $where = "";

        if($publisherId > 0)
        {
            $where = "xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')";
        }
        elseif($bookId > 0)
        {
            // get publisher
            $publishers = BiBookBiPublisher::where('bi_book_xid', '=', $bookId)->get();
            if($publishers != null and count($publishers) > 0)
            {
                foreach ($publishers as $publisher)
                {
                    $where = "bi_publisher_xid='".$publisher->bi_publisher_xid."' or ";
                }

                $where = "xid In (Select bi_book_xid From bi_book_bi_publisher Where ".rtrim($where, " or ").")";
            }
        }

        return $this->lists($request, true, ($where == ""), $where);
    }

    // find by creator
    public function findByCreator(Request $request)
    {
        $creatorId = $request["creatorId"];
        $bookId = $request["bookId"];
        $where = "";

        if($creatorId > 0)
        {
            $where = "xid In (Select xbookid From bookir_partnerrule Where xcreatorid='$creatorId')";
        }
        elseif($bookId > 0)
        {
            // get creator
            $creators = BookirPartnerrule::where('xbookid', '=', $bookId)->get();
            if($creators != null and count($creators) > 0)
            {
                foreach ($creators as $creator)
                {
                    $where = "xcreatorid='".$creator->xcreatorid."' or ";
                }

                $where = "xid In (Select xbookid From bookir_partnerrule Where ".rtrim($where, " or ").")";
            }
        }

        return $this->lists($request, true, ($where == ""), $where);
    }

    // find by ver
    public function findByVer(Request $request)
    {
        $bookId = $request["bookId"];
        $where = "xid='$bookId' or xparent='$bookId'";

        return $this->lists($request, true, ($where == ""), $where);
    }

    // find by subject
    public function findBySubject(Request $request)
    {
        $subjectId = $request["subjectId"];

        $where = $subjectId != "" ? "xid In (Select bi_book_xid From bi_book_bi_subject Where bi_subject_xid='$subjectId')" : "";

        return $this->lists($request, true, ($where == ""), $where);
    }

    // list
    public function lists(Request $request, $defaultWhere = true, $isNull = false, $where = "")
    {
        $name = (isset($request["name"])) ? $request["name"] : "";
        $isbn = (isset($request["isbn"])) ? $request["isbn"] : "";
        $currentPageNumber = (isset($request["currentPageNumber"])) ? $request["currentPageNumber"] : 0;
        $data = null;
        $status = 404;
        $pageRows = 50;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        if(!$isNull)
        {
            // read books
            $books = BookirBook::orderBy('xpublishdate', 'desc');
            if($defaultWhere) $books->whereRaw("(xparent='-1' or xparent='0')");//$books->where('xparent', '=', '-1');//->orwhere('xparent', '=', '0');
            if($name != "") $books->where('xname', 'like', "%$name%");
            if($isbn != "") $books->where('xisbn', '=', $isbn);
            if($where != "") $books->whereRaw($where);
            $books = $books->skip($offset)->take($pageRows)->get();
            if($books != null and count($books) > 0)
            {
                foreach ($books as $book)
                {
                    $publishers = null;

                    $bookPublishers = DB::table('bi_book_bi_publisher')
                        ->where('bi_book_xid', '=', $book->xid)
                        ->join('bookir_publisher', 'bi_book_bi_publisher.bi_publisher_xid', '=', 'bookir_publisher.xid')
                        ->select('bookir_publisher.xid as id', 'bookir_publisher.xpublishername as name')
                        ->get();
                    if($bookPublishers != null and count($bookPublishers) > 0)
                    {
                        foreach ($bookPublishers as $bookPublisher)
                        {
                            $publishers[] = ["id" => $bookPublisher->id, "name" => $bookPublisher->name];
                        }
                    }

                    //
                    $data[] =
                        [
                            "id" => $book->xid,
                            "name" => $book->xname,
                            "publishers" => $publishers,
                            "language" => $book->xlang,
                            "year" => BookirBook::getShamsiYear($book->xpublishdate),
                            "printNumber" => $book->xprintnumber,
                            "format" => $book->xformat,
                            "pageCount" => $book->xpagecount,
                            "isbn" => $book->xisbn,
                            "price" => priceFormat($book->xcoverprice),
                            "image" => $book->ximgeurl,
                        ];
                }

                $status = 200;
            }

            //
            $books = BookirBook::orderBy('xpublishdate', 'desc');
            if($defaultWhere) $books->whereRaw("(xparent='-1' or xparent='0')");
            if($name != "") $books->where('xname', 'like', "%$name%");
            if($isbn != "") $books->where('xisbn', '=', $isbn);
            if($where != "") $books->whereRaw($where);
            $totalRows = $books->count();
            $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;
        }

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
        $book = BookirBook::where('xid', '=', $bookId)/*->where('xparent', '=', '-1')*/->first();
        if($book != null and $book->xid > 0)
        {
            $publishersData = null;
            $subjectsData = null;
            $creatorsData = null;

            $bookPublishers = DB::table('bi_book_bi_publisher')
                ->where('bi_book_xid', '=', $book->xid)
                ->join('bookir_publisher', 'bi_book_bi_publisher.bi_publisher_xid', '=', 'bookir_publisher.xid')
                ->select('bookir_publisher.xid as id', 'bookir_publisher.xpublishername as name')
                ->get();
            if($bookPublishers != null and count($bookPublishers) > 0)
            {
                foreach ($bookPublishers as $bookPublisher)
                {
                    $publishersData[] = ["id" => $bookPublisher->id, "name" => $bookPublisher->name];
                }
            }

            $bookSubjects = DB::table('bi_book_bi_subject')
                ->where('bi_book_xid', '=', $book->xid)
                ->join('bookir_subject', 'bi_book_bi_subject.bi_subject_xid', '=', 'bookir_subject.xid')
            ->select('bookir_subject.xid as id', 'bookir_subject.xsubject as title')
            ->get();
            if($bookSubjects != null and count($bookSubjects) > 0)
            {
                foreach ($bookSubjects as $subject)
                {
                    $subjectsData[] = ["id" => $subject->id, "title" => $subject->title];
                }
            }

            $bookPartnerRules = DB::table('bookir_partnerrule')
                ->where('xbookid', '=', $book->xid)
                ->join('bookir_partner', 'bookir_partnerrule.xcreatorid', '=', 'bookir_partner.xid')
                ->join('bookir_rules', 'bookir_partnerrule.xroleid', '=', 'bookir_rules.xid')
                ->select('bookir_partner.xid as id', 'bookir_partner.xcreatorname as name', 'bookir_rules.xrole as role')
                ->get();
            if($bookPartnerRules != null and count($bookPartnerRules) > 0)
            {
                foreach ($bookPartnerRules as $partner)
                {
                    $creatorsData[] = ["id" => $partner->id, "name" => $partner->name, "role" => $partner->role];
                }
            }

            //
            $dataMaster =
                [
                    "isbn" => $book->xisbn,
                    "name" => $book->xname,
                    "dioCode" => $book->xdiocode,
                    "publishers" => $publishersData,
                    "subjects" => $subjectsData,
                    "creators" => $creatorsData,
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

    // market
    public function market(Request $request)
    {
        $bookId = $request["bookId"];
        $isbn = "";
        $isbn2 = "";
        $data = null;
        $status = 404;

        // read book
        $book = BookirBook::where('xid', '=', $bookId)->first();
        if($book != null and $book->xid > 0)
        {
            $isbn = $book->xisbn;
            $isbn2 = $book->xisbn2;
        }

        if($isbn != "")
        {
            // read books of digi
            $books = BookDigi::where('shabak', '=', $isbn);
            if($isbn2 != "") $books->orwhere('shabak', '=', $isbn2);
            $books = $books->get();
            if($books != null and count($books) > 0)
            {
                foreach ($books as $book)
                {
//                    if($book->price > 0)
                    $data[] =
                        [
                            "source" => "دیجیکالا",
                            "price" => $book->price,
                        ];
                }
            }

            // read books of gisoom
            $books = BookGisoom::where('shabak10', '=', $isbn)->orwhere('shabak13', '=', $isbn);
            if($isbn2 != "") $books->orwhere('shabak10', '=', $isbn2)->orwhere('shabak13', '=', $isbn2);
            $books = $books->get();
            if($books != null and count($books) > 0)
            {
                foreach ($books as $book)
                {
//                    if($book->price > 0)
                    $data[] =
                        [
                            "source" => "گیسوم",
                            "price" => $book->price,
                        ];
                }
            }

            // read books of 30Book
            $books = Book30book::where('shabak', '=', $isbn);
            if($isbn2 != "") $books->orwhere('shabak', '=', $isbn2);
            $books = $books->get();
            if($books != null and count($books) > 0)
            {
                foreach ($books as $book)
                {
//                    if($book->price > 0)
                    $data[] =
                        [
                            "source" => "سی بوک",
                            "price" => $book->price,
                        ];
                }
            }
        }

        //
        if($data != null) $status = 200;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $data]
            ],
            $status
        );
    }

    // search dio
    public function searchDio(Request $request)
    {
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 404;

        // read
        $books = BookirBook::where('xdiocode', 'like', "%$searchWord%")->orderBy('xdiocode', 'asc')->get();
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $data[md5($book->xdiocode)] =
                    [
                        "id" => $book->xdiocode,
                        "value" => $book->xdiocode,
                    ];
            }

            $status = 200;
            $data = array_values($data);
        }

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $data]
            ],
            $status
        );
    }
}
