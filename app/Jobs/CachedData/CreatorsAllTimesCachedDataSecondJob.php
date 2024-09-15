<?php

namespace App\Jobs\CachedData;

use App\Models\MongoDBModels\CreatorCacheData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreatorsAllTimesCachedDataSecondJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $creatorId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($creatorId)
    {
        $this->creatorId = $creatorId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = CreatorCacheData::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'average' => [
                            '$ne' => 0
                        ],
                        'year' => [
                            '$ne' => 0
                        ],
                        'creator_id' => $this->creatorId
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
        if ($data != null) {
            foreach ($data as $item) {
                CreatorCacheData::updateOrCreate(
                    ['creator_id' => $item['_id'], 'year' => 0]
                    ,
                    [
                        'average' => round($item['price'] / $item['count'])
                    ]
                );
            }
        }
    }
}
