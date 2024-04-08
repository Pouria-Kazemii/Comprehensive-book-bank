<?php

namespace App\Console\Commands\CrawlerSites;

use Illuminate\Console\Command;
use App\Models\Crawler as CrawlerM;

class GetketabejamNewestBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:ketabejamNewestBooks {crawlerId} {runNumber?}';

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
        $function_caller = 'KetabejamNewestBooks';

        try {
            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "            ------------ ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-' . $function_caller . '-' . $this->argument('crawlerId'), 'status' => 1));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ------------ ");
        }

        if (isset($newCrawler)) {
            if ($this->argument('runNumber') && $this->argument('runNumber') == 1) {
                $catCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-KetabejamAllCategoriesAndAllBooks-' . $this->argument('crawlerId'), 'status' => 1));

                updateKetabejamCategories();
                updateKetabejamCategoriesAllBooks();

                $catCrawler->status = 2;
                $catCrawler->save();
            } else {
                $catCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-KetabejamCategoriesFirstPageBooks-' . $this->argument('crawlerId'), 'status' => 1));

                updateKetabejamCategoriesFirstPageBooks();

                $catCrawler->status = 2;
                $catCrawler->save();
            }
            $newCrawler->status = 2;
            $newCrawler->save();
        }
       
    }
}
