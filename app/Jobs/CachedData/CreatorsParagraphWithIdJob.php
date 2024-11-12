<?php

namespace App\Jobs\CachedData;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\CreatorCacheData;
use App\Models\MongoDBModels\ParsiToEnglishFormat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreatorsParagraphWithIdJob implements ShouldQueue
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
                    '$unwind' => '$partners'
                ],
                [
                    '$match' => [
                        'partners.xcreator_id' => $this->id
                    ]
                ],
                [
                    '$group' => [
                        '_id' =>[
                            'partner_id' =>'$partners.xcreator_id',
                            'format' =>'$xformat'
                        ],
                        'total_page' => ['$sum' => '$xtotal_page']
                    ]
                ],
                [
                    '$sort' => [
                        '_id.partner_id' => 1
                    ]
                ]
            ]);
        });

        foreach ($books as $book) {
            if (ParsiToEnglishFormat::where('fa_title', $book->_id->format)->exists()) {
                $column = ParsiToEnglishFormat::where('fa_title', $book->_id->format)->first()->en_title;
                $paragraph = takeBookParagraph($book->_id->format, $book['total_page']);
                CreatorCacheData::updateOrCreate(
                    ['creator_id' => $book->_id->partner_id, 'year' => $this->year]
                    ,
                    [
                        $column => $paragraph
                    ]
                );
            }
        }
    }
}
