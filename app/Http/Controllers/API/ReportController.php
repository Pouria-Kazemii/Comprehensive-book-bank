<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookirBook;
use App\Models\BookirPartner;
use App\Models\BookirPublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{


    // publisher
    public function publisher(Request $request)
    {
        $publisherId = (isset($request["publisherId"])) ? $request["publisherId"] : 0;
        $yearStart = (isset($request["yearStart"])) ? $request["yearStart"] : 0;
        $yearEnd = (isset($request["yearEnd"])) ? $request["yearEnd"] : 0;
        $currentPageNumber = (isset($request["currentPageNumber"])) ? $request["currentPageNumber"] : 0;
        $data = null;
        $status = 404;
        $pageRows = 50;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;

        // read
        $books = BookirBook::orderBy('xpublishdate', 'desc');
        $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");

        $totalRows = $books->count(); // get count records
        $books = $books->skip($offset)->take($pageRows)->get(); // get list

        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $dioCode = $book->xdiocode;
                $creators = DB::table('bookir_partnerrule')
                    ->where('xbookid', '=', $book->xid)->where('xroleid', '=', '1')
                    ->join('bookir_partner', 'bookir_partnerrule.xroleid', '=', 'bookir_partner.xid')
                    ->select('bookir_partner.xcreatorname as name')
                    ->get();

                $data[$dioCode] = array
                (
                    "creator" => (isset($data[$dioCode]) and $data[$dioCode]["creator"] != null) ? array_merge($data[$dioCode]["creator"], $creators) : $creators,
                    "translate" => $book->xlang == "فارسی" ? 0 : 1,
                    "circulation" => $book->xcirculation + ((isset($data[$dioCode])) ? $data[$dioCode]["circulation"] : 0),
                    "dio" => $dioCode,
                );
            }
        }

        //
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


}
