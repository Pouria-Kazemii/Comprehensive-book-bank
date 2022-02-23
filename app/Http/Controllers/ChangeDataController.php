<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BookirBook;
use App\Models\BookirPartnerrule;
use App\Models\BookirRules;
use Illuminate\Support\Facades\DB;

class ChangeDataController extends Controller
{
    public function check_is_translate($roleid,$from, $limit,$order)
    {

        $motarjemBooks = BookirPartnerrule::select('xbookid')->where('xroleid', $roleid)->skip($from)->take($limit)->orderBy('xid',$order)->get();

        if ($motarjemBooks->count() > 0) {
            $books = $motarjemBooks->pluck('xbookid');
            BookirBook::whereIn('xid', $books)->update(['is_translate' => $roleid]);
            echo "successfully update is_translate info";
        } else {
            echo "nothing for update";
        }
    }
}
