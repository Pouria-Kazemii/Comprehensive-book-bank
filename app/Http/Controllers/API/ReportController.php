<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookirBook;
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
        $data = null;
        $status = 404;

        $yearStart = ($yearStart > 0) ? BookirBook::generateMiladiDate($yearStart) : "";
        $yearEnd = ($yearEnd > 0) ? BookirBook::generateMiladiDate($yearEnd, true) : "";

        // read
        $books = BookirBook::orderBy('xpublishdate', 'desc');
        $books->whereRaw("xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')");
        if($yearStart != "") $books->where("xpublishdate", ">=", "$yearStart");
        if($yearEnd != "") $books->where("xpublishdate", "<=", "$yearEnd");
        $books = $books->get(); // get list
        if($books != null and count($books) > 0)
        {
            foreach ($books as $book)
            {
                $dioCode = $book->xdiocode;
                $creatorsData = null;

                $creators = DB::table('bookir_partnerrule')
                    ->where('xbookid', '=', $book->xid)->where('xroleid', '=', '1')
                    ->join('bookir_partner', 'bookir_partnerrule.xroleid', '=', 'bookir_partner.xid')
                    ->groupBy('bookir_partner.xid')
                    ->select('bookir_partner.xid as id', 'bookir_partner.xcreatorname as name')
                    ->get();

                if($creators != null and count($creators) > 0)
                {
                    foreach ($creators as $creator)
                    {
                        if(!(isset($data[$dioCode]) and $data[$dioCode]["creators"] != null and array_search($creator->name, array_column($data[$dioCode]["creators"], "name"))))
                            $creatorsData[] = ["id" => $creator->id, "name" => $creator->name];
                    }
                }

                $data[$dioCode] = array
                (
                    "creators" => $creatorsData,
                    "translate" => $book->xlang == "فارسی" ? 0 : 1,
                    "circulation" => $book->xcirculation + ((isset($data[$dioCode])) ? $data[$dioCode]["circulation"] : 0),
                    "dio" => $dioCode,
                );
            }

            $data = array_values($data);
            $status = 200;
        }

        // response
        return response()->json
        (
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $data]
            ],
            $status
        );
    }


}
