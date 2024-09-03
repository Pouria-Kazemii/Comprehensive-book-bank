<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\creators;

use App\Jobs\CachedData\CreatorsAllTimesCachedDataJob;
use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\CreatorCacheData;
use Illuminate\Console\Command;

class MakingCreatorsAllTimesCachedDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:creator_alltime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached add data except average for creators all times';

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
        $start = microtime('true');
        $this->info('start cache all data except average for publisher all times');
        $rows = BookIrCreator::count();
        $progressBar = $this->output->createProgressBar($rows);
        $progressBar->start();
       BookIrCreator::chunk(1000,function ($creators)use($progressBar){
           foreach ($creators  as $creator){
               CreatorsAllTimesCachedDataJob::dispatch($creator);
               $progressBar->advance();
           }
       });
        $progressBar->finish();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $start;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
