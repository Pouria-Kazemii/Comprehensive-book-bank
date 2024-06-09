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
    public function lists(Request $request, $isNull = false,$defaultWhere = true, $where = [], $subjectId = 0, $mainCreatorId = 0, $publisherId = 0)
    {
        $roleName = (isset($request["roleName"])) ? $request["roleName"] : "";
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"] : "xcreatorname";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"]) ? $request["perPage"] : 50;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        if (!$isNull) {
            // read books
            $creators = BookIrCreator::orderBy($column, $sortDirection);
            if ($searchText != "") $creators->where('xcreatorname', 'like', "%$searchText%");

            if ($roleName != "") {
                $creatorsId = [];
                $partners = BookIrBook2::where('partners.xrule', $roleName)->pluck('partners');
                foreach ($partners as $partner) {
                    foreach ($partner as $key => $value) {
                        if ($value['xrule'] == $roleName) {
                            $creatorsId [] = [$value['xcreator_id']];
                        }
                    }
                }
                $creators->where(function ($query) use ($creatorsId) {
                    $query->where('_id', $creatorsId[0][0]);
                    for ($i = 1; $i < count($creatorsId); $i++) {
                        $query->orWhere('_id', $creatorsId[$i][0]);
                    }
                });
            }

            if (!$defaultWhere) {
                if (count($where) > 0) {
                    if (count($where[0]) == 2) {
                        $creators->where(function ($query) use ($where) {
                            $query->where($where[0][0], $where[0][1]); // Apply the first condition using where()
                            // Apply subsequent conditions using orWhere()
                            for ($i = 1; $i < count($where); $i++) {
                                $query->orWhere($where[$i][0], $where[$i][1]);
                            }
                        });
                    };
                } else {
                    return response()->json([
                        "status" => 404,
                        "message" => "not found",
                        "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
                    ], 404);
                }
            }

            $totalRows = $creators->count();
            $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;
            $creators = $creators->skip($offset)->take($pageRows)->get();

            if ($creators != null and count($creators) > 0) {
                foreach ($creators as $creator) {
                    $creatorId = $creator->_id;
                    if ($subjectId > 0) $bookCount = BookIrBook2::orderBy('xpublishdate_shamsi', -1)->where('partners.xcreator_id', $creatorId)->where('subjects.xsubject_id', (int)$subjectId)->count();
                    else if ($publisherId > 0) $bookCount = BookIrBook2::orderBy('xpublishdate_shamsi', -1)->where('partners.xcreator_id', $creatorId)->where('publisher.xpublisher_id', $publisherId)->count();
                    elseif ($mainCreatorId > 0 and $mainCreatorId != $creatorId) $bookCount = BookIrBook2::where('partners.xcreator_id', $creatorId)->where('partners.xcreator_id', $mainCreatorId)->count();
                    elseif ($subjectId == 0 and $publisherId == 0) $bookCount = BookIrBook2::where('partners.xcreator_id', $creatorId)->count();
                    else  $bookCount = 0;
                    $data[] =
                        [
                            "publisherId" => $publisherId,
                            "publisherName" => $publisherId > 0 ? BookIrPublisher::where('_id', $publisherId)->first()->xpublishername : "",
                            "mainCreatorId" => $mainCreatorId,
                            "mainCreatorName" => $mainCreatorId > 0 ? BookIrCreator::where('_id', $mainCreatorId)->first()->xcreatorname : "",
                            "subjectId" => $subjectId,
                            "id" => $creator->_id,
                            "bookCount" => $bookCount,
                            "name" => $creator->xcreatorname,
                        ];
                }
                $status = 200;
            }
        }
        if ($data != null)
            // response
            return response()->json(
                [
                    "status" => $status,
                    "message" => $status == 200 ? "ok" : "not found",
                    "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
                ],
                $status
            );
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
            //TODO should make a script for add bookIranKetabPartner to BookIrCreators
//            BookIranKetabPartner::where('partner_master_id', $creatorId)->first();
            $dataMaster =
                [
                    "name" => $creator->xcreatorname,
                    "roles" =>  $roles->map(function($role) {
                        return ['title' => $role->_id];
                    }),
                    "image" => !empty($partnerInfo->partnerImage) ? $partnerInfo->partnerImage : null,
                    "desc" => !empty($partnerInfo->partnerDesc) ? $partnerInfo->partnerDesc : null,
                    "enName" => !empty($partnerInfo->partnerEnName) ? $partnerInfo->partnerEnName : null,
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
