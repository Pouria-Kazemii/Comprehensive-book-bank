<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookirBook;
use App\Models\BookirPublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublisherController extends Controller
{
    // find
    public function find(Request $request)
    {
        return $this->lists($request);
    }

    // find by creator
    public function findByCreator(Request $request)
    {
        $creatorId = $request["creatorId"];

        $where = $creatorId != "" ? "xid In (Select bi_publisher_xid From bi_book_bi_publisher Where bi_book_xid In (Select xbookid From bookir_partnerrule Where xcreatorid='$creatorId'))" : "";

        return $this->lists($request, false, ($where == ""), $where);
    }

    // find by subject
    public function findBySubject(Request $request)
    {
        $subjectId = $request["subjectId"];

        $where = $subjectId != "" ? "xid In (Select bi_publisher_xid From bi_book_bi_publisher Where bi_book_xid In (Select bi_book_xid From bi_book_bi_subject Where bi_subject_xid='$subjectId'))" : "";

        return $this->lists($request, false, ($where == ""), $where);
    }

    // list
    public function lists(Request $request, $defaultWhere = true, $isNull = false, $where = "")
    {
        $name = (isset($request["name"])) ? $request["name"] : "";
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
            $publishers = BookirPublisher::orderBy('xpublishername', 'asc')->where('xpublishername', '!=', '');
//            if($defaultWhere) $publishers->where('xparent', '=', '-1');
            if($name != "") $publishers->where('xpublishername', 'like', "%$name%");
            if($where != "") $publishers->whereRaw($where);
            $publishers = $publishers->skip($offset)->take($pageRows)->get();
            if($publishers != null and count($publishers) > 0)
            {
                foreach ($publishers as $publisher)
                {
                    $data[] =
                        [
                            "id" => $publisher->xid,
                            "name" => $publisher->xpublishername,
                        ];
                }

                $status = 200;
            }

            //
            $publishers = BookirPublisher::orderBy('xpublishername', 'asc')->where('xpublishername', '!=', '');
//            if($defaultWhere) $publishers->where('xparent', '=', '-1');
            if($name != "") $publishers->where('xpublishername', 'like', "%$name%");
            if($where != "") $publishers->whereRaw($where);
            $totalRows = $publishers->count();
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

    // search
    public function search(Request $request)
    {
        $searchWord = (isset($request["search-word"])) ? $request["search-word"] : "";
        $data = null;
        $status = 404;

        // read
        $publishers = BookirPublisher::where('xpublishername', '!=', '')->where('xpublishername', 'like', "%$searchWord%")->orderBy('xpublishername', 'asc')->get();
        if($publishers != null and count($publishers) > 0)
        {
            foreach ($publishers as $publisher)
            {
                $data[] =
                    [
                        "id" => $publisher->xid,
                        "name" => $publisher->xpublishername,
                    ];
            }

            $status = 200;
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

    // annual activity by title
    public function annualActivityByTitle(Request $request)
    {
        $publisherId = $request["publisherId"];
        $yearPrintCountData = null;

        // read books for year printCount by title
        $books = BookirBook::whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')")->orderBy('xpublishdate', 'asc')->get();
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $year = BookirBook::getShamsiYear($book->xpublishdate);
                $printCount = 1;

                $yearPrintCountData[$year] = ["year" => $year, "printCount" => (isset($yearPrintCountData[$year])) ? $printCount + $yearPrintCountData[$year]["printCount"] : $printCount];
            }

            $yearPrintCountData = ["label" => array_column($yearPrintCountData, 'year'), "value" => array_column($yearPrintCountData, 'printCount')];
        }

        if($yearPrintCountData != null) $status = 200;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["yearPrintCount" => $yearPrintCountData]
            ],
            $status
        );
    }

    // annual activity by circulation
    public function annualActivityByCirculation(Request $request)
    {
        $publisherId = $request["publisherId"];
        $yearPrintCountData = null;

        // read books for year printCount by circulation
        $books = BookirBook::whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')")->orderBy('xpublishdate', 'asc')->get();
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $year = BookirBook::getShamsiYear($book->xpublishdate);
                $printCount = $book->xcirculation;

                $yearPrintCountData[$year] = ["year" => $year, "printCount" => (isset($yearPrintCountData[$year])) ? $printCount + $yearPrintCountData[$year]["printCount"] : $printCount];
            }

            $yearPrintCountData = ["label" => array_column($yearPrintCountData, 'year'), "value" => array_column($yearPrintCountData, 'printCount')];
        }

        if($yearPrintCountData != null) $status = 200;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["yearPrintCount" => $yearPrintCountData]
            ],
            $status
        );
    }

    // detail
    public function detail(Request $request)
    {
        $publisherId = $request["publisherId"];
        $dataMaster = null;

        // read
        $publisher = BookirPublisher::where('xid', '=', $publisherId)->first();
        if($publisher != null and $publisher->xid > 0)
        {
            $dataMaster =
                [
                    "name" => $publisher->xpublishername,
                    "manager" => $publisher->xmanager,
                    "activity" => $publisher->xactivity,
                    "place" => $publisher->xplace,
                    "address" => $publisher->xaddress,
                    "zipCode" => $publisher->xzipcode,
                    "phone" => $publisher->xphone,
                    "cellphone" => $publisher->xcellphone,
                    "fax" => $publisher->xfax,
                    "type" => $publisher->xtype,
                    "email" => $publisher->xemail,
                    "site" => $publisher->xsite,
                    "image" => $publisher->ximageurl,
                ];
        }

        if($dataMaster != null) $status = 200;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["master" => $dataMaster]
            ],
            $status
        );
    }

    // translate authorship
    public function translateAuthorship(Request $request)
    {
        $publisherId = $request["publisherId"];
        $data = null;
        $status = 404;

        // read books
        $books = BookirBook::whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')")->get();
        if($books != null and count($books) > 0)
        {
            $totalBooks = count($books);
            $data["authorship"] = 0;
            $data["translate"] = 0;

            foreach ($books as $book)
            {
                $type = $book->xlang == "فارسی" ? "authorship" : "translate";
                $data[$type] += 1;
            }

            $dataTmp = null;
            $dataTmp["تالیف"] = ($data["authorship"] > 0) ? round(($data["authorship"] / $totalBooks) * 100, 2) : 0;
            $dataTmp["ترجمه"] = ($data["translate"] > 0) ? round(($data["translate"] / $totalBooks) * 100, 2) : 0;

            //
            $data = ["label" => array_keys($dataTmp), "value" => array_values($dataTmp)];
        }

        //
        if($data != null) $status = 200;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["data" => $data]
            ],
            $status
        );
    }

    // statistic subject
    public function statisticSubject(Request $request)
    {
        $publisherId = $request["publisherId"];
        $data = null;
        $status = 404;
        $totalSubjects = 0;

        // read books
        $books = BookirBook::whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')")->get();
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $bookSubjects = DB::table('bi_book_bi_subject')
                    ->where('bi_book_xid', '=', $book->xid)
                    ->join('bookir_subject', 'bi_book_bi_subject.bi_subject_xid', '=', 'bookir_subject.xid')
                    ->select('bookir_subject.xsubject as title')
                    ->get();
                if($bookSubjects != null and count($bookSubjects) > 0)
                {
                    foreach ($bookSubjects as $bookSubject)
                    {
                        if(!isset($data[$bookSubject->title])) $totalSubjects += 1;

                        $data[$bookSubject->title] = (isset($data[$bookSubject->title])) ? $data[$bookSubject->title] + 1 : 1;
                    }
                }

//                if(!isset($data[$book->xdiocode])) $totalSubjects += 1;
//                $data[$book->xdiocode] = (isset($data[$book->xdiocode])) ? $data[$book->xdiocode] + 1 : 1;
            }

            /*foreach ($data as $key => $value)
            {
                $data[$key] = round(($value / $totalSubjects) * 100, 2);
            }*/

            //
            $data = ["label" => array_keys($data), "value" => array_values($data)];
        }

        //
        if($data != null) $status = 200;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["data" => $data]
            ],
            $status
        );
    }
}
