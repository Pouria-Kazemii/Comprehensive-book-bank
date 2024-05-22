<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    ///////////////////////////////////////////////Search///////////////////////////////////////////////////
    public function search(Request $request)
    {
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 404;
        $roles = [];

        // read
        $partnersRule = BookIrBook2::where('partners.xrule', 'LIKE' , "%$searchWord%")->pluck('partners');
        foreach ($partnersRule as $partnerRules) {
            foreach ($partnerRules as $partnerRule) {
                $roles [] = $partnerRule['xrule'];
            }
        }
        if ($roles != null and count($roles ) > 0) {
            $roles = array_unique($roles);
            $matches = [];
            foreach ($roles as $key => $value) {
                if (stripos($value, $searchWord) !== false) {
                    $matches[] = $value;
                }
            }
            foreach ($matches as $match) {
                {
                    $data[] =
                        [
                            "value" => $match,
                        ];
                    $status = 200;
                }
            }
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
