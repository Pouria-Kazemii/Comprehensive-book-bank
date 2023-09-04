<?php

namespace App\Console\Commands;

use App\Models\AgeGroup;
use App\Models\BookirBook;
use App\Models\BookirPartner;
use App\Models\BookirPublisher;
use App\Models\BookirSubject;
use App\Models\Crawler as CrawlerM;
use App\Models\MajmaApiBook;
use App\Models\MajmaApiPublisher;
use Goutte\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
                unset($filtered);

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

                    MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => '200']);

                    ////////////////////////////////////////////////// book data  ///////////////////////////////////////////////
                    $book_content = json_decode($book_content);

                    $bookIrBook = BookirBook::where('xpageurl', 'http://ketab.ir/bookview.aspx?bookid=' . $recordNumber)->orWhere('xpageurl2', 'https://ketab.ir/book/' . $book_content->uniqueId)->first();
                    if (!is_null($book_content->bookType)) {
                        $is_translate = ($book_content->bookType == 'تالیف') ? 1 : 2;
                    } else {
                        $is_translate = (isset($bookIrBook->is_translate)) ? $bookIrBook->is_translate : 0;
                    }
                    // '' => $book_content->id ,
                    // '' => $book_content->publicationId ,
                    // '' => $book_content->mainTitle ,
                    // '' => $book_content->subjects ,
                    // '' => $book_content->parentSubject ,
                    // '' => $book_content->publisherTitle ,
                    // '' => $book_content->publisherId ,
                    // '' => $book_content->contentStatusId ,
                    // '' => $book_content->ageGroup ,
                    // '' => $book_content->authors ,

                    if (!is_null($book_content->isbn)) {

                        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];

                        $num = range(0, 9);
                        $book_content->isbn = str_replace($persian, $num, $book_content->isbn);
                        $book_content->isbn = str_replace($arabic, $num, $book_content->isbn);

                        $book_content->isbn = trim($book_content->isbn, ' ');
                        $book_content->isbn = rtrim($book_content->isbn, ' ');
                        $book_content->isbn = ltrim($book_content->isbn, ' ');

                        $book_content->isbn = trim($book_content->isbn, '');
                        $book_content->isbn = rtrim($book_content->isbn, '');
                        $book_content->isbn = ltrim($book_content->isbn, '');

                        $book_content->isbn = trim($book_content->isbn, '.');
                        $book_content->isbn = rtrim($book_content->isbn, '.');

                        $book_content->isbn = ltrim($book_content->isbn, ',');
                        $book_content->isbn = ltrim($book_content->isbn, ',');

                        $book_content->isbn = ltrim($book_content->isbn, '.');
                        $book_content->isbn = ltrim($book_content->isbn, '"');

                        $book_content->isbn = str_replace(" ", "", $book_content->isbn);
                        $book_content->isbn = str_replace(".", "", $book_content->isbn);
                        $book_content->isbn = str_replace("،", "", $book_content->isbn);
                        $book_content->isbn = str_replace("-", "", $book_content->isbn);
                        $book_content->isbn = str_replace("+", "", $book_content->isbn);

                        $book_content->isbn = str_replace(",", "", $book_content->isbn);
                        $book_content->isbn = str_replace("،", "", $book_content->isbn);
                        $book_content->isbn = str_replace("#", "", $book_content->isbn);
                        $book_content->isbn = str_replace('"', "", $book_content->isbn);

                        $book_content->isbn = str_replace(",", "", $book_content->isbn);
                        $book_content->isbn = str_replace("،", "", $book_content->isbn);
                        $book_content->isbn = str_replace("#", "", $book_content->isbn);
                        $isbn13 = str_replace("-", "", str_replace("0", "", $book_content->isbn));
                        if (empty($isbn13)) {
                            $book_content->isbn = $isbn13;
                        }
                    }

                    if (!is_null($book_content->isbn10)) {

                        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];

                        $num = range(0, 9);
                        $book_content->isbn10 = str_replace($persian, $num, $book_content->isbn10);
                        $book_content->isbn10 = str_replace($arabic, $num, $book_content->isbn10);

                        $book_content->isbn10 = trim($book_content->isbn10, ' ');
                        $book_content->isbn10 = rtrim($book_content->isbn10, ' ');
                        $book_content->isbn10 = ltrim($book_content->isbn10, ' ');

                        $book_content->isbn10 = trim($book_content->isbn10, '');
                        $book_content->isbn10 = rtrim($book_content->isbn10, '');
                        $book_content->isbn10 = ltrim($book_content->isbn10, '');

                        $book_content->isbn10 = trim($book_content->isbn10, '.');
                        $book_content->isbn10 = rtrim($book_content->isbn10, '.');

                        $book_content->isbn10 = ltrim($book_content->isbn10, ',');
                        $book_content->isbn10 = ltrim($book_content->isbn10, ',');

                        $book_content->isbn10 = ltrim($book_content->isbn10, '.');
                        $book_content->isbn10 = ltrim($book_content->isbn10, '"');

                        $book_content->isbn10 = str_replace(" ", "", $book_content->isbn10);
                        $book_content->isbn10 = str_replace(".", "", $book_content->isbn10);
                        $book_content->isbn10 = str_replace("،", "", $book_content->isbn10);
                        $book_content->isbn10 = str_replace("-", "", $book_content->isbn10);
                        $book_content->isbn10 = str_replace("+", "", $book_content->isbn10);

                        $book_content->isbn10 = str_replace(",", "", $book_content->isbn10);
                        $book_content->isbn10 = str_replace("،", "", $book_content->isbn10);
                        $book_content->isbn10 = str_replace("#", "", $book_content->isbn10);
                        $book_content->isbn10 = str_replace('"', "", $book_content->isbn10);

                        $isbn10 = str_replace("-", "", str_replace("0", "", $book_content->isbn10));
                        if (empty($isbn10)) {
                            $book_content->isbn10 = $isbn10;
                        }
                    }

                    $book_data = array(
                        'xpageurl' => 'http://ketab.ir/bookview.aspx?bookid=' . $recordNumber,
                        'xpageurl2' => 'http://ketab.ir/bookview.aspx?bookid=' . $book_content->uniqueId,
                        'xname' => (!is_null($book_content->title)) ? $book_content->title : $bookIrBook->xname,
                        'xpagecount' => (!is_null($book_content->pageCount)) ? $book_content->pageCount : $bookIrBook->xpagecount,
                        'xformat' => (!is_null($book_content->sizeType)) ? $book_content->sizeType : $bookIrBook->xformat,
                        'xcover' => (!is_null($book_content->coverType)) ? $book_content->coverType : $bookIrBook->xcover,
                        'xprintnumber' => (!is_null($book_content->printVersion)) ? $book_content->printVersion : $bookIrBook->xprintnumber,
                        'xcirculation' => (!is_null($book_content->circulation)) ? $book_content->circulation : $bookIrBook->xcirculation,
                        // 'xcovernumber'=> '', شماره جلد
                        'xcovercount' => (!is_null($book_content->volumeCount)) ? $book_content->volumeCount : $bookIrBook->xcovercount,
                        // 'xapearance'=> '',
                        'xisbn' => (!is_null($book_content->isbn) && !empty($book_content->isbn)) ? $book_content->isbn : $bookIrBook->xisbn,
                        'xisbn3' => (!is_null($book_content->isbn) && !empty($book_content->isbn)) ? str_replace("-", "", $book_content->isbn) : str_replace("-", "", $bookIrBook->xisbn),
                        'xisbn2' => (!is_null($book_content->isbn10) && !empty($book_content->isbn10)) ? $book_content->isbn10 : $bookIrBook->xisbn2,
                        'xpublishdate' => (!is_null($book_content->issueYear)) ? $book_content->issueYear . '/01/01' : $bookIrBook->xpublishdate,
                        'xcoverprice' => (!is_null($book_content->coverPrice)) ? $book_content->coverPrice : $bookIrBook->xcoverprice,
                        // 'xminprice'=>'',
                        // 'xcongresscode'=>'',
                        'xdiocode' => (!is_null($book_content->dewey)) ? $book_content->dewey : $bookIrBook->xdiocode,
                        'xlang' => (!is_null($book_content->language)) ? $book_content->language : $bookIrBook->xlang,
                        'xpublishplace' => (!is_null($book_content->publishPlace)) ? $book_content->publishPlace : $bookIrBook->xpublishplace,
                        'xdescription' => (!is_null($book_content->abstract)) ? $book_content->abstract : $bookIrBook->xdescription,
                        // 'xweight'=>'',
                        'ximgeurl' => (!is_null($book_content->imageAddress)) ? $book_content->imageAddress : $bookIrBook->ximgeurl,
                        'xpdfurl' => (!is_null($book_content->pdfAddress)) ? $book_content->pdfAddress : $bookIrBook->xpdfurl,
                        'xregdate' => time(),
                        'is_translate' => $is_translate,
                    );

                    DB::enableQueryLog();

                    if ($bookIrBook == null) {
                        BookirBook::create($book_data);
                    } else {
                        BookirBook::where('xid', $bookIrBook->xid)->update($book_data);
                    }
                    $this->info('$bookIrBook->xid : ');
                    $this->info($bookIrBook->xid);

                    //////////////////////////////////////////////////////// publisher data /////////////////////////////////////////
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
                        // $this->info($publisher_content);
                        $bookIrPublisher = BookirPublisher::where('xpageurl', 'http://ketab.ir//Publisherview.aspx?Publisherid=' . $publisher_content->id)->orWhere('xpageurl2', $publisher_content->url)->first();

                        if ($bookIrPublisher == null) {$bookIrPublisher = new BookirPublisher;}
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
                        $bookIrPublisher->xisname =  (!is_null($publisher_content->title)) ? 1 : 0;

                        $bookIrPublisher->save();
                        $this->info('$bookIrPublisher->xid');
                        $this->info($bookIrPublisher->xid);
                    }
                    ///////////////////////////////////////////////////////partner data /////////////////////////////////////////////////
                    if (!is_null($book_content->authors)) {
                        foreach ($book_content->authors as $author) {
                            $author_name = explode("،", $author->title);
                            $partner_data = array(
                                'xcreatorname' => $author_name['1'] . '' . $author_name['0'],
                                'xname2' => $author_name['1'] . $author_name['0'],
                                'xketabir_id' => $author->id,
                                'xregdate' => time(),
                            );
                            $partner_current_info = BookirPartner::where('xketabir_id', $author->id)->first();
                            if ($partner_current_info != null and count($partner_current_info) > 0) {
                                BookirPartner::where('xid', $partner_current_info->xid)->update($partner_data);

                            } else {
                                BookirPartner::create($partner_data);
                            }
                        }
                    }
                    ///////////////////////////////////////////////////subject data /////////////////////////////////////////////////////////
                    if(!is_null($book_content->parentSubject)){
                        $subject_data = array(
                            'xsubject' => $book_content->parentSubject,
                            'xsubjectname2' => str_replace(" ", "", $book_content->parentSubject),
                            'xregdate' => time(),
                        );
                        $subject_current_info = BookirSubject::where('xsubject', $book_content->parentSubject)->first();
                        if ($subject_current_info != null) {
                            BookirSubject::where('xsubject', $book_content->parentSubject)->update($subject_data);

                        } else {
                            BookirSubject::create($subject_data);
                        }
                    }

                    if (!is_null($book_content->subjects)) {
                        foreach ($book_content->subjects as $subject) {
                            $subject_data = array(
                                'xsubject' => $subject,
                                'xsubjectname2' => str_replace(" ", "", $subject),
                                'xregdate' => time(),
                            );
                            $subject_current_info = BookirSubject::where('xsubject', $subject)->first();
                            if ($subject_current_info != null) {
                                BookirSubject::where('xsubject', $subject)->update($subject_data);

                            } else {
                                BookirSubject::create($subject_data);
                            }
                        }
                    }

                    ////////////////////////////////////////////////age group////////////////////////////////////////////////////////////////
                    if (!is_null($book_content->ageGroup)) {
                        if ($book_content->ageGroup->a == true) {
                            $ageGroupData['xa'] = 1;
                        }

                        if ($book_content->ageGroup->b == true) {
                            $ageGroupData['xb'] = 1;
                        }

                        if ($book_content->ageGroup->g == true) {
                            $ageGroupData['xg'] = 1;
                        }

                        if ($book_content->ageGroup->d == true) {
                            $ageGroupData['xd'] = 1;
                        }

                        if ($book_content->ageGroup->h == true) {
                            $ageGroupData['xh'] = 1;
                        }

                        // $age_group_info = AgeGroup::where('xbook_id', ???)->first();
                        // if ($age_group_info != null) {
                        //     AgeGroup::where('xbook_id', ???)->update($ageGroupData);

                        // } else {
                        //     AgeGroup::create($ageGroupData);
                        // }


                    }

                    /////////////////////////////////////////////////////////////////////////////////////////////

                    /* BookirBook::updateOrCreate(
                ['name' => 'Rehan'],
                ['age'=> 50]
                );*/

                }

                // $bar->advance();*/
                $recordNumber++;
            }
            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $bar->finish();
        }
    }
}
