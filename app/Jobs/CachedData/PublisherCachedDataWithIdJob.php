<?php

namespace App\Jobs\CachedData;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\PublisherCacheData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublisherCachedDataWithIdJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $year;
    private $id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($year , $id)
    {
        $this->year = $year;
        $this->id  = $id;
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
                        'publisher' => [
                            '$ne' => [],
                        ],
                        'xpublishdate_shamsi' => $this->year,
                        'publisher.xpublishr_id' => $this->id
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$publisher.xpublisher_id',
                        'total_circulation' => ['$sum' => '$xcirculation'],
                        'total_pages' => ['$sum' => '$xtotal_page'],
                        'total_price' => ['$sum' => '$xtotal_price'],
                        'total_book' => ['$sum' => 1],
                    ]
                ]
            ]);
        });

        foreach ($books as $book) {
            PublisherCacheData::updateOrCreate(
                ['publisher_id' => $book['_id'][0] , 'year' => $this->year]
                ,
                [
                    'count' => $book['total_book'],
                    'total_circulation' => $book['total_circulation'],
                    'total_pages' => $book['total_pages'],
                    'total_price' => $book['total_price'],
                ]
            );
        }
    }
}