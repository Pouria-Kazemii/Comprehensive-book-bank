<?php

namespace App\Http\Controllers\Api;

use App\Console\Commands\GetIranketab;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ExcelController;
use App\Models\Author;
use App\Models\AuthorBook30book;
use App\Models\AuthorBookdigi;
use App\Models\AuthorBookgisoom;
use App\Models\BiBookBiPublisher;
use App\Models\BiBookBiSubject;
use App\Models\Book30book;
use App\Models\BookDigi;
use App\Models\BookGisoom;
use App\Models\BookIranketab;
use App\Models\BookirBook;
use App\Models\BookirPartner;
use App\Models\BookirPartnerrule;
use App\Models\BookirPublisher;
use App\Models\BookirRules;
use App\Models\BookirSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;
use Exception;

use Tymon\JWTAuth\Facades\JWTAuth;

class BookController extends Controller
{
    // find
    public function find(Request $request)
    {
        return $this->lists($request);
    }

    // find by publisher
    public function exportExcelBookFindByPublisher(Request $request)
    {
        $where = $this->findByPublisherSelect($request);
        $result = $this->exportLists($request, true, ($where == ""), $where);
        $mainResult = $result->getData();
        if ($mainResult->status == 200) {
            $publisherInfo = BookirPublisher::where('xid', $request["publisherId"])->first();
            $response = ExcelController::booklist($mainResult, 'کتب ناشر' . time(), mb_substr($publisherInfo->xpublishername, 0, 30, 'UTF-8'));
            return response()->json($response);
        } else {
            return $mainResult->status;
        }
    }
    public function findByPublisher(Request $request)
    {
        $where = $this->findByPublisherSelect($request);
        return $this->lists($request, true, ($where == ""), $where);
    }
    public function findByPublisherSelect(Request $request)
    {
        $publisherId = $request["publisherId"];
        $bookId = $request["bookId"];
        $where = "";

        if ($publisherId > 0) {
            $where = "xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')";
        } elseif ($bookId > 0) {
            // get publisher
            $publishers = BiBookBiPublisher::where('bi_book_xid', '=', $bookId)->get();
            if ($publishers != null and count($publishers) > 0) {
                foreach ($publishers as $publisher) {
                    $where = "bi_publisher_xid='" . $publisher->bi_publisher_xid . "' or ";
                }

                $where = "xid In (Select bi_book_xid From bi_book_bi_publisher Where " . rtrim($where, " or ") . ")";
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
            $creatorInfo = BookirPartner::where('xid', $request["creatorId"])->first();
            $response = ExcelController::booklist($mainResult, 'کتب پدیدآورنده' . time(), mb_substr($creatorInfo->xcreatorname, 0, 30, 'UTF-8'));
            return response()->json($response);
        } else {
            return $mainResult->status;
        }
    }

    // find by creator
    public function findByCreator(Request $request)
    {
        $where = $this->findByCreatorSelect($request);
        return $this->lists($request, true, ($where == ""), $where);
    }
    public function findByCreatorSelect($request)
    {
        $creatorId = $request["creatorId"];
        $bookId = $request["bookId"];
        $where = "";

        if ($creatorId > 0) {
            $where = "xid In (Select xbookid From bookir_partnerrule Where xcreatorid='$creatorId')";
        } elseif ($bookId > 0) {
            // get creator
            $creators = BookirPartnerrule::where('xbookid', '=', $bookId)->get();
            if ($creators != null and count($creators) > 0) {
                foreach ($creators as $creator) {
                    $where = "xcreatorid='" . $creator->xcreatorid . "' or ";
                }

                $where = "xid In (Select xbookid From bookir_partnerrule Where " . rtrim($where, " or ") . ")";
            }
        }
        return $where;
    }


    // find by ver
    public function findByVer(Request $request)
    {
        $bookId = $request["bookId"];
        // $bookId = 13349;
        $where = "xid='$bookId' or xparent='$bookId'";

        return $this->listsWithOutGroupby($request, true, ($where == ""), $where);
    }

    // find by subject
    public function findBySubject(Request $request)
    {
        $subjectId = $request["subjectId"];
        $subjectTitle = "";

        $subject = BookirSubject::where('xid', '=', $subjectId)->first();
        if ($subject != null and $subject->xid > 0) {
            $subjectTitle = $subject->xsubject;
        }

        $where = $subjectId != "" ? "xid In (Select bi_book_xid From bi_book_bi_subject Where bi_subject_xid='$subjectId')" : "";

        return $this->lists($request, true, ($where == ""), $where, $subjectTitle);
    }

    public function findByCreatorOfPublisher(Request $request)
    {
        $publisherId = $request["publisherId"];
        $creatorId = $request["creatorId"];
        if ($publisherId == 0) {
            $publisher_books = BookirPartnerrule::where('xcreatorid', $creatorId)->get();
            $publisherName = '';
        } else {
            $publisher_books = BookirPartnerrule::whereIn('xbookid', function ($query) use ($publisherId) {
                $query->select('bi_book_xid')->from('bi_book_bi_publisher')->where('bi_publisher_xid', $publisherId);
            })->get();
            $publisherName =  BookirPublisher::where('xid', $publisherId)->first()->xpublishername;
        }
        if ($creatorId == 0) {
            $creatorName = "";
        } else {
            $creatorName = BookirPartner::where('xid', $creatorId)->first()->xcreatorname;
        }


        if ($publisher_books->count() > 0) {
            foreach ($publisher_books as $key => $item) {
                $collection_data[$key]["id"] = $item->xid;
                $collection_data[$key]["xbookid"] = $item->xbookid;
                $collection_data[$key]["xcreatorid"] = $item->xcreatorid;
                $collection_data[$key]["xroleid"] = $item->xroleid;
            }
            $collection = collect($collection_data);
            // dd($collection);

            $creator_books = $collection->filter(function ($item) use ($creatorId) {
                return data_get($item, 'xcreatorid') == $creatorId;
            });
            $creator_books_array = $creator_books->pluck('xbookid')->all();
            $creator_books_array =  array_unique($creator_books_array);
            $creator_books_string = implode(",", $creator_books_array);
            $where = ($publisherId != "" and $creatorId != "") ? "xid In ($creator_books_string)" : "";

            return $this->lists($request, true, ($where == ""), $where, "", $publisherName, $creatorName);
        }
    }

    public function findBySharedCreators(Request $request)
    { //co-creators
        $creatorId = $request["creatorId"];
        $teammateId = $request["teammateId"];
        $creators = BookirPartner::where('xid', $creatorId)->orwhere('xid', $teammateId)->get();
        $creatorName = $creators->pluck('xcreatorname')->all();
        $shared_creator_books = BookirPartnerrule::select('xbookid')->where('xcreatorid', $creatorId)->whereIn('xbookid', function ($query) use ($teammateId) {
            $query->select('xbookid')->from('bookir_partnerrule')->where('xcreatorid', $teammateId);
        })->get();

        $shared_creator_books_array = $shared_creator_books->pluck('xbookid')->all();
        $shared_creator_books_array =  array_unique($shared_creator_books_array);
        $shared_creator_books_string = implode(",", $shared_creator_books_array);
        $where = ($teammateId != "" and $creatorId != "") ? "xid In ($shared_creator_books_string)" : "";
        return $this->lists($request, true, ($where == ""), $where, "", "", $creatorName);
    }

    //advanced search
    public function advanceSearch(Request $request)
    {
        $where = '';
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

                // serach bby name  
                if (($searchField == 'name') and !empty($comparisonOperators) and !empty($searchValue)) {  // $books->where('xname', 'like', "%$name%");
                    if (!empty($beforeLogicalOperator) or $possibilityEmptyLogicalOperator) {
                        $where .= ' ' . $beforeLogicalOperator . ' ';
                        if ($comparisonOperators == 'like') {
                            $where .= " xname like '%" . $searchValue . "%'";
                        } else {
                            $where .= " xname " . $comparisonOperators . " '" . $searchValue . "'";
                        }
                    }
                }
                // search by isbn
                if (($searchField == 'isbn2') and !empty($comparisonOperators) and !empty($searchValue)) { // $books->where('xisbn2', '=', $isbn);
                    if (!empty($beforeLogicalOperator) or $possibilityEmptyLogicalOperator) {
                        $where .= ' ' . $beforeLogicalOperator . ' ';
                        if ($comparisonOperators == 'like') {
                            $where .= " xisbn2 like '%" . $searchValue . "%'";
                        } else {
                            $where .= " xisbn2 " . $comparisonOperators . " '" . $searchValue . "'";
                        }
                    }
                }
                // search by doi
                if (($searchField == 'doi') and !empty($comparisonOperators) and !empty($searchValue)) { // $books->where('xdiocode', '=', $isbn);
                    if (!empty($beforeLogicalOperator) or $possibilityEmptyLogicalOperator) {
                        $where .= ' ' . $beforeLogicalOperator . ' ';
                        if ($comparisonOperators == 'like') {
                            $where .= " xdiocode like '%" . $searchValue . "%'";
                        } else {
                            $where .= " xdiocode " . $comparisonOperators . " '" . $searchValue . "'";
                        }
                    }
                }
                // serach by publish date
                if (($searchField == 'publishDate') and !empty($comparisonOperators) and !empty($searchValue)) { // $books->where('xpublishdate', '=', $isbn);
                    $searchValue =  Bookirbook::toGregorian($searchValue, '/', '-');
                    if (!empty($beforeLogicalOperator) or $possibilityEmptyLogicalOperator) {
                        $where .= ' ' . $beforeLogicalOperator . ' ';
                        if ($comparisonOperators == 'like') {
                            $where .= " xpublishdate like '%" . $searchValue . "%'";
                        } else {
                            $where .= " xpublishdate " . $comparisonOperators . " '" . $searchValue . "'";
                        }
                    }
                }
                // serach by price
                if (($searchField == 'price') and !empty($comparisonOperators) and !empty($searchValue)) { // $books->where('xcoverprice', '=', $isbn);
                    if (!empty($beforeLogicalOperator) or $possibilityEmptyLogicalOperator) {
                        $where .= ' ' . $beforeLogicalOperator . ' ';
                        if ($comparisonOperators == 'like') {
                            $where .= " xcoverprice like '%" . $searchValue . "%'";
                        } else {
                            $where .= " xcoverprice " . $comparisonOperators .  $searchValue;
                        }
                    }
                }
                // search by xcirculation
                if (($searchField == 'circulation') and !empty($comparisonOperators) and !empty($searchValue)) { // $books->where('xcirculation', '=', $isbn);
                    if (!empty($beforeLogicalOperator) or $possibilityEmptyLogicalOperator) {
                        $where .= ' ' . $beforeLogicalOperator . ' ';
                    }
                    if ($comparisonOperators == 'like') {
                        $where .= " xcirculation like '%" . $searchValue . "%'";
                    } else {
                        $where .= " xcirculation " . $comparisonOperators . $searchValue;
                    }
                }

                //search by publisher
                if (($searchField == 'publisher') and !empty($comparisonOperators) and !empty($searchValue)) {
                    if (!empty($beforeLogicalOperator) or $possibilityEmptyLogicalOperator) {
                        $where .= ' ' . $beforeLogicalOperator . ' ';
                    }
                    if ($comparisonOperators == 'like') {
                        $publishersId = BookirPublisher::where('xpublishername', $comparisonOperators, "%" . $searchValue . "%")->get()->pluck('xid')->all();
                    } else {
                        $publishersId = BookirPublisher::where('xpublishername', $comparisonOperators, $searchValue)->get()->pluck('xid')->all();
                    }
                    $publishersIdStr = implode(',', $publishersId);
                    $where .= "xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid IN ($publishersIdStr))";
                }

                //search by creator
                if (($searchField == 'creator') and !empty($comparisonOperators) and !empty($searchValue)) {
                    if (!empty($beforeLogicalOperator) or $possibilityEmptyLogicalOperator) {
                        $where .= ' ' . $beforeLogicalOperator . ' ';
                        if ($comparisonOperators == 'like') {
                            $creatorsId = BookirPartner::where('xcreatorname', $comparisonOperators, "%" . $searchValue . "%")->get()->pluck('xid')->all();
                        } else {
                            $creatorsId = BookirPartner::where('xcreatorname', $comparisonOperators, $searchValue)->get()->pluck('xid')->all();
                        }
                        $creatorsIdStr = implode(',', $creatorsId);
                        $where .= "xid In (Select xbookid From bookir_partnerrule Where xcreatorid IN ($creatorsIdStr))";
                    }
                }

                // search by subject
                if (($searchField == 'subject') and !empty($comparisonOperators) and !empty($searchValue)) {
                    if (!empty($beforeLogicalOperator) or $possibilityEmptyLogicalOperator) {
                        $where .= ' ' . $beforeLogicalOperator . ' ';
                        if ($comparisonOperators == 'like') {
                            $subjectsId = BookirSubject::where('xsubject', $comparisonOperators, "%" . $searchValue . "%")->get()->pluck('xid')->all();
                        } else {
                            $subjectsId = BookirSubject::where('xsubject', $comparisonOperators, $searchValue)->get()->pluck('xid')->all();
                        }
                        $subjectsIdStr = implode(',', $subjectsId);
                        $where .= "xid In (Select bi_book_xid From bi_book_bi_subject Where bi_subject_xid IN ($subjectsIdStr))";
                    }
                }

                if (isset($search_item['logicalOperator'])) {
                    $beforeLogicalOperator = $search_item['logicalOperator'];
                } else {
                    $beforeLogicalOperator = '';
                    $possibilityEmptyLogicalOperator = false;
                }
            }
        }


