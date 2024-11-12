<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrSubject;
use Collator;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    ///////////////////////////////////////////////List Find///////////////////////////////////////////////////
    public function find(Request $request)
    {
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xpublishdate_shamsi";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] == (1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 1;
        $pageRows = (isset($request["perPage"]) && !empty($request["perPage"])) ? (int)$request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $data = null;

        $pipeline = [];
        // Match conditions based on search criteria
        $matchConditions = [];

        if (!empty($searchText)) {
            $matchConditions['$text'] = ['$search' => $searchText];
        }

        if (!empty($matchConditions)) {
            $pipeline[] = ['$match' => $matchConditions];
        }

        if (!empty($searchText)) {
            $pipeline[] = ['$addFields' => ['score' => ['$meta' => 'textScore']]];
            $pipeline[] = ['$sort' => ['score' => ['$meta' => 'textScore']]];
        }else {
            $pipeline[] = ['$sort' => [$column => $sortDirection]];
        }
        $pipeline[] = ['$skip' => $offset];
        $pipeline[] = ['$limit' => $pageRows];



        $data = BookIrSubject::raw(function ($collection) use($pipeline){
            return $collection->aggregate($pipeline);
        });

        $totalRows = BookIrSubject::raw(function($collection) use($matchConditions){
            return $collection->countDocuments($matchConditions);
        });

        $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

        return response()->json
        (
            [
                "status" => 200,
                "message" => "ok" ,
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
        );
    }

    ///////////////////////////////////////////////Search///////////////////////////////////////////////////
    public function search(Request $request)
    {
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xpublishdate_shamsi";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] == (1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 1;
        $pageRows = (isset($request["perPage"]) && !empty($request["perPage"])) ? (int)$request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $data = null;

        $pipeline = [];
        // Match conditions based on search criteria
        $matchConditions = [];

        if (!empty($searchText)) {
            $matchConditions['$text'] = ['$search' => $searchText];
        }

        if (!empty($matchConditions)) {
            $pipeline[] = ['$match' => $matchConditions];
        }

        if (!empty($searchText)) {
            $pipeline[] = ['$addFields' => ['score' => ['$meta' => 'textScore']]];
            $pipeline[] = ['$sort' => ['score' => ['$meta' => 'textScore']]];
        }else {
            $pipeline[] = ['$sort' => [$column => $sortDirection]];
        }
        $pipeline[] = ['$skip' => $offset];
        $pipeline[] = ['$limit' => $pageRows];



        $data = BookIrSubject::raw(function ($collection) use($pipeline){
            return $collection->aggregate($pipeline);
        });

        $totalRows = BookIrSubject::raw(function($collection) use($matchConditions){
            return $collection->countDocuments($matchConditions);
        });

        $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

        return response()->json
        (
            [
                "status" => 200,
                "message" => "ok" ,
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
        );
    }
    ///////////////////////////////////////////////SearchForSelectComponent///////////////////////////////////////////////////
    public function searchForSelectComponent(Request $request)
    {
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xpublishdate_shamsi";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] == (1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 1;
        $pageRows = (isset($request["perPage"]) && !empty($request["perPage"])) ? (int)$request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $data = null;

        $pipeline = [];
        // Match conditions based on search criteria
        $matchConditions = [];

        if (!empty($searchText)) {
            $matchConditions['$text'] = ['$search' => $searchText];
        }

        if (!empty($matchConditions)) {
            $pipeline[] = ['$match' => $matchConditions];
        }

        if (!empty($searchText)) {
            $pipeline[] = ['$addFields' => ['score' => ['$meta' => 'textScore']]];
            $pipeline[] = ['$sort' => ['score' => ['$meta' => 'textScore']]];
        }else {
            $pipeline[] = ['$sort' => [$column => $sortDirection]];
        }
        $pipeline[] = ['$skip' => $offset];
        $pipeline[] = ['$limit' => $pageRows];



        $data = BookIrSubject::raw(function ($collection) use($pipeline){
            return $collection->aggregate($pipeline);
        });

        $totalRows = BookIrSubject::raw(function($collection) use($matchConditions){
            return $collection->countDocuments($matchConditions);
        });

        $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

        return response()->json
        (
            [
                "status" => 200,
                "message" => "ok" ,
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
        );
    }
}
