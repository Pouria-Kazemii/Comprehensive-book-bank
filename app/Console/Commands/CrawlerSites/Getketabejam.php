<?php

namespace App\Console\Commands\CrawlerSites;

use Illuminate\Console\Command;
use App\Models\SiteBookLinks;

class Getketabejam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:ketabejam {crawlerId} ';

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
        SiteBookLinks::where('domain', 'https://ketabejam.com/')->where('status', 0)->orderBy('id','ASC')->chunk(100, function ($bookLinks) {
            foreach ($bookLinks as $bookLink) {
                $this->info($bookLink->book_links);
                $function_caller = 'updateKetabejamBookInfo';
                updateKetabejamBook($bookLink, $function_caller);
            }
        });
    }
}