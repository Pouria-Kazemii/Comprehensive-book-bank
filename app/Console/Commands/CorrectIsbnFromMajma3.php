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

class CorrectIsbnFromMajma3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:CorrectIsbnFromMajma3 {crawlerId}';

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
        $count = BookirBook::whereRaw('CHAR_LENGTH(xisbn3) < 13')->where('check_circulation',0)->where('xid', '>', 1000000)->count();
        $startC = 0;
        $endC   =  $count;
        $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
        $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Correct-Isbn-From-Majma3-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
                

        if (isset($newCrawler)) {


            $bar = $this->output->createProgressBar(CrawlerM::$crawlerSize);
            $bar->start();

            // $books = BookirBook::whereRaw('CHAR_LENGTH(xisbn3) < 13')->get();
            BookirBook::whereRaw('CHAR_LENGTH(xisbn3) < 13')->where('check_circulation',0)->where('xid', '>', 1000000)->orderby('xid', 'ASC')->chunk(2000, function ($books, $startC) {
                foreach ($books as $book) {
                    BookirBook::where('xid',$book->xid)->update(['check_circulation'=>1]);

                    $pageUrl = str_replace("http://ketab.ir/bookview.aspx?bookid=", '', $book->xpageurl);
                    $recordNumber = str_replace("https://db.ketab.ir/bookview.aspx?bookid=", '', $pageUrl);
                    $this->info($recordNumber);
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
                        // MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => '500']);
                    } else {
                        $this->info(' recordNumber : ' . $recordNumber);
                        // MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => '200']);

                        ////////////////////////////////////////////////// book data  ///////////////////////////////////////////////
                        $book_content = json_decode($book_content);
                        $bookIrBook = BookirBook::where('xpageurl', 'http://ketab.ir/bookview.aspx?bookid=' . $recordNumber)->orwhere('xpageurl', 'https://db.ketab.ir/bookview.aspx?bookid=' . $recordNumber)->orWhere('xpageurl2', 'https://ketab.ir/book/' . $book_content->uniqueId)->firstOrNew();

                        $book_content->isbn = self::validateIsbn($book_content->isbn);
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

                        $bookIrBook->xisbn = (!is_null($book_content->isbn) && !empty($book_content->isbn)) ? $book_content->isbn : $bookIrBook->xisbn;
                        $bookIrBook->xisbn3 = (!is_null($book_content->isbn) && !empty($book_content->isbn)) ? str_replace("-", "", $book_content->isbn) : substr(str_replace("-", "", $bookIrBook->xisbn), 0, 20);
                        $bookIrBook->xisbn2 = (!is_null($book_content->isbn10) && !empty($book_content->isbn10)) ? str_replace("-", "",$book_content->isbn10) : $bookIrBook->xisbn2;
                        $bookIrBook->save();
                    }

                    // $bar->advance();*/
                    CrawlerM::where('name', 'Correct-Isbn-From-Majma3-' . $this->argument('crawlerId'))->where('start', $startC)->update(['last' => $recordNumber]);
                }
            });



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
        // $isbn = str_replace("-", "", $isbn);
        $isbn = str_replace("+", "", $isbn);

        $isbn = str_replace(",", "", $isbn);
        $isbn = str_replace("،", "", $isbn);
        $isbn = str_replace("#", "", $isbn);
        $isbn = str_replace('"', "", $isbn);

        return $isbn;
    }
}
