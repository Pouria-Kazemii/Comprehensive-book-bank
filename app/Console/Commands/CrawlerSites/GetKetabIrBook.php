<?php

namespace App\Console\Commands\CrawlerSites;

use Illuminate\Console\Command;

class GetKetabIrBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill_publisher_books_from_ketabir';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fill bookirbook - bookirpublisher - bookirauthor tables';

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
        app()->call('App\Http\Controllers\API\CrawlerKetabirController@publisher_list');  

    }
}
