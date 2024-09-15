<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\publishers;

use App\Jobs\CachedData\PublisherAllTimesCachedDataSecondJob;
use App\Models\MongoDBModels\BookIrPublisher;
use Illuminate\Console\Command;

class MakingPublishersAllTimesCachedDataSecondCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:publisher_alltime_average {id?} {--S}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached average data for every publisher all times';

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
        $option = $this->option('S');
        if ($option){
            $id  = $this->argument('id');
            PublisherAllTimesCachedDataSecondjob::dispatch($id);
        } else {
            $row = BookIrPublisher::count();
            $progressBar = $this->output->createProgressBar($row);
            $progressBar->start();
            BookIrPublisher::chunk(1000, function ($publishers) use ($progressBar) {
                foreach ($publishers as $publisher) {
                    PublisherAllTimesCachedDataSecondjob::dispatch($publisher->_id);
                    $progressBar->advance();
                }
            });
            $progressBar->finish();
        }
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $start;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
