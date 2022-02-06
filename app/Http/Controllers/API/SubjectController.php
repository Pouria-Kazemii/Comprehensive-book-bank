<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookirSubject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    // list subject
    public function find(Request $request)
    {
        $title = $request["title"];
        $currentPageNumber = $request["currentPageNumber"];
        $data = null;
        $status = 404;
        $pageRows = 50;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // read books
        $subjects = BookirSubject::orderBy('xsubject', 'asc');
        if($title != "") $subjects->where('xsubject', 'like', "%$title%");
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
        $subjects = BookirSubject::orderBy('xsubject', 'asc');
        if($title != "") $subjects->where('xsubject', 'like', "%$title%");
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
}
