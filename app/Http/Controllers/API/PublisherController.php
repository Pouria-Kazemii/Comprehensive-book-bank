<?php

namespace App\Http\Controllers\Api;

use App\Models\BookirBook;
use App\Models\BookirRules;
use Illuminate\Http\Request;
use App\Models\BookirPartner;
use App\Models\BookirPublisher;
use App\Models\BookirPartnerrule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use PHPUnit\TextUI\XmlConfiguration\Group;

class PublisherController extends Controller
{
    // find
    public function find(Request $request)
    {
        return $this->lists($request);
    }

    // find by creator
    public function findByCreator(Request $request)
    {
        $creatorId = $request["creatorId"];

        $where = $creatorId != "" ? "xid In (Select bi_publisher_xid From bi_book_bi_publisher Where bi_book_xid In (Select xbookid From bookir_partnerrule Where xcreatorid='$creatorId'))" : "";

        return $this->lists($request, false, ($where == ""), $where);
    }

    // find by subject
    public function findBySubject(Request $request)
    {
        $subjectId = $request["subjectId"];

        $where = $subjectId != "" ? "xid In (Select bi_publisher_xid From bi_book_bi_publisher Where bi_book_xid In (Select bi_book_xid From bi_book_bi_subject Where bi_subject_xid='$subjectId'))" : "";

        return $this->lists($request, false, ($where == ""), $where);
    }

