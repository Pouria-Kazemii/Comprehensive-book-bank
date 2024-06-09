<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Http\Request;

class BookCoverController extends Controller
{
    ///////////////////////////////////////////////List///////////////////////////////////////////////////
    public function list()
    {
        $status = 404;
        $data = null;

        $covers = BookIrBook2::all()->pluck('xcover')->toArray();
        $covers = array_unique($covers);
        if ($covers != null and count($covers) > 0){
            foreach ($covers as $cover){
                if ($cover != '') {
                    $data[] =
                        [
                            "value" => $cover
                        ];
                }
                $status = 200;
            }
        }
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
