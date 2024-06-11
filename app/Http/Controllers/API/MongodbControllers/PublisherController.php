<?php

namespace App\Http\Controllers\API\MongodbControllers;

use App\Http\Controllers\Controller;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrPublisher;
use Illuminate\Http\Request;

class PublisherController extends Controller
{
    ///////////////////////////////////////////////General///////////////////////////////////////////////////
    public function lists(Request $request, $defaultWhere = true, $isNull = false, $where = [], $creatorId = "")
    {
        $start = $start = microtime(true);
        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"] : "xpublisher_name";
        $sortDirection = 1;
//        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? (int)$request["sortDirection"] : 1;
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? (int)$request["page"] : 1;
        $pageRows = (isset($request["perPage"]) && !empty($request["perPage"])) ? (int)$request["perPage"] : 50;
        $offset = ($currentPageNumber - 1) * $pageRows;
        $data = [];
        $status = 404;

        if (!$isNull) {
            // Initialize query
            $publishersQuery = BookIrPublisher::query();

            if (!empty($searchText)) {
                $publishersQuery->where(['$text' => ['$search' => $searchText]]);
            }

            if (!$defaultWhere && !empty($where)) {
                if (count($where) > 0) {
                    if (count($where[0]) == 2) {
                        $publishersQuery->where(function ($query) use ($where) {
                            $query->where($where[0][0], $where[0][1]); // Apply the first condition using where()
                            // Apply subsequent conditions using orWhere()
                            for ($i = 1; $i < count($where); $i++) {
                                $query->orWhere($where[$i][0], $where[$i][1]);
                            }
                        });
                    };
                }
            }

            // Order by clause
            $publishersQuery->orderBy($column, $sortDirection);

            $totalRows = $publishersQuery->count();
            $totalPages = $totalRows > 0 ? (int)ceil($totalRows / $pageRows) : 0;

            $publishers = $publishersQuery->skip($offset)->take($pageRows)->get();

            if ($publishers->isNotEmpty()) {
                foreach ($publishers as $publisher) {
                    $data[] = [
                        "id" => $publisher->_id,
                        "name" => $publisher->xpublishername,
                    ];
                }
                $status = 200;
            }
        }
        $end = microtime(true);
        $elapsedTime = $end - $start;

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
        $publishers = BookIrBook2::where('partners.xcreator_id', $creatorId)->pluck('publisher');
        $where = [];

        foreach ($publishers as $publisher) {
            foreach ($publisher as $key => $value) {
                $where[] = ['_id', $value['xpublisher_id']];
            }
        }
        return $this->lists($request, false, ($where == []), $where, $creatorId);
    }

    ///////////////////////////////////////////////Subject///////////////////////////////////////////////////
    public function findBySubject(Request $request)
    {
        $subjectId = $request["subjectId"];
        $publishers = BookIrBook2::where('subjects.xsubject_id', (int)$subjectId)->pluck('publisher');
        $where = [];

        foreach ($publishers as $publisher) {
            foreach ($publisher as $key => $value) {
                $where[] = ['_id', $value['xpublisher_id']];
            }
        }
        return $this->lists($request, false, ($where == []), $where);
    }

    ///////////////////////////////////////////////Search///////////////////////////////////////////////////
    public function search(Request $request)
    {
        $searchWord = (isset($request["searchWord"])) ? $request["searchWord"] : "";
        $data = null;
        $status = 404;

        // read
        $publishers = BookIrPublisher::where('xpublishername', '!=', '')->where('xpublishername', 'like', "%$searchWord%")->orderBy('xpublishername', 1)->get();
        if ($publishers != null and count($publishers) > 0) {
            foreach ($publishers as $publisher) {
                $data[] =
                    [
                        "id" => $publisher->_id,
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

    ///////////////////////////////////////////////Detail///////////////////////////////////////////////////
    public function detail(Request $request)
    {
        $publisherId = $request["publisherId"];
        $dataMaster = null;
        $status = 404;

        // read
        $publisher = BookIrPublisher::where('_id', $publisherId)->first();
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

    ///////////////////////////////////////////////Annual Activity By Title///////////////////////////////////////////////////
    public function annualActivityByTitle(Request $request)
    {
        $publisherId = $request["publisherId"];
        $yearPrintCountData = null;
        $status = 404;

        // read books for year printCount by title
        $books = BookIrBook2::where('publisher.xpublisher_id', $publisherId)->orderBy('xpublishdate_shamsi', 'asc')->get();
        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $year = $book->xpublishdate_shamsi;
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

    ///////////////////////////////////////////////Annual Activity By Circulation///////////////////////////////////////////////////
    public function annualActivityByCirculation(Request $request)
    {
        $publisherId = $request["publisherId"];
        $yearPrintCountData = null;
        $status = 404;

        // read books for year printCount by circulation
        $books = BookIrBook2::where('publisher.xpublisher_id', $publisherId)->orderBy('xpublishdate_shamsi', 'asc')->get();
        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $year = $book->xpublishdate_shamsi;
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

    ///////////////////////////////////////////////Translate Authorship///////////////////////////////////////////////////
    public function translateAuthorship(Request $request)
    {
        $publisherId = $request["publisherId"];
        $data = null;
        $status = 404;

        // read books
        $books = BookIrBook2::where('publisher.xpublisher_id', $publisherId)->orderBy('xpublishdate_shamsi', 'asc')->get();
        if ($books != null and count($books) > 0) {
            $totalBooks = count($books);
            $data["authorship"] = 0;
            $data["translate"] = 0;

            foreach ($books as $book) {
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

    ///////////////////////////////////////////////Statistic Subject///////////////////////////////////////////////////
    public function statisticSubject(Request $request)
    {
        $publisherId = $request["publisherId"];
        $data = null;
        $status = 404;
        $totalSubjects = 0;

        // read books
        $books = BookIrBook2::where('publisher.xpublisher_id', $publisherId)->orderBy('xpublishdate_shamsi', 'asc')->get();
        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $bookSubjects = $book->subjects;
                if ($bookSubjects != null and count($bookSubjects) > 0) {
                    foreach ($bookSubjects as $bookSubject) {
                        if (!isset($data[$bookSubject['xsubject_name']])) $totalSubjects += 1;
                        $data[$bookSubject['xsubject_name']] = (isset($data[$bookSubject['xsubject_name']])) ? $data[$bookSubject['xsubject_name']] + 1 : 1;
                    }
                }
            }
            arsort($data);
            $data = ["label" => array_keys($data), "value" => array_values($data)];
        }

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

    ///////////////////////////////////////////////Publisher Role///////////////////////////////////////////////////
    public function publisherRole(Request $request)
    {
        $publisherId = $request["publisherId"];
        $data = null;
        $status = 404;
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
