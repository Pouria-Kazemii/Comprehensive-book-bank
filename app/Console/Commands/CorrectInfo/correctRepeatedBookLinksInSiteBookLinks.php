<?php

namespace App\Console\Commands\CorrectInfo;

use App\Models\Crawler as CrawlerM;
use App\Models\SiteBookLinks;
use Illuminate\Console\Command;

class correctRepeatedBookLinksInSiteBookLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:correctRepeatedBookLinksInSiteBookLinks {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete repeted book_links in table site_book_links Command';

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
        $total = SiteBookLinks::where('domain','https://barkhatbook.com/')->where('check_repeat',0)->count();
        try {

            $startC = 1;
            $endC = $total;

            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-correct-repeated-bookLinks-in-SiteBookLinks-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 5));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }

        if (isset($newCrawler)) {
            $bar = $this->output->createProgressBar($total);
            $bar->start();
            SiteBookLinks::where('domain','https://barkhatbook.com/')->where('check_repeat',0)->orderBy('id', 'DESC')->chunk(200, function ($books) use ($bar, $newCrawler) {
                foreach ($books as $book) {
                    // DB::enableQueryLog();
                    $book->check_repeat = 1;
                    $book->save();
                    SiteBookLinks::where('domain','https://barkhatbook.com/')->where('book_links',$book->book_links)->where('id','!=',$book->id)->update(['check_repeat' => 2]);

                    $bar->advance();
                    $newCrawler->last = $book->id;
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
