<?php

namespace App\Console\Commands\CorrectInfo;

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
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpClient\HttpClient;

class DeleteSameRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:DeleteSameRecords {crawlerId} {miss?}';

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
                $lastCrawler = CrawlerM::where('name', 'Crawler-delete-same-records' . $this->argument('crawlerId'))->where('status', 1)->orderBy('id', 'DESC')->first();
                if (isset($lastCrawler) and !empty($lastCrawler)) {
                    $startC = $lastCrawler->last;
                    $endC   = $lastCrawler->end;
                    $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
                    $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-delete-same-records' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
                }
            } catch (\Exception $e) {
                $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
            }
        } else {
            try {
                $lastCrawler = CrawlerM::where('name', 'Crawler-delete-same-records' . $this->argument('crawlerId'))->where('status', 2)->orderBy('id', 'desc')->first();
                if (isset($lastCrawler) and !empty($lastCrawler)) {
                    $startC = $lastCrawler->end + 1;
                    // $endC = $startC + CrawlerM::$crawlerSize;
                    $endC = 500000;
                } else {
                    $startC = 1;
                    // $endC = $startC + CrawlerM::$crawlerSize;
                    $endC = 500000;
                }

                $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
                $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-delete-same-records' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
            } catch (\Exception $e) {
                $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
            }
        }

        if (isset($newCrawler)) {


            $bar = $this->output->createProgressBar(CrawlerM::$crawlerSize);
            $bar->start();

            // $books = BookirBook::whereRaw('CHAR_LENGTH(xisbn3) < 13')->get();
            BookirBook::select(DB::raw('count(xpageurl2) as count_group'))->whereNotNull('xpageurl')->groupBy('xpageurl2')->having('count_group','>',1)->orderby('count_group','DESC')->chunk(2000, function ($books, $startC) {
                foreach ($books as $book) {
                    $pageUrl = str_replace("http://ketab.ir/bookview.aspx?bookid=", '', $book->xpageurl);
                    $recordNumber = str_replace("https://db.ketab.ir/bookview.aspx?bookid=", '', $pageUrl);
                    $this->info($recordNumber);
                    $timeout = 120;
                    $url = 'http://dcapi.k24.ir/test_get_book_id_majma/' . $recordNumber.'/';
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
                        $bookIrBook->xisbn2 = (!is_null($book_content->isbn10) && !empty($book_content->isbn10)) ? $book_content->isbn10 : $bookIrBook->xisbn2;
                        $bookIrBook->save();
                    }

                    // $bar->advance();*/
                    CrawlerM::where('name', 'Crawler-delete-same-records' . $this->argument('crawlerId'))->where('start', $startC)->update(['last' => $recordNumber]);
                }
            });



            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $bar->finish();
        }
    }

}
