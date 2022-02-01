<?php

namespace App\Http\Controllers\Api;

use App\Helpers\BookMasterData;
use App\Http\Controllers\Controller;
use App\Models\BookDigi;
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

class PublisherController extends Controller
{
    // list publisher
    public function find(Request $request)
    {
        $title = $request["title"];
        $currentPageNumber = $request["currentPageNumber"];
        $data = null;
        $status = 404;
        $pageRows = 50;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // read books
        $publishers = TblPublisher::orderBy('title', 'asc');
        if($title != "") $publishers->where('title', 'like', "%$title%");
        $publishers = $publishers->skip($offset)->take($pageRows)->get();
        if($publishers != null and count($publishers) > 0)
        {
            foreach ($publishers as $publisher)
            {
                $data[] =
                    [
                        "id" => $publisher->id,
                        "title" => $publisher->title,
                    ];
            }

            $status = 200;
        }

        //
        $publishers = TblPublisher::orderBy('title', 'asc');
        if($title != "") $publishers->where('title', 'like', "%$title%");
        $totalRows = $publishers->count();
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

    // detail publisher
    public function detail(Request $request)
    {
        $bookId = $request["bookId"];
        $dataMaster = null;
        $data = null;

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
            }

            $data[] = ["bookSource" => "ketab.ir", "books" => $dataTemp];
        }

        if($dataMaster != null and $data != null) $status = 200;

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["master" => $dataMaster, "list" => $data]
            ],
            $status
        );
    }
}
