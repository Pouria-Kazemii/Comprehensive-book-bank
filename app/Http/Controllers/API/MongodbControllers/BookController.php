<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ExcelController;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\BookIrPublisher;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;


class BookController extends Controller
{
    ///////////////////////////////////////////////General///////////////////////////////////////////////////
    public function getTotal()
    {
        return count(BookIrBook2::all());
    }
    public function listsWithOutGroupby(Request $request, $defaultWhere = true, $isNull = false, $where = [], $subjectTitle = "", $publisherName = "", $creatorName = "")
    {
        $start  = microtime(true);
        $isbn = (isset($request["isbn"]) and  preg_match('/\p{L}/u', $request["isbn"])) ? str_replace("-", "", $request["isbn"]) : "";
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xpublishdate_shamsi";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 1;
        $pageRows = (isset($request["perPage"]) && !empty($request["perPage"])) ? (int)$request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $data = [];
        $totalPages = 0 ;
        $totalRows = 0;

        if (!$isNull) {

            $bookQuery = BookIrBook2::query();

            if (!empty($searchText)) {
                $bookQuery->where(['$text' => ['$search' => $searchText]]);
            }

            if ($isbn != "") {
                $bookQuery->where(function ($query) use ($isbn) {
                    $query->where('xisbn2', 'LIKE', "%$isbn%")
                        ->orWhere('xisbn3', 'LIKE', "%$isbn%")
                        ->orWhere('xisbn1', 'LIKE', "%$isbn%");
                });
            }

            if (!$defaultWhere) {
                if (count($where) > 0) {
                    if (count($where[0]) == 2) {
                        $bookQuery->where(function ($query) use ($where) {
                            $query->where($where[0][0], $where[0][1]); // Apply the first condition using where()
                            // Apply subsequent conditions using orWhere()
                            for ($i = 1; $i < count($where); $i++) {
                                $query->orWhere($where[$i][0], $where[$i][1]);
                            }
                        });
                    };
                    if (count($where[0]) == 4) {

                        for ($i = 0; $i < count($where); $i++) {
                            if ($where[$i][3] == '') {
                                $bookQuery->where($where[$i][0], $where[$i][2], $where[$i][1]);
                            } elseif ($where[$i][3] == 'AND') {
                                $bookQuery->where($where[$i][0], $where[$i][2], $where[$i][1]);

                            } elseif ($where[$i][3] == 'OR') {

                                $bookQuery->where(function ($query) use ($where, &$i) {
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
            }

            $bookQuery->orderBy($column, $sortDirection);

            // Get total count without fetching all records
            $totalRows = $bookQuery->count();
            $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

            // Fetch paginated results
            $books = $bookQuery->skip($offset)->take($pageRows)->get();

            if ($books->isNotEmpty()) {
                foreach ($books as $book) {
                    $dossier_id = ($book->xparent == -1 || $book->xparent == 0) ? $book->_id : $book->xparent;

                    $publishers = [];

                    foreach ($book->publisher as $bookPublisher) {
                        $publishers[] = ["id" => $bookPublisher['xpublisher_id'], "name" => $bookPublisher['xpublishername']];
                    }

                    $data[] = [
                        "id" => $book->_id,
                        "dossier_id" => $dossier_id,
                        "name" => $book->xname,
                        "publishers" => $publishers,
                        "language" => $book->languages,
                        "year" => $book->xpublishdate_shamsi,
                        "printNumber" => $book->xprintnumber,
                        "circulation" => priceFormat($book->xcirculation),
                        "format" => $book->xformat,
                        "cover" => ($book->xcover != null && $book->xcover != "null") ? $book->xcover : "",
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
        $end = microtime(true);
        $elapsedTime = $end - $start;
        $status = 200;
        return response()->json([
            "status" => $status,
            "message" => "ok",
            "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows, "subjectTitle" => $subjectTitle, "publisherName" => $publisherName, "creatorName" => $creatorName],
            'time' => $elapsedTime,
        ], $status);
    }


    //TODO : must implement after making book Dossier for groupBys
    public function lists(Request $request, $defaultWhere = true, $isNull = false, $where = [], $subjectTitle = "", $publisherName = "", $creatorName = "")
    {
        $start  = microtime(true);
        $isbn = (isset($request["isbn"]) and  preg_match('/\p{L}/u', $request["isbn"])) ? str_replace("-", "", $request["isbn"]) : "";
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && preg_match('/\p{L}/u', $request["column"])) ? $request["column"] : "xpublishdate_shamsi";
        $sortDirection = (isset($request["sortDirection"]) && $request['sortDirection'] ==(1 or -1)) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 1;
        $pageRows = (isset($request["perPage"]) && !empty($request["perPage"])) ? (int)$request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $data = [];
        $totalPages = 0 ;
        $totalRows = 0;

        if (!$isNull) {

            $bookQuery = BookIrBook2::query();

            if (!empty($searchText)) {
                $bookQuery->where(['$text' => ['$search' => $searchText]]);
            }

            if ($isbn != "") {
                $bookQuery->where(function ($query) use ($isbn) {
                    $query->where('xisbn2', 'LIKE', "%$isbn%")
                        ->orWhere('xisbn3', 'LIKE', "%$isbn%")
                        ->orWhere('xisbn1', 'LIKE', "%$isbn%");
                });
            }

            if (!$defaultWhere) {
                if (count($where) > 0) {
                    if (count($where[0]) == 2) {
                        $bookQuery->where(function ($query) use ($where) {
                            $query->where($where[0][0], $where[0][1]); // Apply the first condition using where()
                            // Apply subsequent conditions using orWhere()
                            for ($i = 1; $i < count($where); $i++) {
                                $query->orWhere($where[$i][0], $where[$i][1]);
                            }
                        });
                    };
                    if (count($where[0]) == 4) {

                        for ($i = 0; $i < count($where); $i++) {
                            if ($where[$i][3] == '') {
                                $bookQuery->where($where[$i][0], $where[$i][2], $where[$i][1]);
                            } elseif ($where[$i][3] == 'AND') {
                                $bookQuery->where($where[$i][0], $where[$i][2], $where[$i][1]);

                            } elseif ($where[$i][3] == 'OR') {

                                $bookQuery->where(function ($query) use ($where, &$i) {
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
            }

            $bookQuery->orderBy($column, $sortDirection);

            // Get total count without fetching all records
            $totalRows = $bookQuery->count();
            $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

            // Fetch paginated results
            $books = $bookQuery->skip($offset)->take($pageRows)->get();

            if ($books->isNotEmpty()) {
                foreach ($books as $book) {
                    $dossier_id = ($book->xparent == -1 || $book->xparent == 0) ? $book->_id : $book->xparent;

                    $publishers = [];

                    foreach ($book->publisher as $bookPublisher) {
                        $publishers[] = ["id" => $bookPublisher['xpublisher_id'], "name" => $bookPublisher['xpublishername']];
                    }

                    $data[] = [
                        "id" => $book->_id,
                        "dossier_id" => $dossier_id,
                        "name" => $book->xname,
                        "publishers" => $publishers,
                        "language" => $book->languages,
                        "year" => $book->xpublishdate_shamsi,
                        "printNumber" => $book->xprintnumber,
                        "circulation" => priceFormat($book->xcirculation),
                        "format" => $book->xformat,
                        "cover" => ($book->xcover != null && $book->xcover != "null") ? $book->xcover : "",
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
        $end = microtime(true);
        $elapsedTime = $end - $start;
        $status = 200;

        return response()->json([
            "status" => $status,
            "message" => "ok",
            "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows, "subjectTitle" => $subjectTitle, "publisherName" => $publisherName, "creatorName" => $creatorName],
            'time' => $elapsedTime,
        ], $status);
    }


    public function exportLists(Request $request, $defaultWhere = true, $isNull = false, $where = "", $subjectTitle = "", $publisherName = "", $creatorName = "")
    {
        $name = (isset($request["name"])) ? $request["name"] : "";
        $isbn = (isset($request["isbn"])) ? str_replace("-", "", $request["isbn"]) : "";
        $yearStart = (isset($request["yearStart"]) && $request["yearStart"] != 0) ? $request["yearStart"] : "";
        $yearEnd = (isset($request["yearEnd"]) && $request["yearEnd"] != 0) ? $request["yearEnd"] : "";
        $data = null;
        $status = 200;

        if (!$isNull) {
            $books = BookIrBook2::query();
            if ($name != "") {
                $books->where(['$text' => ['$search' => $name]]);
            }


            if ($isbn != "") {
                $books->where(function ($query) use ($isbn) {
                    $query->where('xisbn2', 'LIKE', "%$isbn%")
                        ->orWhere('xisbn3', 'LIKE', "%$isbn%")
                        ->orWhere('xisbn1', 'LIKE', "%$isbn%");
                });
            }

            if (!$defaultWhere) {
                if (count($where) > 0) {
                    if (count($where[0]) == 2) {
                        $books->where(function ($query) use ($where) {
                            $query->where($where[0][0], $where[0][1]); // Apply the first condition using where()
                            // Apply subsequent conditions using orWhere()
                            for ($i = 1; $i < count($where); $i++) {
                                $query->orWhere($where[$i][0], $where[$i][1]);
                            }
                        });
                    };

                    if (count($where[0]) == 4) {

                        for ($i = 0; $i < count($where); $i++) {
                            if ($where[$i][3] == '') {
                                $books->where($where[$i][0], $where[$i][2], $where[$i][1]);
                            } elseif ($where[$i][3] == 'AND') {
                                $books->where($where[$i][0], $where[$i][2], $where[$i][1]);

                            } elseif ($where[$i][3] == 'OR') {

                                $books->where(function ($query) use ($where, &$i) {
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

        // response
        return response()->json(
            [
                "status" => $status,
                "message" =>"ok",
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
        return $this->lists($request, false, ($where == []), $where);
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
            $books = BookIrBook2::where('_id', new ObjectId($bookId))->get();
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
        $result = $this->exportLists($request, false, ($where == []), $where);
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
        return $this->lists($request, false, ($where == []), $where);
    }
    public function findByCreatorSelect(Request $request)
    {
        $creatorId = $request["creatorId"];
        $bookId = $request["bookId"];
        $where = [];

        if ($creatorId > 0) {
            $where[] = ['partners.xcreator_id' , $creatorId];

        } elseif ($bookId > 0) {
            // get publisher
            $books = BookIrBook2::where('_id', new ObjectId($bookId))->first();
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
        $result = $this->exportLists($request, false, ($where == []), $where);
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
        $book = BookIrBook2::where(function ($query) use ($request) {
            $query->where('xisbn', $request["searchIsbnBook"])->OrWhere('xisbn2', $request["searchIsbnBook"])->OrWhere('xisbn3', $request["searchIsbnBook"]);
        });
        $book->where('xparent', -1);
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
            $book = BookIrBook2::where('_id' , new ObjectId($bookId))->first();
            if ($book != null) {
                //TODO : must change after implement book dossier collection
                $where [] = [ 'xparent' , $book->xsqlid];
            }
        $where [] = ['_id' , $bookId];

        return $this->listsWithOutGroupby($request, false, ($where == []), $where);
    }


    ///////////////////////////////////////////////Subject///////////////////////////////////////////////////

    public function findBySubject(Request $request)
    {
        $subjectId = $request["subjectId"];
        $subjectTitle = "";
        $mainSubject = [];

        $bookSubjects = BookIrBook2::where('subjects.xsubject_id', (int)$subjectId)->first();

            if($bookSubjects != null) {
                $bookSubjects = $bookSubjects['subjects'];
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

        return $this->lists($request, false, ($where == []), $where, $subjectTitle);

    }



    ///////////////////////////////////////////////Detail///////////////////////////////////////////////////

    public function detail(Request $request)
    {
        $bookId = $request["bookId"];
        $dataMaster = null;
        $yearPrintCountData = null;
        $publisherPrintCountData = null;
        $status = 200;

        // read books
        $book = BookIrBook2::where('_id', new ObjectId($bookId))->first();
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
        //TODO : must change after implement book dossier collection
        $books = BookIrBook2::where('_id', new ObjectId($bookId))->orwhere('xparent', $book != null ? $book->xsqlid : '')->get();
        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $year =$book->xpublishdate_shamsi;
                $printCount = $book->xcirculation;

                $yearPrintCountData[$year] = ["year" => $year, "printCount" => (isset($yearPrintCountData[$year])) ? $printCount + $yearPrintCountData[$year]["printCount"] : $printCount];
            }
            $yearPrintCountData = ["label" => array_column($yearPrintCountData, 'year'), "value" => array_column($yearPrintCountData, 'printCount')];
        }


        // read books for publisher PrintCount
        //TODO : must change after implement book dossier collection
        if ($bookId != null) {
            $bookSqlId = BookIrBook2::where('_id', new ObjectId($bookId))->first();
            $bookSqlId != null ? $bookSqlId = $bookSqlId->xsqlid : $bookSqlId = null ;
        }else{
            $bookSqlId = null;
        }

        $pipeline = [
            // Match documents based on xid or xparent
            [
                '$match' => [
                    '$or' => [
                        ['_id' => new ObjectId($bookId)],
                        ['xparent' => $bookSqlId]
                    ]
                ]
            ],
            // Unwind the nested publishers array
            [
                '$unwind' => '$publisher'
            ],
            // Group by publisher ID and calculate the sum of xpagecount
            [
                '$group' => [
                    '_id' => '$publisher.xpublisher_id',
                    'name' => ['$first' => '$publisher.xpublishername'],
                    'printCount' => ['$sum' => '$xpagecount']
                ]
            ],
            // Project the desired output
            [
                '$project' => [
                    '_id' => 0,
                    'name' => 1,
                    'printCount' => 1
                ]
            ]
        ];

        $books = BookIrBook2::raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        if ($books != null and count($books) > 0) {
            $totalPrintCount = 0;
            foreach ($books as $book) {
                $totalPrintCount += $book->printCount;
            }

            foreach ($books as $book) {
                $publisherName = $book->name;
                $percentPrintCount = ($book->printCount > 0 and $totalPrintCount > 0) ? round(($book->printCount / $totalPrintCount) * 100, 2) : 0;

                $publisherPrintCountData[] = ["name" => $publisherName, "percentPrintCount" => $percentPrintCount];
            }

            $publisherPrintCountData = ["label" => array_column($publisherPrintCountData, 'name'), "value" => array_column($publisherPrintCountData, 'percentPrintCount')];
        }

        // response
        return response()->json(
            [
                "status" => $status,
                "message" => "ok",
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
        $status = 200;

        $book = BookIrBook2::where('_id',new ObjectId($bookId))->first();

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
                    "lang" => $book->languages,
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
        //TODO : must change after implement book dossier collection
        $books = BookIrBook2::where('_id',new ObjectId($bookId))->orwhere('xparent', $book != null ? $book->xsqlid : '')->get();
        if ($books != null and count($books) > 0) {
            foreach ($books as $value) {
                $year =$value->xpublishdate_shamsi;
                $printCount = $value->xcirculation;

                $yearPrintCountData[$year] = ["year" => $year, "printCount" => (isset($yearPrintCountData[$year])) ? $printCount + $yearPrintCountData[$year]["printCount"] : $printCount];
            }
            $yearPrintCountData = ["label" => array_column($yearPrintCountData, 'year'), "value" => array_column($yearPrintCountData, 'printCount')];
        }


        // read books for publisher PrintCount
        //TODO : must change after implement book dossier collection
        if ($bookId != null) {
            $bookSqlId = BookIrBook2::where('_id', new ObjectId($bookId))->first();
            $bookSqlId != null ? $bookSqlId = $bookSqlId->xsqlid : $bookSqlId = null ;
        }else{
            $bookSqlId = null;
        }

        $pipeline = [
            // Match documents based on xid or xparent
            [
                '$match' => [
                    '$or' => [
                        ['_id' => new ObjectId($bookId)],
                        ['xparent' => $bookSqlId]
                    ]
                ]
            ],
            // Unwind the nested publishers array
            [
                '$unwind' => '$publisher'
            ],
            // Group by publisher ID and calculate the sum of xpagecount
            [
                '$group' => [
                    '_id' => '$publisher.xpublisher_id',
                    'name' => ['$first' => '$publisher.xpublishername'],
                    'printCount' => ['$sum' => '$xpagecount']
                ]
            ],
            // Project the desired output
            [
                '$project' => [
                    '_id' => 0,
                    'name' => 1,
                    'printCount' => 1
                ]
            ]
        ];

        $books = BookIrBook2::raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        if ($books != null and count($books) > 0) {
            $totalPrintCount = 0;
            foreach ($books as $book) {
                $totalPrintCount += $book->printCount;
            }

            foreach ($books as $book) {
                $publisherName = $book->name;
                $percentPrintCount = ($book->printCount > 0 and $totalPrintCount > 0) ? round(($book->printCount / $totalPrintCount) * 100, 2) : 0;

                $publisherPrintCountData[] = ["name" => $publisherName, "percentPrintCount" => $percentPrintCount];
            }

            $publisherPrintCountData = ["label" => array_column($publisherPrintCountData, 'name'), "value" => array_column($publisherPrintCountData, 'percentPrintCount')];
        }
        // response
        return response()->json(
            [
                "status" => $status,
                "message" =>"ok",
                "data" => ["master" => $dataMaster, "yearPrintCount" => $yearPrintCountData, "publisherPrintCount" => $publisherPrintCountData]
            ],
            $status
        );
    }


    ///////////////////////////////////////////////Dio///////////////////////////////////////////////////
    public function searchDio(Request $request)
    {
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 200;

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
            $data = array_values($data);
        }

        // response
        return response()->json(
            [
                "status" => $status,
                "message" => "ok",
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

        if ($publisherId != "" and $creatorId != "") {
            $publisherBooks = BookIrBook2::where('partners.xcreator_id', $creatorId)->where('publisher.xpublisher_id', $publisherId)->get();
            if (BookIrPublisher::where('_id',new ObjectId($publisherId))->count() > 0)
                $publisherName = BookIrPublisher::where('_id', new ObjectId($publisherId))->first()->xpublishername;
            if ($creatorId != 0 and BookIrCreator::where('_id',new ObjectId( $creatorId))->count() > 0) {
                $creatorName = BookIrCreator::where('_id', new ObjectId($creatorId))->first()->xcreatorname;
            }
        } elseif ($publisherId != "" or $creatorId != "") {
            if ($publisherId == 0) {
                $publisherBooks = BookIrBook2::where('partners.xcreator_id', $creatorId)->get();
            } else {
                $publisherBooks = BookIrBook2::where('publisher.xpublisher_id', $publisherId)->get();
                if (BookIrPublisher::where('_id', new ObjectId($publisherId))->count() > 0)
                    $publisherName = BookIrPublisher::where('_id', new ObjectId($publisherId))->first()->xpublishername;
            }
            if ($creatorId != 0 and BookIrCreator::where('_id', new ObjectId($creatorId))->count() > 0) {
                $creatorName = BookIrCreator::where('_id', new ObjectId($creatorId))->first()->xcreatorname;
            }
        }

                if ($publisherName != '' or $creatorName != '') {
                    foreach ($publisherBooks as $publisherBook) {
                        $where [] = ['_id', new ObjectId($publisherBook->_id)];
                    }
                }


        return $this->lists($request, false, ($where == []), $where, "", $publisherName, $creatorName);
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
        if (count($books) > 0) {
            foreach ($books as $book) {
                foreach ($book->partners as $partner) {
                    if ($partner['xcreator_id'] == $teammateId) {

                        $where [] = ['_id', new ObjectId($book->_id)];

                        $teammateName = $partner['xcreatorname'];
                        break;
                    }
                }
            }
        }

        if (BookIrCreator::where('_id', new ObjectId($creatorId))->count() > 0){
            $creatorName = [BookIrCreator::where('_id', new ObjectId($creatorId))->pluck('xcreatorname')[0] , $teammateName];
        }

        return $this->lists($request, false, ($where == []), $where, "", "", $creatorName);
    }


    ///////////////////////////////////////////////AdvanceSearch///////////////////////////////////////////////////
    public function advanceSearch(Request $request)
    {
        $where = [];

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
                    if ($comparisonOperators == 'Like') {
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