    // list
    public function lists(Request $request, $defaultWhere = true, $isNull = false, $where = "")
    {
        $name = (isset($request["name"])) ? $request["name"] : "";
        $currentPageNumber = (isset($request["currentPageNumber"])) ? $request["currentPageNumber"] : 0;
        $data = null;
        $status = 404;
        $pageRows = 50;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        if (!$isNull) {
            // read books
            $publishers = BookirPublisher::orderBy('xpublishername', 'asc')->where('xpublishername', '!=', '');
            //            if($defaultWhere) $publishers->where('xparent', '=', '-1');
            if ($name != "") $publishers->where('xpublishername', 'like', "%$name%");
            if ($where != "") $publishers->whereRaw($where);
            $publishers = $publishers->skip($offset)->take($pageRows)->get();
            if ($publishers != null and count($publishers) > 0) {
                foreach ($publishers as $publisher) {
                    $data[] =
                        [
                            "id" => $publisher->xid,
                            "name" => $publisher->xpublishername,
                        ];
                }

                $status = 200;
            }

            //
            $publishers = BookirPublisher::orderBy('xpublishername', 'asc')->where('xpublishername', '!=', '');
            //            if($defaultWhere) $publishers->where('xparent', '=', '-1');
            if ($name != "") $publishers->where('xpublishername', 'like', "%$name%");
            if ($where != "") $publishers->whereRaw($where);
            $totalRows = $publishers->count();
            $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;
        }

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

    // search
    public function search(Request $request)
    {
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 404;

        // read
        $publishers = BookirPublisher::where('xpublishername', '!=', '')->where('xpublishername', 'like', "%$searchWord%")->orderBy('xpublishername', 'asc')->get();
        if ($publishers != null and count($publishers) > 0) {
            foreach ($publishers as $publisher) {
                $data[] =
                    [
                        "id" => $publisher->xid,
                        "value" => $publisher->xpublishername,
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

    // annual activity by title
    public function annualActivityByTitle(Request $request)
    {
        $publisherId = $request["publisherId"];
        $yearPrintCountData = null;

        // read books for year printCount by title
        $books = BookirBook::whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')")->orderBy('xpublishdate', 'asc')->get();
        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $year = BookirBook::getShamsiYear($book->xpublishdate);
                $printCount = 1;

                $yearPrintCountData[$year] = ["year" => $year, "printCount" => (isset($yearPrintCountData[$year])) ? $printCount + $yearPrintCountData[$year]["printCount"] : $printCount];
            }

            $yearPrintCountData = ["label" => array_column($yearPrintCountData, 'year'), "value" => array_column($yearPrintCountData, 'printCount')];
        }

        if ($yearPrintCountData != null) $status = 200;

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

    // annual activity by circulation
    public function annualActivityByCirculation(Request $request)
    {
        $publisherId = $request["publisherId"];
        $yearPrintCountData = null;

        // read books for year printCount by circulation
        $books = BookirBook::whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')")->orderBy('xpublishdate', 'asc')->get();
        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $year = BookirBook::getShamsiYear($book->xpublishdate);
                $printCount = $book->xcirculation;

                $yearPrintCountData[$year] = ["year" => $year, "printCount" => (isset($yearPrintCountData[$year])) ? $printCount + $yearPrintCountData[$year]["printCount"] : $printCount];
            }

            $yearPrintCountData = ["label" => array_column($yearPrintCountData, 'year'), "value" => array_column($yearPrintCountData, 'printCount')];
        }

        if ($yearPrintCountData != null) $status = 200;

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

    // detail
    public function detail(Request $request)
    {
        $publisherId = $request["publisherId"];
        $dataMaster = null;

        // read
        $publisher = BookirPublisher::where('xid', '=', $publisherId)->first();
        if ($publisher != null and $publisher->xid > 0) {
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

    // translate authorship
    public function translateAuthorship(Request $request)
    {
        $publisherId = $request["publisherId"];
        $data = null;
        $status = 404;

        // read books
        $books = BookirBook::whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')")->get();
        if ($books != null and count($books) > 0) {
            $totalBooks = count($books);
            $data["authorship"] = 0;
            $data["translate"] = 0;

            foreach ($books as $book) {
                // $type = $book->xlang == "فارسی" ? "authorship" : "translate";
                $type = $book->is_translate == "1" ? "authorship" : "translate";
                $data[$type] += 1;
            }

            $dataTmp = null;
            $dataTmp["تالیف"] = ($data["authorship"] > 0) ? round(($data["authorship"] / $totalBooks) * 100, 2) : 0;
            $dataTmp["ترجمه"] = ($data["translate"] > 0) ? round(($data["translate"] / $totalBooks) * 100, 2) : 0;

            //
            $data = ["label" => array_keys($dataTmp), "value" => array_values($dataTmp)];
        }

        //
        if ($data != null) $status = 200;

        // response
        return response()->json(
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["data" => $data]
            ],
            $status
        );
    }

    // statistic subject
    public function statisticSubject(Request $request)
    {
        $publisherId = $request["publisherId"];
        $data = null;
        $status = 404;
        $totalSubjects = 0;

        // read books
        $books = BookirBook::whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')")->get();
        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $bookSubjects = DB::table('bi_book_bi_subject')
                    ->where('bi_book_xid', '=', $book->xid)
                    ->join('bookir_subject', 'bi_book_bi_subject.bi_subject_xid', '=', 'bookir_subject.xid')
                    ->select('bookir_subject.xsubject as title')
                    ->get();
                if ($bookSubjects != null and count($bookSubjects) > 0) {
                    foreach ($bookSubjects as $bookSubject) {
                        if (!isset($data[$bookSubject->title])) $totalSubjects += 1;

                        $data[$bookSubject->title] = (isset($data[$bookSubject->title])) ? $data[$bookSubject->title] + 1 : 1;
                    }
                }

                //                if(!isset($data[$book->xdiocode])) $totalSubjects += 1;
                //                $data[$book->xdiocode] = (isset($data[$book->xdiocode])) ? $data[$book->xdiocode] + 1 : 1;
            }

            /*foreach ($data as $key => $value)
            {
                $data[$key] = round(($value / $totalSubjects) * 100, 2);
            }*/

            //
            $data = ["label" => array_keys($data), "value" => array_values($data)];
        }

        //
        if ($data != null) $status = 200;

        // response
        return response()->json(
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["data" => $data]
            ],
            $status
        );
    }

    public function role(Request $request)
    {
        $publisherId = $request["publisherId"];
        // if(isset($_GET['publisherId'])){$publisherId =$_GET['publisherId'];}
        $data = null;
        $status = 404;

        $publisher_books = BookirPartnerrule::whereIn('xbookid', function ($query) use ($publisherId) {
            $query->select('bi_book_xid')->from('bi_book_bi_publisher')->where('bi_publisher_xid', $publisherId);
        })->get();
        // dd($publisher_books);

        if ($publisher_books->count() > 0) {
            foreach ($publisher_books as $key => $item) {
                $collection_data[$key]["id"] = $item->xid;
                $collection_data[$key]["xbookid"] = $item->xbookid;
                $collection_data[$key]["xcreatorid"] = $item->xcreatorid;
                $collection_data[$key]["xroleid"] = $item->xroleid;
            }
            $collection = collect($collection_data);
            // dd($collection);

            ///////////////////////////role///////////////////////
            $role_collection = $publisher_books->pluck('xroleid')->all();
            $role_collection =  array_unique($role_collection);
            // dd($role_collection);

            $roles_name = BookirRules::whereIn('xid', $role_collection)->get();
            $roles_name_array = $roles_name->pluck('xrole', 'xid')->all();
            // dd( $roles_name_array );

            ////////////////////creator////////////////////////////
            $creator_collection = $publisher_books->pluck('xcreatorid')->all();
            $creator_collection =  array_unique($creator_collection);

            $creators = BookirPartner::whereIn('xid', $creator_collection)->get();
            $creators_name_arary = $creators->pluck('xcreatorname', 'xid')->all();
            // dd($creators_name_arary);


            foreach ($role_collection as $key => $role_item) {
                $data[$key]['role_id'] = $role_item;
                $data[$key]['role_name'] = $roles_name_array[$role_item];

                /////////////////////////////////// role partners/////////////////////////////
                $role_creators = $collection->filter(function ($item) use ($role_item) {
                    return data_get($item, 'xroleid') == $role_item;
                });
                // dd($$role_creators);
                $role_creator_collection = $role_creators->pluck('xcreatorid')->all();
                $role_creator_collection =  array_unique($role_creator_collection);
                // dd($role_creator_collection);

                $role_creators_data = array();
                foreach ($role_creator_collection as $role_creator_collection_value) {
                    $role_creators_data[$role_creator_collection_value] = $creators_name_arary[$role_creator_collection_value];
                }
                $data[$key]['partners_count'] = count($role_creators_data);
                // dd($role_creators_data);
                foreach ($role_creators_data as $role_creators_data_key => $role_creators_data_item) {
                    $data[$key]['partners'][$role_creators_data_key]['partner_id'] = $role_creators_data_key;
                    $data[$key]['partners'][$role_creators_data_key]['partner_name'] = $role_creators_data_item;

                    ///////////////////////////////role partner book////////////////////////////////
                    $role_creators_books = $collection->filter(function ($item) use ($role_creators_data_key, $role_item) {
                        return data_get($item, 'xcreatorid') == $role_creators_data_key && data_get($item, 'xroleid') == $role_item;
                    });
                    // dd($role_creators_books);
                    $role_creators_books_array = $role_creators_books->pluck('xbookid')->all();
                    $role_creators_books_array =  array_unique($role_creators_books_array);
                    $data[$key]['partners'][$role_creators_data_key]['book_count'] = count($role_creators_books_array);
                }
            }
            // dd($data);
        }

        if ($data != null) $status = 200;
        return response()->json(
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["data" => $data]
            ],
            $status
        );
    }
}
