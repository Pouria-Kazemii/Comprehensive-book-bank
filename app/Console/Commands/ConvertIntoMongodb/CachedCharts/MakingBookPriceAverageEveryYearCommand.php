<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts;

use App\Jobs\HomePageCachedData\BookAveragePriceYearlyJob;
use Illuminate\Console\Command;

class MakingBookPriceAverageEveryYearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:book_price_average_yearly {year} {--A}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached data for take book price average for every year';

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
        $this->info("Start cache book price average yearly");
        $year = (int)$this->argument('year');
        $option = $this->option('A');
        $startTime = microtime(true);
        if ($option){
            $currentYear = getYearNow();
            $progressBar = $this->output->createProgressBar($currentYear-$year);
            $progressBar->start($year);
            while($year <= $currentYear) {
                BookAveragePriceYearlyJob::dispatch($year);
                $progressBar->advance();
                $year++;
            }
        }else{
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->start($year);
            BookAveragePriceYearlyJob::dispatch($year);
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
