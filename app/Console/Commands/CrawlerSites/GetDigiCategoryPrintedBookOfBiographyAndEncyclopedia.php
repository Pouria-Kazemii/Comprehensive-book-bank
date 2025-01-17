<?php

namespace App\Console\Commands\CrawlerSites;

use Illuminate\Console\Command;
use App\Models\BookDigi;
use App\Models\Crawler as CrawlerM;

class GetDigiCategoryPrintedBookOfBiographyAndEncyclopedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:digiCategoryPrintedBookOfBiographyAndEncyclopedia {crawlerId}';

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
        $search_books_category = 'category-printed-book-of-biography-and-encyclopedia';
        $function_caller = $search_books_category;
        $startC = 1;
        $endC = 2;
        try {

            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ------------ ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-digi-' . $function_caller . '-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ------------ ");
        }

        if (isset($newCrawler)) {

            $pageCounter = $startC;
            while ($pageCounter <= $endC) {

                try {
                    $pageUrl = 'https://api.digikala.com/v1/categories/printed-book-of-biography-and-encyclopedia/search/?sort=1&page='.$pageCounter;
                    $this->info(" \n ---------- Page URL  " . $pageUrl . "              ------------ ");
                    $json = file_get_contents($pageUrl);
                    $headers = get_headers($pageUrl);
                    $status_code = substr($headers[0], 9, 3);
                } catch (\Exception $e) {
                    $crawler = null;
                    $status_code = 500;
                }
                $this->info(" \n ---------- STATUS Get  " . $status_code . "              ------------ ");

                if ($status_code == "200") {

                    $products_all = json_decode($json);

                    $bar = $this->output->createProgressBar(count($products_all->data->products));
                    $bar->start();

                    foreach ($products_all->data->products as $pp) {
                        if (check_digi_id_is_book($pp->id)) {
                            $bookDigi = BookDigi::where('recordNumber', 'dkp-' . $pp->id)->firstOrNew();
                            $bookDigi->recordNumber = 'dkp-' . $pp->id;
                            updateDigiBook($pp->id, $bookDigi,  'checkBook'.$function_caller);
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
            $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ------------ ");
        }
    }
}
