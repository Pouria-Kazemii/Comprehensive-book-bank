<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function CheckBook()
    {
        // read --> bookk24
        $books= BookK24::where('book_master_id', '=', '0')->take(200)->get();
        if($books != null)
        {
            foreach ($books as $book)
            {
                // check book exist in TblBookMaster
                $tblBookMaster = TblBookMaster::where('shabak', '=', $book->shabak)->first();
                if($tblBookMaster != null) // exist
                {
                    if($tblBookMaster->title == "") $tblBookMaster->title = $book->title;
                    if($tblBookMaster->publisher != $book->nasher) $tblBookMaster->publisher = $tblBookMaster->publisher.",".$book->nasher;
                    if($tblBookMaster->language == "") $tblBookMaster->language = $book->lang;
                    if($tblBookMaster->category != $book->cats) $tblBookMaster->category = $tblBookMaster->category.",".$book->cats;
                    if($tblBookMaster->first_year_publication > $book->saleNashr) $tblBookMaster->first_year_publication = $book->saleNashr;
                    if($tblBookMaster->last_year_publication < $book->saleNashr) $tblBookMaster->last_year_publication = $book->saleNashr;
                    if($tblBookMaster->count_pages == 0) $tblBookMaster->count_pages = $book->tedadSafe;
                    if($tblBookMaster->book_size == "") $tblBookMaster->book_size = $book->ghatechap;
                    if($tblBookMaster->print_period_count < $book->nobatChap) $tblBookMaster->print_period_count = $book->nobatChap;
                    $tblBookMaster->print_count = $tblBookMaster->print_count + $book->printCount;
                    if($tblBookMaster->print_location == "") $tblBookMaster->print_location = $book->printLocation;
                    if($tblBookMaster->translation == 0) $tblBookMaster->translation = $book->tarjome;
                    if($tblBookMaster->desc == "") $tblBookMaster->desc = $book->desc;
                    if($tblBookMaster->image == "") $tblBookMaster->image = $book->image;
                    if($tblBookMaster->price == 0) $tblBookMaster->price = $book->price;
                    if($tblBookMaster->dio_code == "") $tblBookMaster->dio_code = $book->DioCode;
                    $resultSave = $tblBookMaster->save();
                }
                else // no exist
                {
                    $tblBookMaster = new TblBookMaster();
                    $tblBookMaster->record_number = $book->recordNumber;
                    $tblBookMaster->shabak = $book->shabak;
                    $tblBookMaster->title = $book->title;
                    $tblBookMaster->title_en = '';
                    $tblBookMaster->publisher = $book->nasher;
                    $tblBookMaster->author = '';
                    $tblBookMaster->translator = '';
                    $tblBookMaster->language = $book->lang;
                    $tblBookMaster->category = $book->cats;
                    $tblBookMaster->weight = 0;
                    $tblBookMaster->book_cover_type = '';
                    $tblBookMaster->paper_type = '';
                    $tblBookMaster->type_printing = '';
                    $tblBookMaster->editor = '';
                    $tblBookMaster->first_year_publication = $book->saleNashr;
                    $tblBookMaster->last_year_publication = $book->saleNashr;
                    $tblBookMaster->count_pages = $book->tedadSafe;
                    $tblBookMaster->book_size = $book->ghatechap;
                    $tblBookMaster->print_period_count = $book->nobatChap;
                    $tblBookMaster->print_count = $book->printCount;
                    $tblBookMaster->print_location = $book->printLocation;
                    $tblBookMaster->translation = $book->tarjome;
                    $tblBookMaster->desc = $book->desc;
                    $tblBookMaster->image = $book->image;
                    $tblBookMaster->price = $book->price;
                    $tblBookMaster->dio_code = $book->DioCode;
                    $resultSave = $tblBookMaster->save();
                }

                if($resultSave)
                {
                    $bookMasterId = $tblBookMaster->id;

                    // save bookMaster id in bookk24
                    $book->book_master_id = $bookMasterId;
                    $book->save();

                    // save category
                    $categories = $book->cats;
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
                                    $tblBookMasterCategory = TblBookMasterCategory::where('book_master_id', '=', $bookMasterId)->where('category_id', '=', $categoryId)->first();
                                    if($tblBookMasterCategory == null)
                                    {
                                        $tblBookMasterCategory = new TblBookMasterCategory();
                                        $tblBookMasterCategory->book_master_id = $bookMasterId;
                                        $tblBookMasterCategory->category_id = $categoryId;
                                        $tblBookMasterCategory->save();
                                    }
                                }
                            }
                        }
                    }

                    // save publisher
                    $publisher = $book->nasher;
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
                        $tblBookMasterPublisher = TblBookMasterPublisher::where('book_master_id', '=', $bookMasterId)->where('publisher_id', '=', $publisherId)->first();
                        if($tblBookMasterPublisher == null)
                        {
                            $tblBookMasterPublisher = new TblBookMasterPublisher();
                            $tblBookMasterPublisher->book_master_id = $bookMasterId;
                            $tblBookMasterPublisher->publisher_id = $publisherId;
                            $tblBookMasterPublisher->save();
                        }
                    }
                }
            }
        }
    }
}
