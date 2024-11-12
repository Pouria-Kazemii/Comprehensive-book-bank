<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedDioCharts\books;

use App\Jobs\DioCodeBooksCachedData\BookAllDataExceptAverageAccordingToDioCodesForAllTimesJob;
use Illuminate\Console\Command;

class MakingBookAllTimeDataCachedExceptAverageAccordingToDioCodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dio_chart:book_data_all_times';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached all books data except average for all times according ro dio code subject ';

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
        $this->info('start to cache book all data except average according to dio codes subject totally');
        $progressBar = $this->output->createProgressBar(1);
        $progressBar->start();
        BookAllDataExceptAverageAccordingToDioCodesForAllTimesJob::dispatch();
        $progressBar->advance();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $start;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
