<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrPublisher;
use App\Models\MongoDBModels\DioSubject;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;

class PublisherController extends Controller
{
    ///////////////////////////////////////////////General///////////////////////////////////////////////////
    public function lists(Request $request, $defaultWhere = true, $isNull = false, $where = [], $creatorId = "")
    {
        $start = microtime(true);
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xpublishername";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 1;
        $pageRows = (isset($request["perPage"]) && !empty($request["perPage"])) ? (int)$request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $totalPages = 0 ;
        $totalRows = 0;
        $data = [];
        $status = 200;

        if (!$isNull) {
                $matchConditions = ['xpublishername' => ['$ne' => '']];

                if (!empty($searchText)) {
                    $matchConditions['$text'] = ['$search' => $searchText];
                }

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
                $totalRows = BookIrPublisher::raw(function ($collection) use ($matchConditions) {
                    return $collection->countDocuments($matchConditions);
                });

                $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

                // Fetch the paginated results
                $publishers = BookIrPublisher::raw(function ($collection) use ($matchConditions, $offset, $pageRows, $column, $sortDirection, $searchText) {
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

                $publishers = iterator_to_array($publishers);

                if (!empty($publishers)) {
                    foreach ($publishers as $publisher) {
                        $data[] = [
                            "id" => $publisher['_id'],
                            "name" => $publisher['xpublishername'],
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
                    "totalRows" => $totalRows,
                    "creatorId" => $creatorId
                ],
                'time' => $elapsedTime
            ], $status);
    }


    function unique_multidim_array($array, $keys) {
        $temp_array = [];
        $key_array = [];

        foreach ($array as $val) {
            // Generate a unique hash based on the specified keys
            $hash = '';
            foreach ($keys as $key) {
                $hash .= $val[$key];
            }

            // Check if the hash already exists in the key array
            if (!isset($key_array[$hash])) {
                // Add the hash to the key array and the value to the temp array
                $key_array[$hash] = true;
                $temp_array[] = $val;
            }
        }

        return $temp_array;
    }

    ///////////////////////////////////////////////Find///////////////////////////////////////////////////
    public function find(Request $request)
    {
        return $this->lists($request);
    }

    ///////////////////////////////////////////////Creator///////////////////////////////////////////////////
    public function findByCreator(Request $request)
    {
        $creatorId = $request["creatorId"];

        $pipeline = [
            ['$match' => ['partners.xcreator_id' => $creatorId]], // Change 'partners' to 'publishers'
            ['$unwind' => '$publisher'],
            ['$group' => ['_id' => '$publisher.xpublisher_id']]
            ];

        $publishers = BookIrBook2::raw(function($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        $publishers = $publishers->toArray();
        $idArray = array_column($publishers, '_id');

        $uniqueIds = array_unique($idArray);
        $where = [];

        foreach ($uniqueIds as $uniqueId) {
            $where[] = ['_id', new ObjectId($uniqueId)];
        }

        return $this->lists($request, false,false, $where, $creatorId);
    }

    ///////////////////////////////////////////////Subject///////////////////////////////////////////////////
    public function findBySubject(Request $request)
    {
        $subjectId = $request["subjectId"];

        $subjectId = preg_replace("/[^0-9]/", "", $subjectId); // Remove non-numeric characters
        $integerSubjectId = (int)$subjectId;

        $pipeline = [
            ['$match' => ['subjects.xsubject_id' => $integerSubjectId]],
            ['$unwind' => '$publisher'],
            ['$group' => ['_id' => '$publisher.xpublisher_id']]
        ];

        $publishers = BookIrBook2::raw(function($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        $where = [];
        foreach ($publishers as $publisher) {
            $where[] = ['_id', new ObjectId($publisher->_id)];
        }
        return $this->lists($request, false, false, $where);
    }

    ///////////////////////////////////////////////Search///////////////////////////////////////////////////
    public function search(Request $request)
    {
        $start = microtime(true);
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 200;


        // read
        $publishers = BookIrPublisher::where(['$text' => ['$search' => $searchWord]])->orderBy('xpublishername', 1)->get();
        if ($publishers != null and count($publishers) > 0) {
            foreach ($publishers as $publisher) {
                $data[] =
                    [
                        "id" => $publisher->_id,
                        "value" => $publisher->xpublishername,
                    ];
            }
        }
        $end = microtime(true);
        $time = $end - $start;
        // response
        return response()->json(
            [
                "status" => $status,
                "message" => "ok",
                "data" => ["list" => $data],
                'time' => $time
            ],
            $status
        );
    }

    ///////////////////////////////////////////////Detail///////////////////////////////////////////////////
    public function detail(Request $request)
    {
        $start = microtime(true);
        $publisherId = $request["publisherId"];
        $dataMaster = null;
        $status = 200;

        // read
        $publisher = BookIrPublisher::where('_id', new ObjectId($publisherId))->first();
        if ($publisher != null and $publisher->_id > 0) {
            $dataMaster =
                [
                    "name" => $publisher->xpublishername,
                    "manager" => $publisher->xmanager,
                    "activity" => $publisher->xactivity,
                    "place" => $publisher->xplace,
                    "address" => $publisher->xaddress,
                    "zipCode" => $publisher->xzipcode,
                    "phone" => $publisher->xphone,
                    "cellphone" => $publisher->xcellphone,
                    "fax" => $publisher->xfax,
                    "type" => $publisher->xtype,
                    "email" => $publisher->xemail,
                    "site" => $publisher->xsite,
                    "image" => $publisher->ximageurl,
                ];
        }

        $end = microtime(true);
        $time = $end - $start;
        // response
        return response()->json(
            [
                "status" => $status,
                "message" =>"ok" ,
                "data" => ["master" => $dataMaster] ,
                'time' => $time
            ],
            $status
        );
    }

    ///////////////////////////////////////////////Annual Activity By Title///////////////////////////////////////////////////
    public function annualActivityByTitle(Request $request)
    {
        $start = microtime(true);
        $publisherId = $request["publisherId"];
        $yearPrintCountData = null;
        $status = 200;

        // read books for year printCount by title
        $books = BookIrBook2::where('publisher.xpublisher_id', $publisherId)->orderBy('xpublishdate_shamsi', 1)->get();
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
                "message" =>"ok",
                "data" => ["yearPrintCount" => $yearPrintCountData] ,
                'time' => $time
            ],
            $status
        );
    }

    ///////////////////////////////////////////////Annual Activity By Circulation///////////////////////////////////////////////////
    public function annualActivityByCirculation(Request $request)
    {
        $start = microtime(true);
        $publisherId = $request["publisherId"];
        $yearPrintCountData = null;
        $status = 200;

        // read books for year printCount by circulation
        $books = BookIrBook2::where('publisher.xpublisher_id', $publisherId)->orderBy('xpublishdate_shamsi', 1)->get();
        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $year = $book->xpublishdate_shamsi;
                $printCount = $book->xcirculation;

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
                "data" => ["yearPrintCount" => $yearPrintCountData] ,
                'time' => $time
            ],
            $status
        );
    }

    ///////////////////////////////////////////////Translate Authorship///////////////////////////////////////////////////
    public function translateAuthorship(Request $request)
    {
        $start = microtime(true);
        $publisherId = $request["publisherId"];
        $data = null;
        $status = 200;

        // read books
        $books = BookIrBook2::where('publisher.xpublisher_id', $publisherId)->orderBy('xpublishdate_shamsi', 1)->get();
        if ($books != null and count($books) > 0) {
            $totalBooks = count($books);
            $data["authorship"] = 0;
            $data["translate"] = 0;
            $data['unknown'] = 0;

            foreach ($books as $book) {
                if ($book->is_translate == 1){
                    $data['authorship'] += 1;
                }elseif($book->is_translate == 2){
                    $data['translate'] += 1;
                }elseif($book->is_translate == 3){
                    $data['unknown'] += 1;
                }
            }

            $dataTmp = null;
            $dataTmp["تالیف"] = ($data["authorship"] > 0) ? round(($data["authorship"] / $totalBooks) * 100, 2) : 0;
            $dataTmp["ترجمه"] = ($data["translate"] > 0) ? round(($data["translate"] / $totalBooks) * 100, 2) : 0;
            $dataTmp["نامشخص"] = ($data["unknown"] > 0) ? round(($data["unknown"] / $totalBooks) * 100, 2) : 0;
            //
            $data = ["label" => array_keys($dataTmp), "value" => array_values($dataTmp)];
        }

        $end = microtime(true);
        $time = $end - $start;
        // response
        return response()->json(
            [
                "status" => $status,
                "message" => "ok",
                "data" => ["data" => $data] ,
                'time' => $time
            ],
            $status
        );
    }

    ///////////////////////////////////////////////Statistic Subject///////////////////////////////////////////////////
    public function statisticSubject(Request $request)
    {
        $start = microtime(true);
        $publisherId = $request["publisherId"];
        $data = [];
        $status = 200;
        $subjects = BookIrBook2::raw(function ($collection) use($publisherId){
            return $collection->aggregate([
                [
                    '$match' =>  [
                        'publisher.xpublisher_id' => $publisherId
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            '$first' => '$diocode_subject'
                        ],
                        'count' => ['$sum' => 1]
                    ]
                ]
            ]);
        });

        foreach ($subjects as $subject){
            $storageArray = $subject->_id->getArrayCopy();
            $value = reset($storageArray);
            $data['label'][] = $value;
            $data['value'][]  = $subject->count;
        }

        $end = microtime(true);
        $time = $end-$start;
        // response
        return response()->json(
            [
                "status" => $status,
                "message" => "ok",
                "data" => ["data" => $data] ,
                'time' => $time
            ],
            $status
        );
    }

    ///////////////////////////////////////////////Publisher Role///////////////////////////////////////////////////
    public function publisherRole(Request $request)
    {
        $start = microtime(true);
        $publisherId = $request["publisherId"];
        $data = null;
        $status = 200;
        $roles = [];
        $partners = [];
        $role_partner = [];
        $partnerCount = 0;
        $bookCount = 0;

        $books = BookIrBook2::where('publisher.xpublisher_id', $publisherId)->get();
        $book_partners = BookIrBook2::where('publisher.xpublisher_id', $publisherId)->pluck('partners');
        foreach ($book_partners as $book_partner) {
            foreach ($book_partner as $key => $value) {
                $roles [] = $value['xrule'];
                $partners [] = $value;
            }
        }
        $unique_roles = array_unique($roles);
        $unique_partners = $this->unique_multidim_array($partners , ['xcreator_id' , 'xrule']);

        foreach ($unique_roles as $key => $unique_role) {

            foreach ($unique_partners as $unique_partner) {
                if ($unique_partner['xrule'] == $unique_role) {
                    $bookCount = BookIrBook2::where('publisher.xpublisher_id', $publisherId)->where('partners.xcreator_id' , $unique_partner['xcreator_id'])->where('partners.xrule',$unique_role)->count();
                    $role_partner [] = [$unique_partner['xcreator_id'] => ['partner_id' => $unique_partner['xcreator_id'], 'partnername' => $unique_partner['xcreatorname'] , 'book_count' => $bookCount]];
                    $partnerCount++;
                }
            }

            $data [] = [
                'role_name' => $unique_role,
                'partner_count' => $partnerCount,
                'partners' => $role_partner

            ];
            $role_partner = [];
            $partnerCount = 0;
            $bookCount = 0;
        }

        $end= microtime(true);
        $time = $end - $start;
        return response()->json(
            [
                "status" => $status,
                "message" => "ok",
                "data" => ["data" => $data] ,
                'time' => $time
            ],
            $status
        );
    }
}
