<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedDioCharts\books;

use App\Jobs\DioCodeBooksCachedData\BookTotalCountAccordinToDioCodesJob;
use Illuminate\Console\Command;

class MakingBookTotalCountAccordingToDioCodeYearlyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dio_chart:book_total_count_yearly {year} {--A}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached data for take total count for every year according to dio codes subject';

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
        $this->info('start to caching books total count according to dio code subjects yearly');
        $year = (int)$this->argument('year');
        $option = $this->option('A');
        $start = microtime(true);
        if ($option){
            $currentYear = getYearNow();
            $progressBar = $this->output->createProgressBar($currentYear-$year);
            $progressBar->start();
            while($year <= $currentYear){
                BookTotalCountAccordinToDioCodesJob::dispatch($year);
                $progressBar->advance();
                $year++;
            }
            $progressBar->finish();
        } else {
            BookTotalCountAccordinToDioCodesJob::dispatch($year);
        }
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $start;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
