<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Book;

class FindBookNlai extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'find:booknlai {txt?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find book data in nlai api';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
                $host= "z3950.nlai.ir:210";

                $z = yaz_connect($host);

                // $isbn = 9786000421021;
                // yaz_search($z, 'rpn', '@attr 1=7 "' .$isbn. '"');

                // $name = 'تاریخ امرا';
                // yaz_search($z, 'rpn', '@attr 1=4 "' .$name. '"');

                //$book = Book::orderBy('created_at', 'desc')->first();
                $book = Book::find(1330584);
                $this->info(" \n ---------- Get Book Nlai  ".$book->id." === ".$book->Title."        ---------=-- \n ");
                yaz_search($z, 'rpn', '@attr 1=4 "' .$book->Title.'"');

                yaz_wait();
                $error = yaz_error($z);
                if (!empty($error)) {
                    echo "Error: $error";
                } else {
                    $hits = yaz_hits($z);

                    if ($hits == 0){
                        $export = NULL;
                        header('Content-Type: application/json');
                        die(json_encode($export));
                    }


                    for ($p = 1; $p <= 1; $p++) {
                        $rec = yaz_record($z, $p, "string");
                        if (empty($rec)) continue;


                        $list = explode("\n", $rec);
                    }
                }

                print_r($list);
                $subjects = array();
                $creators = array();
                $publishers = array();
                foreach($list as $key => $val){
                    //----------------رده دیویی----------------/
                    if (strpos($val, '676 ') !== false) {
                        $temp = explode("$", $val);
                        foreach($temp as $key2 => $val2){
                            if (strpos($val2, 'a ') !== false) {
                                $diocode = str_replace("a ", "" , $val2);
                                $diocode = str_replace("/", "." , $diocode);
                                $diocode = cleanFaAlphabet(faCharToEN($diocode));
                                $diocode = str_replace("‮فا‬", "fa" , $diocode);
                            }
                        }
                    }
                    //----------------رده دیویی----------------/
                    //----------------تعداد صفحه----------------/
                    if (strpos($val, '215 ') !== false) {
                        $temp = explode("$", $val);
                        foreach($temp as $key2 => $val2){
                            if (strpos($val2, 'a ') !== false) {
                                $pagecount = str_replace("a ", "" , $val2);
                                $pagecount = str_replace("ص.", "" , $pagecount);
                                $pagecount = str_replace(" ", "" , $pagecount);
                                $pagecount = cleanFaAlphabet(faCharToEN(trim($pagecount)));
                                $pagecount = cleanFaAlphabet(faCharToEN($pagecount));
                            }
                        }
                    }
                    //----------------تعداد صفحه----------------/
                    //----------------قیمت----------------/
                    if (strpos($val, '010 ') !== false) {
                        $prices = array();
                        $temp = explode("$", $val);
                        foreach($temp as $key2 => $val2){
                            if (strpos($val2, 'd ') !== false) {
                                $price = str_replace("d ", "" , $val2);
                                $price = str_replace("ریال", "" , $price);
                                $price = str_replace(" ", "" , $price);
                                $price = cleanFaAlphabet(faCharToEN(trim($price)));
                                $price = cleanFaAlphabet(faCharToEN($price));
                                array_push($prices, $price);
                            }
                        }
                        $maxprice = max($prices);
                    }
                    //----------------قیمت----------------/
                    //----------------isbn----------------/
                    if (strpos($val, '010 ') !== false) {
                        $temp = explode("$", $val);
                        foreach($temp as $key2 => $val2){
                            if (strpos($val2, 'a ') !== false) {
                                $newisbn = str_replace("a ", "" , $val2);
                                $newisbn = str_replace(" ", "" , $newisbn);
                                $newisbn = str_replace(":", "" , $newisbn);
                                $newisbn = cleanFaAlphabet(faCharToEN(trim($newisbn)));
                            }
                        }
                    }
                    //----------------isbn----------------/
                    //----------------عنوان----------------/
                    if (strpos($val, '200 ') !== false) {
                        $temp = explode("$", $val);
                        foreach($temp as $key2 => $val2){
                            if (strpos($val2, 'a ') !== false) {
                                $title = str_replace("a ", "" , $val2);
                                $title = faAlphabetKeep(trim($title));
                            }
                        }
                    }
                    //----------------عنوان----------------/
                    //----------------سال انتشار----------------/
                    if (strpos($val, '210 ') !== false) {
                        $temp = explode("$", $val);
                        foreach($temp as $key2 => $val2){
                            if (strpos($val2, 'd ') !== false) {
                                $year = str_replace("d ", "" , $val2);
                                $year = str_replace(" ", "" , $year);
                                $year = cleanFaAlphabet(faCharToEN(trim($year)));
                                $year = cleanFaAlphabet(faCharToEN($year));
                            }
                        }
                    }
                    //----------------سال انتشار----------------/
                    //----------------ناشر----------------/
                    if (strpos($val, '210 ') !== false) {
                        $temp = explode("$", $val);
                        foreach($temp as $key2 => $val2){
                            if (strpos($val2, 'c ') !== false) {
                                $publisher = str_replace("c ", "" , $val2);
                                $publisher = str_replace(":", "" , $publisher);
                                $publisher = faAlphabetKeep(trim($publisher));
                            }
                        }
                        array_push($publishers, $publisher);
                    }
                    //----------------ناشر----------------/
                    //----------------موضوع----------------/
                    if (strpos($val, '606 ') !== false) {
                        $temp = explode("$", $val);
                        $sub1 = "";
                        $sub2 = "";
                        foreach($temp as $key2 => $val2){
                            if (strpos($val2, 'a ') !== false) {
                                $sub1 = str_replace("a ", "" , $val2);
                                $sub1 = faAlphabetKeep(trim($sub1));
                            }
                            if (strpos($val2, 'z ') !== false) {
                                $sub2 = str_replace("z  -- ", "" , $val2);
                                $sub2 = str_replace("z --", "" , $sub2);
                                $sub2 = faAlphabetKeep(trim($sub2));
                            }
                            if ($sub2 != ""){
                                $subject = $sub1 . " - " .$sub2;
                                //برای پاک کردن کلمه اول که قبلا در آرایه تگها ریخته شده است
                                if (($key3 = array_search($sub1, $subjects)) !== false) {
                                    //unset($subjects[$key3]);
                                    array_splice($subjects, $key3, 1);
                                }

                            }else{
                                $subject = $sub1;
                            }
                            $subject = cleanFaAlphabet(faCharToEN(trim($subject)));
                            if (!in_array($subject, $subjects) && $subject != "")
                            {
                                array_push($subjects, $subject);
                            }
                        }
                    }
                    //----------------موضوع----------------/
                    //----------------پدیدآورنده اصلی----------------/
                    if (strpos($val, '700 ') !== false) {
                        //$name = get_string_between($val, "b ،", "$");
                        $name = $val;
                        //$family = get_string_between($val, "a ", "$");
                        $family = $val;

                        if ($name != 'null'){
                            $newname = faAlphabetKeep($name . " " . $family);
                            if (!in_array($newname, $creators) && $newname != "")
                            {
                                array_push($creators, $newname);
                            }
                        }
                    }
                    //----------------پدیدآورنده اصلی----------------/
                    //----------------پدیدآورنده فرعی----------------/
                    if (strpos($val, '702 ') !== false) {
                        //$name = get_string_between($val, "b ،", "$");
                        $name = $val;
                        //$family = get_string_between($val, "a ", "$");
                        $family = $val;

                        if ($name != 'null'){
                            $newname = faAlphabetKeep($name . " " . $family);
                            if (!in_array($newname, $creators) && $newname != "")
                            {
                                array_push($creators, $newname);
                            }
                        }

                    }
                    //----------------پدیدآورنده فرعی----------------/



                }

                    $export = array();
                    $export['BookID'] = 0;
                    $export['Title'] = $title;
                    $export['ISBN'] = $newisbn;
                    $export['Price'] = $maxprice;
                    $export['PageCount'] = $pagecount;
                    $export['PicAddress'] = "";
                    $export['Context'] = "";
                    $export['PubDate'] = $year;
                    $export['PrintNumber'] = "";
                    $export['DioCode'] = $diocode;
                    $export['Lang'] = "";
                    $export['Circulation'] = "";
                    $export['PubPlace'] = "";
                    $export['Format'] = "";
                    $expopublishers = array();
                    foreach($publishers as $key => $val){
                        $expopublishers[$key]['PublisherName'] = trim($val) ;
                    }
                    $export['PublisherList'] = $expopublishers;


                    $expocreators = array();
                    foreach($creators as $key => $val){
                        $expocreators[$key]['CreatorName'] = trim($val) ;
                    }
                    $export['CreatorList'] = $expocreators;
                    $export['SubjectArray'] = $subjects;
                    $export['SubjectList'] = implode("," ,$subjects);

                    print_r($export);

    }
}
