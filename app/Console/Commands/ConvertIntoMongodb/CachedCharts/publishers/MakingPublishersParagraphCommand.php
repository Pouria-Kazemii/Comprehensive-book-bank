<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\publishers;

use App\Jobs\CachedData\PublishersParagraphJob;
use App\Jobs\CachedData\PublishersParagraphWithIdJob;
use App\Jobs\CachedData\PublishersTotalParagraphJob;
use App\Jobs\CachedData\PublishersTotalParagraphWithIdJob;
use Illuminate\Console\Command;

class MakingPublishersParagraphCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:publishers_paragraph {year} {id?} {--A}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached paragraph data for every Publisher per year';

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
        $this->info("Start cache paragraph data for publishers yearly");
        $startTime = microtime('true');
        $year = (int)$this->argument('year');
        $id = $this->argument('id');
        $option = $this->option('A');
        if (!$option){
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->start();
            if ($id != null){
                PublishersParagraphWithIdJob::dispatch($year , $id);
                PublishersTotalParagraphWithIdJob::dispatch($year, $id);
            } else {
                PublishersParagraphJob::dispatch($year);
                PublishersTotalParagraphJob::dispatch($year);
            }
            $progressBar->finish();
        } else {
            if ($id == null) {
                $currentYear = getYearNow();
                $progressBar = $this->output->createProgressBar($currentYear - $year);
                $progressBar->start();
                while ($year <= $currentYear) {
                    PublishersParagraphJob::dispatch($year);
                    PublishersTotalParagraphJob::dispatch($year);
                    $progressBar->advance();
                    $year++;
                }
                $progressBar->finish();
            }else{
                $this->info('can not use --A when entered id');
            }
        }

        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
