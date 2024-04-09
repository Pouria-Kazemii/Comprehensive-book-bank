<?php

namespace App\Console\Commands\CrawlerSites;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use App\Models\BookGisoom;
use App\Models\Crawler as CrawlerM;

class GetGissom extends Command
{
    /*
    The name and signature of the console command.
    @var string
    */
    protected $signature = 'get:gissom {crawlerId} {runNumber?}';
    /*
    The console command description.
    @var string
    */
    protected $description = 'crawle Gisoom book from site html';

    /*
    Create a new command instance.
    @return void

    */
    public function __construct()
    {
        parent::__construct();
    }
    /*
    Execute the console command.
    @return int

    */

    public function handle()
    {
        $function_caller = 'ّGissomBookInfo';
        if ($this->argument('runNumber') && $this->argument('runNumber') == 1) {
            $startC = 11000000;
            $endC = 11400000;
        } else {
            $startC = BookGisoom::orderBy('recordNumber', 'DESC')->first()->recordNumber;
            $endC = $startC + 100;
        }

        try {

            $this->info(" \n ---------- Create Crawler " . $this->argument('crawlerId') . " $startC -> $endC ------------ ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-' . $function_caller . '-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler " . $this->argument('crawlerId') . " ------------ ");
        }

        if (isset($newCrawler)) {
            $client = new Client(HttpClient::create(['timeout' => 30]));
            $bar = $this->output->createProgressBar(CrawlerM::$crawlerSize);
            $bar->start();
            $recordNumber = $startC;
            while ($recordNumber <= $endC) {

                $bookGissom = BookGisoom::where('recordNumber', $recordNumber)->firstOrNew();
                updateGisoomBook($recordNumber, $bookGissom, 'checkBook'.$function_caller);
                
                $newCrawler->last = $recordNumber;
                $newCrawler->save();

                $bar->advance();

                $recordNumber++;
            }
            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler " . $this->argument('crawlerId') . " $startC -> $endC ------------ ");
            $bar->finish();
        }
    }
}
