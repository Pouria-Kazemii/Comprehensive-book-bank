<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\publishers;

use App\Jobs\CachedData\PublisherFirstCoverNumberCachedDataSecondJob;
use Illuminate\Console\Command;

class MakingPublishersFirstCoverNumberCachedDataSecondCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:publishers_firstprintnumber_average {year} {--A}';

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
        $option = $this->option('A');
        if ($option) {
            $currentYear = getYearNow();
            $progressBar = $this->output->createProgressBar($currentYear - $year);
            $progressBar->start();
            while ($year <= $currentYear) {
                PublisherFirstCoverNumberCachedDataSecondJob::dispatch($year);
                $progressBar->advance();
                $year++;
            }
        } else {
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->start();
            PublisherFirstCoverNumberCachedDataSecondJob::dispatch($year);
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
