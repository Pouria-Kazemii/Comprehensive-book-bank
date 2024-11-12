<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\publishers;

use App\Jobs\CachedData\PublisherCachedDataSecondJob;
use App\Jobs\CachedData\PublisherCachedDataWithIdSecondJob;
use Illuminate\Console\Command;

class MakingPublishersCachedDataSecondCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:publishers_average {year} {id?} {--A}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached average data for every publisher per year';

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
        $this->info("Start cache average price data every publishers book count");
        $startTime = microtime('true');
        $year = (int)$this->argument('year');
        $id = $this->argument('id');
        $option = $this->option('A');
        if (!$option){
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->start();
            if ($id != null){
                PublisherCachedDataWithIDSecondJob::dispatch($year , $id);
            } else {
                PublisherCachedDataSecondJob::dispatch($year);
            }
            $progressBar->finish();
        } else {
            if ($id == null) {
                $currentYear = getYearNow();
                $progressBar = $this->output->createProgressBar($currentYear - $year);
                $progressBar->start();
                while ($year <= $currentYear) {
                    PublisherCachedDataSecondJob::dispatch($year);
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
