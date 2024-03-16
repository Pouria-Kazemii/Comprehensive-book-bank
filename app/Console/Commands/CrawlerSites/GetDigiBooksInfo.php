<?php

namespace App\Console\Commands\CrawlerSites;

use App\Models\BookDigi;
use App\Models\Crawler as CrawlerM;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GetDigiBooksInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:digiBooksInfo {crawlerId}';

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
        die('stop');
        $function_caller = 'Give_Digi-Book-Info';
        $total = BookDigi::whereNull('title')->where('is_book',1)->count();
        try {
            $startC = 0;
            $endC   = $total;
            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-Give_Digi-Book-Info-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }

        if (isset($newCrawler)) {


            $bar = $this->output->createProgressBar($total);
            $bar->start();

            BookDigi::whereNull('title')->where('is_book',1)->orderby('id', 'ASC')->chunk(200, function ($books) use ($bar, $function_caller, $newCrawler) {
                foreach ($books as $book) {
                    
                    $this->info($book->id);
                    $bookDigi = BookDigi::where('recordNumber', $book->recordNumber)->firstOrNew();
                    $bookDigi->recordNumber = $book->recordNumber;
                    $api_status = updateBookDigi($book->recordNumber, $bookDigi, $function_caller);
                    $this->info($api_status);

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
