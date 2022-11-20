<?php

namespace App\Http\Controllers;

use App\Models\BookirPublisher;
use App\Models\BookirPartner;
use App\Models\BookirBook;
use App\Models\CirculationTemp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class CronjobController extends Controller
{
    public function correct_isbns()
    {
        echo 'stop 1401/6/14';
        /*
        echo 'start : ' . date("Y/m/d H:i:s");
        $books =  BookirBook::where('checkIsbn', 0)->limit(10000)->get();
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
                    $updateData['checkIsbn'] = 1;
                    BookirBook::where('xid', $book->xid)->update($updateData);
                }
            }
        }

        echo '</br>';
        echo 'end : ' . date("Y/m/d H:i:s");
        */
    }
    public function correct_isbns_with_chunk()
    {
        echo 'It was not used';
        /*echo 'start : ' . date("Y/m/d H:i:s");
        DB::table('bookir_book')->orderBy('xid')->chunk(100, function ($books) {
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
                    $updateData['checkIsbn'] = 1;
                    BookirBook::where('xid', $book->xid)->update($updateData);
                }
            }
        });

        echo '</br>';
        echo 'end : ' . date("Y/m/d H:i:s");*/
    }
    public function fill_circulation_temp_table()
    {
        // echo 'start : ' . date("Y/m/d H:i:s");
        $books = BookirBook::where('check_circulation', 0)->limit(1)->get();
        if (isset($books) and !empty($books)) {
            foreach ($books as $book) {
                $this->check_book_circulation($book->xid);
                $book->check_circulation = 1;
                $book->update();
            }
        }
        // echo 'end : ' . date("Y/m/d H:i:s");

    }


    public function check_book_circulation($book_id)
    {
        $book_info = BookirBook::where('xid', $book_id)->first();
        if ($book_info->xparent == -1) {
            $searchBookId = $book_info->xid;
        } else {
            $searchBookId = $book_info->xparent;
        }
        $books =  BookirBook::with('publishers', 'partnersRoles', 'children')->where('xid', $searchBookId)->get();
        if (isset($books) and !empty($books)) {
            foreach ($books as $book) {
                DB::transaction(function () use ($book) {
                    try {
                        //reset book circulation year record
                        CirculationTemp::where('xbook_id', $book->xid)->delete();
                        $circulationTempModel = new CirculationTemp([
                            'xbook_id' => $book->xid,
                            'xcirculations_count' => $book->xcirculation,
                            'xfirst_edition_circulations_count' => ($book->xprintnumber == 1) ? $book->xcirculation : 0,
                            'xyear' => BookirBook::getShamsiYear($book->xpublishdate),
                        ]);
                        $circulationTempModel->save();
                        // children 
                        if ($book->children()->exists()) {
                            foreach ($book->children as $book_children) {
                                $selectedCirculationTempInfo = CirculationTemp::where('xbook_id', $book->xid)->where('xyear', BookirBook::getShamsiYear($book_children->xpublishdate))->first();
                                if (isset($selectedCirculationTempInfo) and !empty($selectedCirculationTempInfo)) {
                                    $selectedCirculationTempInfo->xcirculations_count =  $selectedCirculationTempInfo->xcirculations_count + $book_children->xcirculation;
                                    if ($book_children->xprintnumber == 1) {
                                        $selectedCirculationTempInfo->xfirst_edition_circulations_count = $selectedCirculationTempInfo->xfirst_edition_circulations_count + $book_children->xcirculation;
                                    }
                                    $selectedCirculationTempInfo->update();
                                } else {
                                    $circulationTempModel = new CirculationTemp([
                                        'xbook_id' => $book->xid,
                                        'xcirculations_count' => $book_children->xcirculation,
                                        'xfirst_edition_circulations_count' => ($book_children->xprintnumber == 1) ? $book_children->xcirculation : 0,
                                        'xyear' => BookirBook::getShamsiYear($book_children->xpublishdate),
                                    ]);
                                    $circulationTempModel->save();
                                }
                            }
                        }

                        // publisher
                        if ($book->publishers()->exists()) {
                            foreach ($book->publishers as $book_publishers) {
                                $books_of_book_publishers = BookirPublisher::with('books')->where('xid', $book_publishers->xid)->get(); // کتاب های ناشران کتاب
                                if (isset($books_of_book_publishers) and !empty($books_of_book_publishers)) {
                                    foreach ($books_of_book_publishers as $books_of_book_publisher) { // کتاب های ناشر کتاب
                                        if ($books_of_book_publisher->books()->exists()) {
                                            CirculationTemp::where('xpublisher_id', $books_of_book_publisher->xid)->delete();
                                            foreach ($books_of_book_publisher->books as $publisher_books) {
                                                $selectedCirculationTempInfo = CirculationTemp::where('xpublisher_id', $books_of_book_publisher->xid)->where('xyear', BookirBook::getShamsiYear($publisher_books->xpublishdate))->first();
                                                if (isset($selectedCirculationTempInfo) and !empty($selectedCirculationTempInfo)) {
                                                    $selectedCirculationTempInfo->xbooks_count =  $selectedCirculationTempInfo->xbooks_count + 1;
                                                    if ($publisher_books->xprintnumber == 1) {
                                                        $selectedCirculationTempInfo->xfirst_edition_books_count = $selectedCirculationTempInfo->xfirst_edition_books_count + 1;
                                                    }
                                                    $selectedCirculationTempInfo->xcirculations_count =  $selectedCirculationTempInfo->xcirculations_count + $publisher_books->xcirculation;
                                                    if ($publisher_books->xprintnumber == 1) {
                                                        $selectedCirculationTempInfo->xfirst_edition_circulations_count = $selectedCirculationTempInfo->xfirst_edition_circulations_count + $publisher_books->xcirculation;
                                                    }
                                                    $selectedCirculationTempInfo->update();
                                                } else {
                                                    $circulationTempModel = new CirculationTemp([
                                                        'xpublisher_id' => $books_of_book_publisher->xid,
                                                        'xbooks_count' => 1,
                                                        'xfirst_edition_books_count' => ($publisher_books->xprintnumber == 1) ? 1 : 0,
                                                        'xcirculations_count' => $publisher_books->xcirculation,
                                                        'xfirst_edition_circulations_count' => ($publisher_books->xprintnumber == 1) ? $publisher_books->xcirculation : 0,
                                                        'xyear' => BookirBook::getShamsiYear($publisher_books->xpublishdate),
                                                    ]);
                                                    $circulationTempModel->save();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // author
                        if ($book->partnersRoles()->exists()) {
                            foreach ($book->partnersRoles as $book_authors) {
                                $books_of_book_authors = BookirPartner::with('books')->where('xid', $book_authors->xid)->get(); // کتاب های پدیدآورندگان کتاب
                                if (isset($books_of_book_authors) and !empty($books_of_book_authors)) {
                                    foreach ($books_of_book_authors as $books_of_book_author) { // کتاب های پدیدآورنده کتاب
                                        if ($books_of_book_author->books()->exists()) {
                                            CirculationTemp::where('xauthor_id', $books_of_book_author->xid)->delete();
                                            foreach ($books_of_book_author->books as $author_books) {
                                                $selectedCirculationTempInfo = CirculationTemp::where('xauthor_id', $books_of_book_author->xid)->where('xyear', BookirBook::getShamsiYear($author_books->xpublishdate))->first();
                                                if (isset($selectedCirculationTempInfo) and !empty($selectedCirculationTempInfo)) {
                                                    $selectedCirculationTempInfo->xbooks_count =  $selectedCirculationTempInfo->xbooks_count + 1;
                                                    if ($author_books->xprintnumber == 1) {
                                                        $selectedCirculationTempInfo->xfirst_edition_books_count = $selectedCirculationTempInfo->xfirst_edition_books_count + 1;
                                                    }
                                                    $selectedCirculationTempInfo->xcirculations_count =  $selectedCirculationTempInfo->xcirculations_count + $author_books->xcirculation;
                                                    if ($author_books->xprintnumber == 1) {
                                                        $selectedCirculationTempInfo->xfirst_edition_circulations_count = $selectedCirculationTempInfo->xfirst_edition_circulations_count + $author_books->xcirculation;
                                                    }
                                                    $selectedCirculationTempInfo->update();
                                                } else {
                                                    $circulationTempModel = new CirculationTemp([
                                                        'xauthor_id' => $books_of_book_author->xid,
                                                        'xbooks_count' => 1,
                                                        'xfirst_edition_books_count' => ($author_books->xprintnumber == 1) ? 1 : 0,
                                                        'xcirculations_count' => $author_books->xcirculation,
                                                        'xfirst_edition_circulations_count' => ($author_books->xprintnumber == 1) ? $author_books->xcirculation : 0,
                                                        'xyear' => BookirBook::getShamsiYear($author_books->xpublishdate),
                                                    ]);
                                                    $circulationTempModel->save();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } catch (Exception $Exception) {
                        //throw $th;
                    }
                });
            }
        }
    }

    public function fast_fill_circulation_temp_table()
    {

        BookirBook::with('publishers:xid', 'partnersRoles', 'children')->where('check_circulation', 0)->where('xparent', -1)->chunk(100, function ($books) {
            if (isset($books) and !empty($books)) {
                foreach ($books as $book) {
                    //reset book circulation year record
                    DB::transaction(function () use ($book) {
                        try {
                            CirculationTemp::where('xbook_id', $book->xid)->delete();
                            $circulationTempModel = new CirculationTemp([
                                'xbook_id' => $book->xid,
                                'xcirculations_count' => $book->xcirculation,
                                'xfirst_edition_circulations_count' => ($book->xprintnumber == 1) ? $book->xcirculation : 0,
                                'xyear' => BookirBook::getShamsiYear($book->xpublishdate),
                            ]);
                            $circulationTempModel->save();
                            // children 
                            if ($book->children()->exists()) {
                                foreach ($book->children as $book_children) {
                                    $selectedCirculationTempInfo = CirculationTemp::where('xbook_id', $book->xid)->where('xyear', BookirBook::getShamsiYear($book_children->xpublishdate))->first();
                                    if (isset($selectedCirculationTempInfo) and !empty($selectedCirculationTempInfo)) {
                                        $selectedCirculationTempInfo->xcirculations_count =  $selectedCirculationTempInfo->xcirculations_count + $book_children->xcirculation;
                                        if ($book_children->xprintnumber == 1) {
                                            $selectedCirculationTempInfo->xfirst_edition_circulations_count = $selectedCirculationTempInfo->xfirst_edition_circulations_count + $book_children->xcirculation;
                                        }
                                        $selectedCirculationTempInfo->update();
                                    } else {
                                        $circulationTempModel = new CirculationTemp([
                                            'xbook_id' => $book->xid,
                                            'xcirculations_count' => $book_children->xcirculation,
                                            'xfirst_edition_circulations_count' => ($book_children->xprintnumber == 1) ? $book_children->xcirculation : 0,
                                            'xyear' => BookirBook::getShamsiYear($book_children->xpublishdate),
                                        ]);
                                        $circulationTempModel->save();
                                    }
                                }
                            }

                            // publisher
                            if ($book->publishers()->exists()) {
                                foreach ($book->publishers as $book_publishers) {
                                    $books_of_book_publishers = BookirPublisher::with('books')->where('xid', $book_publishers->xid)->get(); // کتاب های ناشران کتاب
                                    if (isset($books_of_book_publishers) and !empty($books_of_book_publishers)) {
                                        foreach ($books_of_book_publishers as $books_of_book_publisher) { // کتاب های ناشر کتاب
                                            if ($books_of_book_publisher->books()->exists()) {
                                                CirculationTemp::where('xpublisher_id', $books_of_book_publisher->xid)->delete();
                                                foreach ($books_of_book_publisher->books as $publisher_books) {
                                                    $selectedCirculationTempInfo = CirculationTemp::where('xpublisher_id', $books_of_book_publisher->xid)->where('xyear', BookirBook::getShamsiYear($publisher_books->xpublishdate))->first();
                                                    if (isset($selectedCirculationTempInfo) and !empty($selectedCirculationTempInfo)) {
                                                        $selectedCirculationTempInfo->xbooks_count =  $selectedCirculationTempInfo->xbooks_count + 1;
                                                        if ($publisher_books->xprintnumber == 1) {
                                                            $selectedCirculationTempInfo->xfirst_edition_books_count = $selectedCirculationTempInfo->xfirst_edition_books_count + 1;
                                                        }
                                                        $selectedCirculationTempInfo->xcirculations_count =  $selectedCirculationTempInfo->xcirculations_count + $publisher_books->xcirculation;
                                                        if ($publisher_books->xprintnumber == 1) {
                                                            $selectedCirculationTempInfo->xfirst_edition_circulations_count = $selectedCirculationTempInfo->xfirst_edition_circulations_count + $publisher_books->xcirculation;
                                                        }
                                                        $selectedCirculationTempInfo->update();
                                                    } else {
                                                        $circulationTempModel = new CirculationTemp([
                                                            'xpublisher_id' => $books_of_book_publisher->xid,
                                                            'xbooks_count' => 1,
                                                            'xfirst_edition_books_count' => ($publisher_books->xprintnumber == 1) ? 1 : 0,
                                                            'xcirculations_count' => $publisher_books->xcirculation,
                                                            'xfirst_edition_circulations_count' => ($publisher_books->xprintnumber == 1) ? $publisher_books->xcirculation : 0,
                                                            'xyear' => BookirBook::getShamsiYear($publisher_books->xpublishdate),
                                                        ]);
                                                        $circulationTempModel->save();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            // author
                            if ($book->partnersRoles()->exists()) {
                                foreach ($book->partnersRoles as $book_authors) {
                                    $books_of_book_authors = BookirPartner::with('books')->where('xid', $book_authors->xid)->get(); // کتاب های پدیدآورندگان کتاب
                                    if (isset($books_of_book_authors) and !empty($books_of_book_authors)) {
                                        foreach ($books_of_book_authors as $books_of_book_author) { // کتاب های پدیدآورنده کتاب
                                            if ($books_of_book_author->books()->exists()) {
                                                CirculationTemp::where('xauthor_id', $books_of_book_author->xid)->delete();
                                                foreach ($books_of_book_author->books as $author_books) {
                                                    $selectedCirculationTempInfo = CirculationTemp::where('xauthor_id', $books_of_book_author->xid)->where('xyear', BookirBook::getShamsiYear($author_books->xpublishdate))->first();
                                                    if (isset($selectedCirculationTempInfo) and !empty($selectedCirculationTempInfo)) {
                                                        $selectedCirculationTempInfo->xbooks_count =  $selectedCirculationTempInfo->xbooks_count + 1;
                                                        if ($author_books->xprintnumber == 1) {
                                                            $selectedCirculationTempInfo->xfirst_edition_books_count = $selectedCirculationTempInfo->xfirst_edition_books_count + 1;
                                                        }
                                                        $selectedCirculationTempInfo->xcirculations_count =  $selectedCirculationTempInfo->xcirculations_count + $author_books->xcirculation;
                                                        if ($author_books->xprintnumber == 1) {
                                                            $selectedCirculationTempInfo->xfirst_edition_circulations_count = $selectedCirculationTempInfo->xfirst_edition_circulations_count + $author_books->xcirculation;
                                                        }
                                                        $selectedCirculationTempInfo->update();
                                                    } else {
                                                        $circulationTempModel = new CirculationTemp([
                                                            'xauthor_id' => $books_of_book_author->xid,
                                                            'xbooks_count' => 1,
                                                            'xfirst_edition_books_count' => ($author_books->xprintnumber == 1) ? 1 : 0,
                                                            'xcirculations_count' => $author_books->xcirculation,
                                                            'xfirst_edition_circulations_count' => ($author_books->xprintnumber == 1) ? $author_books->xcirculation : 0,
                                                            'xyear' => BookirBook::getShamsiYear($author_books->xpublishdate),
                                                        ]);
                                                        $circulationTempModel->save();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            BookirBook::where('xid', $book->xid)->orwhere('xparent', $book->xid)->update(['check_circulation' => 1]);
                        } catch (Exception $Exception) {
                            //throw $th;
                        }
                    });
                }
            }
        });
    }

    public function fill_publisher_circulation_temp_table() // start in 2022-11-20
    {
        BookirPublisher::with('books')->chunk(10, function ($books_of_book_publishers) { // کتاب های ناشران کتاب
            if (isset($books_of_book_publishers) and !empty($books_of_book_publishers)) {
                DB::transaction(function () use ($books_of_book_publishers) {
                    try {
                        foreach ($books_of_book_publishers as $books_of_book_publisher) { // کتاب های ناشر کتاب
                            if ($books_of_book_publisher->books()->exists()) {
                                CirculationTemp::where('xpublisher_id', $books_of_book_publisher->xid)->delete();
                                foreach ($books_of_book_publisher->books as $publisher_books) {
                                    $selectedCirculationTempInfo = CirculationTemp::where('xpublisher_id', $books_of_book_publisher->xid)->where('xyear', BookirBook::getShamsiYear($publisher_books->xpublishdate))->first();
                                    if (isset($selectedCirculationTempInfo) and !empty($selectedCirculationTempInfo)) {
                                        $selectedCirculationTempInfo->xbooks_count =  $selectedCirculationTempInfo->xbooks_count + 1;
                                        if ($publisher_books->xprintnumber == 1) {
                                            $selectedCirculationTempInfo->xfirst_edition_books_count = $selectedCirculationTempInfo->xfirst_edition_books_count + 1;
                                        }
                                        $selectedCirculationTempInfo->xcirculations_count =  $selectedCirculationTempInfo->xcirculations_count + $publisher_books->xcirculation;
                                        if ($publisher_books->xprintnumber == 1) {
                                            $selectedCirculationTempInfo->xfirst_edition_circulations_count = $selectedCirculationTempInfo->xfirst_edition_circulations_count + $publisher_books->xcirculation;
                                        }
                                        $selectedCirculationTempInfo->update();
                                    } else {
                                        $circulationTempModel = new CirculationTemp([
                                            'xpublisher_id' => $books_of_book_publisher->xid,
                                            'xbooks_count' => 1,
                                            'xfirst_edition_books_count' => ($publisher_books->xprintnumber == 1) ? 1 : 0,
                                            'xcirculations_count' => $publisher_books->xcirculation,
                                            'xfirst_edition_circulations_count' => ($publisher_books->xprintnumber == 1) ? $publisher_books->xcirculation : 0,
                                            'xyear' => BookirBook::getShamsiYear($publisher_books->xpublishdate),
                                        ]);
                                        $circulationTempModel->save();
                                    }
                                }
                            }
                        }
                    } catch (Exception $Exception) {
                        //throw $th;
                    }
                });
            }
        });
    }
}
