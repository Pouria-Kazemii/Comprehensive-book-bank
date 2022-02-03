<?php

namespace App\Http\Controllers\Api;

use App\Helpers\BookMasterData;
use App\Http\Controllers\Controller;
use App\Models\BiBookBiPublisher;
use App\Models\BiBookBiSubject;
use App\Models\BookDigi;
use App\Models\BookirBook;
use App\Models\BookirPublisher;
use App\Models\BookirSubject;
use App\Models\BookK24;
use App\Models\TblBookMaster;
use App\Models\TblBookMasterCategory;
use App\Models\TblBookMasterPerson;
use App\Models\TblBookMasterPublisher;
use App\Models\TblCategory;
use App\Models\TblPerson;
use App\Models\TblPublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    // list books
    public function find(Request $request)
    {
        $name = $request["name"];
        $isbn = $request["isbn"];
        $currentPageNumber = $request["currentPageNumber"];
        $data = null;
        $status = 404;
        $pageRows = 50;
        $totalPages = 0;
        $totalRows = 50;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // read books
        $books = BookirBook::orderBy('xid', 'desc')->where('xparent', '=', '-1');
        if($name != "") $books->where('xname', 'like', "%$name%");
        if($isbn != "") $books->where('xisbn', '=', $isbn);
        $books = $books->skip($offset)->take($pageRows)->get();
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $publisherNames = "";
                $subjectTitles = "";
                $wherePublisher = null;
                $whereSubject = null;

                $bookPublishers = BiBookBiPublisher::where('bi_book_xid', '=', $book->xid)->get();
                if($bookPublishers != null)
                {
                    foreach ($bookPublishers as $bookPublisher)
                    {
                        $wherePublisher[] = ['xid', '=', $bookPublisher->bi_publisher_xid];
                    }

                    $publishers = BookirPublisher::where($wherePublisher)->get();
                    if($publishers != null)
                    {
                        foreach ($publishers as $publisher)
                        {
                            $publisherNames .= $publisher->xpublishername." - ";
                        }
                    }
                    $publisherNames = rtrim($publisherNames, " - ");
                }

                $bookSubjects = BiBookBiSubject::where('bi_book_xid', '=', $book->xid)->get();
                if($bookSubjects != null)
                {
                    foreach ($bookSubjects as $bookSubject)
                    {
                        $whereSubject[] = ['xid', '=', $bookSubject->bi_subject_xid];
                    }

                    $subjects = BookirSubject::where($whereSubject)->get();
                    if($subjects != null)
                    {
                        foreach ($subjects as $subject)
                        {
                            $subjectTitles .= $subject->xsubject." - ";
                        }
                    }
                    $subjectTitles = rtrim($subjectTitles, " - ");
                }

                //
                $data[] =
                    [
                        "id" => $book->xid,
                        "name" => $book->xname,
                        "publisher" => $publisherNames,
                        "language" => $book->xlang,
                        "subject" => $subjectTitles,
                        "publishDate" => $book->xpublishdate,
                        "printNumber" => $book->xprintnumber,
                        "format" => $book->xformat,
                        "pageCount" => $book->xpagecount,
                        "isbn" => $book->xisbn,
                        "price" => $book->xcoverprice
                    ];
            }

            $status = 200;
        }

        //
        $books = BookirBook::orderBy('xid', 'desc')->where('xparent', '=', '-1');
        if($name != "") $books->where('xname', 'like', "%$name%");
        if($isbn != "") $books->where('xisbn', '=', $isbn);
        $totalRows = $books->count();
        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
            $status
        );
    }

    // list books by publisher
    public function publisherFind(Request $request)
    {
        $bookId = $request["bookId"];
        $currentPageNumber = $request["currentPageNumber"];
        $data = null;
        $status = 404;
        $pageRows = 50;
        $totalPages = 0;
        $totalRows = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // get publisher
        $publisherTitles = null;
        $bookMasterPublishers = TblBookMasterPublisher::where('book_master_id', '=', $bookId)->get();
        if($bookMasterPublishers != null)
        {
            foreach ($bookMasterPublishers as $bookMasterPublisher)
            {
                $publishers = TblPublisher::where('id', '=', $bookMasterPublisher->publisher_id)->get();
                if($publishers != null)
                {
                    foreach ($publishers as $publisher)
                    {
                        $publisherTitles[] = $publisher->title;
                    }
                }
            }
        }

        if($publisherTitles != null)
        {
            // read books
            $books = TblBookMaster::orderBy('last_year_publication', 'desc')->orderBy('first_year_publication', 'desc');
            foreach ($publisherTitles as $publisherTitle)
            {
                $books->orWhere('publisher', '=', "$publisherTitle");
            }
            $books = $books->skip($offset)->take($pageRows)->get();
            if($books != null and count($books) > 0)
            {
                foreach ($books as $book)
                {
                    $data[] =
                        [
                            "id" => $book->id,
                            "title" => $book->title,
                            "publisher" => $book->publisher,
                            "language" => $book->language,
                            "category" => $book->category,
                            "firstYearPublication" => $book->first_year_publication,
                            "lastYearPublication" => $book->last_year_publication,
                            "printPeriodCount" => $book->print_period_count,
                            "bookSize" => $book->book_size,
                            "countPages" => $book->count_pages,
                            "isbn" => $book->isbn,
                            "price" => $book->price
                        ];
                }

                $status = 200;
            }

            //
            $books = TblBookMaster::orderBy('last_year_publication', 'desc')->orderBy('first_year_publication', 'desc');
            foreach ($publisherTitles as $publisherTitle)
            {
                $books->orWhere('publisher', '=', "$publisherTitle");
            }
            $totalRows = $books->count();
            $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;
        }

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
            $status
        );
    }

    // list books by author
    public function authorFind(Request $request)
    {
        $bookId = $request["bookId"];
        $currentPageNumber = $request["currentPageNumber"];
        $data = null;
        $status = 404;
        $pageRows = 50;
        $totalPages = 0;
        $totalRows = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // get author
        $authorNames = null;
        $bookMasterPersons = TblBookMasterPerson::where('book_master_id', '=', $bookId)->where('role', '=', 'author')->get();
        if($bookMasterPersons != null)
        {
            foreach ($bookMasterPersons as $bookMasterPerson)
            {
                $persons = TblPerson::where('id', '=', $bookMasterPerson->person_id)->get();
                if($persons != null)
                {
                    foreach ($persons as $person)
                    {
                        $authorNames[] = $person->name;
                    }
                }
            }
        }

        if($authorNames != null)
        {
            // read books
            $books = TblBookMaster::orderBy('last_year_publication', 'desc')->orderBy('first_year_publication', 'desc');
            foreach ($authorNames as $authorName)
            {
                $books->orWhere('author', 'like', "%$authorName%");
            }
            $books = $books->skip($offset)->take($pageRows)->get();
            if($books != null and count($books) > 0)
            {
                foreach ($books as $book)
                {
                    $data[] =
                        [
                            "id" => $book->id,
                            "title" => $book->title,
                            "publisher" => $book->publisher,
                            "language" => $book->language,
                            "category" => $book->category,
                            "firstYearPublication" => $book->first_year_publication,
                            "lastYearPublication" => $book->last_year_publication,
                            "printPeriodCount" => $book->print_period_count,
                            "bookSize" => $book->book_size,
                            "countPages" => $book->count_pages,
                            "isbn" => $book->isbn,
                            "price" => $book->price
                        ];
                }

                $status = 200;
            }

            //
            $books = TblBookMaster::orderBy('last_year_publication', 'desc')->orderBy('first_year_publication', 'desc');
            foreach ($authorNames as $authorName)
            {
                $books->orWhere('author', 'like', "%$authorName%");
            }
            $totalRows = $books->count();
            $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;
        }

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
            $status
        );
    }

    // detail book
    public function detail(Request $request)
    {
        $bookId = $request["bookId"];
        $dataMaster = null;
        $data = null;
        $chartData = null;
        $chartLabelData = null;
        $chartValueData = null;
        $printData = null;

        // read books
        $book = TblBookMaster::where('id', '=', $bookId)->first();
        if($book != null and $book->id > 0)
        {
            $dataMaster =
                [
                    "id" => $book->id,
                    "title" => $book->title,
                    "titleEn" => $book->title_en,
                    "publisher" => $book->publisher,
                    "author" => $book->author,
                    "translator" => $book->translator,
                    "language" => $book->language,
                    "category" => $book->category,
                    "weight" => $book->weight,
                    "bookCoverType" => $book->book_cover_type,
                    "paperType" => $book->paper_type,
                    "typePrinting" => $book->type_printing,
                    "editor" => $book->editor,
                    "firstYearPublication" => $book->first_year_publication,
                    "lastYearPublication" => $book->last_year_publication,
                    "printPeriodCount" => $book->print_period_count,
                    "bookSize" => $book->book_size,
                    "countPages" => $book->count_pages,
                    "printCount" => $book->print_count,
                    "printLocation" => $book->print_location,
                    "isbn" => $book->isbn,
                    "price" => $book->price,
                    "dioCode" => $book->dio_code,
                    "image" => $book->image,
                ];
        }

        // read books
        $books = BookK24::where('book_master_id', '=', $bookId)->get();
        if($books != null and count($books) > 0)
        {
            $dataTemp = null;

            foreach ($books as $book)
            {
                $dataTemp[] =
                    [
                        "title" => $book->title,
                        "titleEn" => '',
                        "publisher" => $book->nasher,
                        "author" => '',
                        "translator" => '',
                        "language" => $book->lang,
                        "category" => $book->cats,
                        "weight" => '',
                        "bookCoverType" => '',
                        "paperType" => '',
                        "typePrinting" => '',
                        "editor" => '',
                        "yearPublication" => $book->saleNashr,
                        "printPeriodCount" => $book->nobatChap,
                        "bookSize" => $book->ghatechap,
                        "countPages" => $book->tedadSafe,
                        "printCount" => $book->printCount,
                        "printLocation" => $book->printLocation,
                        "isbn" => $book->isbn,
                        "price" => $book->price,
                        "dioCode" => $book->DioCode,
                    ];

                if(isset($printData[md5($book->saleNashr)]))
                    $printData[md5($book->saleNashr)] = ["year" => $book->saleNashr, "printCount" => $book->printCount + $printData[md5($book->saleNashr)]["printCount"]];
                else
                    $printData[md5($book->saleNashr)] = ["year" => $book->saleNashr, "printCount" => $book->printCount];
            }

            $data[] = ["bookSource" => "ketab.ir", "books" => $dataTemp];
        }

        // chart data
        if($printData != null and count($printData) > 0)
        {
            foreach ($printData as $publisher)
            {
                $chartLabelData[] = $publisher["year"];
                $chartValueData[] = $publisher["printCount"];
            }
        }

        $chartData = ["label" => $chartLabelData, "value" => $chartValueData];

        //
        if($dataMaster != null and $data != null) $status = 200;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["master" => $dataMaster, "list" => $data, "chart" => $chartData]
            ],
            $status
        );
    }

    // dossier book
    public function dossier(Request $request)
    {
        $bookId = $request["bookId"];
        $dataMaster = null;
        $data = null;
        $chartData = null;
        $chartLabelData = null;
        $chartValueData = null;
        $publisherData = null;
        $totalPrintCount = 0;

        // read books
        $book = TblBookMaster::where('id', '=', $bookId)->first();
        if($book != null and $book->id > 0)
        {
            $dataMaster =
                [
                    "id" => $book->id,
                    "title" => $book->title,
                    "titleEn" => $book->title_en,
                    "publisher" => $book->publisher,
                    "author" => $book->author,
                    "translator" => $book->translator,
                    "language" => $book->language,
                    "category" => $book->category,
                    "weight" => $book->weight,
                    "bookCoverType" => $book->book_cover_type,
                    "paperType" => $book->paper_type,
                    "typePrinting" => $book->type_printing,
                    "editor" => $book->editor,
                    "firstYearPublication" => $book->first_year_publication,
                    "lastYearPublication" => $book->last_year_publication,
                    "printPeriodCount" => $book->print_period_count,
                    "bookSize" => $book->book_size,
                    "countPages" => $book->count_pages,
                    "printCount" => $book->print_count,
                    "printLocation" => $book->print_location,
                    "isbn" => $book->isbn,
                    "price" => $book->price,
                    "dioCode" => $book->dio_code,
                    "image" => $book->image,
                ];
        }

        // read books
        $books = BookK24::where('book_master_id', '=', $bookId)->get();
        if($books != null and count($books) > 0)
        {
            $dataTemp = null;

            foreach ($books as $book)
            {
                $dataTemp[] =
                    [
                        "title" => $book->title,
                        "titleEn" => '',
                        "publisher" => $book->nasher,
                        "author" => '',
                        "translator" => '',
                        "language" => $book->lang,
                        "category" => $book->cats,
                        "weight" => '',
                        "bookCoverType" => '',
                        "paperType" => '',
                        "typePrinting" => '',
                        "editor" => '',
                        "yearPublication" => $book->saleNashr,
                        "printPeriodCount" => $book->nobatChap,
                        "bookSize" => $book->ghatechap,
                        "countPages" => $book->tedadSafe,
                        "printCount" => $book->printCount,
                        "printLocation" => $book->printLocation,
                        "isbn" => $book->isbn,
                        "price" => $book->price,
                        "dioCode" => $book->DioCode,
                    ];

                if(isset($publisherData[md5($book->nasher)]))
                    $publisherData[md5($book->nasher)] = ["name" => $book->nasher, "printCount" => $book->printCount + $publisherData[md5($book->nasher)]["printCount"]];
                else
                    $publisherData[md5($book->nasher)] = ["name" => $book->nasher, "printCount" => $book->printCount];

                $totalPrintCount += $book->printCount;
            }

            $data[] = ["bookSource" => "ketab.ir", "books" => $dataTemp];
        }

        $data[] = ["bookSource" => "DigiKala.com", "books" => null];
        $data[] = ["bookSource" => "30Book.com", "books" => null];
        $data[] = ["bookSource" => "Gisoom.com", "books" => null];
        $data[] = ["bookSource" => "IranKetab.ir", "books" => null];

        // chart data
        if($publisherData != null and count($publisherData) > 0)
        {
            foreach ($publisherData as $publisher)
            {
                $chartLabelData[] = $publisher["name"];
                $chartValueData[] = round(($publisher["printCount"] / $totalPrintCount) * 100, 2);
            }
        }

        $chartData = ["label" => $chartLabelData, "value" => $chartValueData];

        //
        if($dataMaster != null and $data != null) $status = 200;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["master" => $dataMaster, "list" => $data, "chart" => $chartData]
            ],
            $status
        );
    }
}
