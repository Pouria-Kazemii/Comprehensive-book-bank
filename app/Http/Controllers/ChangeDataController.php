<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\BookK24;
use App\Models\BookDigi;
use App\Models\Book30book;
use App\Models\BookGisoom;
use App\Models\BookirBook;
use App\Models\BookirRules;
use Illuminate\Http\Request;
use App\Models\BookIranketab;
use App\Models\BookirPartner;
use App\Models\BookirPartnerrule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\BookIranKetabPartner;
use DateTime;

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
            die("successfully update book_master_id info");
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
            die("successfully update book_master_id info");
        } else {
            die("nothing for update");
        }
    }

    public function update_book_master_id_in_30book($limit)
    {
        // 30book
        $c_books = Book30book::where('book_master_id', 0)->where('shabak', '!=', NULL)->skip(0)->take($limit)->get();
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
            die("successfully update book_master_id info");
        } else {
            die("nothing for update");
        }
    }

    public function update_book_master_id_in_iranketab($limit)
    {
        // iranketab
        $iranketab_books = BookIranketab::where('book_master_id', 0)->where('shabak', '>', 0)->skip(0)->take($limit)->get();
        if ($iranketab_books->count() != 0) {
            foreach ($iranketab_books as $iranketab_book) {
                $search_shabak = $iranketab_book->shabak;
                $main_book_info = BookirBook::where('xparent', '>=', -1)
                    ->where(function ($query) use ($search_shabak) {
                        $query->where('xisbn', $search_shabak);
                        $query->orWhere('xisbn2', $search_shabak);
                    })->first();
                if (!empty($main_book_info)) {
                    if ($main_book_info->xparent == -1) {
                        $book_master_id = $main_book_info->xid;
                    } else {
                        $book_master_id = $main_book_info->xparent;
                    }
                } else {
                    $book_master_id = -10;
                }
                BookIranketab::where('parentId', $iranketab_book->parentId)->update(['book_master_id' => $book_master_id]);
                // var_dump( $query);
                // $iranketab_book->update();

            }
            die("successfully update book_master_id info");
        } else {
            die("nothing for update");
        }
    }

    public function update_partner_master_id_in_iranketab($limit)
    {
        // iranketab
        $iranketab_partners = BookIranKetabPartner::where('partner_master_id', 0)->skip(0)->take($limit)->get();
        if ($iranketab_partners->count() != 0) {
            foreach ($iranketab_partners as $iranketab_partner) {
                echo 'id : ' . $iranketab_partner->id . ' shabak : ' . $iranketab_partner->partnerName . '</br>';
                $search_name = $iranketab_partner->partnerName;
                $main_partner_info = BookirPartner::where('xcreatorname', $search_name)->orWhere('xname2', $search_name)->first();
                if (!empty($main_partner_info)) {
                    $partner_master_id = $main_partner_info->xid;
                } else {
                    $search_name = str_replace(" ", "", $search_name);
                    $search_name = str_replace("ً.", "", $search_name);
                    $search_name = str_replace("ً-", "", $search_name);
                    $search_name = str_replace("ً_", "", $search_name);
                    $search_name = str_replace("ً+", "", $search_name);
                    $search_name = str_replace("ً", "", $search_name);
                    $search_name = str_replace("ٌ", "", $search_name);
                    $search_name = str_replace("ٍ", "", $search_name);
                    $search_name = str_replace("،", "", $search_name);
                    $search_name = str_replace("؛", "", $search_name);
                    $search_name = str_replace(",", "", $search_name);
                    $search_name = str_replace("ّ", "", $search_name);
                    $search_name = str_replace("ِ", "", $search_name);
                    $search_name = str_replace("ُ", "", $search_name);
                    $search_name = str_replace("ة", "ه", $search_name);
                    $search_name = str_replace("ؤ", "و", $search_name);
                    $search_name = str_replace("إ", "ا", $search_name);
                    $search_name = str_replace("أ", "ا", $search_name);
                    $search_name = str_replace("ء", "", $search_name);
                    $search_name = str_replace("ۀ", "ه", $search_name);
                    $search_name = str_replace("سادات", "", $search_name);
                    $search_name = str_replace("السادات", "", $search_name);
                    $search_name = str_replace("حاج", "", $search_name);
                    $search_name = str_replace("حاجی", "", $search_name);
                    $search_name = str_replace("سید", "", $search_name);
                    $search_name = str_replace("آ", "ا", $search_name);
                    $search_name = str_replace("ئ", "ی", $search_name);
                    $search_name = str_replace("ي", "ی", $search_name);
                    echo '$search_nam : ' . $search_name . '</br>';
                    $main_partner_info = BookirPartner::where('xcreatorname', $search_name)->orWhere('xname2', $search_name)->first();
                    if (!empty($main_partner_info)) {
                        $partner_master_id = $main_partner_info->xid;
                    } else {
                        $partner_master_id = -10;
                    }
                }
                echo 'partner_master_id : ' . $partner_master_id . '</br>';
                BookIranKetabPartner::where('id', $iranketab_partner->id)->update(['partner_master_id' => $partner_master_id]);
                // $iranketab_book->update();
            }
            die("successfully update partner_master_id info");
        } else {
            die("nothing for update");
        }
    }

    public function consensus_similar_books_by_iranketab_entitle($limit)
    {
        $allIranketabBooks = BookIranketab::where('temp_book_master_id', 0)->where('enTitle', '!=', '')->skip(0)->take($limit)->get();
        // $allIranketabBooks = BookIranketab::where('enTitle', 'The Little Prince')->skip(0)->take($limit)->get();
        if ($allIranketabBooks->count() != 0) {
            foreach ($allIranketabBooks as $allIranketabBookItem) {
                echo ' book_id : ' . $allIranketabBookItem->id . ' book name : ' . $allIranketabBookItem->title . '  en book name : ' . $allIranketabBookItem->enTitle . '</br>';
                $iranketabBooks = BookIranketab::where('enTitle', $allIranketabBookItem->enTitle)->where('shabak', '!=', '')->get(); // پیدا کردن رکوردها ایران کتاب با عنوان انگلیسی کتاب
                $allBookirBooks = BookirBook::whereIN('xisbn2', $iranketabBooks->pluck('shabak')->all())->get(); // پیدا کردن شابک های کتاب های با نام انگلیسی یکسان
                if ($allBookirBooks->count() != 0) {
                    $allBookirBooksIsbnCollection =  $allBookirBooks->pluck('xisbn2')->all();
                    $allBookirBooksIdCollection =  $allBookirBooks->pluck('xid')->all();

                    // $bookirBooksParent = $allBookirBooks->where('xparent', -1)->pluck('xisbn2', 'xid')->all(); // پیدا کردن شابک های کتاب های با نام انگلیسی یکسان
                    $bookirBooksParent = $allBookirBooks->pluck('xisbn2', 'xid')->all(); // پیدا کردن شابک های کتاب های با نام انگلیسی یکسان

                    $strongBookIsbn = '';
                    $strongBookCount = 0;
                    foreach ($bookirBooksParent as $key => $bookirBookParentItem) { // پیدا کردن آیدی قوی تر
                        $allBookirBooksIsbnCollection = new Collection($allBookirBooksIsbnCollection);
                        $filtered = $allBookirBooksIsbnCollection->filter(function ($isbn) use ($bookirBookParentItem) {
                            return $isbn == $bookirBookParentItem;
                        });
                        if (($filtered->count() == $strongBookCount) and  BookirBook::where('xid', $key)->first()->xparent = -1) {
                            $strongBookCount  = $filtered->count();
                            $strongBookIsbn  = $bookirBookParentItem;
                            $strongBookId  = $key;
                        } elseif ($filtered->count() > $strongBookCount) {
                            $strongBookCount  = $filtered->count();
                            $strongBookIsbn  = $bookirBookParentItem;
                            $strongBookId  = $key;
                        } else
                            echo 'id : ' . $key . 'isbn : ' . $bookirBookParentItem . 'count : ' . $filtered->count()  . '</br>';
                    }

                    try {
                        BookirBook::whereIN('xid', $allBookirBooksIdCollection)->update(['xtempparent' => $strongBookId]);
                        BookirBook::where('xid', $strongBookId)->update(['xtempparent' => -1]);
                        BookIranketab::where('id', $allIranketabBookItem->id)->update(['temp_book_master_id' => $strongBookId]);
                        echo 'update by info id : ' . $strongBookId . 'isbn : ' . $strongBookIsbn . 'count : ' . $strongBookCount . '</br>';
                    } catch (Exception $Exception) {
                        //throw $th;
                        echo " update bookirbook temp_book_master_id exception error " . $Exception->getMessage() . '</br>';
                    }
                } else {
                    BookIranketab::where('id', $allIranketabBookItem->id)->update(['temp_book_master_id' => -10]);
                    echo 'nothing info in bookirbook table' . '</br>';
                }
            }
        } else {
            echo 'nothing record' . '</br>';
        }
    }

    public function consensus_similar_books_by_iranketab_parentId($limit)
    {
        $allIranketabBooks = BookIranketab::where('temp_book_master_id', 0)->where('enTitle', '!=', '')->skip(0)->take($limit)->get();
        // $allIranketabBooks = BookIranketab::where('parentId', 433)->skip(0)->take($limit)->get();
        if ($allIranketabBooks->count() != 0) {
            foreach ($allIranketabBooks as $allIranketabBookItem) {
                // echo ' book_id : ' . $allIranketabBookItem->id . ' book name : ' . $allIranketabBookItem->title . '  en book name : ' . $allIranketabBookItem->enTitle . '</br>';
                echo ' book_id : ' . $allIranketabBookItem->id . ' book parentId : ' . $allIranketabBookItem->parentId  . '</br>';
                $iranketabBooks = BookIranketab::where('parentId', $allIranketabBookItem->parentId)->where('shabak', '!=', '')->get(); // پیدا کردن رکوردها ایران کتاب با parentId
                $allBookirBooks = BookirBook::whereIN('xisbn2', $iranketabBooks->pluck('shabak')->all())->get(); // پیدا کردن شابک های کتاب های با parentId
                if ($allBookirBooks->count() != 0) {
                    $allBookirBooksIsbnCollection =  $allBookirBooks->pluck('xisbn2')->all();
                    $allBookirBooksIdCollection =  $allBookirBooks->pluck('xid')->all();

                    // $bookirBooksParent = $allBookirBooks->where('xparent', -1)->pluck('xisbn2', 'xid')->all(); // پیدا کردن شابک های کتاب های با نام انگلیسی یکسان
                    $bookirBooksParent = $allBookirBooks->pluck('xisbn2', 'xid')->all(); // پیدا کردن شابک های کتاب های با نام انگلیسی یکسان

                    $strongBookIsbn = '';
                    $strongBookCount = 0;
                    foreach ($bookirBooksParent as $key => $bookirBookParentItem) { // پیدا کردن آیدی قوی تر
                        $allBookirBooksIsbnCollection = new Collection($allBookirBooksIsbnCollection);
                        $filtered = $allBookirBooksIsbnCollection->filter(function ($isbn) use ($bookirBookParentItem) {
                            return $isbn == $bookirBookParentItem;
                        });
                        if (($filtered->count() == $strongBookCount) and  BookirBook::where('xid', $key)->first()->xparent = -1) {
                            $strongBookCount  = $filtered->count();
                            $strongBookIsbn  = $bookirBookParentItem;
                            $strongBookId  = $key;
                        } elseif ($filtered->count() > $strongBookCount) {
                            $strongBookCount  = $filtered->count();
                            $strongBookIsbn  = $bookirBookParentItem;
                            $strongBookId  = $key;
                        } else
                            echo 'id : ' . $key . 'isbn : ' . $bookirBookParentItem . 'count : ' . $filtered->count()  . '</br>';
                    }

                    try {
                        // DB::enableQueryLog();
                        BookirBook::whereIN('xid', $allBookirBooksIdCollection)->update(['xtempparent' => $strongBookId]);
                        BookirBook::where('xid', $strongBookId)->update(['xtempparent' => -1]);
                        BookIranketab::where('id', $allIranketabBookItem->id)->update(['temp_book_master_id' => $strongBookId]);
                        echo 'update by info id : ' . $strongBookId . 'isbn : ' . $strongBookIsbn . 'count : ' . $strongBookCount . '</br>';
                        // $query = DB::getQueryLog();
                        // dd($query);
                    } catch (Exception $Exception) {
                        //throw $th;
                        echo " update bookirbook temp_book_master_id exception error " . $Exception->getMessage() . '</br>';
                    }
                } else {
                    BookIranketab::where('id', $allIranketabBookItem->id)->update(['temp_book_master_id' => -10]);
                    echo 'nothing info in bookirbook table' . '</br>';
                }
            }
        } else {
            echo 'nothing record' . '</br>';
        }
    }


   /* public function merge_parentid_tempparentid($limit)
    {
        // update  by parent -1 old parent id
        $books = BookirBook::Where('xname','like','%شازده کوچولو%')->where('xtempparent', -1)->skip(0)->take($limit)->get();
        // $books = BookirBook::where('xid', 1434936)->skip(0)->take($limit)->get();
        if ($books->count() != 0) {
            foreach ($books as $bookItem) {
                $all_old_parent = BookirBook::where('xtempparent', $bookItem->xid)->where('xparent','!=',-1)->get()->pluck('xparent')->all();
                $all_old_parents = array_unique($all_old_parent);
                // echo ' book_id : ' . $allIranketabBookItem->id . ' book name : ' . $allIranketabBookItem->title . '  en book name : ' . $allIranketabBookItem->enTitle . '</br>';
                // echo ' book_id : ' . $bookItem->id . ' book parentId : ' . $bookItem->parentId  .' book xtempparent : ' . $bookItem->xtempparent  . '</br>';
                foreach($all_old_parents as $all_old_parent_item){
                    echo $all_old_parent_item.'</br>';
                    // BookirBook::where('xtempparent', 0)->where('xparent',$all_old_parent_item)->update(['xtempparent' => $bookItem->xtempparent]);
                    BookirBook::where('xid',$all_old_parent_item)->orWhere('xparent',$all_old_parent_item)->update(['xtempparent' => $bookItem->xid]);
                }

            }
        } else {
            echo 'nothing record' . '</br>';
        }
    }*/

    public function update_tempparent_to_other_fields($limit)
    {
        echo 'start : '.date("H:i:s",time()).'</br>';
        $books = BookirBook::where('xmerge', 0)->orderBy('xtempparent', 'ASC')->skip(0)->take($limit)->get();
        if ($books->count() != 0) {
            foreach ($books as $bookItem) {
                if($bookItem->xtempparent > 0){
                    try {
                        BookIranketab::where('book_master_id', $bookItem->xparent)->update(['book_master_id' => $bookItem->xtempparent]);
                        BookGisoom::where('book_master_id', $bookItem->xparent)->update(['book_master_id' => $bookItem->xtempparent]);
                        BookDigi::where('book_master_id', $bookItem->xparent)->update(['book_master_id' => $bookItem->xtempparent]);
                        Book30book::where('book_master_id', $bookItem->xparent)->update(['book_master_id' => $bookItem->xtempparent]);
                        BookirBook::where('xparent', $bookItem->xparent)->update(['xparent' =>  $bookItem->xtempparent]);
                        BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  1]);
                    } catch (Exception $Exception) {
                        //throw $th;
                        echo " update book_master_id error " . $Exception->getMessage() . '</br>';
                    }
                }elseif($bookItem->xtempparent == -1){
                    try {
                        BookirBook::where('xid', $bookItem->xid)->update(['xparent' =>  $bookItem->xtempparent]);
                        BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  -1]);
                    } catch (Exception $Exception) {
                        //throw $th;
                        echo " update book_master_id error " . $Exception->getMessage() . '</br>';
                    }
                }else{
                    BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  -10]);

                }
               
            }
        }
        echo 'end : '.date("H:i:s",time()).'</br>';

    }
    public function update_tempparent_to_other_fields_desc($limit)
    {
        echo 'start : '.date("H:i:s",time()).'</br>';
        $books = BookirBook::where('xmerge', 0)->orderBy('xtempparent', 'DESC')->skip(0)->take($limit)->get();
        if ($books->count() != 0) {
            foreach ($books as $bookItem) {
                if($bookItem->xtempparent > 0){
                    try {
                        BookIranketab::where('book_master_id', $bookItem->xparent)->update(['book_master_id' => $bookItem->xtempparent]);
                        BookGisoom::where('book_master_id', $bookItem->xparent)->update(['book_master_id' => $bookItem->xtempparent]);
                        BookDigi::where('book_master_id', $bookItem->xparent)->update(['book_master_id' => $bookItem->xtempparent]);
                        Book30book::where('book_master_id', $bookItem->xparent)->update(['book_master_id' => $bookItem->xtempparent]);
                        BookirBook::where('xparent', $bookItem->xparent)->update(['xparent' =>  $bookItem->xtempparent]);
                        BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  1]);
                    } catch (Exception $Exception) {
                        //throw $th;
                        echo " update book_master_id error " . $Exception->getMessage() . '</br>';
                    }
                }elseif($bookItem->xtempparent == -1){
                    try {
                        BookirBook::where('xid', $bookItem->xid)->update(['xparent' =>  $bookItem->xtempparent]);
                        BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  -1]);
                    } catch (Exception $Exception) {
                        //throw $th;
                        echo " update book_master_id error " . $Exception->getMessage() . '</br>';
                    }
                }else{
                    BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  -10]);

                }
               
            }
        }
        echo 'end : '.date("H:i:s",time()).'</br>';

    }


    public function check_old_xparent($limit){
        echo 'start : '.date("H:i:s",time()).'</br>';
        $books = BookirBook::where('xparent', 0)->orderBy('xparent', 'ASC')->skip(0)->take($limit)->get();
        if ($books->count() != 0) {
            foreach ($books as $bookItem) {
                $search = BookirBook::where('xparent', $bookItem->xid)->first();
                if ($search->count() != 0) {
                    DB::enableQueryLog();
                    BookirBook::where('xid', $bookItem->xid)->update(['xparent' =>  -1]);
                    $query = DB::getQueryLog();
                    var_dump($query);
                }

            }
        }
        echo 'end : '.date("H:i:s",time()).'</br>';
    }
}
