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
            echo "\n title : ".$crawler->filter('body div.body-content h1')->text();
            echo "\n nasher : ".$crawler->filter('body div.body-content h2 a.site-c')->text();
            echo "\n price : ".$crawler->filter('body div.body-content span.price-slash')->text();
            echo "\n Desc : ".$crawler->filter('body div.body-content p.line-h-2')->text('');

            $cats = array();
            foreach ($crawler->filter('body div.body-content a.indigo') as $cat){
                $cats[]= $cat->textContent;
            }
            echo "\n cats : ";
            print_r($cats);

            $details = array();
            echo "\n table : ";
            foreach ($crawler->filter("body div.body-content table.table-striped tr") as $trTable){
                $trObj = new Crawler($trTable);
                $details[] = array ('name'=> $trObj->filter('td')->first()->text(), 'value'=>$trObj->filter('td')->nextAll()->text());
            }
            print_r($details);

            echo "\n cats array : ";
            $catPath = array();
            foreach ($crawler->filter("body div.body-content li.breadcrumb-item a") as $linkcat){
                $catPath[] = $linkcat->textContent;
            }
            print_r($catPath);
    }
}
