<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrSubject;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;

class ReportController extends Controller
{
    ///////////////////////////////////////////////Publishers///////////////////////////////////////////////////

    public function publisher(Request $request)
    {
        $start  = microtime(true);
        $translate = (isset($request["translate"])) ? $request["translate"] : 0;
        $authorship = (isset($request["authorship"])) ? $request["authorship"] : 0;
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? (int) $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? (int)$request["yearEnd"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xdiocode";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"]) ? (int)$request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $data = null;
        $status = 200;

        $pipeline[] = ['publisher.xpublisher_id' => $publisherId ];

        if ($authorship != 0){
            $pipeline[] = ['is_translate' => 1];
        }

        if ($translate != 0){
            $pipeline[] = ['is_translate' => 2] ;
        }

        if ($yearStart != 0){
            $pipeline[] = ['xpublishdate_shamsi' => ['$gte' => $yearStart]];
        }

        if($yearEnd != 0){
            $pipeline[] = ['xpublishdate_shamsi' => ['$lte' => $yearEnd]];
        }

        $diocodeBooks = BookIrBook2::raw(function ($collection) use ($pipeline,$column,$sortDirection,$offset,$pageRows) {
            return $collection->aggregate([
                ['$match' => ['$and' => $pipeline]] ,
                ['$group' => [
                    '_id' => '$xdiocode',
                    'xdiocode' => ['$first' => '$xdiocode'],
                    'titleCount' => ['$sum' => 1],
                    'total_circulation' => ['$sum' => '$xcirculation'],
                    'total_price' => ['$sum' => '$xtotal_price']
                ]],
                ['$facet' => [
                    'books' => [
                        ['$sort' => [$column => $sortDirection]],
                        ['$skip' => $offset],
                        ['$limit' => $pageRows]
                    ],
                    'totalGroups' => [
                        ['$group' => [
                            '_id' => null,
                            'count' => ['$sum' => 1]
                        ]]
                    ]
                ]]
            ]);
        });

        $totalRows =$diocodeBooks[0]->totalGroups[0]->count;

        $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

        foreach ($diocodeBooks[0]->books as $diocodeBook) {
            $data [] = [
                "diocode" => $diocodeBook->xdiocode,
                "total_circulation" => priceFormat($diocodeBook->total_circulation),
                "book_count" => $diocodeBook->titleCount,
                'total_price' => priceFormat($diocodeBook->total_price),
            ];
        }

