<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrPublisher;
use Collator;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;

class ReportController extends Controller
{
    ///////////////////////////////////////////////Unique Creators///////////////////////////////////////////////////
    function getUniqueCreators($data) {
        $uniqueCreators = [];

        foreach ($data as $group) {
            foreach ($group as $creator) {
                $key = $creator['xcreator_id'] . '-' . $creator['xrule'];
                if (!isset($uniqueCreators[$key])) {
                    $uniqueCreators[$key] = $creator;
                }
            }
        }

        return array_values($uniqueCreators); // Reset array keys to get a flat array
    }
    ///////////////////////////////////////////////Publishers///////////////////////////////////////////////////

    public function publisher(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? (int) $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? (int)$request["yearEnd"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xdiocode";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"]) ? $request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $data = null;
        $status = 200;
        $allUniqueDiocodes = [];
        $books = BookIrBook2::query();
        $books->where('publisher.xpublisher_id', $publisherId);
        if ($yearStart != "") $books->where("xpublishdate_shamsi", ">=", (int)$yearStart);
        if ($yearEnd != "") $books->where("xpublishdate_shamsi", "<=", (int)$yearEnd);
        $books->select("xcirculation", "is_translate", "xdiocode");
        $books->orderBy($column, $sortDirection);
        $books = $books->skip($offset)->take($pageRows)->get(); // get list


        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $allUniqueDiocodes [] = $book->xdiocode;
                $allUniqueDiocodes = array_unique($allUniqueDiocodes);
            }
            foreach ($allUniqueDiocodes as $uniqueDiocode) {
                $totalCirculation = 0;
                foreach ($books as $book) {
                    if ($book->xdiocode == $uniqueDiocode) {
                        $totalCirculation += $book->xcirculation;
                        $translate = $book->first()->is_translate;
                    };
                }

                    $data[$uniqueDiocode] = array
                    (
                        "translate" => $translate,
                        "circulation" => priceFormat($totalCirculation),//$book->xcirculation + ((isset($data[$dioCode])) ? $data[$dioCode]["circulation"] : 0),
                        "dio" => $uniqueDiocode,
                    );

            }

            $data = array_values($data);
        }
        $totalRows = count($allUniqueDiocodes);
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

    ///////////////////////////////////////////////Publisher Dio///////////////////////////////////////////////////
    public function publisherDio(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $dio = (isset($request["dio"])) ? $request["dio"] : "";
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xdiocode";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $dioData = null;
        $status = 200;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"]) ? $request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // read
        $books = BookIrBook2::query();
        $books->where('publisher.xpublisher_id', $publisherId);
        if ($dio != "") $books->where("xdiocode", "=", "$dio");
        if ($yearStart != "") $books->where("xpublishdate_shamsi", ">=", (int)"$yearStart");
        if ($yearEnd != "") $books->where("xpublishdate_shamsi", "<=", (int)"$yearEnd");
        $books->orderBy($column, $sortDirection);
        $books = $books->skip($offset)->take($pageRows)->get();

        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $dioData[md5($book->xdiocode)] = $book->xdiocode;
            }
        }

        if ($dioData != null and count($dioData) > 0) {
            foreach ($dioData as $dio) {
                $books = BookIrBook2::query();
                $books->where('publisher.xpublisher_id', $publisherId);
                if ($dio != "") $books->where("xdiocode", "=", "$dio");
                if ($yearStart != "") $books->where("xpublishdate_shamsi", ">=", (int)"$yearStart");
                if ($yearEnd != "") $books->where("xpublishdate_shamsi", "<=", (int)"$yearEnd");
                $books->orderBy($column, $sortDirection);
                $totalRows = $books->count(); // get total records count
                $books = $books->skip($offset)->take($pageRows)->get();
                if ($books != null and count($books) > 0) {
                    foreach ($books as $book) {
                        $dioCode = $book->xdiocode;

                        $data[$dioCode] = array
                        (
                            "dio" => $dioCode,
                            "countTitle" => 1 + ((isset($data[$dioCode])) ? $data[$dioCode]["countTitle"] : 0),
                            "circulation" => $book->xcirculation + ((isset($data[$dioCode])) ? $data[$dioCode]["circulation"] : 0),
                            "price" => (intval($book->xcoverprice) * $book->xcirculation) + ((isset($data[$dioCode])) ? $data[$dioCode]["price"] : 0),
                        );
                    }

                    foreach ($data as $key => $item) {
                        $data[$key]["circulation"] = priceFormat($item["circulation"]);
                        $data[$key]["price"] = priceFormat($item["price"]);
                    }

                    $data = array_values($data);
                }
            }
        }
        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => "ok" ,
                "data" => ["list" => $data , "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
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
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 200;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"]) ? $request["perPage"] : 50;
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
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 200;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;


        // read
        $books = BookIrBook2::query();
        $books->where('publisher.xpublisher_id', $publisherId);
        if($subjectTitle != "") $books->where("xsubject_name", "LIKE", "%$subjectTitle%");
        if($yearStart != "") $books->where("xpublishdate_shamsi", ">=", (int)"$yearStart");
        if($yearEnd != "") $books->where("xpublishdate_shamsi", "<=", (int)"$yearEnd");
        $totalRows = $books->count(); // get total records count
        $books->orderBy($column,$sortDirection);
        $books = $books->skip($offset)->take($pageRows)->get(); // get list
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $subjectsData = null;
                $subjects = $book->subjects ;
                if($subjects != null and count($subjects) > 0)
                {
                    foreach ($subjects as $subject) {
                            $subjectsData[] = ["id" => $subject['xsubject_id'] ,"title" => $subject['xsubject_name']];
                    }
                }

                //
                $data[] = array
                (
                    "id" => $book->_id,
                    "name" => $book->xname,
                    "subjects" => $subjectsData,
                    "circulation" => priceFormat($book->xcirculation),
                    "year" => $book->xpublishdate_shamsi,
                    "price" => priceFormat($book->xcoverprice),
                    "image" => $book->ximgeurl,
                );
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

    ///////////////////////////////////////////////Publisher Subject Aggregation///////////////////////////////////////////////////

    public function publisherSubjectAggregation(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $subjectTitle = (isset($request["subjectTitle"])) ? $request["subjectTitle"] : "";
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "subjects.xsubject_name";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
        $subjectsData = null;
        $offset = ($currentPageNumber - 1) * $pageRows;


        // read
        $booksSubjects = BookIrBook2::query();
        $booksSubjects->where('publisher.xpublisher_id', $publisherId)->pluck('subjects');
        if($subjectTitle != "") $booksSubjects->where("xsubject_name", "LIKE", "%$subjectTitle%");
        $booksSubjects->orderBy($column,$sortDirection);
        $booksSubjects = $booksSubjects->skip($offset)->take($pageRows)->get(); // get list
        if($booksSubjects != null and count($booksSubjects) > 0)
        {
            foreach ($booksSubjects as $bookSubjects) {
                foreach ($bookSubjects->subjects as $bookSubject) {
                    $subjectsData[$bookSubject['xsubject_id']] = $bookSubject['xsubject_name'];
                }
            }
        }

        if($subjectsData != null and count($subjectsData) > 0)
        {
            foreach ($subjectsData as $subjectId => $subjectTitle)
            {
                $books = BookIrBook2::query();
                $books->where('subjects.xsubject_id' , $subjectId);
                $books->where('publisher.xpublisher_id',$publisherId);
                if($yearStart != "") $books->where("xpublishdate_shamsi", ">=", (int)"$yearStart");
                if($yearEnd != "") $books->where("xpublishdate_shamsi", "<=", (int)"$yearEnd");
                $books->orderBy('xdiocode', 1);
                $books = $books->skip($offset)->take($pageRows)->get(); // get list
                if($books != null and count($books) > 0)
                {
                    foreach ($books as $book)
                    {
                        $data[$subjectId] = array
                        (
                            "id" => $subjectId,
                            "title" => $subjectTitle,
                            "countTitle" => 1 + ((isset($data[$subjectId])) ? $data[$subjectId]["countTitle"] : 0),
                            "circulation" => $book->xcirculation + ((isset($data[$subjectId])) ? $data[$subjectId]["circulation"] : 0),
                        );
                    }

                    foreach ($data as $key => $item)
                    {
                        $data[$key]["circulation"] = priceFormat($item["circulation"]);
                    }

                    $data = array_values($data);
                }
            }
        }

        //
        if($data != null) $status = 200;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $data]
            ],
            $status
        );
    }

    ///////////////////////////////////////////////Subject Aggregation///////////////////////////////////////////////////
    public function subjectAggregation(Request $request)
    {
        $subjectTitle = (isset($request["subjectTitle"])) ? $request["subjectTitle"] : "";
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $translate = (isset($request['translate']) && $request['translate'] ==(1 or 0)) ? (int)$request["translate"] : 0;
        $authorship = (isset($request['authorship']) && $request['authorship'] ==(1 or 0)) ? (int)$request["authorship"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "subjects.xsubject_name";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
        $subjectsData = null;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $matchConditions = [
            ['subjects.xsubject_name' => $subjectTitle],
        ];

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
                'circulation' => ['$sum' => '$xcirculation']
            ]],
            ['$sort' => [$column => $sortDirection]],
            ['$skip' => $offset],
            ['$limit' => $pageRows]
        ];

        $result = BookIrBook2::raw(function($collection) use ($pipeline) {
            return $collection->aggregate($pipeline, ['allowDiskUse' => true]);
        });

        $data = [];
        foreach ($result as $item) {
            $data[] = [
                'publisher' => ['id' => $item->_id, 'name' => $item->publisherName],
                'countTitle' => $item->countTitle,
                'circulation' => priceFormat($item->circulation)
            ];
        }

        $status = 200;

        return response()->json([
            'status' => $status,
            'message' =>  'ok' ,
            'data' => ['list' => $data]
        ], $status);
    }


    ///////////////////////////////////////////////Subject///////////////////////////////////////////////////
    public function subject(Request $request)
    {
        $subjectTitle = (isset($request["subjectTitle"])) ? $request["subjectTitle"] : "";
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $translate = (isset($request['translate']) && $request['translate'] ==(1 or 0)) ? (int)$request["translate"] : 0;
        $authorship = (isset($request['authorship']) && $request['authorship'] ==(1 or 0)) ? (int)$request["authorship"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xpublishdate_shamsi";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 200;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
        $totalRows = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // read
        if($subjectTitle != "")
        {
            // DB::enableQueryLog();
            $books = BookIrBook2::query();
             $books->where("xsubject_name", "LIKE", "%$subjectTitle%");
            // if($translate == 1) $books->where("xlang", "!=", "فارسی");
            if($translate == 1) $books->where("is_translate", 2);
            // if($authorship == 1) $books->where("xlang", "=", "فارسی");
            if($authorship == 1) $books->where("is_translate", 1);
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

            // $query = DB::getQueryLog();
            // return $query;
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
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
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
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "_id";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;

        $data = null;
        $status = 200;
        $totalRows = 0;
        $unique_entries = [];
        $offset = ($currentPageNumber - 1) * $pageRows;


        // read
        if($publisherId > 0)
        {
            $books = BookIrBook2::query();
            $books->where('publisher.xpublisher_id' , $publisherId);
            if($yearStart != "") $books->where('xpublishdate_shamsi', '>=' , (int)$yearStart );
            if($yearEnd != "") $books->where('xpublishdate_shamsi', '<=' , (int)$yearEnd );
            $books = $books->get();
            $creators = $books->pluck('partners');
            $uniqueCreators = $this->getUniqueCreators($creators);

            $totalRows = count($uniqueCreators); // get total records count
            $uniqueCreators = array_slice($uniqueCreators, $offset, $pageRows);
            $collator = new Collator('fa_IR');

            if($uniqueCreators != null and count($uniqueCreators) > 0)
                $totalCirculation = 0;
                $isTranslate = 0;
            foreach ($uniqueCreators as $uniqueCreator) {
                foreach ($books as $book){
                    $partners = $book->partners;
                    foreach ($partners as $key =>$partner) {
                        if ($key+1 < count($partners)) {
                            if ($partners[$key]['xcreatorname'] == $partners[$key + 1]['xcreatorname'] and $partners[$key]['xrule'] == $partners[$key+1]['xrule']){
                                continue;
                            }if($partner['xcreator_id'] == $uniqueCreator['xcreator_id'] and $partner['xrule'] == $uniqueCreator['xrule']) {
                                    $totalCirculation += $book['xcirculation'];
                                    $isTranslate = $book['is_translate'];
                            }
                        }elseif($partner['xcreator_id'] == $uniqueCreator['xcreator_id'] and $partner['xrule'] == $uniqueCreator['xrule']) {
                                $totalCirculation += $book['xcirculation'];
                                $isTranslate = $book['is_translate'];
                        }
                    }

                } $data[] = array
                (
                    "creator" => ["id" => $uniqueCreator['xcreator_id'], "name" => $uniqueCreator['xcreatorname']],
                    "role" => $uniqueCreator['xrule'],
                    // "translate" => $creatorRole->xlang == "فارسی" ? 0 : 1,
                    "translate" => $isTranslate == 2 ? 1 : 0, // if translate return 1
                    "circulation" => priceFormat($totalCirculation)
                );
                $totalCirculation = 0 ;
                $isTranslate = 0;
            }

            $seen = [];
            if ($data != null)
            foreach ($data as $entry) {
                $name = trim($entry["creator"]["name"]);
                $role = $entry["role"];
                $circulation = (int)$entry["circulation"];

                if ($circulation > 0) {
                    $key = $name . "|" . $role;

                    if (!isset($seen[$key])) {
                        $seen[$key] = true;
                        $unique_entries[] = $entry;
                    }
                }
            }
        }

        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => "ok" ,
                "data" => ["list" => $unique_entries, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
            $status
        );
    }

    ///////////////////////////////////////////////Creator Aggregation///////////////////////////////////////////////////
    public function creatorAggregation(Request $request)
    {
        $creatorId = (isset($request["creatorId"])) ? $request["creatorId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "publisher.xpublishername";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $totalRows = 0 ;
        $data = null;
        $publishersData = null;
        $status = 200;


        // read
        $books = BookIrBook2::query();
        $books->where('partners.xcreator_id' , $creatorId);
        $books->orderBy($column,$sortDirection);
        $books = $books->skip($offset)->take($pageRows)->get(); // get list
        if($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $publishers = $book->publisher;
                foreach ($publishers as $publisher) {
                    $publishersData[$publisher['xpublisher_id']] = $publisher['xpublishername'];
                }
            }
        }

        if($publishersData != null and count($publishersData) > 0)
        {
            foreach ($publishersData as $publisherId => $publisherName)
            {
                $books = BookIrBook2::query();
                $books->where('partners.xcreator_id' , $creatorId);
                $books->where('publisher.xpublisher_id' , $publisherId);
                if($yearStart != "") $books->where("xpublishdate_shamsi", ">=", (int)$yearStart);
                if($yearEnd != "") $books->where("xpublishdate_shamsi", "<=", (int)$yearEnd);
                $books->orderBy($column,$sortDirection);
                $totalPages = $books->count();
                $books = $books->skip($offset)->take($pageRows)->get(); // get list                if($books != null and count($books) > 0)
                {
                    foreach ($books as $book)
                    {
                        $data[$creatorId] = array
                        (
                            "publisher" => ["id" => $publisherId, "name" => $publisherName],
                            "countTitle" => 1 + ((isset($data[$creatorId])) ? $data[$creatorId]["countTitle"] : 0),
                            "circulation" => $book->xcirculation + ((isset($data[$creatorId])) ? $data[$creatorId]["circulation"] : 0),
                        );
                    }

                    foreach ($data as $key => $item)
                    {
                        $data[$key]["circulation"] = priceFormat($item["circulation"]);
                    }

                    $data = array_values($data);
                }
            }
        }

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => "ok",
                "data" => ["list" => $data,"currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
            $status
        );
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
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"]) ? $request["perPage"] : 50;
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
}
