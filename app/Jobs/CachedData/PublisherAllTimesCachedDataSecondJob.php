<?php

namespace App\Jobs\CachedData;

use App\Models\MongoDBModels\PublisherCacheData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublisherAllTimesCachedDataSecondJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $publisher;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = PublisherCacheData::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'average' => [
                            '$ne' => 0
                        ],
                        'year' => [
                            '$ne' => 0
                        ],
                        'publisher_id' => $this->publisher->_id
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
        if ($data != null) {
            foreach ($data as $item) {
                PublisherCacheData::updateOrCreate(
                    ['publisher_id' => $item['_id'], 'year' => 0]
                    ,
                    [
                        'average' => round($item['price'] / $item['count'])
                    ]
                );
            }
        }
    }
}
