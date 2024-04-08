<?php

namespace App\Console\Commands\CrawlerSites;

use Illuminate\Console\Command;
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
        $function_caller = 'BarkhatBookNewestBooks';

        try {
            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "           ------------ ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-' . $function_caller . '-' . $this->argument('crawlerId'),  'status' => 1));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ------------ ");
        }

        if (isset($newCrawler)) {

            if ($this->argument('runNumber') && $this->argument('runNumber') == 1) {

                $catCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-BarkhatBookAllCategoriesAndAllBooks-' . $this->argument('crawlerId'),  'status' => 1));
                updateBarKhatBookCategories();
                updateBarKhatBookCategoriesAllBooks();
                $catCrawler->status = 2;
                $catCrawler->save();

            } else {

                $catCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-BarkhatBookCategoriesFirstPageBooks-' . $this->argument('crawlerId'),  'status' => 1));
                updateBarKhatBookCategoriesFirstPageBooks();
                $catCrawler->status = 2;
                $catCrawler->save();

            }

            $newCrawler->status = 2;
            $newCrawler->save();
        }
    }
}
