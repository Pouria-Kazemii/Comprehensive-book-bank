<?php

namespace App\Http\Controllers\Api;

use App\Helpers\BookMasterData;
use App\Http\Controllers\Controller;
use App\Models\BookDigi;
use App\Models\BookK24;
use App\Models\TblBookMaster;
use App\Models\TblBookMasterCategory;
use App\Models\TblBookMasterPublisher;
use App\Models\TblCategory;
use App\Models\TblPublisher;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // list books
    public function find(Request $request)
    {
        $currentPageNumber = $request["currentPageNumber"];
        $data = null;
        $status = 404;
        $pageRows = 20;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // read books
        $books = TblBookMaster::skip($offset)->take(20)->get();
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $data[] =
                    [
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

        $totalRows = TblBookMaster::all()->count();
        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;

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

    // read & check ---> bookk24
    public function checkBookK24()
    {
        $books = BookK24::where('book_master_id', '=', '0')->take(200)->get();
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

                // call check book
                $where = [['shabak', '=', $book->shabak]];

                $bookMasterId = $this->checkBook($bookMasterData, $where);

                // save bookMaster id in bookk24
                $book->book_master_id = $bookMasterId;
                $book->save();
            }
        }
    }

    // read & check ---> bookDigi
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

    // check & save in bookMaster
    /**
     * @param BookMasterData $bookMasterData
     * @param array $where
     * @return integer $bookMasterId
     */
    private function checkBook($bookMasterData, $where)
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
        }

        return $bookMasterId;
    }
}
