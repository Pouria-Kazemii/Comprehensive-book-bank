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
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpClient\HttpClient;

class GetMajmaLastDays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:MajmaLastDays {crawlerId}';

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

        $limit_book = 200;
        // $from_date = BookirBook::orderBy('xpublishdate','DESC')->first()->xpublishdate;
        // $to_date = date("Y-m-d", strtotime("+5 days", strtotime($from_date)));
        $lastDate = CrawlerM::where('name','Crawler-Majma-Last-days-1')->where('status',2)->orderBy('id','DESC')->first()->start;
        $lastDate= substr_replace($lastDate, '-', 4, 0);
        $lastDate= substr_replace($lastDate, '-', 7, 0);
        
        $to_date = date($lastDate);
        $from_date = date("Y-m-d", strtotime("-30 days", strtotime($to_date)));

       
        //give total for foreach
        $timeout = 120;
        $url = 'http://dcapi.k24.ir/test_get_books_majma/' . $from_date . '/' . $to_date . '/0/' . $limit_book;
        $this->info($url);
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
        $total_api_content = curl_exec($ch);
        if (curl_errno($ch)) {
            $this->info(" \n ---------- Try Get BOOK LIST " . $from_date . "/" . $to_date . "/0/" . $limit_book . "              ---------- ");
            echo 'error:' . curl_error($ch);
        } else {
            $total_api_content = json_decode($total_api_content);
            $totalCount = $total_api_content->totalCount;
            $this->info(' total books  : ' . $totalCount);
        }
        try {
            $startC = 1;
            $endC = $totalCount;

            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-Majma-Last-days-' . $this->argument('crawlerId'), 'start' => enNumberKeepOnly($from_date), 'end' => enNumberKeepOnly($to_date), 'status' => 1, 'type' => 0));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }


        if (isset($newCrawler)) {

            $client = new Client(HttpClient::create(['timeout' => 30]));

            $bar = $this->output->createProgressBar($totalCount);
            $bar->start();

            for ($i = 0; $i <= ceil($totalCount / $limit_book); $i++) {
                $timeout = 120;
                $from = $i * 200;
                $url = 'http://dcapi.k24.ir/test_get_books_majma/' . $from_date . '/' . $to_date . '/' . $from . '/' . $limit_book;
                $crawlerItems = CrawlerM::firstOrCreate(array('name' => 'Crawler-Majma-Last-days-items-' . $this->argument('crawlerId'), 'start' => enNumberKeepOnly($from_date), 'end' => enNumberKeepOnly($to_date), 'last' => $from, 'status' => 1, 'type' => 1));
                $this->info($url);
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
                $books_content = curl_exec($ch);

                if (curl_errno($ch)) {
                    $this->info(" \n ---------- Try Get BOOK LIST http://dcapi.k24.ir/test_get_books_majma/" . $from_date . "/" . $to_date . "/" . $from . "/" . $limit_book . "             ---------- ");
                    echo 'error:' . curl_error($ch);
                } else {
                    $books_content = json_decode($books_content);
                }
                // قسمت کتاب ها رو باید بندازم تو foreach که دونه دونه بره اطلاعاتشو بگیره
                foreach ($books_content->items as $item) {
                    $recordNumber = $item->id;
                    $bookIrBook = BookirBook::where('xpageurl', 'http://ketab.ir/bookview.aspx?bookid=' . $recordNumber)->orwhere('xpageurl', 'https://db.ketab.ir/bookview.aspx?bookid=' . $recordNumber)->firstOrNew();
                   

                    if(empty($bookIrBook->xpageurl)){
                        MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => '0', 'xfunction_caller' => 'GetMajmaLastDays-Command']);
                    }

                    
                    $bookIrBook->xpageurl = 'http://ketab.ir/bookview.aspx?bookid=' . $recordNumber;
                    $bookIrBook->save();
                    //updateBookDataWithMajmaApiInfo($recordNumber, $bookIrBook);
                    
                    $bar->advance();
                }

                $crawlerItems->status = 2 ;
                $crawlerItems->save();
            }
           
        }
        $newCrawler->status = 2;
        $newCrawler->save();
        $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
        $bar->finish();
    }

   
}
