<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedDioCharts;

use App\Jobs\DioCodeBooksCachedData\TopCirculationPublishersAccordingToDioCodeSubjectsJob;
use App\Jobs\DioCodeBooksCachedData\TopCirculationPublishersAccordingToDioCodeSubjectsTotallyJob;
use App\Models\MongoDBModels\DioSubject;
use Illuminate\Console\Command;

class MakingTopCirculationPublishersAccordingToDioCodeYearly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dio_chart:top_circulation_publisher_yearly {year} {--A}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached data for taking top circulation publishers for every year according to dio code subjects';

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
        $this->info("Start cache top circulation publisher yearly");
        $startTime = microtime('true');
        $year = (int)$this->argument('year');
        $option = $this->option('A');
        $subjects = DioSubject::pluck('title', 'id_by_law');
        if ($year == 0){
            $progressBar = $this->output->createProgressBar(DioSubject::count());
            $progressBar->start();
            foreach ($subjects as $key => $subject) {
                TopCirculationPublishersAccordingToDioCodeSubjectsTotallyJob::dispatch($key, $subject);
                $progressBar->advance();
            }
        } else if ($option) {
            $currentYear = getYearNow();
            $progressBar = $this->output->createProgressBar($currentYear - $year);
            $progressBar->start();
            while ($year <= $currentYear) {
                foreach ($subjects as $key => $subject) {
                    TopCirculationPublishersAccordingToDioCodeSubjectsJob::dispatch($year, $key, $subject);
                }
                $progressBar->advance();
                $year++;
            }
        } else {
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->start();
            foreach ($subjects as $key => $subject) {
                TopCirculationPublishersAccordingToDioCodeSubjectsJob::dispatch($year, $key, $subject);
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
