<?php

namespace App\Console\Commands\CrawlerSites;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use App\Models\BookDigi;
use App\Models\Crawler as CrawlerM;

class GetDigiCategoryChildrenBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:digiCategoryChildrenBook {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get DigiKala Book Command';

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
        $search_books_category = 'category-children-book';
        // cat: 
        //x category-foreign-printed-book
        // category-children-book
        //x category-printed-book-of-biography-and-encyclopedia
        // category-applied-sciences-technology-and-engineering
        // category-printed-history-and-geography-book
        // category-printed-book-of-philosophy-and-psychology
        // category-textbook-tutorials-and-tests
        // category-language-books
        // category-printed-book-of-art-and-entertainment
        // category-religious-printed-book
        // category-printed-book-of-social-sciences
        // category-printed-book-of-poetry-and-literature
        $function_caller = 'Crawler-digi-' . $search_books_category;
        $startC = 1;
        $endC = 2;
        try {

            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-digi-' . $search_books_category . '-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }

        if (isset($newCrawler)) {

            // $client = new Client(HttpClient::create(['timeout' => 30]));


            $pageCounter = $startC;
            while ($pageCounter <= $endC) {

                try {
                    $pageUrl = 'https://www.digikala.com/ajax/search/' . $search_books_category . '/?pageno=' . $pageCounter . '&sortby=1';
                    $this->info(" \n ---------- Page URL  " . $pageUrl . "              ---------=-- ");
                    $json = file_get_contents($pageUrl);
                    $headers = get_headers($pageUrl);
                    $status_code = substr($headers[0], 9, 3);
                } catch (\Exception $e) {
                    $crawler = null;
                    $status_code = 500;
                    //$this->info(" \n ---------- Failed Get  ".$pageCounter."              ---------=-- ");
                }
                $this->info(" \n ---------- STATUS Get  " . $status_code . "              ---------=-- ");

                if ($status_code == "200") {

                    $products_all = json_decode($json);

                    $bar = $this->output->createProgressBar(count($products_all->data->trackerData->products));
                    $bar->start();

                    foreach ($products_all->data->trackerData->products as $pp) {
                        if (check_digi_id_is_book($pp->product_id)) {
                            $bookDigi = BookDigi::where('recordNumber', 'dkp-' . $pp->product_id)->firstOrNew();
                            $bookDigi->recordNumber = 'dkp-' . $pp->product_id;
                            updateBookDigi($pp->product_id, $bookDigi, $function_caller);
                        }

                        $bar->advance();
                    }
                    $bar->finish();
                }

                $newCrawler->last = $pageCounter;
                $newCrawler->save();
                $pageCounter++;
            }
            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
        }
    }
}
