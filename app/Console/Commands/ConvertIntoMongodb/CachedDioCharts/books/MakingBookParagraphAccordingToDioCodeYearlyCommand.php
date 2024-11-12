<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedDioCharts\books;

use App\Jobs\DioCodeBooksCachedData\BookParagraphAccordinToDioCodesJob;
use App\Jobs\DioCodeBooksCachedData\FirstPrintNumberBooksParagraphAccordingToDioCodesJob;
use Illuminate\Console\Command;

class MakingBookParagraphAccordingToDioCodeYearlyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dio_chart:book_total_paragraph_yearly {year} {--A} {--F}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached data for take total paragraph for every year according to dio codes subject';

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
        if ($option and !$first){
            $this->info('start to caching books total paragraph according to dio code subjects yearly');
            $currentYear = getYearNow();
            $progressBar = $this->output->createProgressBar($currentYear-$year);
            $progressBar->start();
            while($year <= $currentYear){
                BookParagraphAccordinToDioCodesJob::dispatch($year);
                $progressBar->advance();
                $year++;
            }
            $progressBar->finish();
        } elseif ($option and $first){
            $this->info('start to caching first cover number books total paragraph according to dio code subjects yearly');
            $currentYear = getYearNow();
            $progressBar = $this->output->createProgressBar($currentYear-$year);
            $progressBar->start();
            while($year <= $currentYear){
                FirstPrintNumberBooksParagraphAccordingToDioCodesJob::dispatch($year);
                $progressBar->advance();
                $year++;
            }
            $progressBar->finish();
        } elseif ($first and !$option){
            $this->info('start to caching first cover number books total paragraph according to dio code subjects for year '.$year);
            FirstPrintNumberBooksParagraphAccordingToDioCodesJob::dispatch($year);
        } else {
            $this->info('start to caching books total paragraph according to dio code subjects for year '.$year);
            BookParagraphAccordinToDioCodesJob::dispatch($year);
        }
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $start;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
