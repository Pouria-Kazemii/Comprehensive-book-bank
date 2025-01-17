<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BookIranKetabPartner;
use App\Models\BookirBook;
use App\Models\BookirPartner;
use App\Models\BookirPartnerrule;
use App\Models\BookirPublisher;
use App\Models\BookirRules;
use App\Models\BookirSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return $this->lists($request, ($where == ""), $where, 0, 0, $publisherId);
    }

    // find by creator
    public function findByCreator(Request $request)
    {
        $creatorId = $request["creatorId"];

        $where = $creatorId != "" ? "xid In (Select xcreatorid From bookir_partnerrule Where xbookid In (Select xbookid From bookir_partnerrule Where xcreatorid='$creatorId') AND xcreatorid != $creatorId) " : "";

        return $this->lists($request, ($where == ""), $where, 0, $creatorId);
    }
    // list
    public function lists(Request $request, $isNull = false, $where = "", $subjectId = 0, $mainCreatorId = 0, $publisherId = 0)
    {
        $roleId = (isset($request["roleId"])) ? $request["roleId"] : "";
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "xcreatorname";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        if (!$isNull) {
            // read books
            $creators = BookirPartner::orderBy($column, $sortDirection);
            if ($searchText != "") $creators->where('xcreatorname', 'like', "%$searchText%");
            if ($roleId > 0) $creators->whereRaw("xid In (Select xcreatorid From bookir_partnerrule Where xroleid='$roleId')");
            if ($where != "") $creators->whereRaw($where);
            $creators = $creators->skip($offset)->take($pageRows)->get();
            if ($creators != null and count($creators) > 0) {
                foreach ($creators as $creator) {
                    $creatorId = $creator->xid;

                    if ($subjectId > 0) $bookCount = BookirBook::orderBy('xpublishdate', 'desc')->whereRaw("xid In (Select xbookid From bookir_partnerrule Where xcreatorid='$creatorId') and xid In (Select bi_book_xid From bi_book_bi_subject Where bi_subject_xid='$subjectId')")->count();
                    else if ($publisherId > 0) $bookCount = BookirBook::orderBy('xpublishdate', 'desc')->whereRaw("xid In (Select xbookid From bookir_partnerrule Where xcreatorid='$creatorId') and xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')")->count();
                    elseif ($mainCreatorId > 0) $bookCount =  BookirPartnerrule::where('xcreatorid', $creatorId)->whereRaw("xbookid In (Select xbookid From bookir_partnerrule Where xcreatorid='$mainCreatorId') AND xcreatorid != $mainCreatorId")->count(); // add by kiani
                    elseif ($subjectId == 0 and $publisherId == 0) $bookCount = BookirPartnerrule::where('xcreatorid', $creatorId)->count(); // add by kiani
                    else  $bookCount = 0;

                    //
                    $data[] =
                        [
                            "publisherId" => $publisherId,
                            "publisherName" => $publisherId > 0 ? BookirPublisher::where('xid', $publisherId)->first()->xpublishername : "",
                            "mainCreatorId" => $mainCreatorId,
                            "mainCreatorName" => $mainCreatorId > 0 ? BookirPartner::where('xid', $mainCreatorId)->first()->xcreatorname : "",
                            "subjectId" => $subjectId,
                            "id" => $creator->xid,
                            "bookCount" => $bookCount,
                            "name" => $creator->xcreatorname,
                        ];
                }

                $status = 200;
            }

            //
            $creators = BookirPartner::orderBy($column, $sortDirection);
            if ($searchText != "") $creators->where('xcreatorname', 'like', "%$searchText%");
            if ($roleId > 0) $creators->whereRaw("xid In (Select xcreatorid From bookir_partnerrule Where xroleid='$roleId')");
            if ($where != "") $creators->whereRaw($where);
            $totalRows = $creators->count();
            $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;
        }


        // response
        return response()->json(
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
        if ($creators != null and count($creators) > 0) {
            foreach ($creators as $creator) {
                $data[] =
                    [
                        "id" => $creator->xid,
                        "value" => $creator->xcreatorname,
                    ];
            }

            $status = 200;
        }

        // response
        return response()->json(
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
        if ($roles != null and count($roles) > 0) {
            foreach ($roles as $role) {
                $data[] =
                    [
                        "id" => $role->xid,
                        "name" => $role->xrole,
                    ];
            }

            $status = 200;
        }

        // response
        return response()->json(
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
        $status = 404;
        $dataMaster = null;

        // read
        $creator = BookirPartner::where('xid', '=', $creatorId)->first();
        if ($creator != null and $creator->xid > 0) {
            $rolesData = null;
            $creatorId = $creator->xid;

            $roles = BookirRules::orderBy('xrole', 'asc')->whereRaw("xid In (Select xroleid From bookir_partnerrule Where xcreatorid='$creatorId')")->select('xrole as title')->get();
            $partnerInfo = BookIranKetabPartner::where('partner_master_id', $creatorId)->first();
            $dataMaster =
                [
                    "name" => $creator->xcreatorname,
                    "roles" => $roles,
                    "image" => !empty($partnerInfo->partnerImage) ? $partnerInfo->partnerImage : null,
                    "desc" => !empty($partnerInfo->partnerDesc) ? $partnerInfo->partnerDesc : null,
                    "enName" => !empty($partnerInfo->partnerEnName) ? $partnerInfo->partnerEnName : null,
                ];
        }

        if ($dataMaster != null) $status = 200;

        // response
        return response()->json(
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
        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $year = BookirBook::getShamsiYear($book->xpublishdate);
                $printCount = 1;

                $yearPrintCountData[$year] = ["year" => $year, "printCount" => (isset($yearPrintCountData[$year])) ? $printCount + $yearPrintCountData[$year]["printCount"] : $printCount];
            }

            $yearPrintCountData = ["label" => array_column($yearPrintCountData, 'year'), "value" => array_column($yearPrintCountData, 'printCount')];
        }

         $yearPrintCountData != null ? $status = 200:$status =404;

        // response
        return response()->json(
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["yearPrintCount" => $yearPrintCountData]
            ],
            $status
        );
    }
}
