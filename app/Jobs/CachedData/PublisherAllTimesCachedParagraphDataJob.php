<?php

namespace App\Jobs\CachedData;

use App\Models\MongoDBModels\PublisherCacheData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublisherAllTimesCachedParagraphDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $publisherId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($publisherId)
    {
        $this->publisherId = $publisherId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = PublisherCacheData::raw(function ($collection){
            return $collection->aggregate([
                [
                    '$match' => [
                        'year' =>[
                            '$ne' => 0
                        ],
                        'publisher_id'=> $this->publisherId
                    ]
                ],
                [
                    '$group' =>[
                        '_id' => '$publisher_id',
                        'total_paragraph' => ['$sum' => '$paragraph']
                    ]
                ]
            ]);
        });
        if ($data != null){
            foreach ($data as $item){
                PublisherCacheData::updateOrCreate(
                    ['publisher_id' => $item['_id'] , 'year' => 0]
                    ,
                    [
                        'paragraph' => $item['total_paragraph']
                    ]
                );
            }
        }
    }
}
