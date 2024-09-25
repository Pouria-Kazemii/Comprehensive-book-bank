<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedDioCharts\books;

use App\Jobs\DioCodeBooksCachedData\BookTotalCirculationAccordinToDioCodesJob;
use App\Jobs\DioCodeBooksCachedData\FirstPrintNumberTotalCirculationBooksAccordingToDioCodesJob;
use Illuminate\Console\Command;

class MakingBookTotalCirculationAccordingToDioCodeYearlyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dio_chart:book_total_circulation_yearly {year} {--A} {--F}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached data for take total circulation for every year according to dio codes subject';

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
        $year = (int)$this->argument('year');
        $option = $this->option('A');
        $first = $this->option('F');
        $start = microtime(true);
        if ($option and !$first) {
            $this->info('start to caching books total circulation according to dio code subjects yearly');
            $currentYear = getYearNow();
            $progressBar = $this->output->createProgressBar($currentYear - $year);
            $progressBar->start();
            while ($year <= $currentYear) {
                BookTotalCirculationAccordinToDioCodesJob::dispatch($year);
                $progressBar->advance();
                $year++;
            }
            $progressBar->finish();
        } elseif ($first and $option) {
            $this->info('start to caching books first cover number total circulation according to dio code subjects yearly');
            $currentYear = getYearNow();
            $progressBar = $this->output->createProgressBar($currentYear - $year);
            $progressBar->start();
            while ($year <= $currentYear) {
                FirstPrintNumberTotalCirculationBooksAccordingToDioCodesJob::dispatch($year);
                $progressBar->advance();
                $year++;
            }
            $progressBar->finish();
        } elseif ($first and !$option) {
            $this->info('start to caching books first cover number total circulation according to dio code subjects at year ' . $year);
            FirstPrintNumberTotalCirculationBooksAccordingToDioCodesJob::dispatch($year);
        } else {
            $this->info('start to caching books total circulation according to dio code subjects at year' . $year);
            BookTotalCirculationAccordinToDioCodesJob::dispatch($year);
        }
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $start;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
