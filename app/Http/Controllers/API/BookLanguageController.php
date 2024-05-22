<?php

namespace App\Http\Controllers\Api;

use App\Models\BookLanguage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookLanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 404;
        $bookLanguages = BookLanguage::where('name','like', "%$searchWord%")->get();
        if ($bookLanguages != null and count($bookLanguages) > 0) {
            foreach ($bookLanguages as $book_language_items) {
                $data[] =
                [
                    "id" => $book_language_items->id,
                    "value" => $book_language_items->name,
                ];
            }
        }
        if ($data != null or $bookLanguages != "") $status = 200;

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
