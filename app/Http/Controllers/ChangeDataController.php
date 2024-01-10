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
use App\Models\MajmaApiBook;
use App\Models\MajmaApiPublisher;
use App\Models\AgeGroup;
use App\Models\BookCover;
use App\Models\BookFormat;
use App\Models\BookirPublisher;
use App\Models\BookirSubject;
use App\Models\BookLanguage;

class ChangeDataController extends Controller
{
    public function getMajmaForCorrectInfo($skip,$limit){
        // die($skip);
        $xfunction_caller = 'ChangeDataController->getMajmaForCorrectInfo-from:'.$skip.'-Limit:'.$limit;
        $books = bookirbook::WhereNull('xpageurl2')->whereNotNull('xpageurl')->where('check_goodreads',0)->orderBy('xid','ASC')->skip($skip)->take($limit)->get();
        foreach($books as $book){

            BookirBook::where('xid',$book->xid)->update(['check_goodreads'=>1]);

            
            $recordNumber = $book->xpageurl;
            $recordNumber = str_replace("https://db.ketab.ir/bookview.aspx?bookid=","", $recordNumber);
            $recordNumber = str_replace("http://ketab.ir/bookview.aspx?bookid=","",$recordNumber);
            $timeout = 120;
            $url = 'http://dcapi.k24.ir/test_get_book_id_majma/' . $recordNumber;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            $book_content = curl_exec($ch);
            if (curl_errno($ch)) {
                echo (" \n ---------- Try Get BOOK " . $recordNumber . "              ---------- ");
                echo 'error:' . curl_error($ch);
                MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => '500','xfunction_caller'=>$xfunction_caller ]);
            } else {
                echo (' recordNumber : '. $recordNumber);
                MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => '200','xfunction_caller'=>$xfunction_caller]);

                ////////////////////////////////////////////////// book data  ///////////////////////////////////////////////
                $book_content = json_decode($book_content);

                $book_content->title = self::remove_half_space_from_string($book_content->title);
                $book_content->title = self::convert_arabic_char_to_persian($book_content->title);

                ///////////////////////////////////////////////// book language ////////////////////////////////////////////
                if (!is_null($book_content->language) and !empty($book_content->language)) {
                    BookLanguage::firstOrCreate(array('name' => $book_content->language));
                }

                ///////////////////////////////////////////////// book format ////////////////////////////////////////////
                if (!is_null($book_content->sizeType) and !empty($book_content->sizeType)) {
                    BookFormat::firstOrCreate(array('name' => $book_content->sizeType));
                }

                ///////////////////////////////////////////////// book cover ////////////////////////////////////////////
                if (!is_null($book_content->coverType) and !empty($book_content->coverType)) {
                    BookCover::firstOrCreate(array('name' => $book_content->coverType));
                }

                // $bookIrBook = BookirBook::where('xpageurl', 'http://ketab.ir/bookview.aspx?bookid=' . $recordNumber)->orwhere('xpageurl', 'https://db.ketab.ir/bookview.aspx?bookid=' . $recordNumber)->orWhere('xpageurl2', 'https://ketab.ir/book/' . $book_content->uniqueId)->firstOrNew();
                $bookIrBook = BookirBook::where('xid', $book->xid)->firstOrNew();

                // book data
                if (!is_null($book_content->bookType)) {
                    $is_translate = ($book_content->bookType == 'تالیف') ? 1 : 2;
                } else {
                    $is_translate = (isset($bookIrBook->is_translate)) ? $bookIrBook->is_translate : 0;
                }

                echo ('isbn : '.$book_content->isbn);

                $book_content->isbn = self::validateIsbn($book_content->isbn);
                echo ('isbn : '.$book_content->isbn);
                if (!is_null($book_content->isbn)) {

                    $isbn13 = $book_content->isbn;
                    $isbn13 = str_replace("-", "", str_replace("0", "", $isbn13));

                    if (empty($isbn13)) {
                        $book_content->isbn = $isbn13;
                    }
                }

               
                $book_content->isbn10 = self::validateIsbn($book_content->isbn10);
                if (!is_null($book_content->isbn10)) {

                    $isbn10 = $book_content->isbn10;
                    $isbn10 = str_replace("-", "", str_replace("0", "", $isbn10));

                    if (empty($isbn10)) {
                        $book_content->isbn10 = $isbn10;
                    }
                }

                $bookIrBook->xpageurl = 'http://ketab.ir/bookview.aspx?bookid=' . $recordNumber;
                $bookIrBook->xpageurl2 = 'http://ketab.ir/book/' . $book_content->uniqueId;
                $bookIrBook->xname = (!is_null($book_content->title)) ? mb_substr($book_content->title,0,300, "UTF-8") : $bookIrBook->xname;
                $bookIrBook->xname2 = str_replace(" ", "", $bookIrBook->xname);
                $bookIrBook->xpagecount = (!is_null($book_content->pageCount)) ? $book_content->pageCount : $bookIrBook->xpagecount;
                $bookIrBook->xformat = (!is_null($book_content->sizeType)) ? $book_content->sizeType : $bookIrBook->xformat;
                $bookIrBook->xcover = (!is_null($book_content->coverType)) ? $book_content->coverType : $bookIrBook->xcover;
                $bookIrBook->xprintnumber = (!is_null($book_content->printVersion)) ? $book_content->printVersion : $bookIrBook->xprintnumber;
                $bookIrBook->xcirculation = (!is_null($book_content->circulation)) ? $book_content->circulation : $bookIrBook->xcirculation;
                $bookIrBook->xcovercount = (!is_null($book_content->volumeCount)) ? $book_content->volumeCount : $bookIrBook->xcovercount;
                $bookIrBook->xcovernumber =  (!is_null($book_content->volumeNumber)) ? $book_content->volumeNumber : $bookIrBook->xcovernumber;

                // 'xapearance'=> '' ;
                $bookIrBook->xisbn = (!is_null($book_content->isbn) && !empty($book_content->isbn)) ? $book_content->isbn : $bookIrBook->xisbn;
                $bookIrBook->xisbn3 = (!is_null($book_content->isbn) && !empty($book_content->isbn)) ? str_replace("-", "", $book_content->isbn) : substr(str_replace("-", "", $bookIrBook->xisbn),0,20);
                $bookIrBook->xisbn2 = (!is_null($book_content->isbn10) && !empty($book_content->isbn10)) ? str_replace("-", "",$book_content->isbn10) : $bookIrBook->xisbn2;

                $bookIrBook->xpublishdate = (!is_null($book_content->issueDate)) ? BookirBook::toGregorian(substr($book_content->issueDate,0,4) . '/'.substr($book_content->issueDate,4,2).'/'.substr($book_content->issueDate,6,2), '/', '-') : $bookIrBook->xpublishdate;
                $bookIrBook->xcoverprice = (!is_null($book_content->coverPrice)) ? $book_content->coverPrice : $bookIrBook->xcoverprice;
                // 'xminprice'=>'' ;
                // 'xcongresscode'=>'' ;
                $bookIrBook->xdiocode = (!is_null($book_content->dewey)) ? $book_content->dewey : $bookIrBook->xdiocode;
                $bookIrBook->xlang = (!is_null($book_content->language)) ? $book_content->language : $bookIrBook->xlang;
                if (!is_null($book_content->publishPlace)) {
                    //Replace multiple whitespace characters with a single space
                    $book_content->publishPlace = preg_replace('/\s+/', ' ', $book_content->publishPlace);
                }
                $bookIrBook->xpublishplace = (!is_null($book_content->publishPlace)) ? $book_content->publishPlace : $bookIrBook->xpublishplace;
                $bookIrBook->xdescription = (!is_null($book_content->abstract)) ? $book_content->abstract : $bookIrBook->xdescription;
                // 'xweight'=>'' ;
                $bookIrBook->ximgeurl = (!is_null($book_content->imageAddress)) ? $book_content->imageAddress : $bookIrBook->ximgeurl;
                $bookIrBook->xpdfurl = (!is_null($book_content->pdfAddress)) ? $book_content->pdfAddress : $bookIrBook->xpdfurl;
                $bookIrBook->xregdate = time();
                $bookIrBook->is_translate = $is_translate;

                $bookIrBook->save();
                echo ('$bookIrBook->xid : ');
                echo ($bookIrBook->xid);

                //////////////////////////////////////////////// publisher data /////////////////////////////////////////
                $timeout = 120;
                $url = 'http://dcapi.k24.ir/test_get_publisher_id_majma/' . $book_content->publisherId;
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_FAILONERROR, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_ENCODING, "");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                $publisher_content = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo (" \n ---------- Try Get PUBLISHER " . $book_content->publisherId . "              ---------- ");
                    echo 'error:' . curl_error($ch);
                    MajmaApiPublisher::create(['xpublisher_id' => $book_content->publisherId, 'xstatus' => '500']);
                } else {
                    MajmaApiPublisher::create(['xpublisher_id' => $book_content->publisherId, 'xstatus' => '200']);
                    $publisher_content = json_decode($publisher_content);

                    $publisher_content->title = self::remove_half_space_from_string($publisher_content->title);
                    $publisher_content->title = self::convert_arabic_char_to_persian($publisher_content->title);

                    $bookIrPublisher = BookirPublisher::where('xpageurl', 'http://ketab.ir//Publisherview.aspx?Publisherid=' . $publisher_content->id)->orWhere('xpageurl2', $publisher_content->url)->firstOrNew();

                    // publisher data
                    $publisher_manager = '';
                    $publisher_manager .= (!is_null($publisher_content->managerFirstName)) ? $publisher_content->managerFirstName : '';
                    $publisher_manager .= (!is_null($publisher_content->managerLastName)) ? ' ' . $publisher_content->managerLastName : '';

                    $bookIrPublisher->xpageurl = 'http://ketab.ir//Publisherview.aspx?Publisherid=' . $publisher_content->id;
                    $bookIrPublisher->xpageurl2 = $publisher_content->url;
                    $bookIrPublisher->xpublishername = (!is_null($publisher_content->title)) ? $publisher_content->title : $bookIrPublisher->xpublishername;
                    $bookIrPublisher->xmanager = (!empty($publisher_manager)) ? $publisher_manager : $bookIrPublisher->xmanager;
                    // $bookIrPublisher->xactivity = '';
                    if (!is_null($publisher_content->publisherPlace)) {
                        //Replace multiple whitespace characters with a single space
                        $publisher_content->publisherPlace = preg_replace('/\s+/', ' ', $publisher_content->publisherPlace);
                    }
                    $bookIrPublisher->xplace = (!is_null($publisher_content->publisherPlace)) ? $publisher_content->publisherPlace : $bookIrPublisher->xplace;
                    $bookIrPublisher->xaddress = (!is_null($publisher_content->address)) ? $publisher_content->address : $bookIrPublisher->xaddress;
                    // $bookIrPublisher->xpobox = '';
                    $bookIrPublisher->xzipcode = (!is_null($publisher_content->postalCode)) ? $publisher_content->postalCode : $bookIrPublisher->xzipcode;
                    $bookIrPublisher->xphone = (!is_null($publisher_content->phones)) ? implode('،', $publisher_content->phones) : $bookIrPublisher->xphone;
                    $bookIrPublisher->xcellphone = (!is_null($publisher_content->mobile)) ? $publisher_content->mobile : $bookIrPublisher->xcellphone;
                    $bookIrPublisher->xfax = (!is_null($publisher_content->fax)) ? $publisher_content->fax : $bookIrPublisher->xfax;
                    $bookIrPublisher->xlastupdate = (!is_null($publisher_content->lastUpdateDate)) ? $publisher_content->lastUpdateDate : $bookIrPublisher->xlastupdate;
                    // $bookIrPublisher->xtype = '';
                    $bookIrPublisher->xpermitno = (!is_null($publisher_content->permitNumber)) ? $publisher_content->permitNumber : $bookIrPublisher->xpermitno;
                    $bookIrPublisher->xemail = (!is_null($publisher_content->mail)) ? $publisher_content->mail : $bookIrPublisher->xemail;
                    $bookIrPublisher->xsite = (!is_null($publisher_content->site)) ? $publisher_content->site : $bookIrPublisher->xsite;
                    $bookIrPublisher->xisbnid = (!is_null($publisher_content->isbns)) ? implode(",", array_unique($publisher_content->isbns)) : $bookIrPublisher->xisbnid;
                    $bookIrPublisher->xfoundingdate = (!is_null($publisher_content->foundingDate)) ? $publisher_content->foundingDate : $bookIrPublisher->xfoundingdate;
                    // $bookIrPublisher->xispos = '';
                    $bookIrPublisher->ximageurl = (!is_null($publisher_content->image)) ? $publisher_content->image : $bookIrPublisher->ximageurl;
                    $bookIrPublisher->xregdate = time();
                    $bookIrPublisher->xpublishername2 = str_replace(" ", "", $publisher_content->title);
                    $bookIrPublisher->xisname = (!is_null($publisher_content->title)) ? 1 : 0;

                    $bookIrPublisher->save();
                    // echo ('$bookIrPublisher->xid');
                    // echo ($bookIrPublisher->xid);

                    if (isset($bookIrPublisher->xid) and !empty($bookIrPublisher->xid)) {
                        $bookIrBook->publishers()->sync($bookIrPublisher->xid);
                    }
                }

                //////////////////////////////////////////////// partner data /////////////////////////////////////////////////
                unset($partner_array);
                if (!is_null($book_content->authors)) {
                    foreach ($book_content->authors as $author_key => $author) {

                        $BookirPartner = BookirPartner::where('xketabir_id', $author->id)->firstOrNew();
                        $author->title = self::remove_half_space_from_string($author->title);
                        $author->title = self::convert_arabic_char_to_persian($author->title);

                        // partner data
                        if (mb_strpos($author->title, "،") > 0) {
                            $author_name = explode("،", $author->title);
                            $BookirPartner->xcreatorname = $author_name['1'] . ' ' . $author_name['0'];
                            $BookirPartner->xname2 = str_replace(" ", "", $BookirPartner->xcreatorname);
                        } else {
                            $BookirPartner->xcreatorname = $author->title;
                            $BookirPartner->xname2 = str_replace(" ", "", $author->title);
                        }

                        $BookirPartner->xketabir_id = $author->id;
                        $BookirPartner->xregdate = time();

                        $BookirPartner->save();

                        $BookirRules = BookirRules::where('xrole', $author->role)->first();
                        //rule data
                        if (empty($BookirRules)) {
                            $roleData = array(
                                'xrole' => $author->role,
                                'xregdate' => time(),
                            );
                            BookirRules::create($roleData);
                            $BookirRules = BookirRules::where('xrole', $author->role)->first();
                        }

                        $partner_array[$author_key]['xcreatorid'] = $BookirPartner->xid;
                        $partner_array[$author_key]['xroleid'] = $BookirRules->xid;

                        // echo ('$BookirPartner->xid');
                        // echo ($BookirPartner->xid);
                    }

                    if (isset($partner_array) and !empty($partner_array)) {
                        $bookIrBook->partnersRoles()->sync($partner_array);
                    }
                }

                //////////////////////////////////////////////// subject data /////////////////////////////////////////////////////////
                unset($subjects_array);
                if (!is_null($book_content->parentSubject)) {

                    $book_content->parentSubject = self::remove_half_space_from_string($book_content->parentSubject);
                    $book_content->parentSubject = self::convert_arabic_char_to_persian($book_content->parentSubject);

                    $BookirSubject = BookirSubject::where('xsubject', $book_content->parentSubject)->firstOrNew();
                    $BookirSubject->xsubject = $book_content->parentSubject;
                    $BookirSubject->xsubjectname2 = str_replace(" ", "", $book_content->parentSubject);
                    $BookirSubject->xregdate = time();

                    $BookirSubject->save();
                    $subjects_array[] = $BookirSubject->xid;
                    // echo ('$BookirSubject->xid');
                    // echo ($BookirSubject->xid);
                }

                if (!is_null($book_content->subjects)) {
                    foreach ($book_content->subjects as $subject) {
                        $subject = self::remove_half_space_from_string($subject);
                        $subject = self::convert_arabic_char_to_persian($subject);

                        $BookirSubject = BookirSubject::where('xsubject', $subject)->firstOrNew();
                        $BookirSubject->xsubject = $subject;
                        $BookirSubject->xsubjectname2 = str_replace(" ", "", $subject);
                        $BookirSubject->xregdate = time();

                        $BookirSubject->save();
                        // echo ('$BookirSubject->xid');
                        // echo ($BookirSubject->xid);
                        $subjects_array[] = $BookirSubject->xid;
                    }
                }

                if (isset($subjects_array) and !empty($subjects_array)) {
                    $bookIrBook->subjects()->sync($subjects_array);
                }

                //////////////////////////////////////////////// age group////////////////////////////////////////////////////////////////
                unset($ageGroup_array);
                if (!is_null($book_content->ageGroup)) {
                    ($book_content->ageGroup->a == true) ? $ageGroup_array['xa'] = 1 : $ageGroup_array['xa'] = 0;
                    ($book_content->ageGroup->b == true) ? $ageGroup_array['xb'] = 1 : $ageGroup_array['xb'] = 0;
                    ($book_content->ageGroup->g == true) ? $ageGroup_array['xg'] = 1 : $ageGroup_array['xg'] = 0;
                    ($book_content->ageGroup->d == true) ? $ageGroup_array['xd'] = 1 : $ageGroup_array['xd'] = 0;
                    ($book_content->ageGroup->h == true) ? $ageGroup_array['xh'] = 1 : $ageGroup_array['xh'] = 0;

                    if (isset($ageGroup_array) and !empty($ageGroup_array)) {
                        AgeGroup::updateOrCreate(
                            ['xbook_id' => $bookIrBook->xid],
                            $ageGroup_array
                        );
                    }
                }

            }

        }
    } 

    public static function convert_arabic_char_to_persian($string)
    {
        $string = str_replace("ي", "ی", $string);
        $string = str_replace("ك", "ک", $string);
        $string = str_replace("ة", "ه", $string);
        return $string;
    }

    /*  delete name space */
    public static function remove_half_space_from_string($string)
    {
        $string = urlencode($string);
        $string = str_replace('%E2%80%8C', ' ', $string);
        $string = urldecode($string);
        return $string;
    }

    public static function convert_arabic_num_to_english($string)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];

        $num = range(0, 9);
        $convertedPersianNums = str_replace($persian, $num, $string);
        $englishNumbersOnly = str_replace($arabic, $num, $convertedPersianNums);

        return $englishNumbersOnly;
    }

    public static function validateIsbn($isbn) //correction  isbn
    {
        $isbn = self::convert_arabic_num_to_english($isbn);
        $isbn = trim($isbn, ' ');
        $isbn = rtrim($isbn, ' ');
        $isbn = ltrim($isbn, ' ');

        $isbn = trim($isbn, '');
        $isbn = rtrim($isbn, '');
        $isbn = ltrim($isbn, '');

        $isbn = trim($isbn, '.');
        $isbn = rtrim($isbn, '.');

        $isbn = ltrim($isbn, ',');
        $isbn = ltrim($isbn, ',');

        $isbn = ltrim($isbn, '.');
        $isbn = ltrim($isbn, '"');

        $isbn = str_replace(" ", "", $isbn);
        $isbn = str_replace(".", "", $isbn);
        $isbn = str_replace("،", "", $isbn);
        // $isbn = str_replace("-", "", $isbn);
        $isbn = str_replace("+", "", $isbn);

        $isbn = str_replace(",", "", $isbn);
        $isbn = str_replace("،", "", $isbn);
        $isbn = str_replace("#", "", $isbn);
        $isbn = str_replace('"', "", $isbn);

        return $isbn;
    }

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
                        $query->orWhere('xisbn3', $search_shabak);
                        $query->orWhere('xisbn', $search_shabak1);
                        $query->orWhere('xisbn2', $search_shabak1);
                        $query->orWhere('xisbn3', $search_shabak1);
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
    public function update_temp_book_master_id_in_gissom($limit)
    {
        //gisoom table
        $gisoom_books = BookGisoom::where('temp_book_master_id', 0)->where('shabak10', '!=', NULL)->where('shabak13', '!=', NULL)->skip(0)->take($limit)->get();
        if ($gisoom_books->count() != 0) {
            foreach ($gisoom_books as $gisoom_book) {
                $search_shabak = $gisoom_book->shabak10;
                $search_shabak1 = $gisoom_book->shabak13;
                $main_book_info = BookirBook::where('xparent', '>=', -1)
                    ->where(function ($query) use ($search_shabak, $search_shabak1) {
                        $query->where('xisbn', $search_shabak);
                        $query->orWhere('xisbn2', $search_shabak);
                        $query->orWhere('xisbn3', $search_shabak);
                        $query->orWhere('xisbn', $search_shabak1);
                        $query->orWhere('xisbn2', $search_shabak1);
                        $query->orWhere('xisbn3', $search_shabak1);
                    })->first();
                if (!empty($main_book_info)) {
                    if ($main_book_info->xparent == -1) {
                        $gisoom_book->temp_book_master_id = $main_book_info->xid;
                    } else {
                        $gisoom_book->temp_book_master_id = $main_book_info->xparent;
                    }
                } else {
                    $gisoom_book->temp_book_master_id = -10;
                }

                $gisoom_book->update();
            }
            die("successfully update temp_book_master_id info");
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
                        $query->orWhere('xisbn3', $search_shabak);
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

    public function update_temp_book_master_id_in_digi($limit)
    {
        //digi
        $digi_books = BookDigi::where('temp_book_master_id', 0)->where('shabak', '!=', NULL)->skip(0)->take($limit)->get();
        if ($digi_books->count() != 0) {
            foreach ($digi_books as $digi_book) {
                $search_shabak = $digi_book->shabak;
                $main_book_info = BookirBook::where('xparent', '>=', -1)
                    ->where(function ($query) use ($search_shabak) {
                        $query->where('xisbn', $search_shabak);
                        $query->orWhere('xisbn2', $search_shabak);
                        $query->orWhere('xisbn3', $search_shabak);
                    })->first();
                if (!empty($main_book_info)) {
                    if ($main_book_info->xparent == -1) {
                        $digi_book->temp_book_master_id = $main_book_info->xid;
                    } else {
                        $digi_book->temp_book_master_id = $main_book_info->xparent;
                    }
                } else {
                    $digi_book->temp_book_master_id = -10;
                }

                $digi_book->update();
            }
            die("successfully update temp_book_master_id info");
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
                        $query->orWhere('xisbn3', $search_shabak);
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

    public function update_temp_book_master_id_in_30book($limit)
    {
        // 30book
        $c_books = Book30book::where('temp_book_master_id', 0)->where('shabak', '!=', NULL)->skip(0)->take($limit)->get();
        if ($c_books->count() != 0) {
            foreach ($c_books as $c_book) {
                $search_shabak = $c_book->shabak;
                $main_book_info = BookirBook::where('xparent', '>=', -1)
                    ->where(function ($query) use ($search_shabak) {
                        $query->where('xisbn', $search_shabak);
                        $query->orWhere('xisbn2', $search_shabak);
                        $query->orWhere('xisbn3', $search_shabak);
                    })->first();
                if (!empty($main_book_info)) {
                    if ($main_book_info->xparent == -1) {
                        $c_book->temp_book_master_id = $main_book_info->xid;
                    } else {
                        $c_book->temp_book_master_id = $main_book_info->xparent;
                    }
                } else {
                    $c_book->temp_book_master_id = -10;
                }

                $c_book->update();
            }
            die("successfully update temp_book_master_id info");
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
                        $query->orWhere('xisbn3', $search_shabak);
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

    public function update_temp_book_master_id_in_iranketab($limit)
    {
        // iranketab
        $iranketab_books = BookIranketab::where('temp_book_master_id', 0)->where('shabak', '>', 0)->skip(0)->take($limit)->get();
        if ($iranketab_books->count() != 0) {
            foreach ($iranketab_books as $iranketab_book) {
                $search_shabak = $iranketab_book->shabak;
                $main_book_info = BookirBook::where('xparent', '>=', -1)
                    ->where(function ($query) use ($search_shabak) {
                        $query->where('xisbn', $search_shabak);
                        $query->orWhere('xisbn2', $search_shabak);
                        $query->orWhere('xisbn3', $search_shabak);
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
                BookIranketab::where('parentId', $iranketab_book->parentId)->update(['temp_book_master_id' => $book_master_id]);
                // var_dump( $query);
                // $iranketab_book->update();

            }
            die("successfully update temp_book_master_id info");
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


    public function consensus_similar_books_isbn($limit){
        bookirbook::where('xparent',0)->orderBy('xid','DESC')->chunk(100, function ($books) {
            foreach($books as $book){
                $same_books = BookirBook::where('xisbn3',$book->xisbn3)->where('xparent','!=',0)->get();
                
            }
        });

    }
    public function consensus_similar_books_by_iranketab_parentId_new($limit)
    {
        DB::enableQueryLog();
        /////////////////////////////////////////پرونده سازی براساس پرونده های خانه کتاب /////////////////
        // سرگروه های پرونده در ایران کتاب
        $parentIranketabBooks = BookIranketab::whereColumn('parentId', 'recordNumber')->skip(0)->take($limit)->get();
        foreach($parentIranketabBooks as $parentIranketabBook){
            //  کتاب های پرونده کتاب در ایران کتاب 
            $childrenIranketabBooks = BookIranketab::select('shabak')->where('parentId',$parentIranketabBook->recordNumber )->get();
            // شابک های پرونده کتاب در ایران کتاب
            $dossier_isbns = array_unique(array_filter($childrenIranketabBooks->pluck('shabak')->all()));
            // کتاب های مشابه پرونده در خانه کتاب
            // $books = BookirBook::whereIN('xisbn',$dossier_isbns)->orWhereIN('xisbn2',$dossier_isbns)->orWhereIN('xisbn3',$dossier_isbns)->get();

            // پیدا کردن سرگروه براساس خانه کتاب  
            // سال چاپ پایین تر و شماره چاپ پایین تر = اولین کتاب چاپ شده در پرونده
            $bookIrBookParent = BookirBook::whereIN('xisbn3',$dossier_isbns)/*->orWhereIN('xisbn2',$dossier_isbns)->orWhereIN('xisbn',$dossier_isbns)*/->orderBy('xpublishdate', 'DESC')->orderBy('xprintnumber', 'ASC')->first();
            BookirBook::whereIN('xisbn3',$dossier_isbns)/*->orWhereIN('xisbn2',$dossier_isbns)->orWhereIN('xisbn',$dossier_isbns)*/->update(['xtempparent' => $bookIrBookParent->xid]);
            BookirBook::where('xid',$bookIrBookParent->xid)->update(['xtempparent' => -1]);
        }
        ///////////////////////////////////////// پرونده سازی براساس نام کتاب ////////////////////////////////////////
        ///////////////////////////////////////// پرونده سازی کتا ب های خارجی براساس نام نویسنده ////////////////////////////////////////

        // echo '<pre>'; print_r($books);
        $query = DB::getQueryLog();
        dd($query);
    }
    public function consensus_similar_books_by_iranketab_parentId($limit)
    {
        $allIranketabBooks = BookIranketab::where('temp_book_master_id', 0)->where('enTitle', '!=', '')->skip(0)->take($limit)->get();
        // $allIranketabBooks = BookIranketab::where('parentId', 433)->skip(0)->take($limit)->get();
        if ($allIranketabBooks->count() != 0) {
            foreach ($allIranketabBooks as $allIranketabBookItem) {
                // echo ' book_id : ' . $allIranketabBookItem->id . ' book name : ' . $allIranketabBookItem->title . '  en book name : ' . $allIranketabBookItem->enTitle . '</br>';
                // echo ' book_id : ' . $allIranketabBookItem->id . ' book parentId : ' . $allIranketabBookItem->parentId  . '</br>';
                $iranketabBooks = BookIranketab::where('parentId', $allIranketabBookItem->parentId)->where('shabak', '!=', '')->get(); // پیدا کردن رکوردها ایران کتاب با parentId
                // $allBookirBooks = BookirBook::whereIN('xisbn2', $iranketabBooks->pluck('shabak')->all())->get(); // پیدا کردن شابک های کتاب های با parentId
                $allBookirBooks = BookirBook::where('xparent','>=',-1)->where('xrequest_manage_parent','!=',1);
                $allBookirBooks->where(function($query) use($iranketabBooks){
                    $query->whereIN('xisbn', $iranketabBooks->pluck('shabak'));
                    $query->OrwhereIN('xisbn2', $iranketabBooks->pluck('shabak'));
                    $query->OrwhereIN('xisbn3', $iranketabBooks->pluck('shabak'));
                })->all()->get(); // پیدا کردن شابک های کتاب های با parentId
                if ($allBookirBooks->count() != 0) {
                    $allBookirBooksIsbn1Collection =  $allBookirBooks->pluck('xisbn')->all();
                    $allBookirBooksIsbn2Collection =  $allBookirBooks->pluck('xisbn2')->all();
                    $allBookirBooksIsbn3Collection =  $allBookirBooks->pluck('xisbn3')->all();
                    $allBookirBooksIsbn2_3Collection = array_merge($allBookirBooksIsbn2Collection,$allBookirBooksIsbn3Collection);
                    $allBookirBooksIsbnCollection = array_merge($allBookirBooksIsbn1Collection,$allBookirBooksIsbn2_3Collection);

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
                        }
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

    public function merge_parentid_tempparentid($limit)
    {
        echo 'start : ' . date("H:i:s", time()) . '</br>';
        $books = BookirBook::where('xmerge', 0)->skip(0)->take($limit)->get();
        if ($books->count() != 0) {
            foreach ($books as $bookItem) {
                if ($bookItem->xparent < -1) {
                    BookirBook::where('xid', $bookItem->xid)->update(['xmergeparent' =>  $bookItem->xparent]);
                    BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  -1]);
                }elseif ($bookItem->xtempparent == -1 or $bookItem->xtempparent > 0) {
                    BookirBook::where('xid', $bookItem->xid)->update(['xmergeparent' =>  $bookItem->xtempparent]);
                    BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  1]);
                } elseif ($bookItem->xtempparent == 0) {
                    if($bookItem->xparent == -1){
                        $suggest_parent = BookirBook::where('xparent',$bookItem->xid)->where('xtempparent','>',0)->first();
                        // var_dump($suggest_parent);
                        if(!empty($suggest_parent) and !empty($suggest_parent->xtempparent)){
                            BookirBook::where('xid', $bookItem->xid)->update(['xmergeparent' =>  $suggest_parent->xtempparent]);
                            BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  2]);
                        }else{
                            BookirBook::where('xid', $bookItem->xid)->update(['xmergeparent' =>  $bookItem->xparent]);
                            BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  3]);
                        }
                    }elseif($bookItem->xparent > 0){
                        $suggest_parent = BookirBook::where('xparent',$bookItem->xparent)->where('xtempparent','>',0)->first();
                        // var_dump($suggest_parent);
                        if(!empty($suggest_parent) and !empty($suggest_parent->xtempparent)){
                            BookirBook::where('xid', $bookItem->xid)->update(['xmergeparent' =>  $suggest_parent->xtempparent]);
                            BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  4]);
                        }else{
                            BookirBook::where('xid', $bookItem->xid)->update(['xmergeparent' =>  $bookItem->xparent]);
                            BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  5]);
                        }
                    }else{
                        BookirBook::where('xid', $bookItem->xid)->update(['xmergeparent' =>  $bookItem->xparent]);
                        BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  6]);
                    }
                    
                } 
            }
        }
        echo 'end : ' . date("H:i:s", time()) . '</br>';
    }
    public function merge_parentid_tempparentid_desc($limit)
    {
        echo 'start : ' . date("H:i:s", time()) . '</br>';
        $books = BookirBook::where('xmerge', 0)->orderBy('xid','DESC')->skip(0)->take($limit)->get();
        if ($books->count() != 0) {
            foreach ($books as $bookItem) {
                if ($bookItem->xparent < -1) {
                    BookirBook::where('xid', $bookItem->xid)->update(['xmergeparent' =>  $bookItem->xparent]);
                    BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  -1]);
                }elseif ($bookItem->xtempparent == -1 or $bookItem->xtempparent > 0) {
                    BookirBook::where('xid', $bookItem->xid)->update(['xmergeparent' =>  $bookItem->xtempparent]);
                    BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  1]);
                } elseif ($bookItem->xtempparent == 0) {
                    if($bookItem->xparent == -1){
                        $suggest_parent = BookirBook::where('xparent',$bookItem->xid)->where('xtempparent','>',0)->first();
                        // var_dump($suggest_parent);
                        if(!empty($suggest_parent) and !empty($suggest_parent->xtempparent)){
                            BookirBook::where('xid', $bookItem->xid)->update(['xmergeparent' =>  $suggest_parent->xtempparent]);
                            BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  2]);
                        }else{
                            BookirBook::where('xid', $bookItem->xid)->update(['xmergeparent' =>  $bookItem->xparent]);
                            BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  3]);
                        }
                    }elseif($bookItem->xparent > 0){
                        $suggest_parent = BookirBook::where('xparent',$bookItem->xparent)->where('xtempparent','>',0)->first();
                        // var_dump($suggest_parent);
                        if(!empty($suggest_parent) and !empty($suggest_parent->xtempparent)){
                            BookirBook::where('xid', $bookItem->xid)->update(['xmergeparent' =>  $suggest_parent->xtempparent]);
                            BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  4]);
                        }else{
                            BookirBook::where('xid', $bookItem->xid)->update(['xmergeparent' =>  $bookItem->xparent]);
                            BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  5]);
                        }
                    }else{
                        BookirBook::where('xid', $bookItem->xid)->update(['xmergeparent' =>  $bookItem->xparent]);
                        BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  6]);
                    }
                    
                } 
            }
        }
        echo 'end : ' . date("H:i:s", time()) . '</br>';
    }

   /* public function update_tempparent_to_other_fields($limit)
    {
        echo 'start : ' . date("H:i:s", time()) . '</br>';
        $books = BookirBook::where('xmerge', 0)->orderBy('xtempparent', 'ASC')->skip(0)->take($limit)->get();
        if ($books->count() != 0) {
            foreach ($books as $bookItem) {
                if ($bookItem->xtempparent > 0) {
                    try {
                        BookIranketab::where('book_master_id', $bookItem->xparent)->update(['book_master_id' => $bookItem->xtempparent]);
                        BookGisoom::where('book_master_id', $bookItem->xparent)->update(['book_master_id' => $bookItem->xtempparent]);
                        BookDigi::where('book_master_id', $bookItem->xparent)->update(['book_master_id' => $bookItem->xtempparent]);
                        Book30book::where('book_master_id', $bookItem->xparent)->update(['book_master_id' => $bookItem->xtempparent]);
                        BookirBook::where('xparent', $bookItem->xparent)->update(['xparent' =>  $bookItem->xtempparent]);
                        BookirBook::where('xid', $bookItem->xid)->update(['xparent' =>  $bookItem->xtempparent]);
                        BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  1]);
                    } catch (Exception $Exception) {
                        //throw $th;
                        echo " update book_master_id error " . $Exception->getMessage() . '</br>';
                    }
                } elseif ($bookItem->xtempparent == -1) {
                    try {
                        BookirBook::where('xid', $bookItem->xid)->update(['xparent' =>  $bookItem->xtempparent]);
                        BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  -1]);
                    } catch (Exception $Exception) {
                        //throw $th;
                        echo " update book_master_id error " . $Exception->getMessage() . '</br>';
                    }
                } else {
                    BookirBook::where('xid', $bookItem->xid)->update(['xmerge' =>  -10]);
                }
            }
        }
        echo 'end : ' . date("H:i:s", time()) . '</br>';
    }*/
}
