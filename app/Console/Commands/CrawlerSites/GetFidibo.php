<?php

namespace App\Console\Commands\CrawlerSites;

use App\Models\BookFidibo;
use App\Models\Crawler as CrawlerM;
use Illuminate\Console\Command;


class GetFidibo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:fidibo {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get fidibo Book Command';

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
        $function_caller = 'FidiboInfo';

        $total = BookFidibo::whereNull('title')->count();

        try {
            $startC = 0;
            $endC   = $total;
            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ------------ ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-'.$function_caller.'-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ------------ ");
        }

      

        if (isset($newCrawler)) {

            
            $bar = $this->output->createProgressBar($total);
            $bar->start();

            BookFidibo::whereNull('title')->orderby('id', 'DESC')->chunk(200, function ($books) use ($bar, $newCrawler,$function_caller ) {
                foreach ($books as $book) {
                    
                    $this->info($book->recordNumber);
                    $bookFidibo = BookFidibo::where('id', $book->id)->first();

                    updateFidiboBook($book->recordNumber,$bookFidibo,'checkBook'.$function_caller);

                    $bar->advance();
                    $newCrawler->last = $book->recordNumber;
                    $newCrawler->save();
                }
            });
        }
    }
}
