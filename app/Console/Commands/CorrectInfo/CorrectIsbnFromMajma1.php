<?php

namespace App\Console\Commands\CorrectInfo;

use App\Models\BookirBook;
use App\Models\Crawler as CrawlerM;
use Illuminate\Console\Command;



class CorrectIsbnFromMajma1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:CorrectIsbnFromMajma1 {crawlerId} ';

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
        $function_caller = 'Correct-Isbn-From-Majma1';
        $total = BookirBook::where('xisbn', 'not like', "%-%")->where('check_circulation', 0)->where('xid', '>', 500000)->where('xid', '<', 1000000)->count();
        try {
            $startC = 0;
            $endC   = $total;
            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Correct-Isbn-From-Majma1-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }

        if (isset($newCrawler)) {


            $bar = $this->output->createProgressBar($total);
            $bar->start();

            // BookirBook::whereNotNull('xpageurl2')->where('xisbn', 'not like', "%-%")->where('check_circulation', 0)->where('xid', '>', 500000)->where('xid', '<', 1000000)->orderby('xid', 'ASC')->chunk(2000, function ($books) use ($bar, $function_caller, $newCrawler) {
            $books = BookirBook::where('xisbn', 'not like', "%-%")->where('check_circulation', 0)->where('xid', '>', 500000)->where('xid', '<', 1000000)->orderby('xid', 'ASC')->limit(60)->get();

            foreach ($books as $book) {

                $pageUrl = str_replace("http://ketab.ir/bookview.aspx?bookid=", '', $book->xpageurl);
                $recordNumber = str_replace("https://db.ketab.ir/bookview.aspx?bookid=", '', $pageUrl);
                $this->info('recordNumber :' . $recordNumber);

                $bookIrBook = BookirBook::where('xid', $book->xid)->first();

                $api_status = updateBookDataWithMajmaApiInfo($recordNumber, $bookIrBook, $function_caller);
                $bookIrBook->check_circulation = $api_status;
                $bookIrBook->save();

                // $apiResult = returnBookDataFromMajmaApi($recordNumber, $function_caller);
                // if ($apiResult) {
                //     $book_content = $apiResult;
                //     $book_content->isbn = validateIsbn($book_content->isbn);
                //     $this->info($book_content->isbn);
                //     $book->xisbn = (!is_null($book_content->isbn) && !empty($book_content->isbn)) ? $book_content->isbn : $book->xisbn;
                //     $book->save();
                // }
                // BookirBook::where('xid', $book->xid)->update(['check_circulation' => 1]);

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
