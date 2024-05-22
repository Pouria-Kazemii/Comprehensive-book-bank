<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Http\Request;

class BookLanguageController extends Controller
{
    ///////////////////////////////////////////////List///////////////////////////////////////////////////
    public function list(Request $request)
    {
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 404;
        $languages = [];
        $booksLanguages =BookIrBook2::where('languages.name','like', "%$searchWord%")->pluck('languages');
        foreach ($booksLanguages as $bookLanguages) {
            foreach ($bookLanguages as $bookLanguage) {
                $languages [] = $bookLanguage['name'];
            }
        }
        if ($languages != null and count($languages ) > 0) {
            $languages = array_unique($languages);
            $matches = [];
            foreach ($languages as $key => $value) {
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
        return response()->json(
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $data]
            ],
            $status
        );
    }

}
