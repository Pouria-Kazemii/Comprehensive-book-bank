<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Http\Request;

class BookFormatController extends Controller
{
    ///////////////////////////////////////////////List///////////////////////////////////////////////////
    public function list()
    {
        $status = 404;
        $data = null;

        $formats = BookIrBook2::all()->pluck('xformat')->toArray();
        $formats = array_unique($formats);
        if ($formats != null and count($formats) > 0){
            foreach ($formats as $format){
                $data[] =
                    [
                        "value" => $format,
                    ];
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
