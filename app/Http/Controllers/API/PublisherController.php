<?php

namespace App\Http\Controllers\Api;

use App\Helpers\BookMasterData;
use App\Http\Controllers\Controller;
use App\Models\BiBookBiPublisher;
use App\Models\BookDigi;
use App\Models\BookirBook;
use App\Models\BookirPartnerrule;
use App\Models\BookirPublisher;
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
            $publishers = BookirPublisher::orderBy('xpublishername', 'desc');
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
            $publishers = BookirPublisher::orderBy('xpublishername', 'desc');
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
}
