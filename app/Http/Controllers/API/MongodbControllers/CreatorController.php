<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\BookIrPublisher;
use Illuminate\Http\Request;

class CreatorController extends Controller
{

    ///////////////////////////////////////////////General///////////////////////////////////////////////////
    public function lists(Request $request, $isNull = false, $defaultWhere = true, $where = [], $subjectId = 0, $mainCreatorId = 0, $publisherId = 0)
    {
        $roleName = $request->input("roleName", "");
        $searchText = $request->input("searchText", "");
        $column = $request->input("column", "xcreatorname");
        $sortDirection = (int)$request->input("sortDirection", 1);
        $currentPageNumber = (int)$request->input("page", 1);
        $pageRows = (int)$request->input("perPage", 50);

        $offset = ($currentPageNumber - 1) * $pageRows;

        $data = [];
        $status = 404;

        if (!$isNull) {
            // Read books
            $creatorsQuery = BookIrCreator::orderBy($column, $sortDirection);

            if (!empty($searchText)) {
                $creatorsQuery->where('xcreatorname', 'like', "%$searchText%");
            }

            // Role filtering using aggregation pipeline
            if (!empty($roleName)) {
                $creatorsId = BookIrBook2::raw(function ($collection) use ($roleName) {
                    return $collection->aggregate([
                        ['$unwind' => '$partners'],
                        ['$match' => ['partners.xrule' => $roleName]],
                        ['$group' => ['_id' => '$partners.xcreator_id']],
                    ]);
                })->pluck('_id')->toArray();

                $creatorsQuery->whereIn('_id', $creatorsId);
            }

            // Default and additional where conditions
            if (!$defaultWhere) {
                $creatorsQuery->where(function ($query) use ($where) {
                    foreach ($where as $condition) {
                        $query->orWhere($condition[0], $condition[1]);
                    }
                });
            }

            $totalRows = $creatorsQuery->count();
            $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

            $creators = $creatorsQuery->skip($offset)->take($pageRows)->get();

            if ($creators->isNotEmpty()) {
                $creatorIds = $creators->pluck('_id')->toArray();

                // Batch fetch all necessary counts in one go using aggregation
                $bookCounts = BookIrBook2::raw(function ($collection) use ($creatorIds, $subjectId, $publisherId, $mainCreatorId) {
                    $pipeline = [
                        ['$match' => ['partners.xcreator_id' => ['$in' => $creatorIds]]],
                        ['$unwind' => '$partners'],
                        ['$match' => ['partners.xcreator_id' => ['$in' => $creatorIds]]]
                    ];

                    if ($subjectId > 0) {
                        $pipeline[] = ['$match' => ['subjects.xsubject_id' => $subjectId]];
                    }

                    if ($publisherId > 0) {
                        $pipeline[] = ['$match' => ['publisher.xpublisher_id' => $publisherId]];
                    }

                    if ($mainCreatorId > 0) {
                        $pipeline[] = ['$match' => ['partners.xcreator_id' => $mainCreatorId]];
                    }

                    $pipeline[] = [
                        '$group' => [
                            '_id' => '$partners.xcreator_id',
                            'book_count' => ['$sum' => 1],
                        ],
                    ];

                    return $collection->aggregate($pipeline);
                })->pluck('book_count', '_id')->toArray();

                foreach ($creators as $creator) {
                    $creatorId = $creator->_id;
                    $bookCount = $bookCounts[$creatorId] ?? 0;

                    $data[] = [
                        "publisherId" => $publisherId,
                        "publisherName" => $publisherId > 0 ? BookIrPublisher::find($publisherId)->xpublishername : "",
                        "mainCreatorId" => $mainCreatorId,
                        "mainCreatorName" => $mainCreatorId > 0 ? BookIrCreator::find($mainCreatorId)->xcreatorname : "",
                        "subjectId" => $subjectId,
                        "id" => $creator->_id,
                        "bookCount" => $bookCount,
                        "name" => $creator->xcreatorname,
                    ];
                }
                $status = 200;
            }
        }

        // Response
        return response()->json([
            "status" => $status,
            "message" => $status == 200 ? "ok" : "not found",
            "data" => [
                "list" => $data,
                "currentPageNumber" => $currentPageNumber,
                "totalPages" => $totalPages,
                "pageRows" => $pageRows,
                "totalRows" => $totalRows
            ]
        ], $status);
    }
    ///////////////////////////////////////////////Find///////////////////////////////////////////////////
    public function find(Request $request)
    {
        return $this->lists($request);
    }
    ///////////////////////////////////////////////Subject///////////////////////////////////////////////////
    public function findBySubject(Request $request)
    {
        $subjectId = $request["subjectId"];

        $partners = BookIrBook2::where('subjects.xsubject_id', (int)$subjectId)->pluck('partners');
        $where = [];
        foreach ($partners as $partner) {
            foreach ($partner as $key => $value) {
                $where [] = ['_id', $value['xcreator_id']];
            }
        }
        return $this->lists($request,false ,false, $where, $subjectId);
    }

