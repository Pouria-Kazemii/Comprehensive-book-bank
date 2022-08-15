<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookirPublisher;
use App\Models\BookirSubject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    // list subject
    public function find(Request $request)
    {
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "xsubject";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0; 
        $data = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // read books
        $subjects = BookirSubject::orderBy($column, $sortDirection);
        if($searchText != "") $subjects->where('xsubject', 'like', "%$searchText%");
        $subjects = $subjects->skip($offset)->take($pageRows)->get();
        if($subjects != null and count($subjects) > 0)
        {
            foreach ($subjects as $subject)
            {
                $data[] =
                    [
                        "id" => $subject->xid,
                        "title" => $subject->xsubject,
                    ];
            }

            $status = 200;
        }

        //
        $subjects = BookirSubject::orderBy($column, $sortDirection);
        if($searchText != "") $subjects->where('xsubject', 'like', "%$searchText%");
        $totalRows = $subjects->count();
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

    // search
    public function search(Request $request)
    {
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 404;

        // read
        $subjects = BookirSubject::select('xid as id','xsubject as value')->where('xsubject', '!=', '')->where('xsubject', 'like', "%$searchWord")->orderBy('xsubject', 'asc')->get();
        if($subjects != null and count($subjects) > 0)
        {
            // foreach ($subjects as $subject)
            // {
            //     $data[] =
            //         [
            //             "id" => $subject->xid,
            //             "value" => $subject->xsubject,
            //         ];
            // }

            $status = 200;
        }

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $subjects]
            ],
            $status
        );
    }

    public function searchForSelectComponent(Request $request)
    {
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 404;

        // read
        $subjects = BookirSubject::select('xid as value','xsubject as label')->where('xsubject', '!=', '')->where('xsubject', 'like', "%$searchWord%")->orderBy('xsubject', 'asc')->get();
        if($subjects != null and count($subjects) > 0)
        {
            // foreach ($subjects as $subject)
            // {
                // $data[] =
                //     [
                //         "value" => $subject->xid,
                //         "label" => $subject->xsubject,
                //     ];
            // }

            $status = 200;
        }

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $subjects]
            ],
            $status
        );
    }
}
