<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedDioCharts\books;

use App\Jobs\DioCodeBooksCachedData\BookAverageDataAccordingToDioCodesForAllTimesJob;
use App\Jobs\DioCodeBooksCachedData\BookFirstCoverNumberAverageDataAccordingToDioCodesForAllTimesJob;
use Illuminate\Console\Command;

class MakingBookAllTimeAverageDataCachedAccordingToDioCodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dio_chart:book_average_all_times {--F}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached all books average data for all times according ro dio code subject ';

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
        $start = microtime(true);
        $option = $this->option('F');
        if (!$option) {
            $this->info('start to cache book average data according to dio codes subject totally');
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->start();
            BookAverageDataAccordingToDioCodesForAllTimesJob::dispatch();

        } else {
            $this->info('start to cache first cover number book average data according to dio codes subject totally');
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->start();
            BookFirstCoverNumberAverageDataAccordingToDioCodesForAllTimesJob::dispatch();
        }
        $progressBar->advance();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $start;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
