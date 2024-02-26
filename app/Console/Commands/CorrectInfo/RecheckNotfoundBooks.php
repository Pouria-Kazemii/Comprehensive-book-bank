<?php

namespace App\Console\Commands\CorrectInfo;

use App\Models\BookirBook;
use App\Models\Crawler as CrawlerM;
use Illuminate\Console\Command;



class RecheckNotfoundBooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:RecheckNotfoundBooks {crawlerId} ';

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
        $function_caller = 'Recheck-Not-found-Books';
        $total = BookirBook::where('check_circulation', 500)->count();
        try {
            $startC = 0;
            $endC   = $total;
            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Recheck-Not-found-Books-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }

        if (isset($newCrawler)) {


            $bar = $this->output->createProgressBar($total);
            $bar->start();

            // BookirBook::where('check_circulation', 500)->orderby('xid', 'ASC')->chunk(200, function ($books) use ($bar, $function_caller, $newCrawler) {
                $books = BookirBook::where('check_circulation', 500)->orderby('xid', 'ASC')->limit(6)->get();
                foreach ($books as $book) {

                    $pageUrl = str_replace("http://ketab.ir/bookview.aspx?bookid=", '', $book->xpageurl);
                    $recordNumber = str_replace("https://db.ketab.ir/bookview.aspx?bookid=", '', $pageUrl);
                    $this->info('recordNumber :' . $recordNumber);

                    $bookIrBook = BookirBook::where('xid', $book->xid)->first();
                    $api_status = updateBookDataWithMajmaApiInfo($recordNumber, $bookIrBook, $function_caller);
                    if ($api_status == 200) {
                        $bookIrBook->check_circulation = $api_status;
                    } else {
                        $bookIrBook->check_circulation = $bookIrBook->check_circulation + $api_status;
                    }
                    $bookIrBook->save();

                    $bar->advance();
                    $newCrawler->last = $recordNumber;
                    $newCrawler->save();
                }
            // });



            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $bar->finish();
        }
    }
}
