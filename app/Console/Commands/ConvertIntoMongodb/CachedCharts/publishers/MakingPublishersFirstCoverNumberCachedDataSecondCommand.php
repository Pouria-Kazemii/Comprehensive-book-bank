<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\publishers;

use App\Jobs\CachedData\PublisherFirstCoverNumberCachedDataSecondJob;
use App\Jobs\CachedData\PublisherFirstCoverNumberCachedDataWithIdSecondJob;
use Illuminate\Console\Command;

class MakingPublishersFirstCoverNumberCachedDataSecondCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:publishers_firstprintnumber_average {year} {id?} {--A}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached average data for every first cover number publisher per year';

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
        $this->info("Start cache every publishers book count");
        $startTime = microtime('true');
        $year = (int)$this->argument('year');
        $id = $this->argument('id');
        $option = $this->option('A');
        if (!$option){
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->start();
            if ($id != null){
                PublisherFirstCoverNumberCachedDatawithIdSecondJob::dispatch($year , $id);
            } else {
                PublisherFirstCoverNumberCachedDataSecondJob::dispatch($year);
            }
            $progressBar->finish();
        } else {
            if ($id == null) {
                $currentYear = getYearNow();
                $progressBar = $this->output->createProgressBar($currentYear - $year);
                $progressBar->start();
                while ($year <= $currentYear) {
                    PublisherFirstCoverNumberCachedDataSecondJob::dispatch($year);
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