        return $this->lists($request, false, false, $where);
    }
    // list
    public function lists(Request $request, $defaultWhere = true, $isNull = false, $where = "", $subjectTitle = "", $publisherName = "", $creatorName = "")
    {
        $isbn = (isset($request["isbn"])) ? str_replace("-", "", $request["isbn"]) : "";
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "xpublishdate";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;
        // DB::enableQueryLog();
        if (!$isNull) {
            // read books
            $books = BookirBook::orderBy($column, $sortDirection);
            // $books = BookirBook::with('publishers:xid as id,xpublishername as name')->orderBy($column, $sortDirection);
            // if ($defaultWhere) $books->whereRaw("(xparent='-1' or xparent='0')"); //$books->where('xparent', '=', '-1');//->orwhere('xparent', '=', '0');
            if ($searchText != "") $books->where('xname', 'like', "%$searchText%");
            if ($isbn != "") $books->where('xisbn2', '=', $isbn);
            if ($where != "") $books->whereRaw($where);
            $books->groupBy('xparent')->orderBy('xparent');
             // give count ///////////////////
            $countBooks = $books->get();
            $totalRows =  count($countBooks);
            /////give result //////////////////
            $books = $books->skip($offset)->take($pageRows)->get();
            // $data = $books;
            if ($books != null and count($books) > 0) {
                foreach ($books as $book) {
                    if ($book->xparent == -1 or  $book->xparent == 0) {
                        $dossier_id = $book->xid;
                    } else {
                        $dossier_id = $book->xparent;
                    }
                    //publishers
                    $publishers = null;
                    $bookPublishers = DB::table('bi_book_bi_publisher')
                        ->where('bi_book_xid', '=', $book->xid)
                        ->join('bookir_publisher', 'bi_book_bi_publisher.bi_publisher_xid', '=', 'bookir_publisher.xid')
                        ->select('bookir_publisher.xid as id', 'bookir_publisher.xpublishername as name')
                        ->get();
                    if ($bookPublishers != null and count($bookPublishers) > 0) {
                        foreach ($bookPublishers as $bookPublisher) {
                            $publishers[] = ["id" => $bookPublisher->id, "name" => $bookPublisher->name];
                        }
                    }
                    
                    $data[] =
                        [
                            "id" => $book->xid,
                            "dossier_id" => $dossier_id,
                            "name" => $book->xname,
                            "publishers" => $publishers,
                            // "publishers" => $book->publishers,
                            "language" => $book->xlang,
                            "year" => BookirBook::getShamsiYearMonth($book->xpublishdate),
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

            //
            // $books = BookirBook::orderBy($column, $sortDirection);
            // // if ($defaultWhere) $books->whereRaw("(xparent='-1' or xparent='0')");
            // if ($searchText != "") $books->where('xname', 'like', "%$searchText%");
            // if ($isbn != "") $books->where('xisbn', '=', $isbn);
            // if ($where != "") $books->whereRaw($where);
            // $books->groupBy('xparent');
            // $countBooks = $books->get();
            // $totalRows =  count($countBooks);
            $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;
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

    public function listsWithOutGroupby(Request $request, $defaultWhere = true, $isNull = false, $where = "", $subjectTitle = "", $publisherName = "", $creatorName = "")
    {
        $isbn = (isset($request["isbn"])) ? str_replace("-", "", $request["isbn"]) : "";
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "xpublishdate";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        if (!$isNull) {
            // read books
            $books = BookirBook::orderBy($column, $sortDirection);
            // if ($defaultWhere) $books->whereRaw("(xparent='-1' or xparent='0')"); //$books->where('xparent', '=', '-1');//->orwhere('xparent', '=', '0');
            if ($searchText != "") $books->where('xname', 'like', "%$searchText%");
            if ($isbn != "") $books->where('xisbn2', '=', $isbn);
            if ($where != "") $books->whereRaw($where);
            $books = $books->skip($offset)->take($pageRows)->get();
            if ($books != null and count($books) > 0) {
                foreach ($books as $book) {
                    if ($book->xparent == -1 or  $book->xparent == 0) {
                        $dossier_id = $book->xid;
                    } else {
                        $dossier_id = $book->xparent;
                    }
                    $publishers = null;

                    $bookPublishers = DB::table('bi_book_bi_publisher')
                        ->where('bi_book_xid', '=', $book->xid)
                        ->join('bookir_publisher', 'bi_book_bi_publisher.bi_publisher_xid', '=', 'bookir_publisher.xid')
                        ->select('bookir_publisher.xid as id', 'bookir_publisher.xpublishername as name')
                        ->get();
                    if ($bookPublishers != null and count($bookPublishers) > 0) {
                        foreach ($bookPublishers as $bookPublisher) {
                            $publishers[] = ["id" => $bookPublisher->id, "name" => $bookPublisher->name];
                        }
                    }

                    //
                    $data[] =
                        [
                            "id" => $book->xid,
                            "dossier_id" => $dossier_id,
                            "name" => $book->xname,
                            "publishers" => $publishers,
                            "language" => $book->xlang,
                            "year" => BookirBook::getShamsiYearMonth($book->xpublishdate),
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

            //
            $books = BookirBook::orderBy($column, $sortDirection);
            // if ($defaultWhere) $books->whereRaw("(xparent='-1' or xparent='0')");
            if ($searchText != "") $books->where('xname', 'like', "%$searchText%");
            if ($isbn != "") $books->where('xisbn', '=', $isbn);
            if ($where != "") $books->whereRaw($where);
            // $books->groupBy('xisbn');
            $totalRows = $books->count();
            $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;
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
    public function exportLists(Request $request, $defaultWhere = true, $isNull = false, $where = "", $subjectTitle = "", $publisherName = "", $creatorName = "")
    {
        $name = (isset($request["name"])) ? $request["name"] : "";
        $isbn = (isset($request["isbn"])) ? str_replace("-", "", $request["isbn"]) : "";
        $yearStart = (isset($request["yearStart"]) && $request["yearStart"] != 0) ?  BookirBook::toGregorian($request["yearStart"] . '-01-01', '-', '-') : "";
        $yearEnd = (isset($request["yearEnd"]) && $request["yearEnd"] != 0) ? BookirBook::toGregorian($request["yearEnd"] . '-12-29', '-', '-') : "";
        $data = null;
        $status = 404;

        // DB::enableQueryLog();
        if (!$isNull) {
            // read books
            $books = BookirBook::orderBy('xpublishdate', 'desc');
            // if ($defaultWhere) $books->whereRaw("(xparent='-1' or xparent='0')"); //$books->where('xparent', '=', '-1');//->orwhere('xparent', '=', '0');
            if ($name != "") $books->where('xname', 'like', "%$name%");
            if ($isbn != "") $books->where('xisbn2', '=', $isbn);
            if ($where != "") $books->whereRaw($where);
            if ($yearStart != "") $books->where('xpublishdate', '>=', $yearStart);
            if ($yearEnd != "") $books->where('xpublishdate', '<=', $yearEnd);
            $books->orderBy('xisbn');
            $books = $books->get();

            if ($books != null and count($books) > 0) {
                foreach ($books as $book) {
                    if ($book->xparent == -1 or  $book->xparent == 0) {
                        $dossier_id = $book->xid;
                    } else {
                        $dossier_id = $book->xparent;
                    }
                    //publishers
                    $publishers = null;
                    $publisherIds = BiBookBiPublisher::where('bi_book_xid', $book->xid)->get();
                    $bookPublishers =  BookirPublisher::whereIn('xid', $publisherIds->pluck('bi_publisher_xid')->all())->get();
                    if ($bookPublishers != null and count($bookPublishers) > 0) {
                        foreach ($bookPublishers as $bookPublisher) {
                            $publishers[] = ["id" => $bookPublisher->xid, "name" => $bookPublisher->xpublishername];
                        }
                    }
                    //subjects
                    $subjects = null;
                    $subjectIds = BiBookBiSubject::where('bi_book_xid', $book->xid)->get();
                    $bookSubjects = BookirSubject::where('xid', $subjectIds->pluck('bi_subject_xid')->all())->get();
                    if ($bookSubjects != null and count($bookSubjects) > 0) {
                        foreach ($bookSubjects as $bookSubject) {
                            $subjects[] = ["id" => $bookSubject->xid, "name" => $bookSubject->xsubject];
                        }
                    }

                    //authors
                    $authors = null;
                    $authorIds = BookirPartnerrule::where('xbookid', $book->xid)->where('xroleid', 1)->get(); // writer
                    $bookAuthors = BookirPartner::where('xid', $authorIds->where('xbookid', $book->xid)->pluck('xcreatorid')->all())->get();
                    if ($bookAuthors != null and count($bookAuthors) > 0) {
                        foreach ($bookAuthors as $bookAuthor) {
                            $authors[] = ["id" => $bookAuthor->xid, "name" => $bookAuthor->xcreatorname];
                        }
                    }

                    //translator
                    $translators = null;
                    $translatorIds = BookirPartnerrule::where('xbookid', $book->xid)->where('xroleid', 2)->get();
                    $bookTranslators = BookirPartner::where('xid', $translatorIds->where('xbookid', $book->xid)->pluck('xcreatorid')->all())->get();
                    if ($bookTranslators != null and count($bookTranslators) > 0) {
                        foreach ($bookTranslators as $bookTranslator) {
                            $translators[] = ["id" => $bookTranslator->xid, "name" => $bookTranslator->xcreatorname];
                        }
                    }

                    //imager
                    $imagers = null;
                    $imagerIds = BookirPartnerrule::where('xbookid', $book->xid)->where('xroleid', 20)->get();
                    $bookImagers = BookirPartner::where('xid', $imagerIds->pluck('xcreatorid')->all())->get();
                    if ($bookImagers != null and count($bookImagers) > 0) {
                        foreach ($bookImagers as $bookImager) {
                            $imagers[] = ["id" => $bookImager->xid, "name" => $bookImager->xcreatorname];
                        }
                    }

                    //
                    $data[] =
                        [
                            "id" => $book->xid,
                            "dossier_id" => $dossier_id,
                            "name" => $book->xname,
                            "publishers" => $publishers,
                            "language" => $book->xlang,
                            "year" => BookirBook::getShamsiYear($book->xpublishdate),
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

    // detail book
    public function detail(Request $request)
    {
        $bookId = $request["bookId"];
        $dataMaster = null;
        $yearPrintCountData = null;
        $publisherPrintCountData = null;
        $status = 404;

        // read books
        $book = BookirBook::where('xid', '=', $bookId)/*->where('xparent', '=', '-1')*/->first();
        if ($book != null and $book->xid > 0) {
            $publishersData = null;
            $subjectsData = null;
            $creatorsData = null;

            $bookPublishers = DB::table('bi_book_bi_publisher')
                ->where('bi_book_xid', '=', $book->xid)
                ->join('bookir_publisher', 'bi_book_bi_publisher.bi_publisher_xid', '=', 'bookir_publisher.xid')
                ->select('bookir_publisher.xid as id', 'bookir_publisher.xpublishername as name')
                ->get();
            if ($bookPublishers != null and count($bookPublishers) > 0) {
                foreach ($bookPublishers as $bookPublisher) {
                    $publishersData[] = ["id" => $bookPublisher->id, "name" => $bookPublisher->name];
                }
            }

            $bookSubjects = DB::table('bi_book_bi_subject')
                ->where('bi_book_xid', '=', $book->xid)
                ->join('bookir_subject', 'bi_book_bi_subject.bi_subject_xid', '=', 'bookir_subject.xid')
                ->select('bookir_subject.xid as id', 'bookir_subject.xsubject as title')
                ->get();
            if ($bookSubjects != null and count($bookSubjects) > 0) {
                foreach ($bookSubjects as $subject) {
                    $subjectsData[] = ["id" => $subject->id, "title" => $subject->title];
                }
            }

            $bookPartnerRules = DB::table('bookir_partnerrule')
                ->where('xbookid', '=', $book->xid)
                ->join('bookir_partner', 'bookir_partnerrule.xcreatorid', '=', 'bookir_partner.xid')
                ->join('bookir_rules', 'bookir_partnerrule.xroleid', '=', 'bookir_rules.xid')
                ->select('bookir_partner.xid as id', 'bookir_partner.xcreatorname as name', 'bookir_rules.xrole as role')
                ->get();
            if ($bookPartnerRules != null and count($bookPartnerRules) > 0) {
                foreach ($bookPartnerRules as $partner) {
                    $creatorsData[] = ["id" => $partner->id, "name" => $partner->name, "role" => $partner->role];
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
                    "publishDate" => BookirBook::convertMiladi2Shamsi($book->xpublishdate),
                    "printNumber" => $book->xprintnumber,
                    "circulation" => $book->xcirculation,
                    "price" => priceFormat($book->xcoverprice),
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

        // read books for publisher PrintCount
        $books = DB::table('bookir_book')
            ->where('bookir_book.xid', '=', $bookId)->orwhere('bookir_book.xparent', '=', $bookId)
            ->join('bi_book_bi_publisher', 'bi_book_bi_publisher.bi_book_xid', '=', 'bookir_book.xid')
            ->join('bookir_publisher', 'bookir_publisher.xid', '=', 'bi_book_bi_publisher.bi_publisher_xid')
            ->select('bookir_publisher.xpublishername as name', DB::raw('SUM(bookir_book.xpagecount) as printCount'))
            ->groupBy('bookir_publisher.xid')
            ->get();
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

    public function info($bookId)
    {
        $dataMaster = null;
        $yearPrintCountData = null;
        $publisherPrintCountData = null;
        $status = 404;

        // read books
        $book = BookirBook::where('xid', '=', $bookId)/*->where('xparent', '=', '-1')*/->first();
        if ($book != null and $book->xid > 0) {
            $publishersData = null;
            $subjectsData = null;
            $creatorsData = null;

            $bookPublishers = DB::table('bi_book_bi_publisher')
                ->where('bi_book_xid', '=', $book->xid)
                ->join('bookir_publisher', 'bi_book_bi_publisher.bi_publisher_xid', '=', 'bookir_publisher.xid')
                ->select('bookir_publisher.xid as id', 'bookir_publisher.xpublishername as name')
                ->get();
            if ($bookPublishers != null and count($bookPublishers) > 0) {
                foreach ($bookPublishers as $bookPublisher) {
                    $publishersData[] = ["id" => $bookPublisher->id, "name" => $bookPublisher->name];
                }
            }

            $bookSubjects = DB::table('bi_book_bi_subject')
                ->where('bi_book_xid', '=', $book->xid)
                ->join('bookir_subject', 'bi_book_bi_subject.bi_subject_xid', '=', 'bookir_subject.xid')
                ->select('bookir_subject.xid as id', 'bookir_subject.xsubject as title')
                ->get();
            if ($bookSubjects != null and count($bookSubjects) > 0) {
                foreach ($bookSubjects as $subject) {
                    $subjectsData[] = ["id" => $subject->id, "title" => $subject->title];
                }
            }

            $bookPartnerRules = DB::table('bookir_partnerrule')
                ->where('xbookid', '=', $book->xid)
                ->join('bookir_partner', 'bookir_partnerrule.xcreatorid', '=', 'bookir_partner.xid')
                ->join('bookir_rules', 'bookir_partnerrule.xroleid', '=', 'bookir_rules.xid')
                ->select('bookir_partner.xid as id', 'bookir_partner.xcreatorname as name', 'bookir_rules.xid as role_id', 'bookir_rules.xrole as role')
                ->get();
            if ($bookPartnerRules != null and count($bookPartnerRules) > 0) {
                foreach ($bookPartnerRules as $partner) {
                    $creatorsData[] = ["id" => $partner->id, "name" => $partner->name, "role_id" => $partner->role_id,  "role" => $partner->role];
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
                    "publishDate" => BookirBook::convertMiladi2Shamsi($book->xpublishdate),
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

        // read books for publisher PrintCount
        $books = DB::table('bookir_book')
            ->where('bookir_book.xid', '=', $bookId)->orwhere('bookir_book.xparent', '=', $bookId)
            ->join('bi_book_bi_publisher', 'bi_book_bi_publisher.bi_book_xid', '=', 'bookir_book.xid')
            ->join('bookir_publisher', 'bookir_publisher.xid', '=', 'bi_book_bi_publisher.bi_publisher_xid')
            ->select('bookir_publisher.xpublishername as name', DB::raw('SUM(bookir_book.xpagecount) as printCount'))
            ->groupBy('bookir_publisher.xid')
            ->get();
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
    // dossier book
    public function dossier(Request $request)
    {

        $bookId = $request["bookId"];
        $dataMaster = null;
        $yearPrintCountData = null;
        $publisherPrintCountData = null;
        $status = 404;

        // read books
        $book = BookirBook::where('xid', '=', $bookId)/*->where('xparent', '=', '-1')*/->first();
        if (!empty($book)) {
            if ($book->xparent != -1 and $book->xparent != 0) { // found leader
                $book = BookirBook::where('xid', '=', $book->xparent)->first();
                $bookId = $book->xid;
            }
            //SELECT clidren id 
            $dossier_book = BookirBook::where('xid', '=', $book->xid)->orwhere('xparent', '=', $book->xid)->get();
            $dossier_book_id = $dossier_book->pluck('xid')->all();
            if ($book != null and $book->xid > 0) {
                $publishersData = null;
                $subjectsData = null;
                $creatorsData = null;

                $bookPublishers = DB::table('bi_book_bi_publisher')
                    ->whereIn('bi_book_xid', $dossier_book_id)
                    ->join('bookir_publisher', 'bi_book_bi_publisher.bi_publisher_xid', '=', 'bookir_publisher.xid')
                    ->select('bookir_publisher.xid as id', 'bookir_publisher.xpublishername as name')
                    ->groupBy('id')
                    ->get();

                if ($bookPublishers != null and count($bookPublishers) > 0) {
                    foreach ($bookPublishers as $bookPublisher) {
                        $publishersData[] = ["id" => $bookPublisher->id, "name" => ' ' . $bookPublisher->name . ' '];
                    }
                }

                $bookSubjects = DB::table('bi_book_bi_subject')
                    ->whereIn('bi_book_xid', $dossier_book_id)
                    ->join('bookir_subject', 'bi_book_bi_subject.bi_subject_xid', '=', 'bookir_subject.xid')
                    ->select('bookir_subject.xid as id', 'bookir_subject.xsubject as title')
                    ->groupBy('id')
                    ->get();
                if ($bookSubjects != null and count($bookSubjects) > 0) {
                    foreach ($bookSubjects as $subject) {
                        $subjectsData[] = ["id" => $subject->id, "title" => $subject->title];
                    }
                }

                $bookPartnerRules = DB::table('bookir_partnerrule')
                    ->whereIn('xbookid', $dossier_book_id)
                    ->join('bookir_partner', 'bookir_partnerrule.xcreatorid', '=', 'bookir_partner.xid')
                    ->join('bookir_rules', 'bookir_partnerrule.xroleid', '=', 'bookir_rules.xid')
                    ->select('bookir_partner.xid as id', 'bookir_partner.xcreatorname as name', 'bookir_rules.xrole as role', 'bookir_rules.xid as role_id')
                    ->groupBy('id')
                    ->orderBy('role_id')
                    ->get();

                if ($bookPartnerRules != null and count($bookPartnerRules) > 0) {
                    foreach ($bookPartnerRules as $partner) {
                        $creatorsData[] = ["id" => $partner->id, "name" => $partner->name, "role" => $partner->role];
                    }
                }

                //
                // price 
                $coverPrice = BookirBook::where('xcoverprice', '>', 0);
                $coverPrice = $coverPrice->where(function ($query) use ($book) {
                    $query->where('xid', $book->xid)->orwhere('xparent', $book->xid);
                });
                $max_coverPrice = $coverPrice->max('xcoverprice');
                $min_coverPrice = $coverPrice->min('xcoverprice');


                //description
                $book_des = BookirBook::where('xdescription', '!=', '');
                $book_des = $book_des->where(function ($query) use ($book) {
                    $query->where('xid', $book->xid)->orwhere('xparent', $book->xid);
                });
                $book_description = $book_des->orderBy('xdescription', 'DESC')->first();

                //xcover
                $coversData = '';
                $book_cover = BookirBook::select('xcover')->where('xcover', '!=', '')->where('xcover', '!=', 'null');
                $book_cover = $book_cover->where(function ($query) use ($book) {
                    $query->where('xid', $book->xid)->orwhere('xparent', $book->xid);
                });
                $book_covers = $book_cover->groupBy('xcover')->get();

                if ($book_covers != null and count($book_covers) > 0) {
                    foreach ($book_covers as $cover) {
                        $coversData .= $cover->xcover . '-';
                    }
                    $coversData = rtrim($coversData, '-');
                }

                //format
                $formatsData = '';
                $book_format = BookirBook::select('xformat')->where('xformat', '!=', '')->where('xformat', '!=', 'null');
                $book_format = $book_format->where(function ($query) use ($book) {
                    $query->where('xid', $book->xid)->orwhere('xparent', $book->xid);
                });
                $book_formats = $book_format->groupBy('xformat')->get();

                if ($book_formats != null and count($book_formats) > 0) {
                    foreach ($book_formats as $format) {
                        $formatsData .= $format->xformat . '-';
                    }
                    $formatsData = rtrim($formatsData, '-');
                }

                //publish date
                $publish_date = BookirBook::where('xpublishdate', '!=', '')->where('xpublishdate', '!=', 'null');
                $publish_date = $publish_date->where(function ($query) use ($book) {
                    $query->where('xid', $book->xid)->orwhere('xparent', $book->xid);
                });
                $min_publish_date = $publish_date->min('xpublishdate');
                $max_publish_date = $publish_date->max('xpublishdate');

                //publish place 
                $publishPlaceData = '';
                $publish_place = BookirBook::where('xpublishplace', '!=', '')->where('xpublishplace', '!=', 'null');
                $publish_place = $publish_place->where(function ($query) use ($book) {
                    $query->where('xid', $book->xid)->orwhere('xparent', $book->xid);
                });
                $publish_places = $publish_place->groupBy('xpublishplace')->get();

                if ($publish_places != null and count($publish_places) > 0) {
                    foreach ($publish_places as $publish_place) {
                        $publishPlaceData .= $publish_place->xpublishplace . ' , ';
                    }
                    $publishPlaceData = rtrim($publishPlaceData, ' , ');
                }
                //printnumber
                $printNumber = BookirBook::where('xprintnumber', '!=', '')->where('xprintnumber', '!=', 'null');
                $printNumber = $printNumber->where(function ($query) use ($book) {
                    $query->where('xid', $book->xid)->orwhere('xparent', $book->xid);
                });
                $printNumber = $printNumber->max('xprintnumber');

                //circulation
                $circulation = BookirBook::where('xcirculation', '!=', '')->where('xcirculation', '!=', 'null');
                $circulation = $circulation->where(function ($query) use ($book) {
                    $query->where('xid', $book->xid)->orwhere('xparent', $book->xid);
                });
                $circulation = $circulation->sum('xcirculation');
                $dataMaster =
                    [
                        "isbn" => $book->xisbn,
                        "name" => $book->xname,
                        "dioCode" => $book->xdiocode,
                        "publishers" => $publishersData,
                        "subjects" => $subjectsData,
                        "creators" => $creatorsData,
                        "image" => $book->ximgeurl,
                        "publishPlace" => $publishPlaceData,
                        // "format" => $book->xformat,
                        "format" => $formatsData,
                        // "cover" => ($book->xcover != null and $book->xcover != "null") ? $book->xcover : "",
                        "cover" =>  $coversData,
                        "publishDate" => $min_publish_date > 0 && $max_publish_date > 0 ? ' بین ' . BookirBook::convertMiladi2Shamsi_with_slash($min_publish_date) . ' تا ' . BookirBook::convertMiladi2Shamsi_with_slash($max_publish_date) : null,
                        "printNumber" => $printNumber,
                        "circulation" => priceFormat($circulation),
                        "price" => $min_coverPrice > 0 &&  $max_coverPrice > 0 ? ' بین ' . priceFormat($min_coverPrice) . ' تا ' . priceFormat($max_coverPrice) . ' ریال ' : null,
                        "des" => !empty($book_description) ? $book_description->xdescription : null,
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

            // read books for publisher PrintCount
            $books = DB::table('bookir_book')
                ->where('bookir_book.xid', '=', $bookId)->orwhere('bookir_book.xparent', '=', $bookId)
                ->join('bi_book_bi_publisher', 'bi_book_bi_publisher.bi_book_xid', '=', 'bookir_book.xid')
                ->join('bookir_publisher', 'bookir_publisher.xid', '=', 'bi_book_bi_publisher.bi_publisher_xid')
                ->select('bookir_publisher.xpublishername as name', DB::raw('SUM(bookir_book.xpagecount) as printCount'))
                ->groupBy('bookir_publisher.xid')
                ->get();
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

            //
            if ($dataMaster != null) $status = 200;


            //----------------------------------------------book digi------------------------------------//
            $digi_books = BookDigi::where('book_master_id', $bookId)->get();
            if ($digi_books->count() > 0) {
                $digi_titleData = array_unique(array_filter($digi_books->pluck('title')->all()));
                $digi_publishersData = array_unique(array_filter($digi_books->pluck('nasher')->all()));
                $digi_creatorAuthorData =  Author::whereIn('id', AuthorBookdigi::whereIn('book_digi_id', $digi_books->pluck('id')->all())->pluck('author_id')->all())->pluck('d_name')->all();
                $creators_array = array();
                $exist_creators = array();
                foreach ($digi_creatorAuthorData as $creator_items) {
                    if (!in_array($creator_items, $exist_creators)) {
                        $index_key = array_key_last($creators_array);
                        $exist_creators[] = $creator_items;
                        $creators_array[$index_key + 1]['name'] = $creator_items;
                        $creators_array[$index_key + 1]['role'] = "نویسنده";
                    }
                }
                $digi_creatorPartnerData = array_unique(array_filter($digi_books->pluck('partnerArray')->all()));
                foreach ($digi_creatorPartnerData as $creator_items) {
                    if (!in_array($creator_items, $exist_creators)) {
                        $index_key = array_key_last($creators_array);
                        $exist_creators[] = $creator_items;
                        $creators_array[$index_key + 1]['name'] = $creator_items;
                        $creators_array[$index_key + 1]['role'] = "مترجم";
                    }
                }
                $digi_creatorsData = array_filter($creators_array);
                $digi_formatData = array_unique(array_filter($digi_books->pluck('ghatechap')->all()));
                $digi_shabakData = array_unique(array_filter($digi_books->pluck('shabak')->all()));
                $digi_subjectsData = array_unique(array_filter($digi_books->pluck('cat')->all()));
                $digi_noekaghazData = array_unique(array_filter($digi_books->pluck('noekaghaz')->all()));
                $digi_noechapData = array_unique(array_filter($digi_books->pluck('noechap')->all()));
                $digi_coverData = array_unique(array_filter($digi_books->pluck('jeld')->all()));
                $digi_weightData = array_unique(array_filter($digi_books->pluck('vazn')->all()));
                $digi_descriptionData = array_unique(array_filter($digi_books->pluck('desc')->all()));
                if (!empty($digi_descriptionData)) {
                    $digi_descriptionData = reset($digi_descriptionData);
                }
                $features_array = array();
                foreach (array_unique($digi_books->pluck('features')->all()) as $feature_items) {
                    $features_array = explode(":|:", $feature_items);
                }
                $digi_featuresData = array_unique(array_filter($features_array));
                // $digi_imagesData = array_unique(array_filter($digi_books->pluck('images')->all()));
                // if(!empty($digi_imagesData)){
                //     $digi_imagesData = reset($digi_imagesData);
                // }
                $images_array = array();
                foreach ($digi_books->pluck('images')->all() as $image_items) {
                    if ($image_items != null) {
                        $images_array[] = $image_items;
                    }
                }
                $digi_imagesData = array_unique($images_array);

                $digi_circulationData = array_unique($digi_books->pluck('count')->all());
                // $digi_tedadSafeData = array_unique(array_filter($digi_books->pluck('tedadSafe')->all()));
                $digi_min_tedadSafe = $digi_books->min('tedadSafe');
                $digi_max_tedadSafe = $digi_books->max('tedadSafe');
                $digiData =
                    [
                        "isbns" => !empty($digi_shabakData) ? $digi_shabakData : null,
                        "names" => !empty($digi_titleData) ? $digi_titleData : null,
                        "publishers" => !empty($digi_publishersData) ? $digi_publishersData : null,
                        "subjects" => !empty($digi_subjectsData) ? $digi_subjectsData : null,
                        "images" => !empty($digi_imagesData) ? $digi_imagesData : null,
                        "formats" => !empty($digi_formatData) ? $digi_formatData : null,
                        "covers" => !empty($digi_coverData) ? $digi_coverData : null,
                        "circulation" => !empty($digi_circulationData) ? priceFormat($digi_circulationData) : null,
                        "des" => !empty($digi_descriptionData) ? $digi_descriptionData : null,
                        "noekaghazs" => !empty($digi_noekaghazData) ? $digi_noekaghazData : null,
                        "noechaps" => !empty($digi_noechapData) ? $digi_noechapData : null,
                        "weights" => !empty($digi_weightData) ? $digi_weightData : null,
                        "features" => !empty($digi_featuresData) ? $digi_featuresData : null,
                        // "numberPages" => !empty($digi_tedadSafeData) ? $digi_tedadSafeData : null, 
                        "numberPages" => (!empty($digi_min_tedadSafe) && !empty($digi_max_tedadSafe)) ? ' بین ' . $digi_min_tedadSafe . ' تا ' . $digi_max_tedadSafe : null,
                        "creators" => !empty($digi_creatorsData) ? $digi_creatorsData : null,
                    ];
            } else {
                $digiData = null;
            }

            //----------------------------------------------30book------------------------------------//
            $si_books = Book30book::where('book_master_id', $bookId)->get();
            if ($si_books->count() > 0) {
                $si_titleData = array_unique(array_filter($si_books->pluck('title')->all()));
                $si_langData = array_unique(array_filter($si_books->pluck('lang')->all()));
                $si_shabakData = array_unique(array_filter($si_books->pluck('shabak')->all()));
                $subjects_array = array();
                foreach (array_unique(array_filter($si_books->pluck('cats')->all())) as $subject_items) {
                    $subjects_array = explode("-|-", $subject_items);
                }
                $si_subjectsData = array_unique($subjects_array);
                $si_creatorData =  Author::whereIn('id', AuthorBook30book::whereIn('book30book_id', $si_books->pluck('id')->all())->pluck('author_id')->all())->pluck('d_name')->all();
                $si_publishersData = array_unique(array_filter($si_books->pluck('nasher')->all()));
                $si_min_publish_date = $si_books->min('saleNashr');
                $si_max_publish_date = $si_books->max('saleNashr');
                $si_printNumberData = array_unique(array_filter($si_books->pluck('nobatChap')->all()));
                // $si_tedadSafeData = array_unique(array_filter($si_books->pluck('tedadSafe')->all()));
                $si_min_tedadSafe = $si_books->min('tedadSafe');
                $si_max_tedadSafe = $si_books->max('tedadSafe');
                $si_formatData = array_unique(array_filter($si_books->pluck('ghatechap')->all()));
                $si_translateData = array_unique(array_filter($si_books->pluck('tarjome')->all()));
                $si_descriptionData = array_unique(array_filter($si_books->pluck('desc')->all()));
                if (!empty($si_descriptionData)) {
                    $si_descriptionData = reset($si_descriptionData);
                }
                $si_coverData = array_unique(array_filter($si_books->pluck('jeld')->all()));
                $si_weightData = array_unique(array_filter($si_books->pluck('vazn')->all()));
                // $si_imagesData = array_unique(array_filter($si_books->pluck('image')->all()));
                // if(!empty($si_imagesData)){
                //     $si_imagesData = reset($si_imagesData);
                // }
                $images_array = array();
                foreach ($si_books->pluck('images')->all() as $image_items) {
                    if ($image_items != null) {
                        $images_array[] = $image_items;
                    }
                }
                $si_imagesData = array_unique($images_array);

                $si_min_price_date = $si_books->min('price');
                $si_max_price_date = $si_books->max('price');
                $siData =
                    [
                        "isbns" => !empty($si_shabakData) ? $si_shabakData : null,
                        "names" => !empty($si_titleData) ? $si_titleData : null,
                        "lang" => !empty($si_langData) ? $si_langData : null,
                        "publishers" => !empty($si_publishersData) ? $si_publishersData : null,
                        'creators' => !empty($si_creatorData) ? $si_creatorData : null,
                        "subjects" => !empty($si_subjectsData) ? $si_subjectsData : null,
                        "images" => !empty($si_imagesData) ? $si_imagesData : null,
                        "formats" => !empty($si_formatData) ? $si_formatData : null,
                        "covers" => !empty($si_coverData) ? $si_coverData : null,
                        "des" => !empty($si_descriptionData) ? $si_descriptionData : null,
                        "weights" => !empty($si_weightData) ? $si_weightData : null,
                        // "numberPages" => !empty($si_tedadSafeData) ? $si_tedadSafeData : null,
                        "numberPages" => (!empty($si_min_tedadSafe) && !empty($si_max_tedadSafe)) ? ' بین ' . $si_min_tedadSafe . ' تا ' . $si_max_tedadSafe : null,
                        "publishDate" => (!empty($si_min_publish_date) && !empty($si_max_publish_date)) ? ' بین ' . $si_min_publish_date . ' تا ' . $si_max_publish_date : null,
                        "printNumbers" => !empty($si_printNumberData) ? $si_printNumberData : null,
                        "translate" => !empty($si_translateData) ? $si_translateData : null,
                        "price" => (!empty($si_min_price_date) && !empty($si_max_price_date)) ? ' بین ' . priceFormat($si_min_price_date) . ' تا ' . priceFormat($si_max_price_date) . ' ریال ' : null,
                    ];
            } else {
                $siData = null;
            }

            //----------------------------------------------gisoom------------------------------------//
            $gisoom_books = BookGisoom::where('book_master_id', $bookId)->get();
            if ($gisoom_books->count() > 0) {
                $gisoom_titleData = array_unique(array_filter($gisoom_books->pluck('title')->all()));
                $gisoom_langData = array_unique(array_filter($gisoom_books->pluck('lang')->all()));
                $gisoom_editorData = array_unique(array_filter($gisoom_books->pluck('editor')->all()));
                $gisoom_dioCodeData = array_unique(array_filter($gisoom_books->pluck('radeD')->all()));
                $gisoom_publishersData = array_unique(array_filter($gisoom_books->pluck('nasher')->all()));
                $gisoom_creatorData =  Author::whereIn('id', AuthorBookgisoom::whereIn('book_gisoom_id', $digi_books->pluck('id')->all())->pluck('author_id')->all())->pluck('d_name')->all();
                $gisoom_min_publish_date = $gisoom_books->min('saleNashr');
                $gisoom_max_publish_date = $gisoom_books->max('saleNashr');
                $gisoom_printNumberData = array_unique(array_filter($gisoom_books->pluck('nobatChap')->all()));
                $gisoom_circulationData = array_unique(array_filter($gisoom_books->pluck('tiraj')->all()));
                // $gisoom_tedadSafeData = array_unique(array_filter($gisoom_books->pluck('tedadSafe')->all()));
                $gisoom_min_tedadSafe = $gisoom_books->min('tedadSafe');
                $gisoom_max_tedadSafe = $gisoom_books->max('tedadSafe');
                $gisoom_formatData = array_unique(array_filter($gisoom_books->pluck('ghatechap')->all()));
                $gisoom_translateData = array_unique(array_filter($gisoom_books->pluck('tarjome')->all()));
                $gisoom_descriptionData = array_unique(array_filter($gisoom_books->pluck('desc')->all()));
                if (!empty($gisoom_descriptionData)) {
                    $gisoom_descriptionData = reset($gisoom_descriptionData);
                }
                $gisoom_shabak10Data = array_unique(array_filter($gisoom_books->pluck('shabak10')->all()));
                $gisoom_shabak13Data = array_unique(array_filter($gisoom_books->pluck('shabak13')->all()));
                // $gisoom_imagesData = array_unique(array_filter($gisoom_books->pluck('images')->all()));
                // if(!empty($gisoom_imagesData)){
                //     $gisoom_imagesData = reset($gisoom_imagesData);
                // }
                $images_array = array();
                foreach ($gisoom_books->pluck('images')->all() as $image_items) {
                    if ($image_items != null) {
                        $images_array[] = $image_items;
                    }
                }
                $gisoom_imagesData = array_unique($images_array);
                $gisoom_min_price_date = $gisoom_books->min('price');
                $gisoom_max_price_date = $gisoom_books->max('price');
                $gisoom_subjectsData = array_unique(array_filter($gisoom_books->pluck('catText')->all()));
                $gisoomData =
                    [
                        "isbns10" => !empty($gisoom_shabak10Data) ? $gisoom_shabak10Data : null,
                        "isbns13" => !empty($gisoom_shabak13Data) ? $gisoom_shabak13Data : null,
                        "names" => !empty($gisoom_titleData) ? $gisoom_titleData : null,
                        "lang" => !empty($gisoom_langData) ? $gisoom_langData : null,
                        "publishers" => !empty($gisoom_publishersData) ? $gisoom_publishersData : null,
                        "creator" => !empty($gisoom_creatorData) ? $gisoom_creatorData : null,
                        "subjects" => !empty($gisoom_subjectsData) ? $gisoom_subjectsData : null,
                        "images" => !empty($gisoom_imagesData) ? $gisoom_imagesData : null,
                        "circulation" => !empty($gisoom_circulationData) ? priceFormat($gisoom_circulationData) : null,
                        "dioCodes" => !empty($gisoom_dioCodeData) ? $gisoom_dioCodeData : null,
                        "formats" => !empty($gisoom_formatData) ? $gisoom_formatData : null,
                        "creators" => !empty($gisoom_editorData) ? $gisoom_editorData : null,
                        "des" => !empty($gisoom_descriptionData) ? $gisoom_descriptionData : null,
                        // "numberPages" => !empty($gisoom_tedadSafeData) ? $gisoom_tedadSafeData : null, 
                        "numberPages" => (!empty($gisoom_min_tedadSafe) && !empty($gisoom_max_tedadSafe)) ? ' بین ' . $gisoom_min_tedadSafe . ' تا ' . $gisoom_max_tedadSafe : null,
                        "publishDate" => (!empty($gisoom_min_publish_date) && !empty($gisoom_max_publish_date)) ? ' بین ' . $gisoom_min_publish_date . ' تا ' . $gisoom_max_publish_date : null,
                        "price" => (!empty($gisoom_min_price_date) && !empty($gisoom_max_price_date)) ? ' بین ' . priceFormat($gisoom_min_price_date) . ' تا ' . priceFormat($gisoom_max_price_date) . ' ریال ' : null,
                        "printNumbers" => !empty($gisoom_printNumberData) ? $gisoom_printNumberData : null,
                        "translate" => !empty($gisoom_translateData) ? $gisoom_translateData : null,
                    ];
            } else {
                $gisoomData = null;
            }

            //----------------------------------------------iranketab------------------------------------//

            $iranketab_books = BookIranketab::where('book_master_id', $bookId)->get();
            if ($iranketab_books->count() > 0) {
                $iranketab_titleData = array_unique(array_filter($iranketab_books->pluck('title')->all()));
                $iranketab_en_titleData = array_unique(array_filter($iranketab_books->pluck('enTitle')->all()));
                $iranketab_publishersData = array_unique(array_filter($iranketab_books->pluck('nasher')->all()));
                $tags_array = array();
                foreach (array_unique($iranketab_books->pluck('tags')->all()) as $tag_items) {
                    if ($tag_items != null) {
                        $tags_array = explode("#", $tag_items);
                    }
                }
                $iranketab_subjectsData = array_unique(array_filter($tags_array));
                $iranketab_min_publish_date = $iranketab_books->min('saleNashr');
                $iranketab_max_publish_date = $iranketab_books->max('saleNashr');
                $iranketab_printNumberData = array_unique(array_filter($iranketab_books->pluck('nobatChap')->all()));
                // $iranketab_tedadSafeData = array_unique(array_filter($iranketab_books->pluck('tedadSafe')->all()));
                $iranketab_min_tedadSafe = $iranketab_books->min('tedadSafe');
                $iranketab_max_tedadSafe = $iranketab_books->max('tedadSafe');
                $iranketab_formatData = array_unique(array_filter($iranketab_books->pluck('ghatechap')->all()));
                $iranketab_shabakData = array_unique(array_filter($iranketab_books->pluck('shabak')->all()));
                $iranketab_coverData = array_unique(array_filter($iranketab_books->pluck('jeld')->all()));
                $iranketab_translateData = array_unique(array_filter($iranketab_books->pluck('traslate')->all()));
                $iranketab_descriptionData = array_unique(array_filter($iranketab_books->pluck('desc')->all()));
                if (!empty($iranketab_descriptionData)) {
                    $iranketab_descriptionData = reset($iranketab_descriptionData);
                }
                $iranketab_featuresData = $iranketab_books->pluck('features')->first();
                if (!empty($iranketab_featuresData)) {
                    $iranketab_featuresData = json_decode($iranketab_featuresData);
                }

                $iranketab_partsTextData = $iranketab_books->pluck('partsText')->first();
                if (!empty($iranketab_partsTextData)) {
                    $iranketab_partsTextData = json_decode($iranketab_partsTextData);
                }

                $iranketab_notesData = $iranketab_books->pluck('notes')->first();
                if (!empty($iranketab_notesData)) {
                    $iranketab_notesData = json_decode($iranketab_notesData);
                }

                $images_array = array();
                foreach ($iranketab_books->pluck('images')->all() as $image_items) {
                    if ($image_items != null) {
                        $index_key = array_key_last($images_array);
                        $arr_images = explode(" =|= ", $image_items);
                        foreach ($arr_images as $arr_images_items) {
                            if ($arr_images_items != "" and $arr_images_items != null) {
                                $images_array[$index_key + 1] = $arr_images_items;
                            }
                        }
                    }
                }
                $iranketab_imagesData = array_unique($images_array);
                // if(!empty($iranketab_imagesData)){
                //     $iranketab_imagesData = reset($iranketab_imagesData);
                // }
                $iranketab_min_price_date = $iranketab_books->min('price');
                $iranketab_max_price_date = $iranketab_books->max('price');
                $creators_array = array();
                $exist_creators = array();
                foreach (array_unique($iranketab_books->pluck('partnerArray')->all()) as $creator_items) {
                    $item_info = json_decode($creator_items);
                    foreach ($item_info as $items) {
                        if (!in_array($items->name, $exist_creators)) {
                            $index_key = array_key_last($creators_array);
                            $exist_creators[] = $items->name;
                            $creators_array[$index_key + 1]['name'] = $items->name;
                            $creators_array[$index_key + 1]['role'] = ($items->roleId == 1) ? "نویسنده" : "مترجم";
                        }
                    }
                }
                $iranketab_creatorsData = array_filter($creators_array);
                $iranketab_rate_date = array_unique(array_filter($iranketab_books->pluck('rate')->all()));
                $iranketabData =
                    [
                        "isbns" => !empty($iranketab_shabakData) ? $iranketab_shabakData : null,
                        "names" => !empty($iranketab_titleData) ? $iranketab_titleData : null,
                        "en_names" => !empty($iranketab_en_titleData) ? $iranketab_en_titleData : null,
                        "publishers" => !empty($iranketab_publishersData) ? $iranketab_publishersData : null,
                        "subjects" => !empty($iranketab_subjectsData) ? $iranketab_subjectsData : null,
                        "images" => !empty($iranketab_imagesData) ? $iranketab_imagesData : null,
                        "covers" => !empty($iranketab_coverData) ? $iranketab_coverData : null,
                        "formats" => !empty($iranketab_formatData) ? $iranketab_formatData : null,
                        "creators" => !empty($iranketab_creatorsData) ? $iranketab_creatorsData : null,
                        "des" => !empty($iranketab_descriptionData) ? $iranketab_descriptionData : null,
                        "features" => !empty($iranketab_featuresData) ? $iranketab_featuresData : null,
                        "partsTexts" => !empty($iranketab_partsTextData) ? $iranketab_partsTextData : null,
                        "notes" => !empty($iranketab_notesData) ? $iranketab_notesData : null,
                        // "numberPages" => !empty($iranketab_tedadSafeData) ? $iranketab_tedadSafeData : null, 
                        "numberPages" => (!empty($iranketab_min_tedadSafe) && !empty($iranketab_max_tedadSafe)) ? ' بین ' . $iranketab_min_tedadSafe . ' تا ' . $iranketab_max_tedadSafe : null,
                        "publishDate" => (!empty($iranketab_min_publish_date) && !empty($iranketab_max_publish_date)) ? ' بین ' . $iranketab_min_publish_date . ' تا ' . $iranketab_max_publish_date : null,
                        "price" => (!empty($iranketab_min_price_date) && !empty($iranketab_max_price_date)) ? ' بین ' . priceFormat($iranketab_min_price_date) . ' تا ' . priceFormat($iranketab_max_price_date) . ' تومان ' : null,
                        "printNumbers" => !empty($iranketab_printNumberData) ? $iranketab_printNumberData : null,
                        "translate" => !empty($iranketab_translateData) ? $iranketab_translateData : null,
                        "ratings" => !empty($iranketab_rate_date) ? $iranketab_rate_date : null,
                    ];
            } else {
                $iranketabData = null;
            }
        } else {
            $digiData = null;
            $siData = null;
            $gisoomData = null;
            $iranketabData = null;
        }
        // response
        return response()->json(
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => [
                    "master" => $dataMaster,
                    "yearPrintCount" => $yearPrintCountData,
                    "publisherPrintCount" => $publisherPrintCountData,
                    "digiData" => $digiData,
                    "sibookData" => $siData,
                    "gisoomData" => $gisoomData,
                    "iranketabData" => $iranketabData
                ]

            ],
            $status
        );
    }


    // market
    public function market(Request $request)
    {
        $bookId = $request["bookId"];
        $isbn = "";
        $isbn2 = "";
        $data = null;
        $status = 404;

        // read book
        $book = BookirBook::where('xid', '=', $bookId)->first();
        if ($book != null and $book->xid > 0) {
            $isbn = $book->xisbn;
            $isbn2 = $book->xisbn2;
        }

        if ($isbn != "") {
            // read books of digi
            $books = BookDigi::where('shabak', '=', $isbn);
            if ($isbn2 != "") $books->orwhere('shabak', '=', $isbn2);
            $books = $books->get();
            if ($books != null and count($books) > 0) {
                foreach ($books as $book) {
                    //                    if($book->price > 0)
                    $data[] =
                        [
                            "source" => "دیجیکالا",
                            "price" => $book->price,
                        ];
                }
            }

            // read books of gisoom
            $books = BookGisoom::where('shabak10', '=', $isbn)->orwhere('shabak13', '=', $isbn);
            if ($isbn2 != "") $books->orwhere('shabak10', '=', $isbn2)->orwhere('shabak13', '=', $isbn2);
            $books = $books->get();
            if ($books != null and count($books) > 0) {
                foreach ($books as $book) {
                    //                    if($book->price > 0)
                    $data[] =
                        [
                            "source" => "گیسوم",
                            "price" => $book->price,
                        ];
                }
            }

            // read books of 30Book
            $books = Book30book::where('shabak', '=', $isbn);
            if ($isbn2 != "") $books->orwhere('shabak', '=', $isbn2);
            $books = $books->get();
            if ($books != null and count($books) > 0) {
                foreach ($books as $book) {
                    //                    if($book->price > 0)
                    $data[] =
                        [
                            "source" => "سی بوک",
                            "price" => $book->price,
                        ];
                }
            }
        }

        //
        if ($data != null) $status = 200;

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

    // search dio
    public function searchDio(Request $request)
    {
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 404;

        // read
        $books = BookirBook::where('xdiocode', 'like', "%$searchWord%")->orderBy('xdiocode', 'asc')->get();
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

    public function mergeBookDossier(Request $request)
    {
        if (isset($request["mergeBookDossierId"])) {
            $mergeBookDossierArray =  array();
            foreach ($request["mergeBookDossierId"] as $key => $item) {
                $mergeBookDossierArray[$key] =   json_decode($item);
            }
            $mergeBookDossierArrayCollection = new Collection($mergeBookDossierArray);
            $mergeBookDossierId = $mergeBookDossierArrayCollection->pluck('dossier_id')->all();
        } else {
            $mergeBookDossierId = '';
        }
        // $mergeBookDossierId = (isset($request["mergeBookDossierId"])) ? $request["mergeBookDossierId"] : "";
        $status = 404;

        // $allBookirBooks = BookirBook::whereIN('xparent', $mergeBookDossierId)->get();
        $allBookirBooks = BookirBook::whereIN('xid', $mergeBookDossierId)->get();
        $allBookirBooksIsbnCollection =  $allBookirBooks->pluck('xisbn2')->all();
        if ($allBookirBooks->count() != 0) {
            $allBookirBooksIsbnCollection =  $allBookirBooks->pluck('xisbn2')->all();
            $allBookirBooksIdCollection =  $allBookirBooks->pluck('xid')->all();

            $bookirBooksParent = $allBookirBooks->pluck('xisbn2', 'xid')->all();

            $strongBookIsbn = '';
            $strongBookCount = 0;
            foreach ($bookirBooksParent as $key => $bookirBookParentItem) { // پیدا کردن آیدی قوی تر
                $allBookirBooksIsbnCollection = new Collection($allBookirBooksIsbnCollection);
                $filtered = $allBookirBooksIsbnCollection->filter(function ($isbn) use ($bookirBookParentItem) {
                    return $isbn == $bookirBookParentItem;
                });
                if (($filtered->count() == $strongBookCount) and  BookirBook::where('xid', $key)->first()->xparent = -1) {
                    $strongBookCount  = $filtered->count();
                    $strongBookIsbn  = $bookirBookParentItem;
                    $strongBookId  = $key;
                } elseif ($filtered->count() > $strongBookCount) {
                    $strongBookCount  = $filtered->count();
                    $strongBookIsbn  = $bookirBookParentItem;
                    $strongBookId  = $key;
                }
            }

            try {
                BookirBook::whereIN('xparent', $mergeBookDossierId)->update(['xparent' => $strongBookId, 'xrequest_manage_parent' => 1]);
                BookirBook::whereIN('xid', $mergeBookDossierId)->update(['xparent' => $strongBookId, 'xrequest_manage_parent' => 1]);
                BookirBook::where('xid', $strongBookId)->update(['xparent' => -1, 'xrequest_manage_parent' => 1]);
                $result = 'TRUE';
            } catch (Exception $Exception) {
                //throw $th;
                $result = $Exception->getMessage();
            }
            $status = 200;
        } else {
            $result = 'FALSE';
        }
        return response()->json(
            [
                "status" => $status,
                "result" => $result
            ],
            $status
        );
    }

    public function separateFromBookDossier(Request $request)
    {
        if (isset($request["separateFromBookDossierId"])) {
            $separateFromBookDossierArray =  array();
            foreach ($request["separateFromBookDossierId"] as $key => $item) {
                $separateFromBookDossierArray[$key] =   json_decode($item);
            }
            $separateFromBookDossierArrayCollection = new Collection($separateFromBookDossierArray);
            $separateFromBookDossierId = $separateFromBookDossierArrayCollection->pluck('id')->all();
        } else {
            $separateFromBookDossierId = '';
        }

        // $separateFromBookDossierId = (isset($request["separateFromBookDossierId"])) ? $request["separateFromBookDossierId"] : "";
        $status = 404;

        $allBookirBooks = BookirBook::whereIN('xid', $separateFromBookDossierId)->get();
        if ($allBookirBooks->count() != 0) {
            try {
                BookirBook::whereIN('xid', $separateFromBookDossierId)->update(['xparent' => -1, 'xrequest_manage_parent' => 1]);
                $result = 'TRUE';
            } catch (Exception $Exception) {
                //throw $th;
                $result = $Exception->getMessage();
            }
            $status = 200;
        } else {
            $result = 'FALSE';
        }
        return response()->json(
            [
                "status" => $status,
                "result" => $result
            ],
            $status
        );
    }

    /*public function isbn3ToIsbn($isbn) افزودن خط تیره قانون پیچیده ایی داره 
    {
        if (strlen($isbn) == 13) {
            $isbn = substr_replace($isbn, '-', 3, 0);
            $isbn = substr_replace($isbn, '-', 6, 0);
            $isbn = substr_replace($isbn, '-', 11, 0);
            $isbn = substr_replace($isbn, '-', 15, 0);
            return $isbn;
        }
    }*/
    public function store(Request $request)
    {
        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:191',
                'isbn2' => 'nullable|required_without:isbn3|string|max:10',
                'isbn3' => 'nullable|required_without:isbn2|string|min:10',
                'coverPrice' => 'required|numeric',
                'pageCount' => 'required|numeric',
                'weight' => 'required|numeric',
                'printNumber' => 'required|numeric',
                // 'circulation' => 'required|numeric',
                'publishDate' => 'required|date_format:Y-m-d',
                'publisherId' => 'nullable|required_without:newPublisher|numeric',
                'newPublisher' => 'nullable|required_without:publisherId|string',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['validation_errors' => $validator->errors()->messages(), 'status' => 422]);
        } else {

            $checkIssetBookQuery = BookirBook::where('xprintnumber', $request->get('printNumber'))->where('xcoverprice', $request->get('coverPrice'));
            $checkIssetBookQuery->where(function ($query) use ($request) {
                $query->where('xisbn2', $request->get('isbn2'))
                    ->orwhere('xisbn3', $request->get('isbn3'));
            });
            $checkIssetBookReault = $checkIssetBookQuery->first();
            if (isset($checkIssetBookReault->xid) and $checkIssetBookReault->xid != NULL) {
                return response()->json(['message' => 'کتاب با اطلاعات شابک و نوبت چاپ و قیمت مذکور قبلا ثبت شده است .', 'status' => 409]);
            } else {
                $publishDate = '';
                if (!empty($request->get('publishDate')) && $request->get('publishDate') != "undefined") { // $books->where('xpublishdate', '=', $isbn);
                    $publishDate =  Bookirbook::toGregorian($request->get('publishDate'), '-', '-');
                }
                $book = new BookirBook([
                    'xdocid' => 0,
                    'xsiteid' => 1,
                    'xpageurl' => '', // uniqe
                    'xpageurl2' => '',
                    'xname' => $request->get('name'),
                    'xdoctype' => 1,
                    'xpagecount' => $request->get('pageCount'),
                    'xformat' => $request->get('format'),
                    'xcover' => $request->get('cover'),
                    'xprintnumber' => $request->get('printNumber'),
                    'xcirculation' => $request->get('circulation'),
                    'xcovernumber' => 0,
                    'xcovercount' => 0,
                    'xapearance' => '',
                    // 'xisbn' => ($request->get('isbn3')) ? $this->isbn3ToIsbn($request->get('isbn3')) : null,
                    'xisbn2' => $request->get('isbn2'),
                    'xisbn3' => $request->get('isbn3'),
                    'xpublishdate' => $publishDate,
                    'xcoverprice' => $request->get('coverPrice'),
                    'xminprice' => '',
                    'xcongresscode' => '',
                    'xdiocode' => $request->get('doiCode'),
                    'xlang' => $request->get('lang'),
                    'xpublishplace' => $request->get('publishPlace'),
                    'xdescription' => $request->get('description'),
                    'xweight' => $request->get('weight'),
                    'ximgeurl' => '',
                    'xpdfurl' => '',
                    'xregdate' => time(),
                    'xissubject' => !empty($request->get('subjectId')) ? 1 : 0,
                    'xiscreator' => !empty($request->get('creatorId')) ? 1 : 0,
                    'xispublisher' => !empty($request->get('publisherId')) or  !empty($request->get('newPublisher')) ? 1 : 0,
                    'xislibrary' => 0,
                    'xistag' => 0,
                    'xisseller' => 0,
                    'xname2' => str_replace(' ', '', $request->get('name')),
                    'xisname' => !empty($request->get('name')) ? 1 : 0,
                    'xisdoc' => 0,
                    'xisdoc2' => 0,
                    'xiswater' => 0,
                    'xwhite' => 0,
                    'xblack' => 0,
                    'xparent ' => -1,
                    'xreg_userid' => $user->id
                ]);

                // partner and role
                $partner_array = array();
                // $roleId = explode(',', $request->get('roleId'));
                // $creatorId = explode(',', $request->get('creatorId'));
                $roleInfo = $request->get('roleId');
                $creatorInfo = $request->get('creatorId');


                // if (isset($roleInfo) and !empty($roleInfo) and isset($creatorInfo) and !empty($creatorInfo)) {
                //     for ($i = 0; $i < count($roleInfo); $i++) {
                //         if (!empty($roleInfo) && !empty($roleInfo)) {
                //             if (isset($roleInfo[$i]) && !empty($roleInfo[$i] && isset($creatorInfo[$i]) && !empty($creatorInfo[$i])) && $roleInfo[$i]['row_id'] == $creatorInfo[$i]['row_id']) {
                //                 $partner_array[$i]['xcreatorid'] = $creatorInfo[$i]['id'];
                //                 $partner_array[$i]['xroleid'] = $roleInfo[$i]['id'];
                //             }
                //         }
                //     }
                // }
                if (isset($roleInfo) and !empty($roleInfo) and isset($creatorInfo) and !empty($creatorInfo)) {
                    for ($i = 0; $i < count($roleInfo); $i++) {
                        if (!empty($roleInfo) && !empty($roleInfo)) {
                            if (isset($creatorInfo) && !empty($creatorInfo)) {
                                foreach($creatorInfo as $creatorInfoItem){
                                    if($roleInfo[$i]['row_id'] == $creatorInfoItem['row_id']){
                                        $partner_array[$i]['xcreatorid'] = $creatorInfoItem['id'];
                                        $partner_array[$i]['xroleid'] = $roleInfo[$i]['id'];
                                    }
                                   
                                }
                            }
                        }
                    }
                }
                // publisher
                if (!empty($request->get('newPublisher'))) {
                    $selectedPartnerInfo = BookirPublisher::where('xpublishername', $request->get('newPublisher'))->first();
                    if (isset($selectedPartnerInfo) and $selectedPartnerInfo != NULL) {  // isset publisher
                        $publisherId = $selectedPartnerInfo->xid;
                    } else {
                        $bookirpublisherModel = new BookirPublisher([
                            'xtabletype' => 0,
                            'xsiteid' => 0,
                            'xparentid' => 0,
                            'xpageurl' => '',
                            'xpublishername' => $request->get('newPublisher'),
                            'xmanager' => '',
                            'xactivity' => '',
                            'xplace' => '',
                            'xaddress' => '',
                            'xpobox' => '',
                            'xzipcode' => '',
                            'xphone' => '',
                            'xcellphone' => '',
                            'xfax' => '',
                            'xlastupdate' => 0,
                            'xtype' => '',
                            'xpermitno' => '',
                            'xemail' => '',
                            'xisbnid' => '',
                            'xfoundingdate' => '',
                            'xispos' => '',
                            'ximageurl' => '',
                            'xregdate' => time(),
                            'xpublishername2' => str_replace(' ', '', $request->get('newPublisher')),
                            'xiswiki' => 0,
                            'xismajma' => 0,
                            'xisname' => !empty($request->get('newPublisher')) ? 1 : 0,
                            'xsave' => '',
                            'xwhite' => 0,
                            'xblack' => 0,
                            'xreg_userid' => $user->id,

                        ]);
                        $bookirpublisherModel->save();
                        $publisherId = $bookirpublisherModel->xid;
                    }
                } elseif (!empty($request->get('publisherId'))) {
                    $publisherId  = $request->get('publisherId');
                }

                // subject
                $bookSubjectIds = array();
                $subjectInfo = $request->get('subjectId');
                if (!empty($subjectInfo)) {
                    for ($i = 0; $i < count($subjectInfo); $i++) {
                        $bookSubjectIds[$i] = $subjectInfo[$i]['value'];
                    }
                }

                DB::transaction(function () use ($request, $book, $partner_array, $publisherId, $bookSubjectIds) {
                    try {
                        $book->save();
                        $book->publishers()->sync($publisherId);
                        $book->subjects()->sync($bookSubjectIds);
                        if (isset($partner_array) and !empty($partner_array)) {
                            $book->partnersRoles()->sync($partner_array);
                        }

                        // upload image
                        if ($request->has('bookCoverImage') && $request->bookCoverImage != 'undefined') {
                            $imageName =  $book->xid . '.' . $request->bookCoverImage->extension();
                            $request->file('bookCoverImage')->storeAs(config('global.book_image_path'), $imageName);
                            $imageAddress = str_replace('public', 'storage', config('global.book_image_path')) . '/' . $imageName;
                            $book->ximgeurl = $imageAddress;
                            //resize
                            $this->createThumbnail($imageAddress, 300, 420);
                            $book->update();
                        }
                    } catch (Exception $Exception) {
                        //throw $th;
                        echo $Exception->getMessage();
                        // return redirect(route('person-create', app()->getLocale()))->with('warning', $Exception->getMessage());
                    }
                });
                return response()->json(['message' => 'ok', 'book_id' => $book->xid, 'status' => 200]);
            }
        }
    }

    public function update(Request $request, $id)
    {
        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:191',
                'isbn2' => 'nullable|required_without:isbn3|string|max:10',
                'isbn3' => 'nullable|required_without:isbn2|string|min:10',
                'coverPrice' => 'required|numeric',
                'pageCount' => 'required|numeric',
                'weight' => 'required|numeric',
                'printNumber' => 'required|numeric',
                // 'circulation' => 'required|numeric',
                'publishDate' => 'required|date_format:Y-m-d',
                'publisherId' => 'nullable|required_without:newPublisher|numeric',
                'newPublisher' => 'nullable|required_without:publisherId|string',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['validation_errors' => $validator->errors()->messages(), 'status' => 422]);
        } else {
            DB::enableQueryLog();
            $checkIssetBookQuery = BookirBook::where('xid', '!=', $id)->where('xprintnumber', $request->get('printNumber'))->where('xcoverprice', $request->get('coverPrice'));
            $checkIssetBookQuery->where(function ($query) use ($request) {
                $query->where('xisbn2', $request->get('isbn2'))
                    ->orwhere('xisbn3', $request->get('isbn3'));
            });
            $checkIssetBookReault = $checkIssetBookQuery->first();
            if (isset($checkIssetBookReault->xid) and $checkIssetBookReault->xid != NULL) {
                return response()->json(['message' => 'کتاب با اطلاعات شابک و نوبت چاپ و قیمت مذکور قبلا ثبت شده است .', 'status' => 409]);
            } else {
                $publishDate = '';
                if (!empty($request->get('publishDate')) && $request->get('publishDate') != "undefined") { // $books->where('xpublishdate', '=', $isbn);
                    $publishDate =  Bookirbook::toGregorian($request->get('publishDate'), '-', '-');
                }
                $book = BookirBook::findOrFail($id);
                $book->xname = $request->get('name');
                $book->xpagecount = $request->get('pageCount');
                $book->xformat = $request->get('format');
                $book->xcover = $request->get('cover');
                $book->xprintnumber = $request->get('printNumber');
                $book->xcirculation = $request->get('circulation');
                // $book->xisbn = ($request->get('isbn3')) ? $this->isbn3ToIsbn($request->get('isbn3')) : null;
                $book->xisbn2 = $request->get('isbn2');
                $book->xisbn3 = $request->get('isbn3');
                $book->xpublishdate = $publishDate;
                $book->xcoverprice = $request->get('coverPrice');
                $book->xdiocode = $request->get('doiCode');
                $book->xlang = $request->get('lang');
                $book->xpublishplace = $request->get('publishPlace');
                $book->xdescription = $request->get('description');
                $book->xweight = $request->get('weight');
                $book->xissubject = empty($request->get('subjectId')) ? 1 : 0;
                $book->xiscreator = !empty($request->get('creatorId')) ? 1 : 0;
                $book->xispublisher = !empty($request->get('publisherId')) or  !empty($request->get('newPublisher')) ? 1 : 0;
                $book->xname2 = str_replace(' ', '', $request->get('name'));
                $book->xisname = !empty($request->get('name')) ? 1 : 0;

                $partner_array = array();
                // $roleId = explode(',', $request->get('roleId'));
                // $creatorId = explode(',', $request->get('creatorId'));
                $roleInfo = $request->get('roleId');
                $creatorInfo = $request->get('creatorId');


                // if (isset($roleInfo) and !empty($roleInfo) and isset($creatorInfo) and !empty($creatorInfo)) {
                //     for ($i = 0; $i < count($roleInfo); $i++) {
                //         if (!empty($roleInfo) && !empty($roleInfo)) {
                //             if (isset($roleInfo[$i]) && !empty($roleInfo[$i] && isset($creatorInfo[$i]) && !empty($creatorInfo[$i])) && $roleInfo[$i]['row_id'] == $creatorInfo[$i]['row_id']) {
                //                 $partner_array[$i]['xcreatorid'] = $creatorInfo[$i]['id'];
                //                 $partner_array[$i]['xroleid'] = $roleInfo[$i]['id'];
                //             }
                //         }
                //     }
                // }

                if (isset($roleInfo) and !empty($roleInfo) and isset($creatorInfo) and !empty($creatorInfo)) {
                    for ($i = 0; $i < count($roleInfo); $i++) {
                        if (!empty($roleInfo) && !empty($roleInfo)) {
                            if (isset($creatorInfo) && !empty($creatorInfo)) {
                                foreach($creatorInfo as $creatorInfoItem){
                                    if($roleInfo[$i]['row_id'] == $creatorInfoItem['row_id']){
                                        $partner_array[$i]['xcreatorid'] = $creatorInfoItem['id'];
                                        $partner_array[$i]['xroleid'] = $roleInfo[$i]['id'];
                                    }
                                   
                                }
                            }
                        }
                    }
                }
                // publisher
                if (!empty($request->get('newPublisher'))) {
                    $selectedPartnerInfo = BookirPublisher::where('xpublishername', $request->get('newPublisher'))->first();
                    if (isset($selectedPartnerInfo) and $selectedPartnerInfo != NULL) {  // isset publisher
                        $publisherId = $selectedPartnerInfo->xid;
                    } else {
                        $bookirpublisherModel = new BookirPublisher([
                            'xtabletype' => 0,
                            'xsiteid' => 0,
                            'xparentid' => 0,
                            'xpageurl' => '',
                            'xpublishername' => $request->get('newPublisher'),
                            'xmanager' => '',
                            'xactivity' => '',
                            'xplace' => '',
                            'xaddress' => '',
                            'xpobox' => '',
                            'xzipcode' => '',
                            'xphone' => '',
                            'xcellphone' => '',
                            'xfax' => '',
                            'xlastupdate' => 0,
                            'xtype' => '',
                            'xpermitno' => '',
                            'xemail' => '',
                            'xisbnid' => '',
                            'xfoundingdate' => '',
                            'xispos' => '',
                            'ximageurl' => '',
                            'xregdate' => time(),
                            'xpublishername2' => str_replace(' ', '', $request->get('newPublisher')),
                            'xiswiki' => 0,
                            'xismajma' => 0,
                            'xisname' => !empty($request->get('newPublisher')) ? 1 : 0,
                            'xsave' => '',
                            'xwhite' => 0,
                            'xblack' => 0,
                            'xreg_userid' => $user->id,

                        ]);
                        $bookirpublisherModel->save();
                        $publisherId = $bookirpublisherModel->xid;
                    }
                } elseif (!empty($request->get('publisherId'))) {
                    $publisherId  = $request->get('publisherId');
                }

                // subject
                $bookSubjectIds = array();
                $subjectInfo = $request->get('subjectId');
                if (!empty($subjectInfo)) {
                    for ($i = 0; $i < count($subjectInfo); $i++) {
                        $bookSubjectIds[$i] = $subjectInfo[$i]['value'];
                    }
                }


                DB::transaction(function () use ($request, $book, $partner_array, $publisherId, $bookSubjectIds) {
                    try {
                        $book->update();
                        $book->publishers()->sync($publisherId);
                        $book->subjects()->sync($bookSubjectIds);
                        if (isset($partner_array) and !empty($partner_array)) {
                            $book->partnersRoles()->sync($partner_array);
                        }

                        // upload image
                        if ($request->has('bookCoverImage') && $request->bookCoverImage != 'undefined') {
                            $imageName =  $book->xid . '.' . $request->bookCoverImage->extension();
                            $request->file('bookCoverImage')->storeAs(config('global.book_image_path'), $imageName);
                            $imageAddress = str_replace('public', 'storage', config('global.book_image_path')) . '/' . $imageName;
                            $book->ximgeurl = $imageAddress;

                            //resize
                            $this->createThumbnail($imageAddress, 300, 420);
                            $book->update();
                        }
                    } catch (Exception $Exception) {
                        //throw $th;
                        echo $Exception->getMessage();
                        // return redirect(route('person-create', app()->getLocale()))->with('warning', $Exception->getMessage());
                    }
                });
                return response()->json(['message' => 'ok', 'book_id' => $book->xid, 'status' => 200]);
            }
        }
    }

    public function validation10DigitShabk($isbn){
        if(strlen($isbn) == 10){
            $sum = 0;
            $counter = 10;
            for ($i = 0; $i < strlen($isbn); $i++){
                $sum += $isbn[$i]*$counter;
                $counter--;
            }
            if(fmod($sum,11) == 0){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
        
    }

    public function validation13DigitShabk($isbn){
        if(strlen($isbn) == 13){
            $sum = 0;
            $even = 1;
            $odd = 3;
            for ($i = 0; $i < strlen($isbn); $i++){
                if(fmod($i,2)==1){
                    $sum += $isbn[$i]*$odd;
                }elseif(fmod($i,2)==0){
                    $sum += $isbn[$i]*$even;
                }
            }
            if(fmod($sum,10) == 0){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
       
    }

    public function createThumbnail($path, $width, $height)
    {
        $image = Image::make($path)->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });
        $image->save($path);
    }

    public function findIsbn(Request $request)
    {
        $book = BookirBook::where('xparent', -1);
        $book = $book->where(function ($query) use ($book, $request) {
            $query->where('xisbn', $request["searchIsbnBook"])->OrWhere('xisbn2', $request["searchIsbnBook"])->OrWhere('xisbn3', $request["searchIsbnBook"]);
        });
        $book = $book->first();
        if ($book != null and $book->xid > 0) {
            return '/book/edit/' . $book->xid;
        } else {
            return '/book/new';
        }
    }
}
