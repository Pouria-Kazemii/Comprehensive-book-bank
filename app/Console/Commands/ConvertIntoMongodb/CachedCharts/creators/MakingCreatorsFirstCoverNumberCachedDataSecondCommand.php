<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\creators;

use App\Jobs\CachedData\CreatorsFirstCoverNumberCachedDataSecondJob;
use App\Jobs\CachedData\CreatorsFirstCoverNumberWithIdCachedDataSecondJob;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\CreatorCacheData;
use Illuminate\Console\Command;

class MakingCreatorsFirstCoverNumberCachedDataSecondCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:creators_firstprintnumber_average {year} {id?} {--A}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cached average data for every first cover number creators per year';

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
        $this->info("Start cache average data for first cover number creators yearly");
        $startTime = microtime('true');
        $year = (int)$this->argument('year');
        $id = $this->argument('id');
        $option = $this->option('A');
        if (!$option){
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->start();
            if ($id != null){
                CreatorsFirstCoverNumberWithIdCachedDataSecondJob::dispatch($year , $id);
            } else {
                CreatorsFirstCoverNumberCachedDataSecondJob::dispatch($year);
            }
            $progressBar->finish();
        } else {
            if ($id == null) {
                $currentYear = getYearNow();
                $progressBar = $this->output->createProgressBar($currentYear - $year);
                $progressBar->start();
                while ($year <= $currentYear) {
                    CreatorsFirstCoverNumberCachedDataSecondJob::dispatch($year);
                    $progressBar->advance();
                    $year++;
                }
                $progressBar->finish();
            }else{
                $this->info('can not use --A when entered id');
            }
        }

        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
