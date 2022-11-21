<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CirculationTemp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill_temp_table:circulation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The table contains information on book circulations, authors and publications';

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
        // app()->call('App\Http\Controllers\CronjobController@fill_circulation_temp_table');
        // app()->call('App\Http\Controllers\CronjobController@fast_fill_circulation_temp_table');
        app()->call('App\Http\Controllers\CronjobController@fill_publisher_circulation_temp_table'); // fill only publisher
    }
}
