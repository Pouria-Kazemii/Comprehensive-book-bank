<?php

namespace App\Console\Commands\CrawlerSites;

use Illuminate\Console\Command;
use App\Models\SiteBookLinks;
use App\Models\Crawler as CrawlerM;

class Getketabejam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:ketabejam {crawlerId} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get ketabejam book Books from html website';

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
        $function_caller = 'KetabejamInfo';
        $total = SiteBookLinks::where('domain', 'https://ketabejam.com/')->where('status', 0)->count();
        $startC = 1;
        $endC = $total;
        $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-'.$function_caller.'-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1));

        if (isset($newCrawler)) {

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            SiteBookLinks::where('domain', 'https://ketabejam.com/')->where('status', 0)->orderBy('id','ASC')->chunk(100, function ($bookLinks) use ($bar,$newCrawler,$function_caller) {
                foreach ($bookLinks as $bookLink) {
                    updateKetabejamBook($bookLink, 'checkBook'.$function_caller);
                    $bar->advance();
                    
                    $newCrawler->last = $bookLink->id;
                    $newCrawler->save();
                }
            });
            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ------------ ");
            $bar->finish();
        }
    }
}