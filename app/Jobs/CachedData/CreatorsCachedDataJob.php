<?php

namespace App\Jobs\CachedData;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\CreatorCacheData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreatorsCachedDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $year;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($year)
    {
        $this->year = $year;
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
                        'xpublishdate_shamsi' => $this->year
                    ]
                ],
                [
                    '$unwind' => '$partners'
                ],
                [
                    '$group' => [
                        '_id' => '$partners.xcreator_id',
                        'total_circulation' => ['$sum' => '$xcirculation'],
                        'total_pages' => ['$sum' => '$xtotal_page'],
                        'total_price' => ['$sum' => '$xtotal_price'],
                        'total_book' => ['$sum' => 1],
                    ]
                ]
            ]);
        });

        foreach ($books as $book) {
            CreatorCacheData::updateOrCreate(
                ['creator_id' => $book['_id'] , 'year' => $this->year]
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
