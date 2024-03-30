<?php

namespace App\Console\Commands\CrawlerSites;

use Illuminate\Console\Command;
use App\Models\SiteBookLinks;
use App\Models\Crawler as CrawlerM;


class GetBarKhatBookNewestBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:barkhatbookNewestBook {crawlerId} {runNumber?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get barkhatbook book Books from html website';

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
        $function_caller = 'barkhatBook-newest-book';
        $startC = 1;
        $endC = 2;

        try {
            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-' . $function_caller . '-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }

        if (isset($newCrawler)) {


            if ($this->argument('runNumber') && $this->argument('runNumber') == 1) {
                $function_caller = 'updateBarKhatBookCategories';
                updateBarKhatBookCategories($function_caller);
                updateBarKhatBookCategoriesAllBooks();
            } else {

                $function_caller = 'updateBarKhatBookCategoriesAllBooks';
                updateBarKhatBookCategoriesFirstPageBooks($function_caller);
            }

            SiteBookLinks::where('domain', 'https://barkhatbook.com/')->where('status', 0)->chunk(1, function ($bookLinks) {
                foreach ($bookLinks as $bookLink) {
                    $this->info($bookLink->book_links);
                    $function_caller = 'updateBarkhatBookInfo';
                    updateBarkhatBook($bookLink, $function_caller);
                }
            });
        }
    }
}
