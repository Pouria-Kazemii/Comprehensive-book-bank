<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class get30Book extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:30book {Id}';

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
        $client = new Client(HttpClient::create(['timeout' => 30]));
        $recordNumber = $this->argument('Id');
        try {
            $this->info(" \n ---------- Try Get BOOK ".$recordNumber."              ---------- ");
            $crawler = $client->request('GET', 'https://www.30book.com/Book/'.$recordNumber);
            $status_code = $client->getInternalResponse()->getStatusCode();
        } catch (\Exception $e) {
            $crawler = null;
            $status_code = 500;
            $this->info(" \n ---------- Failed Get  ".$recordNumber."              ---------=-- ");
        }
            echo "title : ".$crawler->filter('body div.body-content h1')->text();
            echo "nasher : ".$crawler->filter('body div.body-content h2 a.site-c')->text();
            echo "price : ".$crawler->filter('body div.body-content span.price-slash')->text();
            echo "Desc : ".$crawler->filter('body div.body-content p.line-h-2')->text();
            echo "cats : ".$crawler->filter('body div.body-content a.indigo')->text();
            echo "table : ";
            print_r($crawler->filter("body div.body-content table.table-striped tr"));
            echo "cats array : ";
            print_r($crawler->filter("body div.body-content li.breadcrumb-item"));
    }
}
