<?php

namespace App\Console\Commands\CrawlerSites;

use Illuminate\Console\Command;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class GetTaaghcheBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:taaghcheBook {start} {end}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'crawl books from the taacghche';

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
        $start = $this->argument('start');
        $end = $this->argument('end');
        $total = $end - $start;
        $client = new HttpBrowser(HttpClient::create(['timeout' => 30]));
        for ($i = $start; $i <= $end; $i++) {
            echo " \n ---------- Try Get BOOK " . $i . "              ---------- ";
            $crawler = $client->request('GET', 'https://taaghche.com/audiobook/' . $i, [
                'headers' => [
                    'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                ],
            ]);
            $status_code = $client->getInternalResponse()->getStatusCode();

            if ($status_code == 200) {
                // Filter the script tag with id "__NEXT_DATA__"
                $data = $crawler->filter('script#\\__NEXT_DATA__')->first()->text();

                // Decode the JSON content into a PHP array
                $decodedData = json_decode($data, true);

                // Dump the decoded data for inspection
                dd($decodedData);
            }
        }
    }
}
