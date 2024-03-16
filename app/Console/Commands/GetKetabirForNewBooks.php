<?php

namespace App\Console\Commands;


use App\Models\BookirBook;
use App\Models\Crawler as CrawlerM;
use App\Models\MajmaApiBook;
use Goutte\Client;
use Illuminate\Console\Command;
use Symfony\Component\HttpClient\HttpClient;

class GetKetabirForNewBooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:KetabirForNewBookInfo {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get ketabir Book Command';

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
        $function_caller ='GetKetabirForNewBooksCommand';
        $total = BookirBook::WhereNull('xpageurl2')->whereNotNull('xpageurl')->where('check_circulation', 0)->count();
        try {
            $startC = 0;
            $endC = $total;

            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-Ketabir-New-Books' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }


        if (isset($newCrawler)) {

            $client = new Client(HttpClient::create(['timeout' => 30]));

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            // MajmaApiBook::where('xfunction_caller','GetKetabirLastDays-Command')->where('xstatus',0)->orderBy('xbook_id', 'DESC')->chunk(2000, function ($books) use ($bar, $newCrawler) {
            //     foreach($books as $book){

            //         $this->info($book->xbook_id);
            //         $bookIrBook = BookirBook::WhereNull('xpageurl2')->where('xpageurl', 'http://ketab.ir/bookview.aspx?bookid=' . $book->xbook_id)->first();
            //         if(isset($bookIrBook) and !empty($bookIrBook)){
            //             $api_status = updateBookDataWithKetabirApiInfo($book->xbook_id,$bookIrBook);
            //             MajmaApiBook::where('xbook_id',$book->xbook_id)->update(['xstatus'=>$api_status]);
            //         }else{
            //             MajmaApiBook::where('xbook_id',$book->xbook_id)->update(['xstatus'=>1]);
            //         }

            //         $bar->advance();
            //         $newCrawler->last = $book->xbook_id;
            //         $newCrawler->save();
            //     }
            // });
            BookirBook::WhereNull('xpageurl2')->whereNotNull('xpageurl')->where('check_circulation', 0)->orderBy('xid', 'ASC')->chunk(2000, function ($books) use ($bar, $newCrawler,$function_caller) {
                // $books = bookirBook::WhereNull('xpageurl2')->whereNotNull('xpageurl')->where('check_circulation', 0)->orderBy('xid', 'ASC')->limit('6')->get();
                foreach ($books as $book) {

                    $this->info($book->xid);
                    $recordNumber = str_replace("http://ketab.ir/bookview.aspx?bookid=", "", $book->xpageurl);
                    $this->info('recordNumber : ' . $recordNumber);

                    $bookIrBook = BookirBook::where('xid',$book->xid)->first();
                    $api_status = updateBookDataWithKetabirApiInfo($recordNumber, $bookIrBook, $function_caller);
                    MajmaApiBook::where('xbook_id', $recordNumber)->update(['xstatus' => $api_status]);
                   

                    $bookIrBook->check_circulation = $api_status;
                    $bookIrBook->save();

                    $bar->advance();
                    $newCrawler->last = $book->xid;
                    $newCrawler->save();
                }
            });


            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $bar->finish();
        }
    }
}
