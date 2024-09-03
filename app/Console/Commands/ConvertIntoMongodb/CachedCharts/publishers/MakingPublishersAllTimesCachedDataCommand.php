<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\publishers;

use App\Jobs\CachedData\PublisherAllTimesCachedDataJob;
use App\Models\MongoDBModels\BookIrPublisher;
use App\Models\MongoDBModels\PublisherCacheData;
use Illuminate\Console\Command;

class MakingPublishersAllTimesCachedDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:publisher_alltime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached all data except average for every publisher all times';

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
        $rows = BookIrPublisher::count();
        $progressBar = $this->output->createProgressBar($rows);
        $progressBar->start();
        BookIrPublisher::chunk(1000,function ($publishers)use($progressBar){
            foreach ($publishers as $publisher){
                PublisherAllTimesCachedDataJob::dispatch($publisher);
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
