<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts;

use App\Jobs\CachedData\BooksTotalParagraphJob;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Console\Command;

class MakingBookParagraphEveryYearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:book_total_paragraph_yearly {year} {--A}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached data for take total paragraph (band) for every year';

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
        $this->info("Start cache every creator data");
        $startTime = microtime('true');
        $year = (int)$this->argument('year');
        $option = $this->option('A');
        if ($option){
            $currentYear = getYearNow();
            $progressBar = $this->output->createProgressBar($currentYear-$year);
            $progressBar->start();
            while($year <= $currentYear) {
                BooksTotalParagraphJob::dispatch($year);
                $progressBar->advance();
                $year++;
            }
        }else{
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->start();
            BooksTotalParagraphJob::dispatch($year);
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
