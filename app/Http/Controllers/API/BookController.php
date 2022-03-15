<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BiBookBiPublisher;
use App\Models\Book30book;
use App\Models\BookDigi;
use App\Models\BookGisoom;
use App\Models\BookirBook;
use App\Models\BookirPartner;
use App\Models\BookirPartnerrule;
use App\Models\BookirPublisher;
use App\Models\BookirSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    // find
    public function find(Request $request)
    {
        return $this->lists($request);
    }

    // find by publisher
    public function findByPublisher(Request $request)
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

        return $this->lists($request, true, ($where == ""), $where);
    }

    // find by creator
    public function findByCreator(Request $request)
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

        return $this->lists($request, true, ($where == ""), $where);
    }

    // find by ver
    public function findByVer(Request $request)
    {
        $bookId = $request["bookId"];
        $where = "xid='$bookId' or xparent='$bookId'";

        return $this->lists($request, true, ($where == ""), $where);
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

        $shared_creator_books = BookirPartnerrule::select('xbookid')->where('xcreatorid', $creatorId)->whereIn('xbookid', function ($query) use ($teammateId) {
            $query->select('xbookid')->from('bookir_partnerrule')->where('xcreatorid', $teammateId);
        })->get();

        $shared_creator_books_array = $shared_creator_books->pluck('xbookid')->all();
        $shared_creator_books_array =  array_unique($shared_creator_books_array);
        $shared_creator_books_string = implode(",", $shared_creator_books_array);
        $where = ($teammateId != "" and $creatorId != "") ? "xid In ($shared_creator_books_string)" : "";
        return $this->lists($request, true, ($where == ""), $where);
    }

    // list
    public function lists(Request $request, $defaultWhere = true, $isNull = false, $where = "", $subjectTitle = "", $publisherName = "", $creatorName = "")
    {
        $name = (isset($request["name"])) ? $request["name"] : "";
        $isbn = (isset($request["isbn"])) ? str_replace("-", "", $request["isbn"]) : "";
        $currentPageNumber = (isset($request["currentPageNumber"])) ? $request["currentPageNumber"] : 0;
        $data = null;
        $status = 404;
        $pageRows = 50;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        if (!$isNull) {
            // read books
            $books = BookirBook::orderBy('xpublishdate', 'desc');
            // if ($defaultWhere) $books->whereRaw("(xparent='-1' or xparent='0')"); //$books->where('xparent', '=', '-1');//->orwhere('xparent', '=', '0');
            if ($name != "") $books->where('xname', 'like', "%$name%");
            if ($isbn != "") $books->where('xisbn2', '=', $isbn);
            if ($where != "") $books->whereRaw($where);
            $books->groupBy('xisbn');
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
                            "cover" => $book->xcover != null and $book->xcover != "null" ? $book->xcover : "",
                            "pageCount" => $book->xpagecount,
                            "isbn" => $book->xisbn,
                            "price" => priceFormat($book->xcoverprice),
                            "image" => $book->ximgeurl,
                        ];
                }
            }

            //
            $books = BookirBook::orderBy('xpublishdate', 'desc');
            // if ($defaultWhere) $books->whereRaw("(xparent='-1' or xparent='0')");
            if ($name != "") $books->where('xname', 'like', "%$name%");
            if ($isbn != "") $books->where('xisbn', '=', $isbn);
            if ($where != "") $books->whereRaw($where);
            $books->groupBy('xisbn');
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

            //
            $dataMaster =
                [
                    "isbn" => $book->xisbn,
                    "name" => $book->xname,
                    "dioCode" => $book->xdiocode,
                    "publishers" => $publishersData,
                    "subjects" => $subjectsData,
                    "creators" => $creatorsData,
                    "image" => $book->ximgeurl,
                    "publishPlace" => $book->xpublishplace,
                    "format" => $book->xformat,
                    "cover" => $book->xcover != null and $book->xcover != "null" ? $book->xcover : "",
                    "publishDate" => BookirBook::convertMiladi2Shamsi($book->xpublishdate),
                    "printNumber" => $book->xprintnumber,
                    "circulation" => $book->circulation,
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
                    // "cover" => $book->xcover != null and $book->xcover != "null" ? $book->xcover : "",
                    "cover" =>  $coversData,
                    "publishDate" => ' بین ' . BookirBook::convertMiladi2Shamsi_with_slash($min_publish_date) . ' تا ' . BookirBook::convertMiladi2Shamsi_with_slash($max_publish_date),
                    "printNumber" => $printNumber,
                    "circulation" => priceFormat($circulation),
                    "price" => ' بین ' . priceFormat($min_coverPrice) . ' تا ' . priceFormat($max_coverPrice) . ' ریال ',
                    "des" => $book_description->xdescription,
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
}
