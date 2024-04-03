<?php

namespace App\Console\Commands\CrawlerSites;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\BookShahreKetabOnline;
use App\Models\Crawler as CrawlerM;

class GetShahreKetabOnline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:shahreketabonline {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get shahreketabonline Books from html website';

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
        $function_caller = 'updateShahreketabonlineBookInfo';

        $Last_id = (isset(BookShahreKetabOnline::whereNotNull('title')->orderBy('recordNumber', 'DESC')->first()->recordNumber)) ? BookShahreKetabOnline::whereNotNull('title')->orderBy('recordNumber', 'DESC')->first()->recordNumber : 0;
        try {
            $startC = $Last_id + 1;
            $endC = $startC + 180000;
            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-shahreketabonline-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }

        if (isset($newCrawler)) {
            $bar = $this->output->createProgressBar(CrawlerM::$crawlerSize);
            $bar->start();

            $recordNumber = $startC;
            while ($recordNumber <= $endC) {
                $book = BookShahreKetabOnline::where('recordNumber', $recordNumber)->firstOrNew();
                $book->recordNumber = $recordNumber;
                updateShahreketabonlineBook($recordNumber,$book,$function_caller);
                $bar->advance();
                $newCrawler->last = $recordNumber;
                $recordNumber++;
            }
            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $bar->finish();
        }
    }
   
}
