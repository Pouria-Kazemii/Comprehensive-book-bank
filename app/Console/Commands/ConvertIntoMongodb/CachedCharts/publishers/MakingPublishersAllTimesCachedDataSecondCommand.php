<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\publishers;

use App\Models\MongoDBModels\BookIrPublisher;
use App\Models\MongoDBModels\PublisherCacheData;
use Illuminate\Console\Command;

class MakingPublishersAllTimesCachedDataSecondCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:publisher_alltime_average';

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
        $rows = BookIrPublisher::count();
        $progressBar = $this->output->createProgressBar($rows);
        $progressBar->start();
        $data = PublisherCacheData::raw(function ($collection) use($progressBar){
            return $collection->aggregate([
                [
                    '$match' => [
                        'average' => [
                            '$ne' => 0
                        ],
                        'year' => [
                            '$ne' => 0
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$publisher_id',
                        'price' => ['$sum' => '$average'],
                        'count' => ['$sum' => 1],
                    ]
                ]
            ]);
        });

        foreach ($data as $item){
            $progressBar->advance();
            PublisherCacheData::updateOrCreate(
                ['publisher_id' => $item['_id'] ,'year' => 0 ]
                ,
                [
                    'average' => round($item['price']/$item['count'])
                ]
            );
        }
        $progressBar->finish();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $start;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
