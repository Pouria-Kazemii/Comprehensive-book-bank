<?php

namespace App\Jobs\CachedData;

use App\Models\MongoDBModels\CreatorCacheData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreatorsAllTimesCachedParagraphDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $creator;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($creator)
    {
        $this->creator = $creator;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = CreatorCacheData::raw(function ($collection){
            return $collection->aggregate([
                [
                    '$match' => [
                        'year' =>[
                            '$ne' => 0
                        ],
                        'creator_id' => $this->creator->_id
                    ]
                ],
                [
                    '$group' =>[
                        '_id' => '$creator_id',
                        'total_paragraph' => ['$sum' => '$paragraph']
                    ]
                ]
            ]);
        });

        if ($data != null){
            foreach ($data as $item){
                CreatorCacheData::updateOrCreate(
                    ['creator_id' => $item['_id'] , 'year' => 0]
                    ,
                    [
                        'paragraph' => $item['total_paragraph']
                    ]
                );
            }
        }
    }
}