    ///////////////////////////////////////////////Publisher///////////////////////////////////////////////////
    public function findByPublisher(Request $request)
    {
        $publisherId = $request["publisherId"];

        $partners = BookIrBook2::where('publisher.xpublisher_id', $publisherId)->pluck('partners');
        $where = [];
        foreach ($partners as $partner) {
            foreach ($partner as $key => $value) {
                $where [] = ['_id', $value['xcreator_id']];
            }
        }
        return $this->lists($request,false ,false, $where, 0,  0, $publisherId);
    }
    ///////////////////////////////////////////////Creators///////////////////////////////////////////////////
    public function findByCreator(Request $request)
    {
        $creatorId = $request["creatorId"];
        $partners = BookIrBook2::where('partners.xcreator_id', $creatorId)->pluck('partners');
        $where = [];
        foreach ($partners as $partner) {
            foreach ($partner as $key => $value) {
                if ($value['xcreator_id'] != $creatorId)
                $where [] = ['_id', $value['xcreator_id']];
            }
        }
        return $this->lists($request, false , false, $where, 0, $creatorId);
    }
    ///////////////////////////////////////////////Role///////////////////////////////////////////////////
    public function role(Request $request)
    {
        $data = null;
        $status = 404;

        $books = BookIrBook2::all();
        $uniqueRules = [];

        foreach ($books as $book) {
            $uniqueRules = array_merge($uniqueRules, array_column($book->partners, 'xrule'));
        }

        $uniqueRules = array_unique($uniqueRules);;
        if ($uniqueRules != null and count($uniqueRules) > 0) {
            foreach ($uniqueRules as $uniqueRule) {
                $data[] =
                    [
                        "name" => $uniqueRule,
                    ];
            }
            $status = 200;
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
    ///////////////////////////////////////////////Annual Activity///////////////////////////////////////////////////
    public function annualActivity(Request $request)
    {
        $creatorId = $request["creatorId"];
        $yearPrintCountData = null;

        // read books for year printCount by title
        $books = BookIrBook2::where('partners.xcreator_id' , $creatorId)->get();
        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $year = $book->xpublishdate_shamsi;
                $printCount = 1;

                $yearPrintCountData[$year] = ["year" => $year, "printCount" => (isset($yearPrintCountData[$year])) ? $printCount + $yearPrintCountData[$year]["printCount"] : $printCount];
            }

            $yearPrintCountData = ["label" => array_column($yearPrintCountData, 'year'), "value" => array_column($yearPrintCountData, 'printCount')];
        }

        $yearPrintCountData != null ? $status = 200:$status =404;

        // response
        return response()->json(
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["yearPrintCount" => $yearPrintCountData]
            ],
            $status
        );
    }
    ///////////////////////////////////////////////Detail///////////////////////////////////////////////////
    public function detail(Request $request)
    {
        $creatorId = $request["creatorId"];
        $status = 404;
        $dataMaster = null;


        $creator = BookIrCreator::where('_id', $creatorId)->first();
        if ($creator != null && $creator->_id > 0) {
            $creatorId = $creator->_id;

            $roles = BookIrBook2::raw(function ($collection) use ($creatorId) {
                return $collection->aggregate([
                    ['$unwind' => '$partners'],
                    ['$match' => ['partners.xcreator_id' => $creatorId]],
                    ['$project' => ['_id' => 0, 'role' => '$partners.xrule']],
                    ['$group' => ['_id' => '$role']],
                    ['$sort' => ['_id' => 1]]
                ]);
            });
            $dataMaster =
                [
                    "name" => $creator->xcreatorname,
                    "roles" =>  $roles->map(function($role) {
                        return ['title' => $role->_id];
                    }),
                    'englishName' => $creator->iranketabinfo['enName'],
                    'description' => $creator->iranketabinfo['partnerDesc'],
                    'image' => $creator->iranketabinfo['image']
                ];
        }

        if ($dataMaster != null) $status = 200;

        // response
        return response()->json(
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["master" => $dataMaster]
            ],
            $status
        );
    }
    ///////////////////////////////////////////////Search///////////////////////////////////////////////////
    public function search(Request $request)
    {
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 404;
        // read
        $creators = BookIrCreator::where('xcreatorname', '!=', '')->where('xcreatorname', 'like', "%$searchWord%")->orderBy('xcreatorname', 'asc')->get();
        if ($creators != null and count($creators) > 0) {
            foreach ($creators as $creator) {
                $data[] =
                    [
                        "id" => $creator->_id,
                        "value" => $creator->xcreatorname,
                    ];
            }

            $status = 200;
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
