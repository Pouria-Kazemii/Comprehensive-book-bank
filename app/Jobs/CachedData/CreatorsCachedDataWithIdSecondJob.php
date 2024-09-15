<?php

namespace App\Jobs\CachedData;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\CreatorCacheData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreatorsCachedDataWithIdSecondJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $year;
    private$id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($year , $id)
    {
        $this->year = $year;
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $books = BookIrBook2::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'partners' => [
                            '$ne' => [],
                        ],
                        'xcoverprice' => [
                            '$ne' => 0
                        ],
                        'xpublishdate_shamsi' => $this->year
                    ]
                ],
                [
                    '$unwind' => '$partners'
                ],
                [
                    '$match' => [
                        'partners.xcreator_id' => $this->id
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$partners.xcreator_id',
                        'total_book' => ['$sum' => 1],
                        'price' => ['$sum' => '$xcoverprice'],
                    ]
                ]
            ]);
        });

        foreach ($books as $book) {
            CreatorCacheData::updateOrCreate(
                ['creator_id' => $book['_id'] , 'year' => $this->year]
                ,
                [
                    'average' => round($book['price']/$book['total_book'])
                ]
            );
        }
    }
}
