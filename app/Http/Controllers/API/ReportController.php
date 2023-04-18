<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookirBook;
use App\Models\BookirPartnerrule;
use App\Models\BookirPublisher;
use App\Models\BookirSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class ReportController extends Controller
{
    // publisher
    public function publisher(Request $request)
    {
        
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $currentPageNumber = (isset($request["currentPageNumber"])) ? $request["currentPageNumber"] : 0;

        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "xdiocode";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;


        $data = null;
        $status = 404;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        DB::enableQueryLog();

        $books = BookirBook::orderBy($column, $sortDirection );
        $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");
        if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
        if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
        $books->select("xcirculation", "xlang", "xdiocode");
        $books->selectRaw("REPLACE(REPLACE(REPLACE(xdiocode, '-', ''), ' ', ''), '.', '') as diocode");
       // $books->groupBy("diocode");
        $totalRows = count($books->get()); //$books->count(); // get total records count
        $books = $books->skip($offset)->take($pageRows)->get(); // get list

        $query = DB::getQueryLog();
        dd($query);
        die('stop');
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $dioCode = str_replace("-", "", str_replace(".", "", str_replace(" ", "", $book->xdiocode)));
                // $translate = $book->xlang == "فارسی" ? 0 : 1;
                $translate = $book->is_translate == 2 ? 1 : 0; // if translate return 1

                 $bookCirculation = DB::table('bookir_book')->whereRaw("'$dioCode'=REPLACE(REPLACE(REPLACE(xdiocode, '-', ''), ' ', ''), '.', '')")->selectRaw('SUM(xcirculation) as circulation')->first();
                 $circulation = $bookCirculation != null ? $bookCirculation->circulation : 0;

//                $data["$dioCode*$translate"] = array
                $data[$book->xdiocode] = array
                (
                    "translate" => $translate,
                    "circulation" => priceFormat($circulation),//$book->xcirculation + ((isset($data[$dioCode])) ? $data[$dioCode]["circulation"] : 0),
                    "dio" => $book->xdiocode,
                );
            }

            $data = array_values($data);
        }

        //
        if($data != null) $status = 200;
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

    // publisher dio
    public function publisherDio(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $dio = (isset($request["dio"])) ? $request["dio"] : "";
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "xdiocode";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $dioData = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        $books = BookirBook::orderBy($column, $sortDirection);
        $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");
        if($dio != "") $books->where("xdiocode", "=", "$dio");
        if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
        if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
        // $books = $books->get(); // get list
        $books = $books->skip($offset)->take($pageRows)->get();

        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $dioData[md5($book->xdiocode)] = $book->xdiocode;
            }
        }

        if($dioData != null and count($dioData) > 0)
        {
            foreach ($dioData as $dio)
            {
                $books = BookirBook::orderBy($column, $sortDirection);
                $books->whereRaw("xdiocode='$dio' and xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");
                if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
                if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
                $books = $books->get(); // get list
                if($books != null and count($books) > 0)
                {
                    foreach ($books as $book)
                    {
                        $dioCode = $book->xdiocode;

                        $data[$dioCode] = array
                        (
                            "dio" => $dioCode,
                            "countTitle" => 1 + ((isset($data[$dioCode])) ? $data[$dioCode]["countTitle"] : 0),
                            "circulation" => $book->xcirculation + ((isset($data[$dioCode])) ? $data[$dioCode]["circulation"] : 0),
                            "price" => (intval($book->xcoverprice) * $book->xcirculation) + ((isset($data[$dioCode])) ? $data[$dioCode]["price"] : 0),
                        );
                    }

                    foreach ($data as $key => $item)
                    {
                        $data[$key]["circulation"] = priceFormat($item["circulation"]);
                        $data[$key]["price"] = priceFormat($item["price"]);
                    }

                    $data = array_values($data);
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

    // dio
    public function dio(Request $request)
    {
        $dio = (isset($request["dio"])) ? $request["dio"] : "";
        $translate = (isset($request["translate"])) ? $request["translate"] : 0;
        $authorship = (isset($request["authorship"])) ? $request["authorship"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "xpublishdate";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $dioData = null;
        $status = 404;
        $pageRows = 50;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        if($dio != "")
        {
            $books = BookirBook::orderBy($column , $sortDirection);
            $books->where("xdiocode", "=", "$dio");
            // if($translate == 1) $books->where("xlang", "!=", "فارسی");
            if($translate == 1) $books->where("is_translate", 2);
            // if($authorship == 1) $books->where("xlang", "=", "فارسی");
            if($authorship == 1) $books->where("is_translate", 1);
            if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
            if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
            $totalRows = $books->count(); // get total records count
            $books = $books->skip($offset)->take($pageRows)->get(); // get list
            if($books != null and count($books) > 0)
            {
                foreach ($books as $book)
                {
                    if ($book->xparent == -1 or  $book->xparent == 0) {
                        $dossier_id = $book->xid;
                    } else {
                        $dossier_id = $book->xparent;
                    }
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
                            "dossier_id" => $dossier_id,
                            "name" => $book->xname,
                            "publishers" => $publishers,
                            "language" => $book->xlang,
                            "year" => BookirBook::getShamsiYear($book->xpublishdate),
                            "printNumber" => $book->xprintnumber,
                            "format" => $book->xformat,
                            "pageCount" => $book->xpagecount,
                            "isbn" => $book->xisbn,
                            "price" => $book->xcoverprice,
                            "image" => $book->ximgeurl,
                            "circulation" => $book->xcirculation,
                        ];
                }
            }
        }

        //
        if($data != null) $status = 200;
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

    // publisher book
    public function publisherBook(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "xpublishdate";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        if($publisherId > 0)
        {
            $books = BookirBook::orderBy( $column, $sortDirection);
//            $books->whereRaw("xparent='-1' and xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");
            $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");
            if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
            if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
            $totalRows = $books->count(); // get total records count
            $books = $books->skip($offset)->take($pageRows)->get(); // get list
            if($books != null and count($books) > 0)
            {
                foreach ($books as $book)
                {
                    $creatorsData = null;
                    $circulation = 0;
                    $where = "";

                    $books2 = DB::table('bookir_book')->where('xid', '=', $book->xid)->orwhere('xparent', '=', $book->xid)->get();
                    if($books2 != null and count($books2) > 0)
                    {
                        foreach ($books2 as $book2)
                        {
                            $where .= "xbookid='".$book2->xid."' or ";

                            $circulation += $book2->xcirculation;
                        }

                        $where = rtrim($where, " or ");
                    }

                    if($where != "")
                    {
                        $creators = DB::table('bookir_partnerrule')
                            ->whereRaw($where)
                            ->join('bookir_partner', 'bookir_partnerrule.xcreatorid', '=', 'bookir_partner.xid')
                            ->groupBy('bookir_partner.xid')
                            ->select('bookir_partner.xid as id', 'bookir_partner.xcreatorname as name')
                            ->get();
                        if($creators != null and count($creators) > 0)
                        {
                            foreach ($creators as $creator)
                            {
                                $creatorsData[] = ["id" => $creator->id, "name" => $creator->name];
                            }
                        }
                    }

                    // $bookCirculation = DB::table('bookir_book')->where('xbookid', '=', $book->xid)->orwhere('xparent', '=', $book->xid)->select('SUM(xcirculation) as circulation')->first();
                    // $circulation = $bookCirculation != null ? $bookCirculation->circulation : 0;

                    //
                    $data[] = array
                    (
                        "id" => $book->xid,
                        "name" => $book->xname,
                        "circulation" => priceFormat($circulation),
                        // "translate" => $book->xlang == "فارسی" ? 0 : 1,
                        "translate" =>$book->is_translate == 2 ? 1 : 0, // if translate return 1
                        "price" => priceFormat($book->xcoverprice),
                        "format" => $book->xformat,
                        "creators" => $creatorsData,
                        "image" => $book->ximgeurl,
                    );
                }
            }
        }

        //
        if($data != null) $status = 200;
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

    // publisher subject
    public function publisherSubject(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $subjectId = (isset($request["subjectId"])) ? $request["subjectId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "xdiocode";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50; 
        $dioData = null;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        $books = BookirBook::orderBy($column,$sortDirection);
        $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");
        if($subjectId > 0) $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_subject Where bi_subject_xid='$subjectId')");
        if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
        if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
        $totalRows = $books->count(); // get total records count
        $books = $books->skip($offset)->take($pageRows)->get(); // get list
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $subjectsData = null;
                $subjects = DB::table('bi_book_bi_subject')
                    ->where('bi_book_xid', '=', $book->xid)
                    ->join('bookir_subject', 'bi_book_bi_subject.bi_subject_xid', '=', 'bookir_subject.xid')
                    ->select('bookir_subject.xid as id', 'bookir_subject.xsubject as title')
                    ->get();
                if($subjects != null and count($subjects) > 0)
                {
                    foreach ($subjects as $subject)
                    {
                        $subjectsData[] = ["id" => $subject->id, "title" => $subject->title];
                    }
                }

                //
                $data[] = array
                (
                    "id" => $book->xid,
                    "name" => $book->xname,
                    "subjects" => $subjectsData,
                    "circulation" => priceFormat($book->xcirculation),
                    "year" => BookirBook::getShamsiYear($book->xpublishdate),
                    "price" => priceFormat($book->xcoverprice),
                    "image" => $book->ximgeurl,
                );
            }
        }

        //
        if($data != null) $status = 200;
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

    // publisher subject aggregation
    public function publisherSubjectAggregation(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $subjectId = (isset($request["subjectId"])) ? $request["subjectId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;

        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "xsubject";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50; 
        $subjectsData = null;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        $subjects = BookirSubject::orderBy($column,$sortDirection);
        $subjects->whereRaw("xid In (Select bi_subject_xid From bi_book_bi_subject Where bi_book_xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId'))");
        if($subjectId > 0) $subjects->where("xid", "=", $subjectId);
        $subjects = $subjects->get(); // get list
        if($subjects != null and count($subjects) > 0)
        {
            foreach ($subjects as $subject)
            {
                $subjectsData[$subject->xid] = $subject->xsubject;
            }
        }

        if($subjectsData != null and count($subjectsData) > 0)
        {
            foreach ($subjectsData as $subjectId => $subjectTitle)
            {
                $books = BookirBook::orderBy('xdiocode', 'asc');
                $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_subject Where bi_subject_xid='$subjectId') and xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");
                if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
                if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
                $books = $books->get(); // get list
                if($books != null and count($books) > 0)
                {
                    foreach ($books as $book)
                    {
                        $data[$subjectId] = array
                        (
                            "id" => $subjectId,
                            "title" => $subjectTitle,
                            "countTitle" => 1 + ((isset($data[$subjectId])) ? $data[$subjectId]["countTitle"] : 0),
                            "circulation" => $book->xcirculation + ((isset($data[$subjectId])) ? $data[$subjectId]["circulation"] : 0),
                        );
                    }

                    foreach ($data as $key => $item)
                    {
                        $data[$key]["circulation"] = priceFormat($item["circulation"]);
                    }

                    $data = array_values($data);
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

    // subject aggregation
    public function subjectAggregation(Request $request)
    {
        $subjectId = (isset($request["subjectId"])) ? $request["subjectId"] : 0;
        $translate = (isset($request["translate"])) ? $request["translate"] : 0;
        $authorship = (isset($request["authorship"])) ? $request["authorship"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;

        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "xpublishername";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
        $publishersData = null;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        $publishers = BookirPublisher::orderBy($column,$sortDirection);
        $publishers->whereRaw("xid In (Select bi_publisher_xid From bi_book_bi_publisher Where bi_book_xid In (Select bi_book_xid From bi_book_bi_subject Where bi_subject_xid='$subjectId'))");
        $publishers = $publishers->get(); // get list
        if($publishers != null and count($publishers) > 0)
        {
            foreach ($publishers as $publisher)
            {
                $publishersData[$publisher->xid] = $publisher->xpublishername;
            }
        }

        if($publishersData != null and count($publishersData) > 0)
        {
            foreach ($publishersData as $publisherId => $publisherName)
            {
                $books = BookirBook::orderBy('xdiocode', 'asc');
                $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_subject Where bi_subject_xid='$subjectId') and xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");
                // if($translate == 1) $books->where("xlang", "!=", "فارسی");
                if($translate == 1) $books->where("is_translate", 2);
                // if($authorship == 1) $books->where("xlang", "=", "فارسی");
                if($authorship == 1) $books->where("is_translate", 1);
                if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
                if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
                $books = $books->get(); // get list
                if($books != null and count($books) > 0)
                {
                    foreach ($books as $book)
                    {
                        $data[$subjectId] = array
                        (
                            "publisher" => ["id" => $publisherId, "name" => $publisherName],
                            "countTitle" => 1 + ((isset($data[$subjectId])) ? $data[$subjectId]["countTitle"] : 0),
                            "circulation" => $book->xcirculation + ((isset($data[$subjectId])) ? $data[$subjectId]["circulation"] : 0),
                        );
                    }

                    foreach ($data as $key => $item)
                    {
                        $data[$key]["circulation"] = priceFormat($item["circulation"]);
                    }

                    $data = array_values($data);
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

    // subject
    public function subject(Request $request)
    {
        $subjectId = (isset($request["subjectId"])) ? $request["subjectId"] : 0;
        $translate = (isset($request["translate"])) ? $request["translate"] : 0;
        $authorship = (isset($request["authorship"])) ? $request["authorship"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        
        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "xpublishdate";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;

        $dioData = null;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        if($subjectId > 0)
        {
            // DB::enableQueryLog();
            $books = BookirBook::orderBy($column,$sortDirection);
            $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_subject Where bi_subject_xid='$subjectId')");
            // if($translate == 1) $books->where("xlang", "!=", "فارسی");
            if($translate == 1) $books->where("is_translate", 2);
            // if($authorship == 1) $books->where("xlang", "=", "فارسی");
            if($authorship == 1) $books->where("is_translate", 1);
            if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
            if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
            $totalRows = $books->count(); // get total records count
            $books = $books->skip($offset)->take($pageRows)->get(); // get list
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
                            "year" => BookirBook::getShamsiYear($book->xpublishdate),
                            "price" => priceFormat($book->xcoverprice),
                            "image" => $book->ximgeurl,
                            "circulation" => priceFormat($book->xcirculation),
                        ];
                }
            }

            // $query = DB::getQueryLog();
            // return $query;
        }

        //
        if($data != null) $status = 200;
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

    // creator subject
    public function creatorSubject(Request $request)
    {
        $creatorId = (isset($request["creatorId"])) ? $request["creatorId"] : 0;
        $subjectId = (isset($request["subjectId"])) ? $request["subjectId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;

        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "xdiocode";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;


        $data = null;
        $dioData = null;
        $status = 404;
        $pageRows = 50;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        $books = BookirBook::orderBy($column,$sortDirection);
        $books->whereRaw("xid In (Select xbookid From bookir_partnerrule Where xcreatorid='$creatorId')");
        if($subjectId > 0) $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_subject Where bi_subject_xid='$subjectId')");
        if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
        if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
        $totalRows = $books->count(); // get total records count
        $books = $books->skip($offset)->take($pageRows)->get(); // get list
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $publishersData = null;
                $publishers = DB::table('bi_book_bi_publisher')
                    ->where('bi_book_xid', '=', $book->xid)
                    ->join('bookir_publisher', 'bi_book_bi_publisher.bi_publisher_xid', '=', 'bookir_publisher.xid')
                    ->select('bookir_publisher.xid as id', 'bookir_publisher.xpublishername as title')
                    ->get();
                if($publishers != null and count($publishers) > 0)
                {
                    foreach ($publishers as $publisher)
                    {
                        $publishersData[] = ["id" => $publisher->id, "title" => $publisher->title];
                    }
                }

                //
                $data[] = array
                (
                    "id" => $book->xid,
                    "name" => $book->xname,
                    "publishers" => $publishersData,
                    "circulation" => priceFormat($book->xcirculation),
                    "year" => BookirBook::getShamsiYear($book->xpublishdate),
                    "price" => priceFormat($book->xcoverprice),
                    "image" => $book->ximgeurl,
                );
            }
        }

        //
        if($data != null) $status = 200;
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

    // creator publisher
    public function creatorPublisher(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;

        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "xbookid";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;

        $data = null;
        $status = 404;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";
                
        // read
        if($publisherId > 0)
        {
            $creatorRoles = BookirPartnerrule::orderBy($column, $sortDirection);
            // DB::enableQueryLog();
            $creatorRoles->whereRaw("xbookid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");
            if($yearStart != "") $creatorRoles->whereRaw("xbookid In (Select xid From bookir_book Where xpublishdate >= '$yearStart')");
            if($yearEnd != "") $creatorRoles->whereRaw("xbookid In (Select xid From bookir_book Where xpublishdate <= '$yearEnd')");
            $creatorRoles->join('bookir_rules', 'bookir_partnerrule.xroleid', '=', 'bookir_rules.xid');
            $creatorRoles->join('bookir_partner', 'bookir_partnerrule.xcreatorid', '=', 'bookir_partner.xid');
            $creatorRoles->join('bookir_book', 'bookir_partnerrule.xbookid', '=', 'bookir_book.xid');
            // $creatorRoles->groupBy('bookir_partnerrule.xcreatorid', 'bookir_partnerrule.xroleid', 'bookir_partnerrule.xbookid');
            $creatorRoles->groupBy('bookir_partnerrule.xcreatorid', 'bookir_partnerrule.xroleid');
            $creatorRoles->select('bookir_book.xlang as xlang', 'bookir_book.xcirculation as xcirculation', 'bookir_partnerrule.xbookid as xbookid', 'bookir_partnerrule.xroleid as xroleid', 'bookir_partnerrule.xcreatorid as xcreatorid', 'bookir_partner.xcreatorname as xcreatorname', 'bookir_rules.xrole as xrole');
            $totalRows = count($creatorRoles->get()); // get total records count
            $creatorRoles = $creatorRoles->skip($offset)->take($pageRows)->get(); // get list
            if($creatorRoles != null and count($creatorRoles) > 0)
            {
                foreach ($creatorRoles as $creatorRole)
                {
                    $data[] = array
                    (
                        "creator" => ["id" => $creatorRole->xcreatorid, "name" => $creatorRole->xcreatorname],
                        "role" => $creatorRole->xrole,
                        // "translate" => $creatorRole->xlang == "فارسی" ? 0 : 1,
                        "translate" => $creatorRole->is_translate == 2 ? 1 : 0, // if translate return 1
                        "circulation" => priceFormat($creatorRole->xcirculation),
                    );
                }
            }

        }

        //
        if($data != null) $status = 200;
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

    // creator aggregation
    public function creatorAggregation(Request $request)
    {
        $creatorId = (isset($request["creatorId"])) ? $request["creatorId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;


        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "xpublishername";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;


        $data = null;
        $publishersData = null;
        $status = 404;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        $publishers = BookirPublisher::orderBy($column,$sortDirection);
        $publishers->whereRaw("xid In (Select bi_publisher_xid From bi_book_bi_publisher Where bi_book_xid In (Select xbookid From bookir_partnerrule Where xcreatorid='$creatorId'))");
        $publishers = $publishers->get(); // get list
        if($publishers != null and count($publishers) > 0)
        {
            foreach ($publishers as $publisher)
            {
                $publishersData[$publisher->xid] = $publisher->xpublishername;
            }
        }

        if($publishersData != null and count($publishersData) > 0)
        {
            foreach ($publishersData as $publisherId => $publisherName)
            {
                $books = BookirBook::orderBy('xpublishdate', 'asc');
                $books->whereRaw("xid In (Select xbookid From bookir_partnerrule Where xcreatorid='$creatorId') and xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");
                if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
                if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
                $books = $books->get(); // get list
                if($books != null and count($books) > 0)
                {
                    foreach ($books as $book)
                    {
                        $data[$creatorId] = array
                        (
                            "publisher" => ["id" => $publisherId, "name" => $publisherName],
                            "countTitle" => 1 + ((isset($data[$creatorId])) ? $data[$creatorId]["countTitle"] : 0),
                            "circulation" => $book->xcirculation + ((isset($data[$creatorId])) ? $data[$creatorId]["circulation"] : 0),
                        );
                    }

                    foreach ($data as $key => $item)
                    {
                        $data[$key]["circulation"] = priceFormat($item["circulation"]);
                    }

                    $data = array_values($data);
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
}
