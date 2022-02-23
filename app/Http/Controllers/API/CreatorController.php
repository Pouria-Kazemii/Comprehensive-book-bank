<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookirBook;
use App\Models\BookirPartner;
use App\Models\BookirPartnerrule;
use App\Models\BookirRules;
use App\Models\BookirSubject;
use Illuminate\Http\Request;

class CreatorController extends Controller
{
    // find
    public function find(Request $request)
    {
        return $this->lists($request);
    }

    // find by subject
    public function findBySubject(Request $request)
    {
        $subjectId = $request["subjectId"];

        $where = $subjectId != "" ? "xid In (Select xcreatorid From bookir_partnerrule Where xbookid In (Select bi_book_xid From bi_book_bi_subject Where bi_subject_xid='$subjectId'))" : "";

        return $this->lists($request, ($where == ""), $where, $subjectId);
    }

    // find by publisher
    public function findByPublisher(Request $request)
    {
        $publisherId = $request["publisherId"];

        $where = $publisherId != "" ? "xid In (Select xcreatorid From bookir_partnerrule Where xbookid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId'))" : "";

        return $this->lists($request, ($where == ""), $where, 0, $publisherId);
    }

    // list
    public function lists(Request $request, $isNull = false, $where = "", $subjectId = 0, $publisherId = 0)
    {
        $name = (isset($request["name"])) ? $request["name"] : "";
        $roleId = (isset($request["roleId"])) ? $request["roleId"] : "";
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
            $creators = BookirPartner::orderBy('xcreatorname', 'asc');
            if($name != "") $creators->where('xcreatorname', 'like', "%$name%");
            if($roleId > 0) $creators->whereRaw("xid In (Select xcreatorid From bookir_partnerrule Where xroleid='$roleId')");
            if($where != "") $creators->whereRaw($where);
            $creators = $creators->skip($offset)->take($pageRows)->get();
            if($creators != null and count($creators) > 0)
            {
                foreach ($creators as $creator)
                {
                    $creatorId = $creator->xid;

                    if($subjectId > 0) $bookCount = BookirBook::orderBy('xpublishdate', 'desc')->whereRaw("xid In (Select xbookid From bookir_partnerrule Where xcreatorid='$creatorId') and xid In (Select bi_book_xid From bi_book_bi_subject Where bi_subject_xid='$subjectId')")->count();
                    else if($publisherId > 0) $bookCount = BookirBook::orderBy('xpublishdate', 'desc')->whereRaw("xid In (Select xbookid From bookir_partnerrule Where xcreatorid='$creatorId') and xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')")->count();
                    elseif($subjectId == 0 AND $publisherId ==0 ) $bookCount = BookirPartnerrule::where('xcreatorid',$creatorId)->count(); // add by kiani
                    else  $bookCount = 0;

                    //
                    $data[] =
                        [
                            "id" => $creator->xid,
                            "bookCount" => $bookCount,
                            "name" => $creator->xcreatorname,
                        ];
                }

                $status = 200;
            }

            //
            $creators = BookirPartner::orderBy('xcreatorname', 'asc');
            if($name != "") $creators->where('xcreatorname', 'like', "%$name%");
            if($roleId > 0) $creators->whereRaw("xid In (Select xcreatorid From bookir_partnerrule Where xroleid='$roleId')");
            if($where != "") $creators->whereRaw($where);
            $totalRows = $creators->count();
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
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 404;

        // read
        $creators = BookirPartner::where('xcreatorname', '!=', '')->where('xcreatorname', 'like', "%$searchWord%")->orderBy('xcreatorname', 'asc')->get();
        if($creators != null and count($creators) > 0)
        {
            foreach ($creators as $creator)
            {
                $data[] =
                    [
                        "id" => $creator->xid,
                        "value" => $creator->xcreatorname,
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

    // role
    public function role(Request $request)
    {
        $data = null;
        $status = 404;

        // read books
        $roles = BookirRules::orderBy('xrole', 'asc')->get();
        if($roles != null and count($roles) > 0)
        {
            foreach ($roles as $role)
            {
                $data[] =
                    [
                        "id" => $role->xid,
                        "name" => $role->xrole,
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

    // detail
    public function detail(Request $request)
    {
        $creatorId = $request["creatorId"];
        $dataMaster = null;

        // read
        $creator = BookirPartner::where('xid', '=', $creatorId)->first();
        if($creator != null and $creator->xid > 0)
        {
            $rolesData = null;
            $creatorId = $creator->xid;

            $roles = BookirRules::orderBy('xrole', 'asc')->whereRaw("xid In (Select xroleid From bookir_partnerrule Where xcreatorid='$creatorId')")->select('xrole as title')->get();

            $dataMaster =
                [
                    "name" => $creator->xcreatorname,
                    "roles" => $roles,
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

    // annual activity
    public function annualActivity(Request $request)
    {
        $creatorId = $request["creatorId"];
        $yearPrintCountData = null;

        // read books for year printCount by title
        $books = BookirBook::whereRaw("xid In (Select xbookid From bookir_partnerrule Where xcreatorid='$creatorId')")->get();
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
}
