<?php

namespace App\Console\Commands;

use App\Models\AgeGroup;
use App\Models\BookCover;
use App\Models\BookFormat;
use App\Models\BookirBook;
use App\Models\BookirPartner;
use App\Models\BookirPublisher;
use App\Models\BookirRules;
use App\Models\BookirSubject;
use App\Models\BookLanguage;
use App\Models\Crawler as CrawlerM;
use App\Models\MajmaApiBook;
use App\Models\MajmaApiPublisher;
use Goutte\Client;
use Illuminate\Console\Command;
use Symfony\Component\HttpClient\HttpClient;

class Getmajma extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:majma {crawlerId} {miss?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get majma Book Command';

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
        if ($this->argument('miss') && $this->argument('miss') == 1) {
            try {
                $lastCrawler = CrawlerM::where('type', 2)->where('status', 1)->orderBy('end', 'ASC')->first();
                if (isset($lastCrawler->end)) {
                    $startC = $lastCrawler->start;
                    $endC = $lastCrawler->end;
                    $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
                    $newCrawler = $lastCrawler;
                }
            } catch (\Exception $e) {
                $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
            }
        } else {
            try {
                $lastCrawler = CrawlerM::where('name', 'LIKE', 'Crawler-Majma-%')->where('type', 2)->orderBy('end', 'desc')->first();
                if (isset($lastCrawler->end)) {
                    $startC = $lastCrawler->end + 1;
                } else {
                    $startC = 1;
                }

                $endC = $startC + CrawlerM::$crawlerSize;
                $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
                $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-Majma-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
            } catch (\Exception $e) {
                $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
            }
        }

