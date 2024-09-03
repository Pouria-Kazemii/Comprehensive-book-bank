<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\creators;

use App\Jobs\CachedData\CreatorsFirstCoverNumberCachedDataJob;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\CreatorCacheData;
use Illuminate\Console\Command;

class MakingCreatorsFirstCoverNumberCachedDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:creators_firstprintnumber {year} {--A}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'start cached all data except average for every first cover number creators per year';

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
        $this->info("Start cache first cover number data for every creators per year book");
        $startTime = microtime('true');
        $year = (int)$this->argument('year');
        $option = $this->option('A');
        if ($option) {
            $currentYear = getYearNow();
            $progressBar = $this->output->createProgressBar($currentYear - $year);
            $progressBar->start();
            while ($year <= $currentYear) {
                CreatorsFirstCoverNumberCachedDataJob::dispatch($year);
                $progressBar->advance();
                $year++;
            }
        } else {
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->start();
            CreatorsFirstCoverNumberCachedDataJob::dispatch($year);
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
