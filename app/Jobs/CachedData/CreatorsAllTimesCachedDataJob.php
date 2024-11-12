<?php

namespace App\Jobs\CachedData;

use App\Models\MongoDBModels\CreatorCacheData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreatorsAllTimesCachedDataJob implements ShouldQueue
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
                        'year' => [
                            '$ne' => 0
                        ],
                        'creator_id' => $this->creatorId
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
        if ($data != null) {
            foreach ($data as $item) {
                CreatorCacheData::updateOrCreate(
                    ['creator_id' => $item['_id'], 'year' => 0]
                    ,
                    [
                        'count' => $item['count'],
                        'total_circulation' => $item['total_circulation'],
                        'total_pages' => $item['total_pages'],
                        'total_price' => $item['total_price'],
                    ]
                );
            }
        }
    }
}
