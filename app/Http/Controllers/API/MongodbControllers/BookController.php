<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ExcelController;
use App\Models\BookirBook;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\BookIrPublisher;
use Illuminate\Http\Request;


class BookController extends Controller
{
    ///////////////////////////////////////////////General///////////////////////////////////////////////////

    public function listsWithOutGroupby(Request $request, $defaultWhere = true, $isNull = false, $where = "", $subjectTitle = "", $publisherName = "", $creatorName = "")
    {
        $isbn = (isset($request["isbn"])) ? str_replace("-", "", $request["isbn"]) : "";
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"] : "xpublishdate_shamsi";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        if (!$isNull) {
            // read books
            $books = BookIrBook2::orderBy($column, $sortDirection);

            if ($searchText != "") {
                $books->where('xname', 'like', "%$searchText%");
            }


            if ($isbn != "") {
                $books->where(function ($query) use ($isbn) {
                    $query->where('xisbn2', '=', $isbn)
                        ->orWhere('xisbn3', '=', $isbn);
                });
            }


            if (count($where) > 0) {
                if (count($where[0]) == 2) {
                    $books->where(function ($query) use($where){
                        $query->where($where[0][0], $where[0][1]); // Apply the first condition using where()
                        // Apply subsequent conditions using orWhere()
                        for ($i = 1; $i < count($where); $i++) {
                            $query->orWhere($where[$i][0], $where[$i][1]);
                        }
                    });
                };

                if (count($where[0]) == 4) {

                    for ($i = 0 ; $i < count($where) ; $i++){
                        if ($where[$i][3] == ''){
                            $books->where($where[$i][0] , $where[$i][2] , $where[$i][1]);
                        } elseif ($where[$i][3] == 'AND'){
                            $books->where($where[$i][0] ,$where[$i][2] ,$where[$i][1]);

                        }elseif($where[$i][3] == 'OR'){

                            $books->where(function ($query) use($where,&$i){
                                $query->where($where[$i][0],$where[$i][2],$where[$i][1]);
                                $query->orWhere($where[$i+1][0], $where[$i+1][2] , $where[$i+1][1]);
                                $i++;
                                for ($j = $i ; $j<count($where) ; $j++){
                                    if ($where[$j][3] == 'OR'){
                                        $query->orWhere($where[$j+1][0],$where[$j+1][2],$where[$j+1][1]);
                                        $i++;
                                    }else{
                                        break;
                                    }
                                }
                            });
                        }
                    }
                }
            }
            else {
                return response()->json([
                    "status" => 404,
                    "message" => "not found",
                    "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows, "subjectTitle" => $subjectTitle, "publisherName" => $publisherName, "creatorName" => $creatorName]
                ], 404);
            }

            /////give count ///////////////////
            $countBooks = $books->get();
            $totalRows = count($countBooks);

            /////give result //////////////////
            $books = $books->skip($offset)->take($pageRows)->get();
            if ($books != null and count($books) > 0) {
                foreach ($books as $book) {
                    if ($book->xparent == -1 or $book->xparent == 0) {
                        $dossier_id = $book->_id;
                    } else {
                        $dossier_id = $book->xparent;
                    }

                    $publishers = null;

                    $bookPublishers = $book->publisher;
                    if ($bookPublishers != null and count($bookPublishers) > 0) {
                        foreach ($bookPublishers as $bookPublisher) {
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
                            "circulation" => priceFormat($book->xcirculation),
                            "format" => $book->xformat,
                            "cover" => ($book->xcover != null and $book->xcover != "null") ? $book->xcover : "",
                            "pageCount" => $book->xpagecount,
                            "isbn" => $book->xisbn,
                            "price" => priceFormat($book->xcoverprice),
                            "image" => $book->ximgeurl,
                            "description" => $book->xdescription,
                            "doi" => $book->xdiocode,
                        ];
                }
            }

        }

        if ($data != null or $subjectTitle != "") $status = 200;

        // response
        return response()->json(
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows, "subjectTitle" => $subjectTitle, "publisherName" => $publisherName, "creatorName" => $creatorName]
            ],
            $status
        );
    }


    public function lists(Request $request, $defaultWhere = true, $isNull = false, $where = [], $subjectTitle = "", $publisherName = "", $creatorName = "")
    {
        $isbn = (isset($request["isbn"])) ? str_replace("-", "", $request["isbn"]) : "";
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"] : "xpublishdate_shamsi";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 1;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"]) ? (int)$request["perPage"] : 50;
        $offset = max(0, ($currentPageNumber - 1) * $pageRows); // Ensure offset is non-negative

        $data = [];
        $status = 200;
        $totalRows = 0;
        $totalPages = 0;

        if (!$isNull) {
            // Prepare the base query with filters
            $query = BookIrBook2::query();

            if ($searchText != "") {
                $query->where('xname', 'like', "%$searchText%");
            }

            if ($isbn != "") {
                $query->where(function ($query) use ($isbn) {
                    $query->where('xisbn2', '=', $isbn)
                        ->orWhere('xisbn3', '=', $isbn);
                });
            }

            if (count($where) > 0) {
                if (count($where[0]) == 2) {
                    $query->where(function ($query) use ($where) {
                        $query->where($where[0][0], $where[0][1]);
                        for ($i = 1; $i < count($where); $i++) {
                            $query->orWhere($where[$i][0], $where[$i][1]);
                        }
                    });
                } elseif (count($where[0]) == 4) {
                    for ($i = 0; $i < count($where); $i++) {
                        if ($where[$i][3] == '') {
                            $query->where($where[$i][0], $where[$i][2], $where[$i][1]);
                        } elseif ($where[$i][3] == 'AND') {
                            $query->where($where[$i][0], $where[$i][2], $where[$i][1]);
                        } elseif ($where[$i][3] == 'OR') {
                            $query->where(function ($query) use ($where, &$i) {
                                $query->where($where[$i][0], $where[$i][2], $where[$i][1]);
                                $query->orWhere($where[$i + 1][0], $where[$i + 1][2], $where[$i + 1][1]);
                                $i++;
                                for ($j = $i; $j < count($where); $j++) {
                                    if ($where[$j][3] == 'OR') {
                                        $query->orWhere($where[$j + 1][0], $where[$j + 1][2], $where[$j + 1][1]);
                                        $i++;
                                    } else {
                                        break;
                                    }
                                }
                            });
                        }
                    }
                }
            }

            // Fetch filtered and sorted books IDs
            $bookIds = $query->orderBy($column, $sortDirection)->pluck('_id')->toArray();

            // Use raw MongoDB aggregation to group by xparent, sort and apply pagination
            $books = BookIrBook2::raw(function ($collection) use ($bookIds, $offset, $pageRows) {
                return $collection->aggregate([
                    ['$match' => ['_id' => ['$in' => $bookIds]]],
                    ['$sort' => ['xparent' => 1]],
                    ['$group' => [
                        '_id' => '$xparent',
                        'dossier_id' => ['$first' => '$dossier_id'],
                        'name' => ['$first' => '$xname'],
                        'publishers' => ['$first' => '$publishers'],
                        'language' => ['$first' => '$language'],
                        'year' => ['$first' => '$xpublishdate_shamsi'],
                        'printNumber' => ['$first' => '$xprintnumber'],
                        'circulation' => ['$first' => '$xcirculation'],
                        'format' => ['$first' => '$xformat'],
                        'cover' => ['$first' => '$xcover'],
                        'pageCount' => ['$first' => '$xpagecount'],
                        'isbn' => ['$first' => '$xisbn'],
                        'price' => ['$first' => '$xcoverprice'],
                        'image' => ['$first' => '$ximgeurl'],
                        'description' => ['$first' => '$xdescription'],
                        'doi' => ['$first' => '$xdiocode'],
                    ]],
                    ['$sort' => ['_id' => 1]],
                    ['$skip' => $offset],
                    ['$limit' => $pageRows]
                ]);
            });
//            dd($books);
            // Get total rows after aggregation
            $totalRows = BookIrBook2::raw(function ($collection) use ($bookIds) {
                return $collection->aggregate([
                    ['$match' => ['_id' => ['$in' => $bookIds]]],
                    ['$group' => ['_id' => '$xparent']]
                ]);
            })->toArray();
            $totalRows = count($totalRows);
            $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

            // Process the grouped books
            foreach ($books as $book) {
                $dossier_id = $book->_id == -1 || $book->_id == 0 ? $book->dossier_id : $book->_id;

                // Process publishers
                $publishers = null;
                $bookPublishers = $book->publishers;
                if ($bookPublishers != null && count($bookPublishers) > 0) {
                    foreach ($bookPublishers as $bookPublisher) {
                        $publishers[] = ["id" => $bookPublisher['xpublisher_id'], "name" => $bookPublisher['xpublishername']];
                    }
                }

                // Add book to data
                $data[] = [
                    "id" => $book->_id,
                    "dossier_id" => $dossier_id,
                    "name" => $book->name,
                    "publishers" => $publishers,
                    "language" => $book->language,
                    "year" => $book->year,
                    "printNumber" => $book->printNumber,
                    "circulation" => $book->circulation,
                    "format" => $book->format,
                    "cover" => ($book->cover != null && $book->cover != "null") ? $book->cover : "",
                    "pageCount" => $book->pageCount,
                    "isbn" => $book->isbn,
                    "price" => $book->price,
                    "image" => $book->image,
                    "description" => $book->description,
                    "doi" => $book->doi,
                ];
            }

            if (empty($data)) {
                $status = 404;
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
                "totalRows" => $totalRows,
                "subjectTitle" => $subjectTitle,
                "publisherName" => $publisherName,
                "creatorName" => $creatorName
            ]
        ], $status);
    }


    public function exportLists(Request $request, $defaultWhere = true, $isNull = false, $where = "", $subjectTitle = "", $publisherName = "", $creatorName = "")
    {
        $name = (isset($request["name"])) ? $request["name"] : "";
        $isbn = (isset($request["isbn"])) ? str_replace("-", "", $request["isbn"]) : "";
        $yearStart = (isset($request["yearStart"]) && $request["yearStart"] != 0) ? $request["yearStart"] : "";
        $yearEnd = (isset($request["yearEnd"]) && $request["yearEnd"] != 0) ? $request["yearEnd"] : "";
        $data = null;
        $status = 404;

        if (!$isNull) {
            $books = BookIrBook2::orderBy('xpublishdate_shamsi', -1);
            if ($name != "") {
                $books->where('xname', 'like', "%$name%");
            }


            if ($isbn != "") {
                $books->where(function ($query) use ($isbn) {
                    $query->where('xisbn2', '=', $isbn)
                        ->orWhere('xisbn3', '=', $isbn);
                });
            }

            if (count($where) > 0) {
                if (count($where[0]) == 2) {
                    $books->where(function ($query) use($where){
                        $query->where($where[0][0], $where[0][1]); // Apply the first condition using where()
                        // Apply subsequent conditions using orWhere()
                        for ($i = 1; $i < count($where); $i++) {
                            $query->orWhere($where[$i][0], $where[$i][1]);
                        }
                    });
                };

                if (count($where[0]) == 4) {

                    for ($i = 0 ; $i < count($where) ; $i++){
                        if ($where[$i][3] == ''){
                            $books->where($where[$i][0] , $where[$i][2] , $where[$i][1]);
                        } elseif ($where[$i][3] == 'AND'){
                            $books->where($where[$i][0] ,$where[$i][2] ,$where[$i][1]);

                        }elseif($where[$i][3] == 'OR'){

                            $books->where(function ($query) use($where,&$i){
                                $query->where($where[$i][0],$where[$i][2],$where[$i][1]);
                                $query->orWhere($where[$i+1][0], $where[$i+1][2] , $where[$i+1][1]);
                                $i++;
                                for ($j = $i ; $j<count($where) ; $j++){
                                    if ($where[$j][3] == 'OR'){
                                        $query->orWhere($where[$j+1][0],$where[$j+1][2],$where[$j+1][1]);
                                        $i++;
                                    }else{
                                        break;
                                    }
                                }
                            });
                        }
                    }
                }
            }
            else {
                return response()->json([
                    "status" => 404,
                    "message" => "not found",
                    "data" => ["list" => $data,  "subjectTitle" => $subjectTitle, "publisherName" => $publisherName, "creatorName" => $creatorName]
                ], 404);
            }

            if ($yearStart != "") {
                $books->where('xpublishdate_shamsi', '>=', (int)$yearStart);
            }


            if ($yearEnd != "") {

                $books->where('xpublishdate_shamsi', '<=', (int)$yearEnd);
            }

            $books->orderBy('xisbn');
            $books = $books->get();

            if ($books != null and count($books) > 0) {
                foreach ($books as $book) {
                    if ($book->xparent == -1 or $book->xparent == 0) {
                        $dossier_id = $book->_id;
                    } else {
                        $dossier_id = $book->xparent;
                    }

                    //publishers
                    $publishers = null;
                    $bookPublishers = $book->publisher;
                    if ($bookPublishers != null and count($bookPublishers) > 0) {
                        foreach ($bookPublishers as $bookPublisher) {
                            $publishers [] = ["id" => $bookPublisher['xpublisher_id'], "name" => $bookPublisher['xpublishername']];
                        }
                    }
                    //subjects
                    $subjects = null;
                    $bookSubjects = $book->subjects;
                    if ($bookSubjects != null and count($bookSubjects) > 0) {
                        foreach ($bookSubjects as $bookSubject) {
                            $subjects[] = ["id" => $bookSubject['xsubject_id'], "name" => $bookSubject['xsubject_name']];
                        }
                    }

                    //authors
                    $authors = null;
                    $bookPartners = $book->partners;
                    if ($bookPartners != null and count($bookPartners) > 0) {
                        foreach ($bookPartners as $bookPartner) {
                            if ($bookPartner['xrule'] == 'نويسنده') {
                                $authors [] = ['id' => $bookPartner['xcreator_id'], 'name' => $bookPartner['xcreatorname']];
                            }
                        }
                    }


                    //translator
                    $translators = null;
                    $bookPartners = $book->partners;
                    if ($bookPartners != null and count($bookPartners) > 0) {
                        foreach ($bookPartners as $bookPartner) {
                            if ($bookPartner['xrule'] == 'مترجم') {
                                $translators [] = ['id' => $bookPartner['xcreator_id'], 'name' => $bookPartner['xcreatorname']];
                            }
                        }
                    }


                    //imager
                    $imagers = null;
                    $bookPartners = $book->partners;
                    if ($bookPartners != null and count($bookPartners) > 0) {
                        foreach ($bookPartners as $bookPartner) {
                            if ($bookPartner['xrule'] == 'تصویرگر') {
                                $imagers [] = ['id' => $bookPartner['xcreator_id'], 'name' => $bookPartner['xcreatorname']];
                            }
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
                            "circulation" => priceFormat($book->xcirculation),
                            "format" => $book->xformat,
                            "cover" => ($book->xcover != null and $book->xcover != "null") ? $book->xcover : "",
                            "pageCount" => $book->xpagecount,
                            "isbn" => $book->xisbn,
                            "price" => priceFormat($book->xcoverprice),
                            "image" => ($book->ximgeurl != '../Images/nopic.jpg') ? $book->ximgeurl : '',
                            "description" => $book->xdescription,
                            "doi" => $book->xdiocode,
                            "subjects" => $subjects,
                            "authors" => $authors,
                            "translators" => $translators,
                            "imagers" => $imagers,
                        ];
                }
            }
        }

        if ($data != null or $subjectTitle != "") $status = 200;

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

    ///////////////////////////////////////////////Find Books///////////////////////////////////////////////////
    public function find(Request $request)
    {
        return $this->lists($request);
    }

    ///////////////////////////////////////////////Publishers///////////////////////////////////////////////////
    public function findByPublisher(Request $request)
    {
        $where = $this->findByPublisherSelect($request);
        return $this->lists($request, true, ($where == ""), $where);
    }

    public function findByPublisherSelect(Request $request)
    {
        $publisherId = $request["publisherId"];
        $bookId = $request["bookId"];
        $where = [];

        if ($publisherId > 0) {
            $where[] = ['publisher.xpublisher_id' , $publisherId];
        } elseif ($bookId > 0) {
            // get publisher
            $books = BookIrBook2::where('_id', $bookId)->get();
            $publishers = [];
            foreach ($books as $book) {
                $publishers = $book->publisher;
            }
            if ($publishers != null and count($publishers) > 0) {
                foreach ($publishers as $publisher) {
                    $where[] = ['publisher.xpublisher_id' , $publisher['xpublisher_id'] ];
                }
            }
        }
        return $where;
    }

    public function exportExcelBookFindByPublisher(Request $request)
    {
        $where = $this->findByPublisherSelect($request);
        $result = $this->exportLists($request, true, ($where == ""), $where);
        $mainResult = $result->getData();
        if ($mainResult->status == 200) {
            $publisherInfo = BookIrPublisher::where('_id', $request["publisherId"])->first();
            $response = ExcelController::booklist($mainResult, 'کتب ناشر' . time(), mb_substr($publisherInfo->xpublishername, 0, 30, 'UTF-8'));
            return response()->json($response);
        } else {
            return $mainResult->status;
        }
    }


    ///////////////////////////////////////////////Creators///////////////////////////////////////////////////

    public function findByCreator(Request $request)
    {
        $where = $this->findByCreatorSelect($request);
        return $this->lists($request, true, ($where == ""), $where);
    }
    public function findByCreatorSelect(Request $request)
    {
        $creatorId = $request["creatorId"];
        $bookId = $request["bookId"];
        $where = [];

        if ($creatorId > 0) {
            $where[] = ['partners.xcreator_id' , "$creatorId" ];

        } elseif ($bookId > 0) {
            // get publisher
            $books = BookIrBook2::where('_id', $bookId)->get();
            $creators = [];

            foreach ($books as $book) {
                $creators  = $book->partners;
            }
            if ($creators != null and count($creators) > 0) {
                foreach ($creators as $creator) {
                    $where [] = ['partners.xcreator_id' ,  $creator['xcreator_id']] ;
                }
            }
        }
        return $where;
    }
    public function exportExcelBookFindByCreator(Request $request)
    {
        $where = $this->findByCreatorSelect($request);
        $result = $this->exportLists($request, true, ($where == ""), $where);
        $mainResult = $result->getData();
        if ($mainResult->status == 200) {
            $creatorInfo = BookIrCreator::where('_id', $request["creatorId"])->first();
            $response = ExcelController::booklist($mainResult, 'کتب پدیدآورنده' . time(), mb_substr($creatorInfo->xcreatorname, 0, 30, 'UTF-8'));
            return response()->json($response);
        } else {
            return $mainResult->status;
        }
    }

    ///////////////////////////////////////////////Isbn///////////////////////////////////////////////////

    public function findIsbn(Request $request)
    {
        $book = BookIrBook2::where('xparent', -1);
        $book = $book->where(function ($query) use ($book, $request) {
            $query->where('xisbn', $request["searchIsbnBook"])->OrWhere('xisbn2', $request["searchIsbnBook"])->OrWhere('xisbn3', $request["searchIsbnBook"]);
        });
        $book = $book->first();
        if ($book != null and $book->_id > 0) {
            return '/book/edit/' . $book->_id;
        } else {
            return '/book/new';
        }
    }

    ///////////////////////////////////////////////Ver///////////////////////////////////////////////////

    public function findByVer(Request $request)
    {
        $bookId = $request["bookId"];
        // $bookId = 13349;
        $where [] = ['_id' , $bookId];
        $where [] = [ 'xparent' , $bookId];

        return $this->listsWithOutGroupby($request, true, ($where == ""), $where);
    }


    ///////////////////////////////////////////////Subject///////////////////////////////////////////////////

    public function findBySubject(Request $request)
    {
        $subjectId = $request["subjectId"];
        $subjectTitle = "";
        $mainSubject = [];

        $bookSubjects = BookIrBook2::where('subjects.xsubject_id', (int)$subjectId);

            if($bookSubjects->count() > 0) {
                $bookSubjects = $bookSubjects->first()['subjects'];
                foreach ($bookSubjects as $subject) {
                    if ($subject['xsubject_id'] == (int)$subjectId) {
                        $mainSubject = $subject;
                        break;
                    }
                }
            }
        if ($mainSubject!= null and $mainSubject['xsubject_id'] > 0) {
            $subjectTitle = $mainSubject['xsubject_name'];
        }

        $where[] =  ['subjects.xsubject_id' , (int)$subjectId ] ;

        return $this->lists($request, true, ($where == ""), $where, $subjectTitle);

    }



    ///////////////////////////////////////////////Detail///////////////////////////////////////////////////

    public function detail(Request $request)
    {
        $bookId = $request["bookId"];
        $dataMaster = null;
        $yearPrintCountData = null;
        $publisherPrintCountData = null;
        $status = 404;

        // read books
        $book = BookIrBook2::where('_id', '=', $bookId)->first();
        if ($book != null and $book->_id > 0) {
            $publishersData = null;
            $subjectsData = null;
            $creatorsData = null;


            $bookPublishers = $book->publisher;
            if ($bookPublishers != null and count($bookPublishers) > 0) {
                foreach ($bookPublishers as $bookPublisher) {
                    $publishersData[] = ["id" => $bookPublisher['xpublisher_id'], "name" => $bookPublisher['xpublishername']];
                }
            }


            $bookSubjects = $book->subjects;
            if ($bookSubjects != null and count($bookSubjects) > 0) {
                foreach ($bookSubjects as $bookSubject) {
                    $subjectsData[] = ["id" => $bookSubject['xsubject_id'], "name" => $bookSubject['xsubject_name']];
                }
            }

            $bookCreators = $book->partners;
            if ($bookCreators != null and count($bookCreators) > 0) {
                foreach ($bookCreators as $bookCreator) {
                    $creatorsData[] = ["id" => $bookCreator['xcreator_id'], "name" => $bookCreator['xcreatorname'] , 'role' =>$bookCreator['xrule']];
                }
            }


            $isbn = array();
            if (isset($book->xisbn) and !empty($book->xisbn)) {
                $isbn[] = $book->xisbn;
            }
            if (isset($book->xisbn2) and !empty($book->xisbn2)) {
                $isbn[] = $book->xisbn2;
            }
            if (isset($book->xisbn3) and !empty($book->xisbn3)) {
                $isbn[] = $book->xisbn3;
            }

            //
            $dataMaster =
                [
                    "isbns" => $isbn,
                    "name" => $book->xname,
                    "dioCode" => $book->xdiocode,
                    "publishers" => $publishersData,
                    "subjects" => $subjectsData,
                    "creators" => $creatorsData,
                    "image" => $book->ximgeurl,
                    "publishPlace" => $book->xpublishplace,
                    "format" => $book->xformat,
                    "cover" => ($book->xcover != null and $book->xcover != "null") ? $book->xcover : "",
                    "publishDate" =>$book->xpublishdate_shamsi,
                    "printNumber" => $book->xprintnumber,
                    "circulation" => $book->xcirculation,
                    "price" => priceFormat($book->xcoverprice),
                    "des" => $book->xdescription,
                ];
        }

        // read books for year printCount
        $books = BookIrBook2::where('_id', $bookId)->orwhere('xparent', $bookId)->get();
        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $year =$book->xpublishdate_shamsi;
                $printCount = $book->xcirculation;

                $yearPrintCountData[$year] = ["year" => $year, "printCount" => (isset($yearPrintCountData[$year])) ? $printCount + $yearPrintCountData[$year]["printCount"] : $printCount];
            }

            $yearPrintCountData = ["label" => array_column($yearPrintCountData, 'year'), "value" => array_column($yearPrintCountData, 'printCount')];
        }


        // read books for publisher PrintCount
        //must testing for more complicate examples ?!?!?!?!?!?
        $books = BookIrBook2::where('_id' , $bookId)->orWhere('xparent' , $bookId)->get();

        if ($books != null and count($books) > 0) {
            $totalPrintCount = 0;
            foreach ($books as $book) {
                $totalPrintCount += $book->sum('xpagecount');
            }

            foreach ($books as $book) {
                foreach ($book->publisher as $publisher) {
                    $publisherName = $publisher['xpublishername'];
                    $percentPrintCount = ($book->sum('xpagecount') > 0 and $totalPrintCount > 0) ? round(($book->sum('xpagecount') / $totalPrintCount) * 100, 2) : 0;

                    $publisherPrintCountData[] = ["name" => $publisherName, "percentPrintCount" => $percentPrintCount];
                }
            }

            $publisherPrintCountData = ["label" => array_column($publisherPrintCountData, 'name'), "value" => array_column($publisherPrintCountData, 'percentPrintCount')];
        }

        //
        if ($dataMaster != null) $status = 200;

        // response
        return response()->json(
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["master" => $dataMaster, "yearPrintCount" => $yearPrintCountData, "publisherPrintCount" => $publisherPrintCountData]
            ],
            $status
        );
    }

    ///////////////////////////////////////////////Info///////////////////////////////////////////////////
    public function info($bookId)
    {
        $dataMaster = null;
        $yearPrintCountData = null;
        $publisherPrintCountData = null;
        $status = 404;

        $book = BookIrBook2::where('_id', '=', $bookId)->first();
        if ($book != null and $book->_id > 0) {
            $publishersData = null;
            $subjectsData = null;
            $creatorsData = null;


            $bookPublishers = $book->publisher;
            if ($bookPublishers != null and count($bookPublishers) > 0) {
                foreach ($bookPublishers as $bookPublisher) {
                    $publishersData[] = ["id" => $bookPublisher['xpublisher_id'], "name" => $bookPublisher['xpublishername']];
                }
            }


            $bookSubjects = $book->subjects;
            if ($bookSubjects != null and count($bookSubjects) > 0) {
                foreach ($bookSubjects as $bookSubject) {
                    $subjectsData[] = ["id" => $bookSubject['xsubject_id'], "name" => $bookSubject['xsubject_name']];
                }
            }

            $bookCreators = $book->partners;
            if ($bookCreators != null and count($bookCreators) > 0) {
                foreach ($bookCreators as $bookCreator) {
                    $creatorsData[] = ["id" => $bookCreator['xcreator_id'], "name" => $bookCreator['xcreatorname']];
                }
            }

            //
            $dataMaster =
                [
                    "isbn" => $book->xisbn,
                    "isbn2" => $book->xisbn2,
                    "isbn3" => $book->xisbn3,
                    "name" => $book->xname,
                    "dioCode" => $book->xdiocode,
                    "lang" => $book->xlang,
                    "publishers" => $publishersData,
                    "subjects" => $subjectsData,
                    "creators" => $creatorsData,
                    "image" => ($book->xreg_userid) ? env('APP_URL') . $book->ximgeurl : $book->ximgeurl,
                    "publishPlace" => $book->xpublishplace,
                    "format" => $book->xformat,
                    "cover" => ($book->xcover != null and $book->xcover != "null") ? $book->xcover : "",
                    "publishDate" => $book->xpublishdate_shamsi,
                    "printNumber" => $book->xprintnumber,
                    "pageCount" => $book->xpagecount,
                    "weight" => $book->xweight,
                    "circulation" => $book->xcirculation,
                    "price" => $book->xcoverprice,
                    "des" => $book->xdescription,
                ];
        }

        // read books for year printCount
        $books = BookirBook::where('xid', '=', $bookId)->orwhere('xparent', '=', $bookId)->get();
        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $year = BookirBook::getShamsiYear($book->xpublishdate);
                $printCount = $book->xcirculation;

                $yearPrintCountData[$year] = ["year" => $year, "printCount" => (isset($yearPrintCountData[$year])) ? $printCount + $yearPrintCountData[$year]["printCount"] : $printCount];
            }

            $yearPrintCountData = ["label" => array_column($yearPrintCountData, 'year'), "value" => array_column($yearPrintCountData, 'printCount')];
        }

        // read books for year printCount
        $books = BookIrBook2::where('_id', $bookId)->orwhere('xparent', $bookId)->get();
        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $year =$book->xpublishdate_shamsi;
                $printCount = $book->xcirculation;

                $yearPrintCountData[$year] = ["year" => $year, "printCount" => (isset($yearPrintCountData[$year])) ? $printCount + $yearPrintCountData[$year]["printCount"] : $printCount];
            }

            $yearPrintCountData = ["label" => array_column($yearPrintCountData, 'year'), "value" => array_column($yearPrintCountData, 'printCount')];
        }


        // read books for publisher PrintCount
        //must testing for more complicate examples ?!?!?!?!?!?
        $books = BookIrBook2::where('_id' , $bookId)->orWhere('xparent' , $bookId)->get();

        if ($books != null and count($books) > 0) {
            $totalPrintCount = 0;
            foreach ($books as $book) {
                $totalPrintCount += $book->sum('xpagecount');
            }

            foreach ($books as $book) {
                foreach ($book->publisher as $publisher) {
                    $publisherName = $publisher['xpublishername'];
                    $percentPrintCount = ($book->sum('xpagecount') > 0 and $totalPrintCount > 0) ? round(($book->sum('xpagecount') / $totalPrintCount) * 100, 2) : 0;

                    $publisherPrintCountData[] = ["name" => $publisherName, "percentPrintCount" => $percentPrintCount];
                }
            }

            $publisherPrintCountData = ["label" => array_column($publisherPrintCountData, 'name'), "value" => array_column($publisherPrintCountData, 'percentPrintCount')];
        }

        //
        if ($dataMaster != null) $status = 200;

        // response
        return response()->json(
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["master" => $dataMaster, "yearPrintCount" => $yearPrintCountData, "publisherPrintCount" => $publisherPrintCountData]
            ],
            $status
        );
    }


    ///////////////////////////////////////////////Info///////////////////////////////////////////////////
    public function searchDio(Request $request)
    {
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 404;

        // read
        $books = BookIrBook2::where('xdiocode', 'like', "%$searchWord%")->orderBy('xdiocode', 1)->get();
        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $data[md5($book->xdiocode)] =
                    [
                        "id" => $book->xdiocode,
                        "value" => $book->xdiocode,
                    ];
            }

            $status = 200;
            $data = array_values($data);
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

    ///////////////////////////////////////////////CreatorOfPublisher///////////////////////////////////////////////////
    public function findByCreatorOfPublisher(Request $request)
    {

        $publisherId = $request["publisherId"];
        $creatorId = $request["creatorId"];
        $creatorName = "" ;
        $publisherName = '';
        $where = [];

        if ($publisherId != "" or $creatorId != "") {
            if ($publisherId == 0) {
                $publisherBooks = BookIrBook2::where('partners.xcreator_id', $creatorId)->get();
            } else {
                $publisherBooks = BookIrBook2::where('publisher.xpublisher_id', $publisherId)->get();
                if (BookIrPublisher::where('_id', $publisherId)->count() > 0)
                $publisherName = BookIrPublisher::where('_id', $publisherId)->first()->xpublishername;
            }
            if ($creatorId != 0 and BookIrCreator::where('_id', $creatorId)->count() > 0) {
                    $creatorName = BookIrCreator::where('_id', $creatorId)->first()->xcreatorname;
            }


            if ($publisherBooks->count() > 0) {
                foreach ($publisherBooks as $publisherBook) {
                    $where [] = ['_id', $publisherBook->_id];
                }
            }
        }
        return $this->lists($request, true, ($where == ""), $where, "", $publisherName, $creatorName);
    }


    ///////////////////////////////////////////////FindBySharedCreators///////////////////////////////////////////////////
    public function findBySharedCreators(Request $request)
    {
        $creatorId = $request["creatorId"];
        $teammateId = $request["teammateId"];
        $where = [];
        $creatorName = null;
        $teammateName = '';

        $books = BookIrBook2::where('partners.xcreator_id', $creatorId)->get();
        foreach ($books as $book){
            foreach ($book->partners as $partner) {
                if ($partner['xcreator_id'] == $teammateId) {

                    $where [] = ['_id', $book->_id];

                    $teammateName = $partner['xcreatorname'];
                    break;
                }
            }
        }

        if (BookIrCreator::where('_id', $creatorId)->count() > 0){
            $creatorName = [BookIrCreator::where('_id', $creatorId)->pluck('xcreatorname')[0] , $teammateName];
        }

        return $this->lists($request, true, ($where == ""), $where, "", "", $creatorName);
    }


    ///////////////////////////////////////////////AdvanceSearch///////////////////////////////////////////////////
    //TODO : have rule for search in ui . where must always be true but or where can be false
    public function advanceSearch(Request $request)
    {
        $where = [];
        $possibilityEmptyLogicalOperator = true;
        $beforeLogicalOperator = '';
        foreach ($request['search'] as $key => $item) {
            if (gettype($item) != 'array') {
                $search_item = json_decode($item, true);
            } else {
                $search_item = $item;
            }

            if (!empty($search_item)) {
                //    unset($search_item);
                //    $search_item = array();
                if (isset($search_item['field'])) {
                    $searchField = $search_item['field'];
                } else {
                    $searchField = '';
                }
                if (isset($search_item['comparisonOperator'])) {
                    $comparisonOperators = $search_item['comparisonOperator'];
                } else {
                    $comparisonOperators = '';
                }
                if (isset($search_item['value'])) {
                    $searchValue = $search_item['value'];
                } else {
                    $searchValue = '';
                }
                if (isset($search_item['logicalOperator'])){
                    $logicalOperator = $search_item['logicalOperator'] ;
                } else {
                    $logicalOperator = '';
                }


                // search by name
                if (($searchField == 'name') and !empty($comparisonOperators) and !empty($searchValue)) {
                    if ($comparisonOperators == 'like') {
                        $where [] = ['xname', "%$searchValue%", 'like', $logicalOperator];
                    }else{
                        $where [] = ['xname', "$searchValue", $comparisonOperators, $logicalOperator];
                    }
                }


                // search by dio
                if (($searchField == 'dio') and !empty($comparisonOperators) and !empty($searchValue)) {
                    if ($comparisonOperators == 'like') {
                        $where [] = ['xdiocode', "%$searchValue%", 'like', $logicalOperator];
                    }else{
                        $where [] = ['xdiocode', "$searchValue", $comparisonOperators, $logicalOperator];
                    }
                }


                // search by doi
                if (($searchField == 'isbn2') and !empty($comparisonOperators) and !empty($searchValue)) {
                    if ($comparisonOperators == 'like') {
                        $where [] = ['xisbn2', "%$searchValue%", 'like', 'OR'];
                        $where [] = ['xisbn', "%$searchValue%", 'like', 'OR'];
                        $where [] = ['xisbn3', "%$searchValue%", 'like', $logicalOperator];
                    }else{
                        $where [] = ['xisbn2', "$searchValue", $comparisonOperators, 'OR'];
                        $where [] = ['xisbn', "$searchValue", $comparisonOperators, "OR"];
                        $where [] = ['xisbn3', "$searchValue", $comparisonOperators, $logicalOperator];

                    }
                }


                // search by publish date
                if (($searchField == 'publishDate') and !empty($comparisonOperators) and !empty($searchValue)) {
                        if ($comparisonOperators == 'like') {
                            $where [] = ['xpublishdate_shamsi' , "%$searchValue%" , 'like' , $logicalOperator];
                        } else {
                            $where []= ['xpublishdate_shamsi' , $searchValue , $comparisonOperators , $logicalOperator];
                        }
                }

                // search by price
                if (($searchField == 'price') and !empty($comparisonOperators) and !empty($searchValue)) {
                        if ($comparisonOperators == 'like') {
                            $where []= ['xcoverprice' , "%$searchValue%" , 'like' , $logicalOperator];
                        } else {
                            $where []= [ 'xcoverprice' , $searchValue , $comparisonOperators , $logicalOperator];
                        }

                }
                // search by circulation
                if (($searchField == 'circulation') and !empty($comparisonOperators) and !empty($searchValue)) {
                    if ($comparisonOperators == 'like') {
                        $where []= ['xcirculation' , "%$searchValue%" , 'like' , $logicalOperator];
                    } else {
                        $where [] = ['xcirculation' , $searchValue , $comparisonOperators , $logicalOperator];
                    }
                }

                //search by publisher
                if (($searchField == 'publisher') and !empty($comparisonOperators) and !empty($searchValue)) {
                    if ($comparisonOperators == 'like') {
                        $where [] = ['publisher.xpublishername', "%$searchValue%", 'like', $logicalOperator];
                    } else {
                        $where [] = ['publisher.xpublishername', $searchValue, $comparisonOperators, $logicalOperator];
                    }
                }

                //search by creator
                if (($searchField == 'creator') and !empty($comparisonOperators) and !empty($searchValue)) {
                    if ($comparisonOperators == 'like') {
                        $where [] = ['partners.xcreatorname', "%$searchValue%", 'like', $logicalOperator];
                    } else {
                        $where [] = ['partners.xcreatorname', $searchValue, $comparisonOperators, $logicalOperator];
                    }
                }

                // search by subject
                if (($searchField == 'subject') and !empty($comparisonOperators) and !empty($searchValue)) {
                    if ($comparisonOperators == 'like') {
                        $where [] = ['subjects.xsubject_name', "%$searchValue%", 'like', $logicalOperator];
                    } else {
                        $where [] = ['subjects.xsubject_name', $searchValue, $comparisonOperators, $logicalOperator];
                    }
                }
            }
        }
        return $this->lists($request, false, false, $where);
    }

}

