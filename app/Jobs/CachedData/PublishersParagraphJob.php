<?php

namespace App\Jobs\CachedData;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\ParsiToEnglishFormat;
use App\Models\MongoDBModels\PublisherCacheData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishersParagraphJob implements ShouldQueue
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
                        'xformat' => [
                            '$ne' => ''
                        ],
                        'xtotal_page' => [
                            '$ne' => 0
                        ],
                        'xpublishdate_shamsi' => $this->year
                    ]
                ],
                [
                    '$unwind' => '$publisher'
                ],
                [
                    '$group' => [
                        '_id' =>[
                            'publisher_id' =>'$publisher.xpublisher_id',
                            'format' =>'$xformat'
                        ],
                        'total_page' => ['$sum' => '$xtotal_page']
                    ]
                ],
                [
                    '$sort' => [
                        '_id.publisher_id' => 1
                    ]
                ]
            ]);
        });

        foreach ($books as $book) {
            if (ParsiToEnglishFormat::where('fa_title' , $book->_id->format)->exists()) {
                $column = ParsiToEnglishFormat::where('fa_title', $book->_id->format)->first()->en_title;
                $paragraph = takeBookParagraph($book->_id->format, $book['total_page']);
                PublisherCacheData::updateOrCreate(
                    ['publisher_id' => $book->_id->publisher_id, 'year' => $this->year]
                    ,
                    [
                        $column => $paragraph
                    ]
                );
            }
        }
    }
}
