<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedDioCharts;

use App\Jobs\DioCodeBooksCachedData\TopPriceCreatorsAccordingToDioCodeSubjectsJob;
use App\Models\MongoDBModels\DioSubject;
use Illuminate\Console\Command;

class MakingTopPriceCreatorsAccordingToDioCodeYearly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dio_chart:top_price_creator_yearly {year} {--A}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached data for taking top price creators according to dio codes for every year';

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
        $subjects = DioSubject::pluck('title', 'id_by_law');
        if ($option) {
            $currentYear = getYearNow();
            $progressBar = $this->output->createProgressBar($currentYear - $year);
            $progressBar->start();
            while ($year <= $currentYear) {
                foreach ($subjects as $key => $subject) {
                    TopPriceCreatorsAccordingToDioCodeSubjectsJob::dispatch($year, $key, $subject);
                }
                $progressBar->advance();
                $year++;
            }
        } else {
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->start();
            foreach ($subjects as $key => $subject) {
                TopPriceCreatorsAccordingToDioCodeSubjectsJob::dispatch($year, $key, $subject);
            }
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
