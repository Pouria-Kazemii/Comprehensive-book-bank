<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\BookIrPublisher;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;
use Monolog\Handler\IFTTTHandler;

class CreatorController extends Controller
{

    ///////////////////////////////////////////////General///////////////////////////////////////////////////
    public function lists(Request $request, $isNull = false, $defaultWhere = true, $where = [], $subjectId = 0, $mainCreatorId = 0, $publisherId = 0)
    {
        $start = microtime(true);
        $roleName = (isset($request["roleName"]) && !empty($request["roleName"])) ? $request["roleName"] : "";
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xcreatorname";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] == (1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 1;
        $pageRows = (isset($request["perPage"]) && !empty($request["perPage"])) ? (int)$request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $totalPages = 0;
        $totalRows = 0;
        $data = [];
        $status = 200;

        if (!$isNull) {
            $matchConditions = [];

            if (!empty($searchText)) {
                $matchConditions['$text'] = ['$search' => $searchText];
            }

//            if (!empty($roleName)) {
//                $roleFilter = [
//                    ['$unwind' => '$partners'],
//                    ['$match' => ['partners.xrule' => $roleName]],
//                    ['$group' => ['_id' => '$partners.xcreator_id']],
//                ];
//                $creatorsId = BookIrBook2::raw(function ($collection) use ($roleFilter) {
//                    return $collection->aggregate($roleFilter);
//                })->pluck('_id')->toArray();
//
//                $matchConditions['_id'] = ['$in' => $creatorsId];
//            }

            if (!$defaultWhere) {
                if (count($where) > 0) {
                    if (count($where[0]) == 2) {
                        $orConditions = [];
                        foreach ($where as $condition) {
                            $orConditions[] = [$condition[0] => $condition[1]];
                        }
                        $matchConditions['$or'] = $orConditions;
                    }
                }
            }

            // Perform the count query
            $totalRows = BookIrCreator::raw(function ($collection) use ($matchConditions) {
                return $collection->countDocuments($matchConditions);
            });

            $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

            // Fetch the paginated results
            $creators = BookIrCreator::raw(function ($collection) use ($matchConditions, $offset, $pageRows, $column, $sortDirection, $searchText) {
                if ($searchText == "") {
                    $pipeline = [
                        ['$match' => (object)$matchConditions],
                        ['$sort' => [$column => $sortDirection]],
                        ['$skip' => $offset],
                        ['$limit' => $pageRows]
                    ];
                }else{
                    $pipeline = [
                        ['$match' => (object)$matchConditions],
                        ['$addFields' => ['score' => ['$meta' => 'textScore']]],
                        ['$sort' => ['score' => -1]],
                        ['$skip' => $offset],
                        ['$limit' => $pageRows]
                    ];
                }
                return $collection->aggregate($pipeline);
            });

            $creators = iterator_to_array($creators);

            if (!empty($creators)) {

                foreach ($creators as $creator) {
                    $creatorId = $creator['_id'];

                    $data[] = [
                        "publisherId" => $publisherId,
                        "publisherName" => $publisherId > 0 ? BookIrPublisher::find($publisherId)->xpublishername : "",
                        "mainCreatorId" => $mainCreatorId,
                        "mainCreatorName" => $mainCreatorId > 0 ? BookIrCreator::find($mainCreatorId)->xcreatorname : "",
                        "subjectId" => $subjectId,
                        "id" => $creator['_id'],
                        "bookCount" => BookIrBook2::where('partners.xcreator_id' , $creatorId)->count(),
                        "name" => $creator['xcreatorname'],
                    ];
                }
            }
        }

        $end = microtime(true);
        $elapsedTime = $end - $start;

        return response()->json([
            "status" => $status,
            "message" => "ok",
            "data" => [
                "list" => $data,
                "currentPageNumber" => $currentPageNumber,
                "totalPages" => $totalPages,
                "pageRows" => $pageRows,
                "totalRows" => $totalRows
            ],
            'time' => $elapsedTime,
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
                $where [] = ['_id', new ObjectId($value['xcreator_id'])];
            }
        }
        return $this->lists($request,false ,false, $where, $subjectId);
    }

