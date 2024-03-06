<?php

namespace App\Console\Commands\CorrectInfo;

use App\Models\BookirBook;

use App\Models\Crawler as CrawlerM;
use Illuminate\Console\Command;

class correctRepeatedXpageurl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:correctRepeatedXpageurl {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete repeted xpageurl in table bookirbook Command';

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
        $total = bookirbook::whereNotNull('xpageurl2')->where('check_goodreads',0)->count();
        try {

            $startC = 1;
            $endC = $total;

            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-correct-repeated-xpageurl-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 5));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }

        if (isset($newCrawler)) {
            $bar = $this->output->createProgressBar($total);
            $bar->start();
            bookirbook::whereNotNull('xpageurl2')->where('check_goodreads',0)->orderBy('xid', 'ASC')->chunk(200, function ($books) use ($bar, $newCrawler) {
                foreach ($books as $book) {
                    // DB::enableQueryLog();
                    $book->check_goodreads = 1;
                    $book->save();
                    BookirBook::whereNotNull('xpageurl2')->where('xpageurl2',$book->xpageurl2)->where('xid','!=',$book->xid)->update(['check_goodreads' => 333]);


                    // $same_records = BookirBook::whereNotNull('xpageurl2')->where('xpageurl2',$book->xpageurl2)->where('xid','!=',$book->xid)->get();
                    // foreach($same_records as $detected_books){
                    //     $detected_books->xdocid = 333;
                    //     $detected_books->save();
                    //     echo 'detected same record with xid = '.$detected_books->xid .'</br>';
                    // }
                    // $rr = BookirBook::where('xpageurl',$book->xpageurl)->where('xid','!=',$book->xid)->delete();

                    // if($rr){ echo 'deleteed'; echo '</br>';}

                    // $q = DB::getQueryLog();
                    // echo '<pre>'; print_r($q);
                    // $book->check_goodreads = 1;
                    // $book->save();

                    
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
