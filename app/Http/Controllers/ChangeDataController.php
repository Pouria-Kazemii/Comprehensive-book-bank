<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BookirBook;
use App\Models\BookirPartnerrule;
use App\Models\BookirRules;
use App\Models\BookDigi;
use App\Models\BookGisoom;
use App\Models\BookK24;
use Illuminate\Support\Facades\DB;

class ChangeDataController extends Controller
{
    public function check_is_translate($roleid, $from, $limit, $order)
    {

        $motarjemBooks = BookirPartnerrule::select('xbookid')->where('xroleid', $roleid)->skip($from)->take($limit)->orderBy('xid', $order)->get();

        if ($motarjemBooks->count() > 0) {
            $books = $motarjemBooks->pluck('xbookid');
            BookirBook::whereIn('xid', $books)->update(['is_translate' => $roleid]);
            echo "successfully update is_translate info";
        } else {
            echo "nothing for update";
        }
    }

    public function update_book_master_id_in_gissom($limit)
    {
        //gisoom table
        $gisoom_books = BookGisoom::where('book_master_id', 0)->where('shabak10', '!=', NULL)->where('shabak13', '!=', NULL)->skip(0)->take($limit)->get();
        if ($gisoom_books->count() != 0) {
            foreach ($gisoom_books as $gisoom_book) {
                $search_shabak = $gisoom_book->shabak10;
                $search_shabak1 = $gisoom_book->shabak13;
                $main_book_info = BookirBook::where('xparent', '>=', -1)
                    ->where(function ($query) use ($search_shabak, $search_shabak1) {
                        $query->where('xisbn', $search_shabak);
                        $query->orWhere('xisbn2', $search_shabak);
                        $query->orWhere('xisbn', $search_shabak1);
                        $query->orWhere('xisbn2', $search_shabak1);
                    })->first();
                if (!empty($main_book_info)) {
                    if ($main_book_info->xparent == -1) {
                        $gisoom_book->book_master_id = $main_book_info->xid;
                    } else {
                        $gisoom_book->book_master_id = $main_book_info->xparent;
                    }
                } else {
                    $gisoom_book->book_master_id = -10;
                }

                $gisoom_book->update();
            }
            die("successfully update is_translate info");
        } else {
            die("nothing for update");
        }
    }

    public function update_book_master_id_in_digi($limit)
    {
        //digi
        $digi_books = BookDigi::where('book_master_id', 0)->where('shabak', '!=', NULL)->skip(0)->take($limit)->get();
        if ($digi_books->count() != 0) {
            foreach ($digi_books as $digi_book) {
                $search_shabak = $digi_book->shabak;
                $main_book_info = BookirBook::where('xparent', '>=', -1)
                    ->where(function ($query) use ($search_shabak) {
                        $query->where('xisbn', $search_shabak);
                        $query->orWhere('xisbn2', $search_shabak);
                    })->first();
                if (!empty($main_book_info)) {
                    if ($main_book_info->xparent == -1) {
                        $digi_book->book_master_id = $main_book_info->xid;
                    } else {
                        $digi_book->book_master_id = $main_book_info->xparent;
                    }
                } else {
                    $digi_book->book_master_id = -10;
                }

                $digi_book->update();
            }
            die("successfully update is_translate info");
        } else {
            die("nothing for update");
        }
    }

    public function update_book_master_id_in_30book($limit)
    {
        // 30book
        $c_books = BookDigi::where('book_master_id', 0)->where('shabak', '!=', NULL)->skip(0)->take($limit)->get();
        if ($c_books->count() != 0) {
            foreach ($c_books as $c_book) {
                $search_shabak = $c_book->shabak;
                $main_book_info = BookirBook::where('xparent', '>=', -1)
                    ->where(function ($query) use ($search_shabak) {
                        $query->where('xisbn', $search_shabak);
                        $query->orWhere('xisbn2', $search_shabak);
                    })->first();
                if (!empty($main_book_info)) {
                    if ($main_book_info->xparent == -1) {
                        $c_book->book_master_id = $main_book_info->xid;
                    } else {
                        $c_book->book_master_id = $main_book_info->xparent;
                    }
                } else {
                    $c_book->book_master_id = -10;
                }

                $c_book->update();
            }
            die("successfully update is_translate info");
        } else {
            die("nothing for update");
        }
    }

    public function update_book_master_id_in_k24($limit)
    {
        // bookK24
        $k24_books = BookK24::where('book_master_id', 0)->where('shabak', '!=', NULL)->skip(0)->take($limit)->get();
        if ($k24_books->count() != 0) {
            foreach ($k24_books as $k24_book) {
                $search_shabak = $k24_book->shabak;
                $main_book_info = BookirBook::where('xparent', '>=', -1)
                    ->where(function ($query) use ($search_shabak) {
                        $query->where('xisbn', $search_shabak);
                        $query->orWhere('xisbn2', $search_shabak);
                    })->first();
                if (!empty($main_book_info)) {
                    if ($main_book_info->xparent == -1) {
                        $k24_book->book_master_id = $main_book_info->xid;
                    } else {
                        $k24_book->book_master_id = $main_book_info->xparent;
                    }
                } else {
                    $k24_book->book_master_id = -10;
                }

                $k24_book->update();
            }
            die("successfully update is_translate info");
        } else {
            die("nothing for update");
        }
    }
}
