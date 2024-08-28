<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\creators;

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
        $data = CreatorCacheData::raw(function ($collection) use($progressBar){
            return $collection->aggregate([
                [
                    '$match' => [
                        'year' => [
                            '$ne' => 0
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$creator_id',
                        'total_circulation' => ['$sum' => '$total_circulation'],
                        'total_price' => ['$sum' => '$total_price'],
                        'total_pages' => ['$sum' => '$total_pages'],
                        'count' => ['$sum' => '$count'],
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
                    'count' => $item['count'],
                    'total_circulation' => $item['total_circulation'],
                    'total_pages' => $item['total_pages'],
                    'total_price' => $item['total_price'],
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
