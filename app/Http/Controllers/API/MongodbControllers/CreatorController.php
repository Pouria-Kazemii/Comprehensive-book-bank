<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\BookIrPublisher;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;
class CreatorController extends Controller
{

    ///////////////////////////////////////////////General///////////////////////////////////////////////////
    public function lists(Request $request, $isNull = false, $defaultWhere = true, $where = [], $subjectId = 0, $mainCreatorId = 0, $publisherId = 0)
    {
        $start = microtime(true);
        $roleName = (isset($request["roleName"]) && !empty($request["roleName"])) ? $request["roleName"] : "";
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xcreatorname";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 1;
        $pageRows = (isset($request["perPage"]) && !empty($request["perPage"])) ? (int)$request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $totalPages = 0 ;
        $totalRows = 0;
        $data = [];
        $status = 200;

        if (!$isNull) {
            // Read books
            $creatorsQuery = BookIrCreator::query();

            if (!empty($searchText)) {
                $creatorsQuery->where(['$text' => ['$search' => $searchText]]);
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
                if (count($where) > 0) {
                    if (count($where[0]) == 2) {
                        $creatorsQuery->where(function ($query) use ($where) {
                            $query->where($where[0][0], $where[0][1]); // Apply the first condition using where()
                            // Apply subsequent conditions using orWhere()
                            for ($i = 1; $i < count($where); $i++) {
                                $query->orWhere($where[$i][0], $where[$i][1]);
                            }
                        });
                    };
                }
            }

            $creatorsQuery->orderBy($column, $sortDirection);
            $totalRows = $creatorsQuery->count();
            $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

            $creators = $creatorsQuery->skip($offset)->take($pageRows)->get();
            if ($creators->isNotEmpty()) {
                $creatorIds = $creators->pluck('_id')->toArray();
                // Batch fetch all necessary counts in one go using aggregation
                foreach ($creatorIds as $creatorId) {
                    $book = BookIrBook2::where('partners.xcreator_id', $creatorId);

                    if ($subjectId > 0) {
                        $book->where('subjects.xsubject_id' ,(int) $subjectId) ;
                    }

                    if ($publisherId > 0) {
                        $book->where('publisher.xpublisher_id' , $publisherId);
                    }

                    if ($mainCreatorId > 0) {
                        $book->where('partners.xcreator_id' , $mainCreatorId);
                    }

                    $bookCounts[$creatorId] = $book->count();
                }
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
            }
        }

        $end = microtime(true);
        $elapsedTime = $end - $start;
        // Response
        return response()->json([
            "status" => $status,
            "message" =>  "ok" ,
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

        $partners = BookIrBook2::where('publisher.xpublisher_id', $publisherId)->pluck('partners');
        $where = [];
        foreach ($partners as $partner) {
            foreach ($partner as $key => $value) {
                $where [] = ['_id', new ObjectId($value['xcreator_id'])];
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


        $creator = BookIrCreator::where('_id', new ObjectId($creatorId))->first();
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
                    $creator->iranketabinfo != null ?[
                    'englishName' => $creator->iranketabinfo['enName'],
                    'description' => $creator->iranketabinfo['partnerDesc'],
                    'image' => $creator->iranketabinfo['image']
                    ]: ''
                ];
        }

        if ($dataMaster != null) $status = 200;
        $end = microtime(true);
        $time = $end - $start;
        // response
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