        $end = microtime(true);
        $elapsedTime = $end - $start;
        // response
        return response()->json
        (
            [
                "status" => 200,
                "message" => "ok",
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows],
                'time' => $elapsedTime
            ],
            $status
        );
    }

    ///////////////////////////////////////////////Publisher Dio///////////////////////////////////////////////////
    public function publisherDio(Request $request)
    {
        $start = microtime(true);
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xdiocode";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] == (1 or -1)) ? (int)$request["sortDirection"] : 1;
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $translate = (isset($request["translate"])) ? $request["translate"] : 0;
        $authorship = (isset($request["authorship"])) ? $request["authorship"] : 0;
        $dio = (isset($request["dio"])) ? $request["dio"] : "";
        $yearStart = (isset($request["yearStart"])) ? (int)$request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? (int)$request["yearEnd"] : 0;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"]) ? (int)$request["perPage"] : 50;
        $data = null;
        $dioData = null;
        $status = 200;
        $totalPages = 0;
        $totalRows = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // read
        $books = BookIrBook2::query();
        $books->where('publisher.xpublisher_id', $publisherId);
        $books->where("xdiocode", $dio);
        if ($yearStart != "") $books->where("xpublishdate_shamsi", ">=", (int)"$yearStart");
        if ($yearEnd != "") $books->where("xpublishdate_shamsi", "<=", (int)"$yearEnd");
        if ($translate == 1) $books->where('is_translate', 2);
        if ($authorship == 1) $books->where('is_translate', 1);
        $books->select("xcirculation", "is_translate", "xdiocode", "xtotal_price");
        $books = $books->get();
        if ($books != null and count($books) > 0) {
            $totalPrice = 0;
            $totalCirculation = 0;
            foreach ($books as $book) {
                $totalPrice += $book->xtotal_price;
                $totalCirculation += $book->xcirculation;
            }
            $dioData[] = [
                'main_diocode' => $dio,
                "book_count" => count($books),
                "total_price" => priceFormat($totalPrice),
                "total_circulation" => priceFormat($totalCirculation)
            ];
        }

        $beforeDot = substr($dio, 0, strpos($dio, '.')) != '' ? substr($dio, 0, strpos($dio, '.')) : $dio;

        $pipeline[] = ['publisher.xpublisher_id' => $publisherId];
        $pipeline[] = ['xdiocode' => ['$regex' => '^' . preg_quote($beforeDot, '/')]];

        $pipeline[] = ['xdiocode' => ['$ne' => $dio]];

        if ($authorship != 0) {
            $pipeline[] = ['is_translate' => 1];
        }

        if ($translate != 0) {
            $pipeline[] = ['is_translate' => 2];
        }

        if ($yearStart != 0) {
            $pipeline[] = ['xpublishdate_shamsi' => ['$gte' => $yearStart]];
        }

        if ($yearEnd != 0) {
            $pipeline[] = ['xpublishdate_shamsi' => ['$lte' => $yearEnd]];
        }

        $subBooks = BookIrBook2::raw(function ($collection) use ($pipeline, $column, $sortDirection, $offset, $pageRows) {
            return $collection->aggregate([
                ['$match' => ['$and' => $pipeline]],
                ['$group' => [
                    '_id' => '$xdiocode',
                    'xdiocode' => ['$first' => '$xdiocode'],
                    'titleCount' => ['$sum' => 1],
                    'total_circulation' => ['$sum' => '$xcirculation'],
                    'total_price' => ['$sum' => '$xtotal_price']
                ]],
                ['$facet' => [
                    'books' => [
                        ['$sort' => [$column => $sortDirection]],
                        ['$skip' => $offset],
                        ['$limit' => $pageRows]
                    ],
                    'totalGroups' => [
                        ['$group' => [
                            '_id' => null,
                            'count' => ['$sum' => 1]
                        ]]
                    ]
                ]]
            ]);
        });

        $totalRows = $subBooks[0]->totalGroups[0]->count ?? 0;
        $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

        foreach ($subBooks[0]->books as $subBook) {
            $data [] = [
                'diocode' => $subBook->xdiocode,
                'book_count' => $subBook->titleCount,
                'total_price' => priceFormat($subBook->total_price),
                'total_circulation' => priceFormat($subBook->total_circulation)
            ];
        }


        $end = microtime(true);
        $elapsedTime = $end - $start;
        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => "ok",
                "data" => ["main_list" => $dioData, 'sub_list' => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows],
                'time' => $elapsedTime
            ],
            $status
        );
    }

    ///////////////////////////////////////////////Publisher Book///////////////////////////////////////////////////
    public function publisherBook(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xdiocode";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ?(int) $request["page"] : 0;
        $data = null;
        $status = 200;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"]) ?(int) $request["perPage"] : 50;
        $totalRows = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;


        // read
        if ($publisherId > 0) {
            $books = BookIrBook2::query();
            $books->where('publisher.xpublisher_id', $publisherId);
            if ($yearStart != "") $books->where("xpublishdate_shamsi", ">=", (int)"$yearStart");
            if ($yearEnd != "") $books->where("xpublishdate_shamsi", "<=", (int)"$yearEnd");
            $totalRows = $books->count(); // get total records count
            $books->orderBy($column, $sortDirection);
            $books = $books->skip($offset)->take($pageRows)->get(); // get list
            if ($books != null and count($books) > 0) {
                foreach ($books as $book) {
                    $creatorsData = null;
                    $circulation = 0;
                    $where = [];
                    $books2 = BookIrBook2::where('_id', '=',new  ObjectId($book->_id))->orwhere('xparent', '=', $book->_id)->get();
                    $books3 = BookIrBook2::where('_id', '=', new ObjectId($book->_id))->orwhere('xparent', '=', $book->_id);
                    if ($books2 != null) {
                        foreach ($books2 as $book2) {
                            $where [] = ['_id', $book2['_id']];
                            $circulation += $book2->xcirculation;
                        }
                    }

                    if ($where != []) {
                        $books3->where(function ($query) use ($where) {
                            $query->where($where[0][0], $where[0][1]); // Apply the first condition using where()
                            // Apply subsequent conditions using orWhere()
                                for ($i = 1; $i < count($where); $i++) {
                                    $query->orWhere($where[$i][0], $where[$i][1]);
                                }
                        });
                    }
                    $creators = $books3->pluck('partners');
                    if ($creators != null and count($creators) > 0) {
                        foreach ($creators as $creator) {
                            foreach ($creator as $key => $value) {
                                $creatorsData[] = ["id" => $value['xcreator_id'], "name" => $value['xcreatorname']];
                            }
                        }
                    }
                    $data[] = array
                    (
                        "id" => $book->_id,
                        "name" => $book->xname,
                        "circulation" => priceFormat($circulation),
                        // "translate" => $book->xlang == "فارسی" ? 0 : 1,
                        "translate" => $book->is_translate == 2 ? 1 : 0, // if translate return 1
                        "price" => priceFormat($book->xcoverprice),
                        "format" => $book->xformat,
                        "creators" => $creatorsData,
                        "image" => $book->ximgeurl,
                    );
                }
            }
        }
        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" =>"ok",
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
            $status
        );
    }

    ///////////////////////////////////////////////Publisher Subject///////////////////////////////////////////////////
    public function publisherSubject(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $subjectTitle = (isset($request["subjectTitle"])) ? $request["subjectTitle"] : "";
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xdiocode";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"]) ? (int)$request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;

        $matchConditions = [
            ['publisher.xpublisher_id' => $publisherId]
        ];


        $subjects = BookIrSubject::raw(function ($collection) use ($subjectTitle) {
            return $collection->aggregate([
                ['$match' => ['$text' => ['$search' => $subjectTitle]]],
                ['$project' => ['_id' => 1, 'score' => ['$meta' => 'textScore']]],
                ['$sort' => ['score' => ['$meta' => 'textScore']]],
            ]);
        });
        $subjectIds = [];
        if ($subjects != null) {
            foreach ($subjects as $subject) {
                $subjectIds[] = $subject->_id;
            }
        }
        $matchConditions [] = ['subjects.xsubject_id' => ['$in' => $subjectIds]];


        if (!empty($yearStart)) {
            $matchConditions[] = ['xpublishdate_shamsi' => ['$gte' => (int)$yearStart]];
        }

        if (!empty($yearEnd)) {
            $matchConditions[] = ['xpublishdate_shamsi' => ['$lte' => (int)$yearEnd]];
        }

        $pipeline = [
            ['$match' => ['$and' => $matchConditions]],
            ['$sort' => [$column => $sortDirection]],
            ['$skip' => $offset],
            ['$limit' => $pageRows]
        ];

        $books = BookIrBook2::raw(function($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        $data = [];
        foreach ($books as $book) {
            $subjectsData = [];
            $subjects = $book->subjects;
            if (!empty($subjects)) {
                foreach ($subjects as $subject) {
                    $subjectsData[] = [
                        "id" => $subject['xsubject_id'],
                        "title" => $subject['xsubject_name']
                    ];
                }
            }

            $data[] = [
                "id" => $book->_id,
                "name" => $book->xname,
                "subjects" => $subjectsData,
                "circulation" => priceFormat($book->xcirculation),
                "year" => $book->xpublishdate_shamsi,
                "price" => priceFormat($book->xcoverprice),
                "image" => $book->ximgeurl,
            ];
        }

        $totalRows = BookIrBook2::raw(function($collection) use ($matchConditions) {
            return $collection->countDocuments(['$and' => $matchConditions]);
        });

        $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

        return response()->json(
            [
                "status" => 200,
                "message" => "ok",
                "data" => [
                    "list" => $data,
                    "currentPageNumber" => $currentPageNumber,
                    "totalPages" => $totalPages,
                    "pageRows" => $pageRows,
                    "totalRows" => $totalRows
                ]
            ],
        );
    }

    ///////////////////////////////////////////////Publisher Subject Aggregation///////////////////////////////////////////////////

    public function publisherSubjectAggregation(Request $request)
    {
        //TODO : jame mali add shavad
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $subjectTitle = (isset($request["subjectTitle"])) ? $request["subjectTitle"] : "";
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "circulation";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ?(int) $request["page"] : 0;
        $data = null;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ?(int) $request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;


        // read
        $matchConditions = [
            ['publisher.xpublisher_id' => $publisherId]
        ];

        $subjects = BookIrSubject::raw(function ($collection) use ($subjectTitle) {
            return $collection->aggregate([
                ['$match' => ['$text' => ['$search' => $subjectTitle]]],
                ['$project' => ['_id' => 1, 'score' => ['$meta' => 'textScore']]],
                ['$sort' => ['score' => ['$meta' => 'textScore']]],
            ]);
        });

        $subjectIds = [];
        if ($subjects != null) {
            foreach ($subjects as $subject) {
                $subjectIds[] = $subject->_id;
            }
        }
        $matchConditions [] = ['subjects.xsubject_id' => ['$in' => $subjectIds]];

        if (!empty($yearStart)) {
            $matchConditions[] = ['xpublishdate_shamsi' => ['$gte' => (int)$yearStart]];
        }

        if (!empty($yearEnd)) {
            $matchConditions[] = ['xpublishdate_shamsi' => ['$lte' => (int)$yearEnd]];
        }

        $pipeline = [
            ['$match' => ['$and' => $matchConditions]],
            ['$project' => [
                'filteredSubjects' => [
                    '$filter' => [
                        'input' => '$subjects',
                        'as' => 'subject',
                        'cond' => ['$regexMatch' => ['input' => '$$subject.xsubject_name', 'regex' => $subjectTitle, 'options' => 'i']]
                    ]
                ],
                'xcirculation' => 1,
                'xpublishdate_shamsi' => 1,
            ]],
            ['$unwind' => '$filteredSubjects'],
            ['$group' => [
                '_id' => '$filteredSubjects.xsubject_id',
                'subjectTitle' => ['$first' => '$filteredSubjects.xsubject_name'],
                'countTitle' => ['$sum' => 1],
                'circulation' => ['$sum' => '$xcirculation'],
            ]],
            ['$facet' => [
                'subjects' => [
                    ['$sort' => [$column => $sortDirection]],
                    ['$skip' => $offset],
                    ['$limit' => $pageRows]
                ],
                'totalGroups' => [
                    ['$group' => [
                        '_id' => null,
                        'count' => ['$sum' => 1]
                    ]]
                ]
            ]]
        ];

        $result = BookIrBook2::raw(function($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });



        foreach ($result[0]->subjects as $item) {
            $data[] = [
                'subject' => ['id' => $item->_id, 'name' => $item->subjectTitle],
                'countTitle' => $item->countTitle,
                'circulation' => priceFormat($item->circulation),
            ];
        }

        $totalRows = $result[0]->totalGroups[0]->count;

        $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

        // response
        return response()->json(
            [
                "status" => 200,
                "message" => "ok",
                "data" => [
                    "list" => $data,
                    "currentPageNumber" => $currentPageNumber,
                    "totalPages" => $totalPages,
                    "pageRows" => $pageRows,
                    "totalRows" => $totalRows
                ]
            ],
        );
    }

    ///////////////////////////////////////////////Subject Aggregation///////////////////////////////////////////////////
    public function subjectAggregation(Request $request)
    {
        $subjectTitle = (isset($request["subjectTitle"])) ? $request["subjectTitle"] : "";
        $yearStart = (isset($request["yearStart"])) ? (int)$request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? (int)$request["yearEnd"] : 0;
        $translate = (isset($request['translate']) && $request['translate'] ==(1 or 0)) ? (int)$request["translate"] : 0;
        $authorship = (isset($request['authorship']) && $request['authorship'] ==(1 or 0)) ? (int)$request["authorship"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "subjects.xsubject_name";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 0;
        $data = null;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? (int)$request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;

        $subjects = BookIrSubject::raw(function ($collection) use ($subjectTitle) {
            return $collection->aggregate([
                ['$match' => ['$text' => ['$search' => $subjectTitle]]],
                ['$project' => ['_id' => 1, 'score' => ['$meta' => 'textScore']]],
                ['$sort' => ['score' => ['$meta' => 'textScore']]],
            ]);
        });

        $subjectIds = [];
        if ($subjects != null) {
            foreach ($subjects as $subject) {
                $subjectIds[] = $subject->_id;
            }
        }
        $matchConditions [] = ['subjects.xsubject_id' => ['$in' => $subjectIds]];

        if ($translate == 1) {
            $matchConditions[] = ['is_translate' => 2];
        } elseif ($authorship == 1) {
            $matchConditions[] = ['is_translate' => 1];
        }

        if ($yearStart != "") {
            $matchConditions[] = ['xpublishdate_shamsi' => ['$gte' => (int)$yearStart]];
        }

        if ($yearEnd != "") {
            $matchConditions[] = ['xpublishdate_shamsi' => ['$lte' => (int)$yearEnd]];
        }

        $pipeline = [
            ['$match' => ['$and' => $matchConditions]],
            ['$unwind' => '$publisher'],
            ['$group' => [
                '_id' => '$publisher.xpublisher_id',
                'publisherName' => ['$first' => '$publisher.xpublishername'],
                'countTitle' => ['$sum' => 1],
                'circulation' => ['$sum' => '$xcirculation'],
                'totalPrice' =>['$sum' => '$xtotal_price']
            ]],
            ['$facet' =>[
                'publishers'=> [
                    ['$sort' => [$column => $sortDirection]],
                    ['$skip' => $offset],
                    ['$limit' =>  $pageRows],
                ],
                'count' =>[
                    ['$group' => [
                        '_id' => null,
                        'count' => ['$sum' => 1]
                    ]]
                ]
            ]]
        ];

        $result = BookIrBook2::raw(function($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        $totalRows = $result[0]->count[0]->count;

        $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

        foreach ($result[0]->publishers as $item) {
            $data[] = [
                'publisher' => ['id' => $item->_id, 'name' => $item->publisherName],
                'countTitle' => $item->countTitle,
                'circulation' => priceFormat($item->circulation),
                'totalPrice' => priceFormat($item->totalPrice)
            ];
        }

        $status = 200;

        return response()->json([
            'status' => $status,
            'message' =>  'ok' ,
            'data' => [
                'list' => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows
            ]
        ], $status);
    }

    ///////////////////////////////////////////////Creator Subject Aggregation///////////////////////////////////////////////////
    public function creatorSubjectAggregation(Request $request)
    {
        $creatorId = (isset($request["creatorId"])) ? $request["creatorId"] : 0;
        $subjectTitle = (isset($request["subjectTitle"])) ? $request["subjectTitle"] : "";
        $translate = (isset($request['translate']) && $request['translate'] ==(1 or 0)) ? (int)$request["translate"] : 0;
        $authorship = (isset($request['authorship']) && $request['authorship'] ==(1 or 0)) ? (int)$request["authorship"] : 0;
        $yearStart = (isset($request["yearStart"])) ? (int)$request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? (int)$request["yearEnd"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xdiocode";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ?(int) $request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ?(int) $request["perPage"] : 50;
        $data = null;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $matchConditions = [];

        $matchConditions [] =
            ['partners.xcreator_id' => $creatorId];

        $subjects = BookIrSubject::raw(function ($collection) use ($subjectTitle) {
            return $collection->aggregate([
                ['$match' => ['$text' => ['$search' => $subjectTitle]]],
                ['$project' => ['_id' => 1, 'score' => ['$meta' => 'textScore']]],
                ['$sort' => ['score' => ['$meta' => 'textScore']]],
            ]);
        });

        $subjectIds = [];
        if ($subjects != null) {
            foreach ($subjects as $subject) {
                $subjectIds[] = $subject->_id;
            }
        }
        $matchConditions [] = ['subjects.xsubject_id' => ['$in' => $subjectIds]];

        if ($translate == 1) {
            $matchConditions[] = ['is_translate' => 2];
        } elseif ($authorship == 1) {
            $matchConditions[] = ['is_translate' => 1];
        }

        if ($yearStart != "") {
            $matchConditions[] = ['xpublishdate_shamsi' => ['$gte' => (int)$yearStart]];
        }

        if ($yearEnd != "") {
            $matchConditions[] = ['xpublishdate_shamsi' => ['$lte' => (int)$yearEnd]];
        }

        $pipeline = [
            ['$match' => ['$and' => $matchConditions]],
            ['$unwind' => '$publisher'],
            ['$group' => [
                '_id' => '$publisher.xpublisher_id',
                'publisherName' => ['$first' => '$publisher.xpublishername'],
                'countTitle' => ['$sum' => 1],
                'circulation' => ['$sum' => '$xcirculation'],
                'totalPrice' =>['$sum' => '$xtotal_price']
            ]],
            ['$facet' =>[
                'publishers'=> [
                    ['$sort' => [$column => $sortDirection]],
                    ['$skip' => $offset],
                    ['$limit' =>  $pageRows],
                ],
                'count' =>[
                    ['$group' => [
                        '_id' => null,
                        'count' => ['$sum' => 1]
                    ]]
                ]
            ]]
        ];

        $result = BookIrBook2::raw(function($collection) use ($pipeline) {
            return $collection->aggregate($pipeline, ['allowDiskUse' => true]);
        });

        $totalRows = $result[0]->count[0]->count ;

        $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

        foreach ($result[0]->publishers as $item) {
            $data[] = [
                'publisher' => ['id' => $item->_id, 'name' => $item->publisherName],
                'countTitle' => $item->countTitle,
                'circulation' => priceFormat($item->circulation),
                'totalPrice' => priceFormat($item->totalPrice)
            ];
        }

        $status = 200;

        return response()->json([
            'status' => $status,
            'message' =>  'ok' ,
            'data' => [
                'list' => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows
            ]
        ], $status);
    }
    ///////////////////////////////////////////////Subject///////////////////////////////////////////////////
    public function subject(Request $request)
    {
        $subjectTitle = (isset($request["subjectTitle"])) ? $request["subjectTitle"] : "";
        $yearStart = (isset($request["yearStart"])) ?(int) $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? (int)$request["yearEnd"] : 0;
        $translate = (isset($request['translate']) && $request['translate'] ==(1 or 0)) ? (int)$request["translate"] : 0;
        $authorship = (isset($request['authorship']) && $request['authorship'] ==(1 or 0)) ? (int)$request["authorship"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xpublishdate_shamsi";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ?(int) $request["page"] : 0;
        $data = null;
        $status = 200;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? (int) $request["perPage"] : 50;
        $totalRows = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $matchConditions = [];
        // read
        $subjects = BookIrSubject::raw(function ($collection) use ($subjectTitle) {
            return $collection->aggregate([
                ['$match' => ['$text' => ['$search' => $subjectTitle]]],
                ['$project' => ['_id' => 1, 'score' => ['$meta' => 'textScore']]],
                ['$sort' => ['score' => ['$meta' => 'textScore']]],
            ]);
        });

        $subjectIds = [];
        if ($subjects != null) {
            foreach ($subjects as $subject) {
                $subjectIds[] = $subject->_id;
            }
        }
        $matchConditions [] = ['subjects.xsubject_id' => ['$in' => $subjectIds]];
            if (!empty($yearStart)) {
                $matchConditions[] = ['xpublishdate_shamsi' => ['$gte' => $yearStart]];
            }

            if (!empty($yearEnd)) {
                $matchConditions[] = ['xpublishdate_shamsi' => ['$lte' => $yearEnd]];
            }

            if ($translate == 1) {
                $matchConditions[] = ['is_translate' => 2];
            } elseif ($authorship == 1) {
                $matchConditions[] = ['is_translate' => 1];
            }

            $pipeline = [
                ['$match' => ['$and' => $matchConditions]],
                ['$sort' => [$column => $sortDirection]],
                ['$skip' => $offset],
                ['$limit' => $pageRows]
            ];
            $books = BookIrBook2::raw(function($collection) use ($pipeline) {
                return $collection->aggregate($pipeline);
            });

            $totalRows = BookIrBook2::raw(function ($collection) use($matchConditions){
               return $collection->countDocuments(['$and' => $matchConditions]);
            });

            if($books != null and count($books) > 0)
            {
                foreach ($books as $book)
                {
                    $publishers = null;
                    $bookPublishers = $book->publisher;

                    if($bookPublishers != null and count($bookPublishers) > 0)
                    {
                        foreach ($bookPublishers as $bookPublisher)
                        {
                            $publishers[] = ["id" => $bookPublisher['xpublisher_id'], "name" => $bookPublisher['xpublishername']];
                        }
                    }

                    //
                    $data[] =
                        [
                            "id" => $book->_id,
                            "name" => $book->xname,
                            "publishers" => $publishers,
                            "year" => $book->xpublishdate_shamsi,
                            "price" => priceFormat($book->xcoverprice),
                            "image" => $book->ximgeurl,
                            "circulation" => priceFormat($book->xcirculation),
                        ];
                }
            }


        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => "ok" ,
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
            $status
        );
    }


    ///////////////////////////////////////////////Creator Subject///////////////////////////////////////////////////
    public function creatorSubject(Request $request)
    {
        $creatorId = (isset($request["creatorId"])) ? $request["creatorId"] : 0;
        $subjectTitle = (isset($request["subjectTitle"])) ? $request["subjectTitle"] : "";
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xdiocode";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ?(int) $request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ?(int) $request["perPage"] : 50;
        $data = null;
        $status = 200;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // read
        $books = BookIrBook2::query();
        $books->where('partners.xcreator_id' , $creatorId);
        if($subjectTitle != "") $books->where('subjects.xsubject_name' , 'LIKE',"%$subjectTitle%");
        if($yearStart != "") $books->where("xpublishdate_shamsi", ">=", (int)"$yearStart");
        if($yearEnd != "") $books->where("xpublishdate_shamsi", "<=", (int)"$yearEnd");
        $totalRows = $books->count(); // get total records count
        $books->orderBy($column,$sortDirection);
        $books = $books->skip($offset)->take($pageRows)->get(); // get list
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $publishers = null;
                $bookPublishers = $book->publisher;

                if($bookPublishers != null and count($bookPublishers) > 0)
                {
                    foreach ($bookPublishers as $bookPublisher)
                    {
                        $publishers[] = ["id" => $bookPublisher['xpublisher_id'], "name" => $bookPublisher['xpublishername']];
                    }
                }
                //
                $data[] = array
                (
                    "id" => $book->_id,
                    "name" => $book->xname,
                    "publishers" => $publishers,
                    "circulation" => priceFormat($book->xcirculation),
                    "year" => $book->xpublishdate_shamsi,
                    "price" => priceFormat($book->xcoverprice),
                    "image" => $book->ximgeurl,
                );
            }
        }

        //
        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => "ok",
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
            $status
        );
    }

    ///////////////////////////////////////////////Creator Publisher///////////////////////////////////////////////////
    public function creatorPublisher(Request $request)
    {
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "totalCountRule";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] == (1 or -1)) ? (int)$request["sortDirection"] : 1;
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? (int)$request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? (int)$request["yearEnd"] : 0;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"]) ? (int)$request["perPage"] : 50;
        $data = [];
        $status = 200;
        $offset = ($currentPageNumber - 1) * $pageRows;

        $pipeline [] = ['publisher.xpublisher_id' => $publisherId];

        if ($yearStart != 0) {
            $pipeline[] = ['xpublishdate_shamsi' => ['$gte' => $yearStart]];
        }

        if ($yearEnd != 0) {
            $pipeline[] = ['xpublishdate_shamsi' => ['$lte' => $yearEnd]];
        }


        $creators = BookIrBook2::raw(function ($collection) use ($pipeline, $offset, $pageRows, $column, $sortDirection) {
            return $collection->aggregate([
                ['$match' => ['$and' => $pipeline]],
                ['$unwind' => '$partners'],
                // Group by xcreator_id and xrule to count occurrences of each rule per creator
                ['$group' => [
                    '_id' => ['creator_id' => '$partners.xcreator_id', 'xrule' => '$partners.xrule'],
                    'creatorName' => ['$first' => '$partners.xcreatorname'], // Assuming xcreatorname is within the partners array
                    'countXrule' => ['$sum' => 1], // Count occurrences of each xrule
                    'countCirculation' => ['$sum' => '$xcirculation'],
                    'countPrice' => ['$sum' => '$xtotal_price']
                ]],
                // Group by xcreator_id to accumulate the counts of different rules
                ['$group' => [
                    '_id' => '$_id.creator_id',
                    'creatorName' => ['$first' => '$creatorName'],
                    'xrules' => [
                        '$push' => [
                            'xrule' => '$_id.xrule',
                            'countRule' => '$countXrule',
                            'countCirculation' => '$countCirculation',
                            'countPrice' => '$countPrice'
                        ]
                    ],
                    // Sum up counts across all xrules for each creator
                    'totalCountRule' => ['$sum' => '$countXrule'],
                    'totalCountCirculation' => ['$sum' => '$countCirculation'],
                    'totalCountPrice' => ['$sum' => '$countPrice'],
                    'totalRow' => ['$sum' => 1]
                ]],
                ['$facet' => [
                    'paginatedResults' => [
                        ['$sort' => [$column => $sortDirection]],
                        ['$skip' => $offset],
                        ['$limit' => $pageRows],
                        ['$project' => [
                            '_id' => 0, // Exclude the default _id field
                            'xcreator_id' => '$_id',
                            'creatorName' => 1,
                            'xrules' => 1,
                            'totalCountRule' => 1,
                            'totalCountCirculation' => 1,
                            'totalCountPrice' => 1
                        ]]
                    ],
                    'totalUniqueRules' => [
                        ['$group' => [
                            '_id' => null,
                            'totalUniqueRulesCount' => ['$sum' => '$totalRow']
                        ]]
                    ]
                ]]
            ]);
        });
        foreach ($creators[0]->paginatedResults as $creator)
            foreach ($creator->xrules as $xrule) {
                $data [] = [
                    'creator' => ['id' => $creator->xcreator_id, 'name' => $creator->creatorName],
                    'role' => $xrule->xrule,
                    'bookCount' => $xrule->countRule,
                    'circulation' => priceFormat($xrule->countCirculation),
                    'totalPrice' => priceFormat($xrule->countPrice)
                ];
            }
        $totalRows = $creators[0]['totalUniqueRules'][0]['totalUniqueRulesCount'] ;
        $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => "ok",
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
            $status
        );

    }

    ///////////////////////////////////////////////Creator Aggregation///////////////////////////////////////////////////
    public function creatorAggregation(Request $request)
    {
        $creatorId = (isset($request["creatorId"])) ? $request["creatorId"] : 0;
        $yearStart = (isset($request["yearStart"])) ?(int) $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? (int)$request["yearEnd"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "publisher.xpublishername";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? (int)$request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $totalPages = 0 ;
        $data = null;
        $publishersData = null;
        $status = 200;

        $matchConditions [] =
            ['partners.xcreator_id' => $creatorId];

        if ($yearStart != "") {
            $matchConditions[] = ['xpublishdate_shamsi' => ['$gte' => (int)$yearStart]];
        }

        if ($yearEnd != "") {
            $matchConditions[] = ['xpublishdate_shamsi' => ['$lte' => (int)$yearEnd]];
        }

        $pipeline = [
            ['$match' => ['$and' => $matchConditions]],
            ['$unwind' => '$publisher'],
            ['$group' => [
                '_id' => '$publisher.xpublisher_id',
                'publisherName' => ['$first' => '$publisher.xpublishername'],
                'countTitle' => ['$sum' => 1],
                'circulation' => ['$sum' => '$xcirculation'],
                'totalPrice' =>['$sum' => '$xtotal_price']
            ]],
            ['$facet' =>[
                'publishers'=> [
                    ['$sort' => [$column => $sortDirection]],
                    ['$skip' => $offset],
                    ['$limit' =>  $pageRows],
                ],
                'count' =>[
                    ['$group' => [
                        '_id' => null,
                        'count' => ['$sum' => 1]
                    ]]
                ]
            ]]
        ];

        $result = BookIrBook2::raw(function($collection) use ($pipeline) {
            return $collection->aggregate($pipeline, ['allowDiskUse' => true]);
        });

        $totalRows = $result[0]->count[0]->count ;

        $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

        foreach ($result[0]->publishers as $item) {
            $data[] = [
                'publisher' => ['id' => $item->_id, 'name' => $item->publisherName],
                'countTitle' => $item->countTitle,
                'circulation' => priceFormat($item->circulation),
                'totalPrice' => priceFormat($item->totalPrice)
            ];
        }

        $status = 200;

        return response()->json([
            'status' => $status,
            'message' =>  'ok' ,
            'data' => [
                'list' => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows
            ]
        ], $status);
    }

    ///////////////////////////////////////////////Dio///////////////////////////////////////////////////
    public function dio(Request $request)
    {
        $dio = (isset($request["dio"])) ? $request["dio"] : "";
        $translate = (isset($request["translate"])) ? $request["translate"] : 0;
        $authorship = (isset($request["authorship"])) ? $request["authorship"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xpublishdate_shamsi";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ?(int) $request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"]) ?(int) $request["perPage"] : 50;
        $data = null;
        $status = 200;
        $totalRows = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // read
        if($dio != "")
        {
            $books = BookIrBook2::orderBy($column , $sortDirection);
            $books->where("xdiocode", "$dio");
            // if($translate == 1) $books->where("xlang", "!=", "فارسی");
            if($translate == 1) $books->where("is_translate", 2);
            // if($authorship == 1) $books->where("xlang", "=", "فارسی");
            if($authorship == 1) $books->where("is_translate", 1);
            if($yearStart != "") $books->where("xpublishdate_shamsi", ">=", (int)"$yearStart");
            if($yearEnd != "") $books->where("xpublishdate_shamsi", "<=", (int)"$yearEnd");
            $totalRows = $books->count(); // get total records count
            $books = $books->skip($offset)->take($pageRows)->get(); // get list
            if($books != null and count($books) > 0)
            {
                foreach ($books as $book)
                {
                    if ($book->xparent == -1 or  $book->xparent == 0) {
                        $dossier_id = $book->_id;
                    } else {
                        $dossier_id = $book->xparent;
                    }
                    $publishers = null;
                    $bookPublishers = $book->publisher;
                    if($bookPublishers != null and count($bookPublishers) > 0)
                    {
                        foreach ($bookPublishers as $bookPublisher)
                        {
                            $publishers[] = ["id" => $bookPublisher['xpublisher_id'], "name" => $bookPublisher['xpublishername']];
                        }
                    }

                    //
                    $data[] =
                        [
                            "id" => $book->_id,
                            "dossier_id" => $dossier_id,
                            "name" => $book->xname,
                            "publishers" => $publishers,
                            "language" => $book->languages,
                            "year" => $book->xpublishdate_shamsi,
                            "printNumber" => $book->xprintnumber,
                            "format" => $book->xformat,
                            "pageCount" => $book->xpagecount,
                            "isbn" => $book->xisbn,
                            "price" => $book->xcoverprice,
                            "image" => $book->ximgeurl,
                            "circulation" => $book->xcirculation,
                        ];
                }
            }
        }

        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" =>"ok" ,
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
            $status
        );
    }

    public function new()
    {
        //id nasher
        //id padid avarande
        //kalame subject
        //sale shoro
        //sale tamom
        // talif ya tarjome ya hardo
        //shomare chap
        //kode dio == na like
        //khoroji ketabha
    }
}
