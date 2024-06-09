<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use Collator;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    ///////////////////////////////////////////////List Find///////////////////////////////////////////////////
    public function find(Request $request)
    {
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"] : "xsubject_name";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;

        $books = BookIrBook2::all();

        $uniqueSubjects = [];
        foreach ($books as $book) {
            $subjects = collect($book->subjects);
            $subjectPairs = $subjects->map(function ($subject) {
                return [
                    'xsubject_name' => $subject['xsubject_name'],
                    'xsubject_id' => $subject['xsubject_id'],
                ];
            });

            // Add unique subject pairs to uniqueSubjects collection
            foreach ($subjectPairs as $pair) {
                if (!in_array($pair, $uniqueSubjects)) {
                    $uniqueSubjects[] = $pair;
                }
            }
        }
        $collator = new Collator('fa_IR');
        if ($searchText != "") {
            $uniqueSubjects = array_filter($uniqueSubjects, function ($uniqueSubjects) use ($searchText) {
                return strpos($uniqueSubjects['xsubject_name'], $searchText) !== false;
            });
        }


        if ($sortDirection == 1) {
            usort($uniqueSubjects, function ($a, $b) use ($collator) {
                return -$collator->compare($a['xsubject_name'], $b['xsubject_name']);
            });

            $totalRows = count($uniqueSubjects);
            $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;
            $uniqueSubjects = array_slice($uniqueSubjects, $offset, $pageRows);


            usort($uniqueSubjects, function ($a, $b) use ($collator) {
                return $collator->compare($a['xsubject_name'], $b['xsubject_name']);
            });
        }


        if ($sortDirection == -1) {
            usort($uniqueSubjects, function ($a, $b) use ($collator) {
                return $collator->compare($a['xsubject_name'], $b['xsubject_name']);
            });

            $totalRows = count($uniqueSubjects);
            $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;
            $uniqueSubjects = array_slice($uniqueSubjects, $offset, $pageRows);


            usort($uniqueSubjects, function ($a, $b) use ($collator) {
                return -$collator->compare($a['xsubject_name'], $b['xsubject_name']);
            });
        }


        if ($uniqueSubjects != null and count($uniqueSubjects) > 0) {
            foreach ($uniqueSubjects as $uniqueSubject) {
                $data[] =
                    [
                        "id" => $uniqueSubject['xsubject_id'],
                        "name" => $uniqueSubject['xsubject_name'],
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
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
            $status
        );
    }

    ///////////////////////////////////////////////Search///////////////////////////////////////////////////
    public function search(Request $request)
    {
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $status = 404;

        $books = BookIrBook2::all();
        $uniqueSubjects = [];

        foreach ($books as $book) {
            $subjects = collect($book->subjects);
            $subjectPairs = $subjects->map(function ($subject) {
                return [
                    'xsubject_name' => $subject['xsubject_name'],
                    'xsubject_id' => $subject['xsubject_id'],
                ];
            });

            // Add unique subject pairs to uniqueSubjects collection
            foreach ($subjectPairs as $pair) {
                if (!in_array($pair, $uniqueSubjects)) {
                    $uniqueSubjects[] = $pair;
                }
            }
        }

        $collator = new Collator('fa_IR');
        if ($searchWord != "") {
            $uniqueSubjects = array_filter($uniqueSubjects, function ($uniqueSubjects) use ($searchWord) {
                return strpos($uniqueSubjects['xsubject_name'], $searchWord) !== false;
            });
        }


        usort($uniqueSubjects, function ($a, $b) use ($collator) {
                return $collator->compare($a['xsubject_name'], $b['xsubject_name']);
        });

        if($uniqueSubjects != null and count($uniqueSubjects) > 0)
        {
            $status = 200;
        }

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $uniqueSubjects]
            ],
            $status
        );
    }
    ///////////////////////////////////////////////SearchForSelectComponent///////////////////////////////////////////////////
    public function searchForSelectComponent(Request $request)
    {
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $status = 404;

        $books = BookIrBook2::all();
        $uniqueSubjects = [];

        foreach ($books as $book) {
            $subjects = collect($book->subjects);
            $subjectPairs = $subjects->map(function ($subject) {
                return [
                    'xsubject_name' => $subject['xsubject_name'],
                    'xsubject_id' => $subject['xsubject_id'],
                ];
            });

            // Add unique subject pairs to uniqueSubjects collection
            foreach ($subjectPairs as $pair) {
                if (!in_array($pair, $uniqueSubjects)) {
                    $uniqueSubjects[] = $pair;
                }
            }
        }

        $collator = new Collator('fa_IR');
        if ($searchWord != "") {
            $uniqueSubjects = array_filter($uniqueSubjects, function ($uniqueSubjects) use ($searchWord) {
                return strpos($uniqueSubjects['xsubject_name'], $searchWord) !== false;
            });
        }

        usort($uniqueSubjects, function ($a, $b) use ($collator) {
            return $collator->compare($a['xsubject_name'], $b['xsubject_name']);
        });

        if ($uniqueSubjects != null and count($uniqueSubjects) > 0) {
            $status = 200;
        }

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $uniqueSubjects]
            ],
            $status
        );
    }
}
