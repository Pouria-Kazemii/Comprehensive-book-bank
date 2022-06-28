<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookirPublisher;
use App\Models\BookirRules;
use App\Models\BookirSubject;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // list subject
  
    // search
    public function search(Request $request)
    {
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 404;

        // read
        $subjects = BookirRules::where('xrole', '!=', '')->where('xrole', 'like', "%$searchWord%")->orderBy('xrole', 'asc')->get();
        if($subjects != null and count($subjects) > 0)
        {
            foreach ($subjects as $subject)
            {
                $data[] =
                    [
                        "id" => $subject->xid,
                        "value" => $subject->xrole,
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
}
