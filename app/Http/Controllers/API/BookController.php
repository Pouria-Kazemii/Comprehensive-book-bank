<?php

namespace App\Http\Controllers\Api;

use App\Helpers\BookMasterData;
use App\Http\Controllers\Controller;
use App\Models\BiBookBiPublisher;
use App\Models\BookDigi;
use App\Models\BookirBook;
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
        $title = $request["title"];
        $shabak = $request["shabak"];
        $currentPageNumber = $request["currentPageNumber"];
        $data = null;
        $status = 404;
        $pageRows = 50;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // read books
        $books = TblBookMaster::orderBy('last_year_publication', 'desc')->orderBy('first_year_publication', 'desc');
        if($title != "") $books->where('title', 'like', "%$title%");
        if($shabak != "") $books->where('shabak', '=', $shabak);
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
                        "shabak" => $book->shabak,
                        "price" => $book->price
                    ];
            }

            $status = 200;
        }

//        $totalRows = TblBookMaster::count();
        $books = TblBookMaster::orderBy('last_year_publication', 'desc')->orderBy('first_year_publication', 'desc');
        if($title != "") $books->where('title', 'like', "%$title%");
        if($shabak != "") $books->where('shabak', '=', $shabak);
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
                            "shabak" => $book->shabak,
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
                            "shabak" => $book->shabak,
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
                    "shabak" => $book->shabak,
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
                        "shabak" => $book->shabak,
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
                    "shabak" => $book->shabak,
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
                        "shabak" => $book->shabak,
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

    // read & check
    public function checkBook()
    {
        $books = BookirBook::where('xparent', '=', '0')->take(10)->get();
        if($books != null)
        {
            foreach ($books as $book)
            {
                $id = $book->xid;
                $isbn = $book->xisbn;
                $isbn2 = $book->xisbn2;
                $name = $book->xname;
                $isPublisher = $book->xispublisher;
                $publisherIds = null;

                $bookBiPublishers = BiBookBiPublisher::where('bi_book_xid', '=', $id)->get();
                if($bookBiPublishers != null)
                {
                    foreach ($bookBiPublishers as $bookBiPublisher)
                    {
                        $publisherIds[] = $bookBiPublisher->bi_publisher_xid;
                    }
                }

//                $books = BookirBook::where('xparent', '=', '0')->where('xid', '!=', $id)->where('xisbn', '=', $isbn)->orwhere('xisbn2', '=', $isbn2)->get();
                $similarBooks = BookirBook::whereRaw("xid!='$id' and xparent='0' and (xisbn='$isbn' or xisbn2='$isbn2')")->get();

print_r($similarBooks);

                /*
                $bookMasterData = new BookMasterData();
                $bookMasterData->record_number = $book->recordNumber;
                $bookMasterData->shabak = $book->shabak;
                $bookMasterData->title = $book->title;
                $bookMasterData->title_en = '';
                $bookMasterData->publisher = $book->nasher;
                $bookMasterData->author = '';
                $bookMasterData->translator = '';
                $bookMasterData->language = $book->lang;
                $bookMasterData->category = $book->cats;
                $bookMasterData->weight = 0;
                $bookMasterData->book_cover_type = '';
                $bookMasterData->paper_type = '';
                $bookMasterData->type_printing = '';
                $bookMasterData->editor = '';
                $bookMasterData->first_year_publication = $book->saleNashr;
                $bookMasterData->last_year_publication = $book->saleNashr;
                $bookMasterData->count_pages = $book->tedadSafe;
                $bookMasterData->book_size = $book->ghatechap;
                $bookMasterData->print_period_count = $book->nobatChap;
                $bookMasterData->print_count = $book->printCount;
                $bookMasterData->print_location = $book->printLocation;
                $bookMasterData->translation = $book->tarjome;
                $bookMasterData->desc = $book->desc;
                $bookMasterData->image = $book->image;
                $bookMasterData->price = $book->price;
                $bookMasterData->dio_code = $book->DioCode;

                // find authors
                $authorsData = null;
                $whereAuthors = null;

                $authorsK24 = DB::table('author_book_k24')->where('book_k24_id', '=', $book->id)->get();
                if($authorsK24 != null and count($authorsK24) > 0)
                {
                    foreach ($authorsK24 as $authorK24) $whereAuthors[] = ['id', '=', $authorK24->author_id];

                    if($whereAuthors != null)
                    {
                        $authors = DB::table('author')->where($whereAuthors)->get();
                        if($authors != null and count($authors) > 0)
                        {
                            foreach ($authors as $author)
                            {
                                $authorsData[] = $author->d_name;
                            }
                        }
                    }
                }

                // call check book
                $where = [['shabak', '=', $book->shabak]];

                $bookMasterId = $this->checkBook($bookMasterData, $authorsData, $where);

                // save bookMaster id in bookk24
                $book->tmp_author = 1;
                $book->book_master_id = $bookMasterId;
                $book->save();
                */
            }
        }
    }

    // read & check ---> bookk24
    /*
    public function checkBookK24()
    {
        $books = BookK24::where('book_master_id', '=', '0')->take(10)->get();
        if($books != null)
        {
            foreach ($books as $book)
            {
                $bookMasterData = new BookMasterData();
                $bookMasterData->record_number = $book->recordNumber;
                $bookMasterData->shabak = $book->shabak;
                $bookMasterData->title = $book->title;
                $bookMasterData->title_en = '';
                $bookMasterData->publisher = $book->nasher;
                $bookMasterData->author = '';
                $bookMasterData->translator = '';
                $bookMasterData->language = $book->lang;
                $bookMasterData->category = $book->cats;
                $bookMasterData->weight = 0;
                $bookMasterData->book_cover_type = '';
                $bookMasterData->paper_type = '';
                $bookMasterData->type_printing = '';
                $bookMasterData->editor = '';
                $bookMasterData->first_year_publication = $book->saleNashr;
                $bookMasterData->last_year_publication = $book->saleNashr;
                $bookMasterData->count_pages = $book->tedadSafe;
                $bookMasterData->book_size = $book->ghatechap;
                $bookMasterData->print_period_count = $book->nobatChap;
                $bookMasterData->print_count = $book->printCount;
                $bookMasterData->print_location = $book->printLocation;
                $bookMasterData->translation = $book->tarjome;
                $bookMasterData->desc = $book->desc;
                $bookMasterData->image = $book->image;
                $bookMasterData->price = $book->price;
                $bookMasterData->dio_code = $book->DioCode;

                // find authors
                $authorsData = null;
                $whereAuthors = null;

                $authorsK24 = DB::table('author_book_k24')->where('book_k24_id', '=', $book->id)->get();
                if($authorsK24 != null and count($authorsK24) > 0)
                {
                    foreach ($authorsK24 as $authorK24) $whereAuthors[] = ['id', '=', $authorK24->author_id];

                    if($whereAuthors != null)
                    {
                        $authors = DB::table('author')->where($whereAuthors)->get();
                        if($authors != null and count($authors) > 0)
                        {
                            foreach ($authors as $author)
                            {
                                $authorsData[] = $author->d_name;
                            }
                        }
                    }
                }

                // call check book
                $where = [['shabak', '=', $book->shabak]];

                $bookMasterId = $this->checkBook($bookMasterData, $authorsData, $where);

                // save bookMaster id in bookk24
                $book->tmp_author = 1;
                $book->book_master_id = $bookMasterId;
                $book->save();
            }
        }

        // temp set author
        $books = BookK24::where('tmp_author', '=', '0')->where('book_master_id', '!=', '0')->take(5)->get();
        if($books != null)
        {
            foreach ($books as $book)
            {
                $bookId = $book->id;
                $bookMasterId = $book->book_master_id;
                $whereAuthors = null;
                $authorNames = "";

                $authorsK24 = DB::table('author_book_k24')->where('book_k24_id', '=', $bookId)->get();
                if($authorsK24 != null and count($authorsK24) > 0)
                {
                    foreach ($authorsK24 as $authorK24) $whereAuthors[] = ['id', '=', $authorK24->author_id];

                    if($whereAuthors != null)
                    {
                        $authors = DB::table('author')->where($whereAuthors)->get();
                        if($authors != null and count($authors) > 0)
                        {
                            foreach ($authors as $author)
                            {
//                                echo $author->d_name."<br>";

                                // save in tblPerson
                                $person = TblPerson::where('name', '=', $author->d_name)->first();
                                if($person != null)
                                {
                                    $personId = $person->id;
                                }
                                else
                                {
                                    $person = new TblPerson();
                                    $person->name = $author->d_name;
                                    $person->save();

                                    $personId = $person->id;
                                }

                                // save in tblBookMasterPerson
                                if($personId > 0)
                                {
                                    $bookMasterPerson = TblBookMasterPerson::where('book_master_id', '=', $bookMasterId)->where('person_id', '=', $personId)->first();
                                    if($bookMasterPerson == null)
                                    {
                                        $bookMasterPerson = new TblBookMasterPerson();
                                        $bookMasterPerson->book_master_id = $bookMasterId;
                                        $bookMasterPerson->person_id = $personId;
                                        $bookMasterPerson->role = 'author';
                                        $bookMasterPerson->save();

                                        $authorNames .= $author->d_name." - ";
                                    }
                                }
                            }
                        }
                    }
                }

                //
                $book->tmp_author = 1;
                $book->save();

                // save in book master
                if($authorNames != "")
                {
                    $bookMaster = TblBookMaster::where('id', '=', $bookMasterId)->first();
                    if($bookMaster != null and $bookMaster->id > 0)
                    {
                        $bookMaster->author = $bookMaster->author != "" ? $authorNames.$bookMaster->author : rtrim($authorNames, ' - ');
                        $bookMaster->save();
                    }
                }

//                echo "<hr>";
            }
        }
    }
    */

    // read & check ---> bookDigi
    /*
    public function checkBookDigi()
    {
        $books = BookDigi::where('book_master_id', '=', '0')->where('shabak', '!=', '')->take(200)->get();
        if($books != null)
        {
            foreach ($books as $book)
            {
                $bookMasterData = new BookMasterData();
                $bookMasterData->record_number = $book->recordNumber;
                $bookMasterData->shabak = faCharToEN(str_replace("-", "", $book->shabak));
                $bookMasterData->title = $book->title;
                $bookMasterData->title_en = '';
                $bookMasterData->publisher = $book->nasher;
                $bookMasterData->author = '';
                $bookMasterData->translator = '';
                $bookMasterData->language = '';
                $bookMasterData->category = $book->cat;
                $bookMasterData->weight = $book->vazn;
                $bookMasterData->book_cover_type = $book->jeld;
                $bookMasterData->paper_type = $book->noekaghaz;
                $bookMasterData->type_printing = $book->noechap;
                $bookMasterData->editor = '';
                $bookMasterData->first_year_publication = 0;
                $bookMasterData->last_year_publication = 0;
                $bookMasterData->count_pages = $book->tedadSafe;
                $bookMasterData->book_size = $book->ghatechap;
                $bookMasterData->print_period_count = 0;
                $bookMasterData->print_count = $book->count;
                $bookMasterData->print_location = '';
                $bookMasterData->translation = '';
                $bookMasterData->desc = $book->desc;
                $bookMasterData->image = $book->images;
                $bookMasterData->price = $book->price;
                $bookMasterData->dio_code = '';

                // call check book
                $where = [['shabak', '=', $book->shabak]];

                $bookMasterId = $this->checkBook($bookMasterData, $where);

                // save bookMaster id in bookk24
                $book->book_master_id = $bookMasterId;
                $book->save();
            }
        }
    }
    */

    // check & save in bookMaster
    /**
     * @param BookMasterData $bookMasterData
     * @param array $authorsData
     * @param array $where
     * @return integer $bookMasterId
     */
    /*
    private function checkBook($bookMasterData, $authorsData, $where)
    {
        $bookMasterId = 0;

        // check book exist in TblBookMaster
        $bookMaster = TblBookMaster::where($where)->first();
        if($bookMaster != null) // exist
        {
            if($bookMaster->title == "") $bookMaster->title = $bookMasterData->title;
            if($bookMaster->publisher != $bookMasterData->publisher) $bookMaster->publisher = $bookMaster->publisher.",".$bookMasterData->publisher;
            if($bookMaster->language == "") $bookMaster->language = $bookMasterData->language;
            if($bookMaster->category != $bookMasterData->category) $bookMaster->category = $bookMaster->category.",".$bookMasterData->category;
            if($bookMaster->first_year_publication > $bookMasterData->first_year_publication) $bookMaster->first_year_publication = $bookMasterData->first_year_publication;
            if($bookMaster->last_year_publication < $bookMasterData->last_year_publication) $bookMaster->last_year_publication = $bookMasterData->last_year_publication;
            if($bookMaster->count_pages == 0) $bookMaster->count_pages = $bookMasterData->count_pages;
            if($bookMaster->book_size == "") $bookMaster->book_size = $bookMasterData->book_size;
            if($bookMaster->print_period_count < $bookMasterData->print_period_count) $bookMaster->print_period_count = $bookMasterData->print_period_count;
            $bookMaster->print_count = $bookMaster->print_count + $bookMasterData->print_count;
            if($bookMaster->print_location == "") $bookMaster->print_location = $bookMasterData->print_location;
            if($bookMaster->translation == 0) $bookMaster->translation = $bookMasterData->translation;
            if($bookMaster->desc == "") $bookMaster->desc = $bookMasterData->desc;
            if($bookMaster->image == "") $bookMaster->image = $bookMasterData->image;
            if($bookMaster->price == 0) $bookMaster->price = $bookMasterData->price;
            if($bookMaster->dio_code == "") $bookMaster->dio_code = $bookMasterData->dio_code;
            $resultSave = $bookMaster->save();
        }
        else // no exist
        {
            $bookMaster = new TblBookMaster();
            $bookMaster->record_number = $bookMasterData->record_number;
            $bookMaster->shabak = $bookMasterData->shabak;
            $bookMaster->title = $bookMasterData->title;
            $bookMaster->title_en = $bookMasterData->title_en;
            $bookMaster->publisher = $bookMasterData->publisher;
            $bookMaster->author = $bookMasterData->author;
            $bookMaster->translator = $bookMasterData->translator;
            $bookMaster->language = $bookMasterData->language;
            $bookMaster->category = $bookMasterData->category;
            $bookMaster->weight = $bookMasterData->weight;
            $bookMaster->book_cover_type = $bookMasterData->book_cover_type;
            $bookMaster->paper_type = $bookMasterData->paper_type;
            $bookMaster->type_printing = $bookMasterData->type_printing;
            $bookMaster->editor = $bookMasterData->editor;
            $bookMaster->first_year_publication = $bookMasterData->first_year_publication;
            $bookMaster->last_year_publication = $bookMasterData->last_year_publication;
            $bookMaster->count_pages = $bookMasterData->count_pages;
            $bookMaster->book_size = $bookMasterData->book_size;
            $bookMaster->print_period_count = $bookMasterData->print_period_count;
            $bookMaster->print_count = $bookMasterData->print_count;
            $bookMaster->print_location = $bookMasterData->print_location;
            $bookMaster->translation = $bookMasterData->translation;
            $bookMaster->desc = $bookMasterData->desc;
            $bookMaster->image = $bookMasterData->image;
            $bookMaster->price = $bookMasterData->price;
            $bookMaster->dio_code = $bookMasterData->dio_code;
            $resultSave = $bookMaster->save();
        }

        if($resultSave)
        {
            $bookMasterId = $bookMaster->id;
            $authorNames = "";

            // save category
            $categories = $bookMasterData->category;
            $categories = explode("-", $categories);

            if($categories != null and count($categories) > 0)
            {
                foreach($categories as $categoryTitle)
                {
                    $categoryTitle = trim($categoryTitle);
                    if($categoryTitle != "" and $categoryTitle != "|")
                    {
                        $tblCategory = TblCategory::where('title', '=', $categoryTitle)->first();
                        if($tblCategory != null)
                        {
                            $categoryId = $tblCategory->id;
                        }
                        else
                        {
                            $tblCategory = new TblCategory();
                            $tblCategory->title = $categoryTitle;
                            $resultSave = $tblCategory->save();
                            if($resultSave) $categoryId = $tblCategory->id;
                        }

                        if($categoryId > 0)
                        {
                            $bookMasterCategory = TblBookMasterCategory::where('book_master_id', '=', $bookMasterId)->where('category_id', '=', $categoryId)->first();
                            if($bookMasterCategory == null)
                            {
                                $bookMasterCategory = new TblBookMasterCategory();
                                $bookMasterCategory->book_master_id = $bookMasterId;
                                $bookMasterCategory->category_id = $categoryId;
                                $bookMasterCategory->save();
                            }
                        }
                    }
                }
            }

            // save publisher
            $publisher = $bookMasterData->publisher;
            if($publisher != '')
            {
                $tblPublisher = TblPublisher::where('title', '=', $publisher)->first();
                if($tblPublisher != null)
                {
                    $publisherId = $tblPublisher->id;
                }
                else
                {
                    $tblPublisher = new TblPublisher();
                    $tblPublisher->title = $publisher;
                    $resultSave = $tblPublisher->save();
                    if($resultSave) $publisherId = $tblPublisher->id;
                }

                if($publisherId > 0)
                {
                    $bookMasterPublisher = TblBookMasterPublisher::where('book_master_id', '=', $bookMasterId)->where('publisher_id', '=', $publisherId)->first();
                    if($bookMasterPublisher == null)
                    {
                        $bookMasterPublisher = new TblBookMasterPublisher();
                        $bookMasterPublisher->book_master_id = $bookMasterId;
                        $bookMasterPublisher->publisher_id = $publisherId;
                        $bookMasterPublisher->save();
                    }
                }
            }

            // authors
            if($authorsData != null and count($authorsData) > 0)
            {
                foreach ($authorsData as $authorName)
                {
                    // save in tblPerson
                    $person = TblPerson::where('name', '=', $authorName)->first();
                    if($person != null)
                    {
                        $personId = $person->id;
                    }
                    else
                    {
                        $person = new TblPerson();
                        $person->name = $authorName;
                        $person->save();

                        $personId = $person->id;
                    }

                    // save in tblBookMasterPerson
                    if($personId > 0)
                    {
                        $bookMasterPerson = TblBookMasterPerson::where('book_master_id', '=', $bookMasterId)->where('person_id', '=', $personId)->first();
                        if($bookMasterPerson == null)
                        {
                            $bookMasterPerson = new TblBookMasterPerson();
                            $bookMasterPerson->book_master_id = $bookMasterId;
                            $bookMasterPerson->person_id = $personId;
                            $bookMasterPerson->role = 'author';
                            $bookMasterPerson->save();

                            $authorNames .= $authorName." - ";
                        }
                    }
                }
            }

            // save in book master
            if($authorNames != "")
            {
                $bookMaster->author = $bookMaster->author != "" ? $authorNames.$bookMaster->author : rtrim($authorNames, ' - ');
                $bookMaster->save();
            }
        }

        return $bookMasterId;
    }
    */
}
