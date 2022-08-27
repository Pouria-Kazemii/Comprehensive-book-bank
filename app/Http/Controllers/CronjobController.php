<?php

namespace App\Http\Controllers;

use App\Models\BookirBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CronjobController extends Controller
{
    public function correct_isbns()
    {
        echo 'start : ' . date("Y/m/d H:i:s");
        $books =  BookirBook::where('checkIsbn', 0)->limit(1000)->get();
        if (isset($books) and !empty($books)) {
            foreach ($books as $book) {
                unset($updateData);
                if (!empty($book->xisbn2) and (strlen($book->xisbn2 > 10))) {
                    // $updateData['xisbn2'] = NULL;
                    $updateData['xisbn3'] = $book->xisbn2;
                }
                if (!empty($book->xisbn3) and (strlen($book->xisbn3 <= 10))) {
                    $updateData['xisbn2'] = $book->xisbn2;
                    // $updateData['xisbn3'] = NULL;
                }
                if (!isset($updateData['xisbn3']) or empty($updateData['xisbn3'])) {
                    $updateData['xisbn3'] = NULL;
                }
                if (!isset($updateData['xisbn2']) or empty($updateData['xisbn2'])) {
                    $updateData['xisbn2'] = NULL;
                }
                if (isset($updateData) and !empty($updateData)) {
                    DB::enableQueryLog();
                    $updateData['checkIsbn'] = 1;
                    BookirBook::where('xid', $book->xid)->update($updateData);
                    $query = DB::getQueryLog();
                }
            }
        }

       /* DB::table('bookir_book')->where('checkIsbn',0)->orderBy('xid')->chunk(100, function ($books) {
            foreach ($books as $book) {
                unset($updateData);
                if (!empty($book->xisbn2) and (strlen($book->xisbn2 > 10))) {
                    // $updateData['xisbn2'] = NULL;
                    $updateData['xisbn3'] = $book->xisbn2;
                }
                if (!empty($book->xisbn3) and (strlen($book->xisbn3 <= 10))) {
                    $updateData['xisbn2'] = $book->xisbn2;
                    // $updateData['xisbn3'] = NULL;
                }
                if (!isset($updateData['xisbn3']) or empty($updateData['xisbn3'])) {
                    $updateData['xisbn3'] = NULL;
                }
                if (!isset($updateData['xisbn2']) or empty($updateData['xisbn2'])) {
                    $updateData['xisbn2'] = NULL;
                }
                if (isset($updateData) and !empty($updateData)) {
                    DB::enableQueryLog();
                    $updateData['checkIsbn'] = 1;
                    BookirBook::where('xid', $book->xid)->update($updateData);
                    $query = DB::getQueryLog();
                }
            }
        }); */

        echo '</br>';
        echo 'end : ' . date("Y/m/d H:i:s");
    }
}
