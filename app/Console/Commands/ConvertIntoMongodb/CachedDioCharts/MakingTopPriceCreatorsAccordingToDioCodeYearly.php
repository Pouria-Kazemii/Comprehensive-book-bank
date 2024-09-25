<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedDioCharts;

use Illuminate\Console\Command;

class MakingTopPriceCreatorsAccordingToDioCodeYearly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dio_chart:top_circulation_creators_yearly {year} {--A} {--F}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached data for taking top circulation creators according to dio codes for every year';

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
     * @return Bool
     */
    public function handle()
    {
        // TODO : NEW
        $year = (int)$this->argument('year');
        $option = $this->option('A');
        $first = $this->option('F');
        $start = microtime(true);
    }
}
