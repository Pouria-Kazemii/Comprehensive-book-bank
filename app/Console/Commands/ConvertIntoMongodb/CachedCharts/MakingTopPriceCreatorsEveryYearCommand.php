<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts;

use App\Jobs\HomePageCachedData\TopPriceCreatorsJob;
use Illuminate\Console\Command;

class MakingTopPriceCreatorsEveryYearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:top_price_creators_yearly {year} {--A}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached data for taking top price creators for every year';

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
        $this->info("Start cache top price creators yearly");
        $startTime = microtime('true');
        $year = (int)$this->argument('year');
        $option = $this->option('A');
        if ($option){
            $currentYear = getYearNow();
            $progressBar = $this->output->createProgressBar($currentYear-$year);
            $progressBar->start();
            while($year <= $currentYear) {
                TopPriceCreatorsJob::dispatch($year);
                $progressBar->advance();
                $year++;
            }
        }else{
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->start();
            TopPriceCreatorsJob::dispatch($year);
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
