<?php

namespace App\Console\Commands\CrawlerSites;

use Illuminate\Console\Command;
use App\Models\Book30book;
use App\Models\Crawler as CrawlerM;

class Get30Book extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:30book {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get 30book Books from html website';

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
        $function_caller = '30BookInfo';

        $Last_id = Book30book::whereNotNull('title')->orderBy('recordNumber','DESC')->first()->recordNumber;
        try{
            $startC = $Last_id + 1;
            $endC = $startC + 100;
            $this->info(" \n ---------- Create Crawler  ".$this->argument('crawlerId')."     $startC  -> $endC         ------------ ");
            $newCrawler = CrawlerM::firstOrCreate(array('name'=>'Crawler-'.$function_caller.'-'.$this->argument('crawlerId'), 'start'=>$startC, 'end'=>$endC, 'status'=>1));
        }catch (\Exception $e){
            $this->info(" \n ---------- Failed Crawler  ".$this->argument('crawlerId')."              ------------ ");
        }

        $bar = $this->output->createProgressBar(100);
        $bar->start();

        $recordNumber = $startC;
        while ($recordNumber <= $endC){

            update30Book( $recordNumber ,'checkBook'.$function_caller);

            $bar->advance();
            $recordNumber ++;
        }
        $newCrawler->status = 2;
        $newCrawler->save();
        $this->info(" \n ---------- Finish Crawler  ".$this->argument('crawlerId')."     $startC  -> $endC         ------------ ");
        $bar->finish();
    }
}
