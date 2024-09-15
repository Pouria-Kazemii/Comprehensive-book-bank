<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\creators;

use App\Jobs\CachedData\CreatorsParagraphJob;
use App\Jobs\CachedData\CreatorsParagraphWithIdJob;
use App\Jobs\CachedData\CreatorsTotalParagraphWithIdJob;
use App\Jobs\CachedData\CreatorsTotalParagraphJob;
use Illuminate\Console\Command;

class MakingCreatorsParagraphCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:creators_paragraph {year} {id?} {--A}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached paragraph data for every creator per year';

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
        $this->info("Start cache paragraph data for creators yearly");
        $startTime = microtime('true');
        $year = (int)$this->argument('year');
        $id = $this->argument('id');
        $option = $this->option('A');
        if (!$option){
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->start();
            if ($id != null){
                CreatorsParagraphWithIdJob::dispatch($year , $id);
                CreatorsTotalParagraphWithIdJob::dispatch($year, $id);
            } else {
                CreatorsParagraphJob::dispatch($year);
                CreatorsTotalParagraphJob::dispatch($year);
            }
            $progressBar->finish();
        } else {
            if ($id == null) {
                $currentYear = getYearNow();
                $progressBar = $this->output->createProgressBar($currentYear - $year);
                $progressBar->start();
                while ($year <= $currentYear) {
                    CreatorsParagraphJob::dispatch($year);
                    CreatorsTotalParagraphJob::dispatch($year);
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
