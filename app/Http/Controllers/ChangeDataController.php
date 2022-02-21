<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BookirBook;
use App\Models\BookirPartnerrule;
use App\Models\BookirRules;

class ChangeDataController extends Controller
{
    public function check_is_translate($from,$limit){
        $books = BookirBook::where('is_translate',0)->skip($from)->take($limit)->get();
        if($books->count() != 0){
            foreach($books as $book){
                $result = BookirPartnerrule::where('xbookid',$book->xid)->where('xroleid',BookirRules::where('xrole','مترجم')->first()->xid)->get();
                if( $result->count() > 0){
                    $book->is_translate = 2;
                }else{
                    $book->is_translate = 1;
                }
                $book->update();
            }
        $this->info("successfully update is_translate info");
        } else {
            $this->info("nothing for update");
        }
    }
}
