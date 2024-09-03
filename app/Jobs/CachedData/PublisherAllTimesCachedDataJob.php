<?php

namespace App\Jobs\CachedData;

use App\Models\MongoDBModels\PublisherCacheData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublisherAllTimesCachedDataJob implements ShouldQueue
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
                        'year' => [
                            '$ne' => 0
                        ],
                        'publisher_id' => $this->publisher->_id
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$publisher_id',
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
                PublisherCacheData::updateOrCreate(
                    ['publisher_id' => $item['_id'], 'year' => 0]
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