        if (isset($newCrawler)) {

            $client = new Client(HttpClient::create(['timeout' => 30]));

            $bar = $this->output->createProgressBar(CrawlerM::$crawlerSize);
            $bar->start();

            $recordNumber = $startC;

            while ($recordNumber <= $endC) {
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
                    $this->info(" \n ---------- Try Get BOOK " . $recordNumber . "              ---------- ");
                    echo 'error:' . curl_error($ch);
                    MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => '500']);
                } else {
                    $this->info(' recordNumber : '. $recordNumber);
                    MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => '200']);

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

                    $bookIrBook = BookirBook::where('xpageurl', 'http://ketab.ir/bookview.aspx?bookid=' . $recordNumber)->orWhere('xpageurl2', 'https://ketab.ir/book/' . $book_content->uniqueId)->firstOrNew();

                    // book data
                    if (!is_null($book_content->bookType)) {
                        $is_translate = ($book_content->bookType == 'تالیف') ? 1 : 2;
                    } else {
                        $is_translate = (isset($bookIrBook->is_translate)) ? $bookIrBook->is_translate : 0;
                    }

                    if (!is_null($book_content->isbn)) {

                       $book_content->isbn = self::validateIsbn($book_content->isbn);
                       $isbn13 = str_replace("-", "", str_replace("0", "", $book_content->isbn));

                        if (empty($isbn13)) {
                            $book_content->isbn = $isbn13;
                        }
                    }

                    if (!is_null($book_content->isbn10)) {

                        $book_content->isbn10 = self::validateIsbn($book_content->isbn10);
                        $isbn10 = str_replace("-", "", str_replace("0", "", $book_content->isbn));

                        if (empty($isbn10)) {
                            $book_content->isbn10 = $isbn10;
                        }
                    }

                    $bookIrBook->xpageurl = 'http://ketab.ir/bookview.aspx?bookid=' . $recordNumber;
                    $bookIrBook->xpageurl2 = 'http://ketab.ir/book/' . $book_content->uniqueId;
                    $bookIrBook->xname = (!is_null($book_content->title)) ? $book_content->title : $bookIrBook->xname;
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
                    $bookIrBook->xisbn2 = (!is_null($book_content->isbn10) && !empty($book_content->isbn10)) ? $book_content->isbn10 : $bookIrBook->xisbn2;

                    $bookIrBook->xpublishdate = (!is_null($book_content->issueDate)) ? BookirBook::toGregorian(substr($book_content->issueDate,0,4) . '/'.substr($book_content->issueDate,4,2).'/'.substr($book_content->issueDate,6,2), '/', '-') : $bookIrBook->xpublishdate;
                    $bookIrBook->xcoverprice = (!is_null($book_content->coverPrice)) ? $book_content->coverPrice : $bookIrBook->xcoverprice;
                    // 'xminprice'=>'' ;
                    // 'xcongresscode'=>'' ;
                    $bookIrBook->xdiocode = (!is_null($book_content->dewey)) ? $book_content->dewey : $bookIrBook->xdiocode;
                    $bookIrBook->xlang = (!is_null($book_content->language)) ? $book_content->language : $bookIrBook->xlang;
                    $bookIrBook->xpublishplace = (!is_null($book_content->publishPlace)) ? $book_content->publishPlace : $bookIrBook->xpublishplace;
                    $bookIrBook->xdescription = (!is_null($book_content->abstract)) ? $book_content->abstract : $bookIrBook->xdescription;
                    // 'xweight'=>'' ;
                    $bookIrBook->ximgeurl = (!is_null($book_content->imageAddress)) ? $book_content->imageAddress : $bookIrBook->ximgeurl;
                    $bookIrBook->xpdfurl = (!is_null($book_content->pdfAddress)) ? $book_content->pdfAddress : $bookIrBook->xpdfurl;
                    $bookIrBook->xregdate = time();
                    $bookIrBook->is_translate = $is_translate;

                    $bookIrBook->save();
                    $this->info('$bookIrBook->xid : ');
                    $this->info($bookIrBook->xid);

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
                        $this->info(" \n ---------- Try Get PUBLISHER " . $book_content->publisherId . "              ---------- ");
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
                        $bookIrPublisher->xisbnid = (!is_null($publisher_content->isbns)) ? implode(",", $publisher_content->isbns) : $bookIrPublisher->xisbnid;
                        $bookIrPublisher->xfoundingdate = (!is_null($publisher_content->foundingDate)) ? $publisher_content->foundingDate : $bookIrPublisher->xfoundingdate;
                        // $bookIrPublisher->xispos = '';
                        $bookIrPublisher->ximageurl = (!is_null($publisher_content->image)) ? $publisher_content->image : $bookIrPublisher->ximageurl;
                        $bookIrPublisher->xregdate = time();
                        $bookIrPublisher->xpublishername2 = str_replace(" ", "", $publisher_content->title);
                        $bookIrPublisher->xisname = (!is_null($publisher_content->title)) ? 1 : 0;

                        $bookIrPublisher->save();
                        // $this->info('$bookIrPublisher->xid');
                        // $this->info($bookIrPublisher->xid);

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

                            // $this->info('$BookirPartner->xid');
                            // $this->info($BookirPartner->xid);
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
                        // $this->info('$BookirSubject->xid');
                        // $this->info($BookirSubject->xid);
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
                            // $this->info('$BookirSubject->xid');
                            // $this->info($BookirSubject->xid);
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

                // $bar->advance();*/
                CrawlerM::where('name','Crawler-Majma-'.$this->argument('crawlerId'))->where('start',$startC)->update(['last'=>$recordNumber]);
                $recordNumber++;
            }
            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $bar->finish();
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
        $isbn = str_replace("-", "", $isbn);
        $isbn = str_replace("+", "", $isbn);

        $isbn = str_replace(",", "", $isbn);
        $isbn = str_replace("،", "", $isbn);
        $isbn = str_replace("#", "", $isbn);
        $isbn = str_replace('"', "", $isbn);

        $isbn = str_replace("-", "", str_replace("0", "", $isbn));
        return $isbn;
    }

}
