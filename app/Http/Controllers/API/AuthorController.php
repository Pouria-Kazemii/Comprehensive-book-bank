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

class AuthorController extends Controller
{
    // list author
    public function find(Request $request)
    {
        $name = $request["name"];
        $currentPageNumber = $request["currentPageNumber"];
        $data = null;
        $status = 404;
        $pageRows = 50;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // read books
        $authors = TblPerson::orderBy('name', 'asc');
        if($name != "") $authors->where('name', 'like', "%$name%");
        $authors = $authors->skip($offset)->take($pageRows)->get();
        if($authors != null and count($authors) > 0)
        {
            foreach ($authors as $author)
            {
                $data[] =
                    [
                        "id" => $author->id,
                        "name" => $author->name,
                    ];
            }

            $status = 200;
        }

        //
        $authors = TblPerson::orderBy('name', 'asc');
        if($name != "") $authors->where('name', 'like', "%$name%");
        $totalRows = $authors->count();
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

    // detail author
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