    ///////////////////////////////////////////////Publisher///////////////////////////////////////////////////
    public function findByPublisher(Request $request)
    {
        $publisherId = $request["publisherId"];

        // Use aggregation pipeline to get distinct creator ids
        $pipeline = [
            ['$match' => ['publisher.xpublisher_id' => $publisherId]],
            ['$unwind' => '$partners'],
            ['$group' => ['_id' => '$partners.xcreator_id']]
        ];

        $partners = BookIrBook2::raw(function($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        $where = [];
        foreach ($partners as $partner) {
            $where[] = ['_id', new ObjectId($partner->_id)];
        }

        return $this->lists($request, false, false, $where, 0, 0, $publisherId);
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
                $where [] = ['_id', new ObjectId($value['xcreator_id'])];
            }
        }
        return $this->lists($request, false , false, $where, 0, $creatorId);
    }
      ///////////////////////////////////////////////Annual Activity///////////////////////////////////////////////////
    public function annualActivity(Request $request)
    {
        $start = microtime(true);
        $creatorId = $request["creatorId"];
        $status = 200;
        $yearPrintCountData = null;
        // read books for year printCount by title
        $books = BookIrBook2::where('partners.xcreator_id' ,$creatorId)->get();
        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $year = $book->xpublishdate_shamsi;
                $printCount = 1;

                $yearPrintCountData[$year] = ["year" => $year, "printCount" => (isset($yearPrintCountData[$year])) ? $printCount + $yearPrintCountData[$year]["printCount"] : $printCount];
            }

            $yearPrintCountData = ["label" => array_column($yearPrintCountData, 'year'), "value" => array_column($yearPrintCountData, 'printCount')];
        }

        $end = microtime(true);
        $time = $end - $start;
        // response
        return response()->json(
            [
                "status" => $status,
                "message" => "ok",
                "data" => ["yearPrintCount" => $yearPrintCountData],
                'time' => $time
            ],
            $status
        );
    }
    ///////////////////////////////////////////////Detail///////////////////////////////////////////////////
    public function detail(Request $request)
    {
        $start = microtime(true);
        $creatorId = $request["creatorId"];
        $status = 200;
        $dataMaster = null;

        // Fetch the creator using indexed query
        $creator = BookIrCreator::find(new ObjectId($creatorId));


        if ($creator) {
            $roles = BookIrBook2::raw(function ($collection) use ($creatorId) {
                return $collection->aggregate([
                    ['$unwind' => '$partners'],
                    ['$match' => ['partners.xcreator_id' => (string) $creatorId]],
                    ['$group' => ['_id' => '$partners.xrule']],
                    ['$sort' => ['_id' => 1]]
                ]);
            });

            $roles = iterator_to_array($roles);

            // Collect unique roles
            $uniqueRoles = [];
            foreach ($roles as $role) {
                if (!in_array($role['_id'], $uniqueRoles)) {
                    $uniqueRoles[] = $role['_id'];
                }
            }

            $roleTitles = array_map(function($role) {
                return ['title' => $role];
            }, $uniqueRoles);

            $dataMaster = [
                "name" => $creator->xcreatorname,
                "roles" => $roleTitles,
            ];

            if (!empty($creator->iranketabinfo)) {
                $dataMaster = array_merge($dataMaster, [
                    'englishName' => $creator->iranketabinfo['enName'] ?? '',
                    'description' => $creator->iranketabinfo['partnerDesc'] ?? '',
                    'image' => $creator->iranketabinfo['image'] ?? ''
                ]);
            }
        }

        $end = microtime(true);
        $time = $end - $start;

        // Response
        return response()->json(
            [
                "status" => $status,
                "message" => "ok",
                "data" => ["master" => $dataMaster],
                'time' => $time
            ],
            $status
        );
    }
    ///////////////////////////////////////////////Search///////////////////////////////////////////////////
    public function search(Request $request)
    {
        $start = microtime(true);
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 200;
        // read
        $creators = BookIrCreator::where(['$text' => ['$search' => $searchWord]])->orderBy('xcreatorname', 1)->get();
        if ($creators != null and count($creators) > 0) {
            foreach ($creators as $creator) {
                $data[] =
                    [
                        "id" => $creator->_id,
                        "value" => $creator->xcreatorname,
                    ];
            }
        }
        $end = microtime(true);
        $time = $end - $start;
        // response
        return response()->json(
            [
                "status" => $status,
                "message" =>"ok",
                "data" => ["list" => $data],
                'time' => $time
            ],
            $status
        );
    }
}
