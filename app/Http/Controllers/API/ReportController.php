<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookirBook;
use App\Models\BookirSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{


    // publisher
    public function publisher(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $data = null;
        $status = 404;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        $books = BookirBook::orderBy('xpublishdate', 'desc');
        $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");
        if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
        if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
        $books = $books->get(); // get list
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $dioCode = $book->xdiocode;
                $creatorsData = null;

                $creators = DB::table('bookir_partnerrule')
                    ->where('xbookid', '=', $book->xid)->where('xroleid', '=', '1')
                    ->join('bookir_partner', 'bookir_partnerrule.xroleid', '=', 'bookir_partner.xid')
                    ->groupBy('bookir_partner.xid')
                    ->select('bookir_partner.xid as id', 'bookir_partner.xcreatorname as name')
                    ->get();

                if($creators != null and count($creators) > 0)
                {
                    foreach ($creators as $creator)
                    {
                        if(!(isset($data[$dioCode]) and $data[$dioCode]["creators"] != null and array_search($creator->name, array_column($data[$dioCode]["creators"], "name"))))
                            $creatorsData[] = ["id" => $creator->id, "name" => $creator->name];
                    }
                }

                $data[$dioCode] = array
                (
                    "creators" => $creatorsData,
                    "translate" => $book->xlang == "فارسی" ? 0 : 1,
                    "circulation" => $book->xcirculation + ((isset($data[$dioCode])) ? $data[$dioCode]["circulation"] : 0),
                    "dio" => $dioCode,
                );
            }

            $data = array_values($data);
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

    // publisher dio
    public function publisherDio(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $dio = (isset($request["dio"])) ? $request["dio"] : "";
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $data = null;
        $dioData = null;
        $status = 404;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        $books = BookirBook::orderBy('xdiocode', 'asc');
        $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");
        if($dio != "") $books->where("xdiocode", "=", "$dio");
        if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
        if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
        $books = $books->get(); // get list
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
                $books = BookirBook::orderBy('xdiocode', 'asc');
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
                        $price = (int) filter_var($item["price"], FILTER_SANITIZE_NUMBER_INT);

                        $data[$key]["price"] = number_format($price);
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
        $data = null;
        $dioData = null;
        $status = 404;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        if($dio != "")
        {
            $books = BookirBook::orderBy('xpublishdate', 'desc');
            $books->where("xdiocode", "=", "$dio");
            if($translate == 1) $books->where("xlang", "!=", "فارسی");
            if($authorship == 1) $books->where("xlang", "=", "فارسی");
            if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
            if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
            $books = $books->get(); // get list
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
                            "publishDate" => BookirBook::getShamsiYear($book->xpublishdate),
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

    // publisher book
    public function publisherBook(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $data = null;
        $status = 404;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        if($publisherId > 0)
        {
            $books = BookirBook::orderBy('xpublishdate', 'desc');
            $books->whereRaw("xparent='-1' and xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");
            if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
            if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
            $books = $books->get(); // get list
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
                            ->join('bookir_partner', 'bookir_partnerrule.xroleid', '=', 'bookir_partner.xid')
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
                        "circulation" => $circulation,
                        "translate" => $book->xlang == "فارسی" ? 0 : 1,
                        "price" => $book->xcoverprice,
                        "format" => $book->xformat,
                        "creators" => $creatorsData,
                        "image" => $book->ximgeurl,
                    );
                }

                $data = array_values($data);
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

    // publisher subject
    public function publisherSubject(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $subjectId = (isset($request["subjectId"])) ? $request["subjectId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $data = null;
        $dioData = null;
        $status = 404;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        $books = BookirBook::orderBy('xdiocode', 'asc');
        $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");
        if($subjectId > 0) $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_subject Where bi_subject_xid='$subjectId')");
        if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
        if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
        $books = $books->get(); // get list
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
                    "circulation" => $book->xcirculation,
                    "year" => BookirBook::getShamsiYear($book->xpublishdate),
                    "price" => $book->xcoverprice,
                    "image" => $book->ximgeurl,
                );
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

    // publisher subject شggregation
    public function publisherSubjectAggregation(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $subjectId = (isset($request["subjectId"])) ? $request["subjectId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $data = null;
        $subjectsData = null;
        $status = 404;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        $subjects = BookirSubject::orderBy('xsubject', 'asc');
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
                            "title" => $subjectTitle,
                            "countTitle" => 1 + ((isset($data[$subjectId])) ? $data[$subjectId]["countTitle"] : 0),
                            "circulation" => $book->xcirculation + ((isset($data[$subjectId])) ? $data[$subjectId]["circulation"] : 0),
                        );
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
