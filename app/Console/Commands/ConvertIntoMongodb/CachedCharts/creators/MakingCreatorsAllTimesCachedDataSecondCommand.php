<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\creators;

use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\CreatorCacheData;
use Illuminate\Console\Command;

class MakingCreatorsAllTimesCachedDataSecondCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:creator_alltime_average';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached average data for every creator all times';

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
        $this->info('start cache average data for creators all times');
        $rows = BookIrCreator::count();
        $progressBar = $this->output->createProgressBar($rows);
        $progressBar->start();
        $data = CreatorCacheData::raw(function ($collection) use($progressBar){
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
                        '_id' => '$creator_id',
                        'price' => ['$sum' => '$average'],
                        'count' => ['$sum' => 1],
                    ]
                ]
            ]);
        });

        foreach ($data as $item){
            $progressBar->advance();
            CreatorCacheData::updateOrCreate(
                ['creator_id' => $item['_id'] ,'year' => 0 ]
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
