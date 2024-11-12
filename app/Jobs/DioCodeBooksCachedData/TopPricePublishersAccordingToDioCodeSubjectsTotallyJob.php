<?php

namespace App\Jobs\DioCodeBooksCachedData;

use App\Models\MongoDBModels\BookDioCachedData;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TopPricePublishersAccordingToDioCodeSubjectsTotallyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $key;
    private $subject;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($key,$subject)
    {
        $this->key = $key;
        $this->subject = $subject;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = BookIrBook2::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'xtotal_price' => [
                            '$ne' => 0
                        ],
                        'diocode_subject' => [
                            '$elemMatch' => [
                                $this->key => $this->subject
                            ]
                        ],

                    ]
                ],
                [
                    '$unwind' => '$publisher'
                ],
                [
                    '$group' => [
                        '_id' => [
                            'id' => '$publisher.xpublisher_id',
                            'name' => '$publisher.xpublishername'
                        ],
                        'total_price' => ['$sum' => '$xtotal_price']
                    ]
                ],
                [
                    '$sort' => ['total_price' => -1] // Sort by total_price in descending order
                ],
                [
                    '$limit' => 50 // Limit to top 30 creators
                ]
            ]);
        });
        $publisher = [];
        if ($data != null) {
            foreach ($data as $value) {
                $publisher[] = [
                    'publisher_id' => $value->_id['id'],
                    'publisher_name' => $value->_id['name'],
                    'total_price' => $value->total_price
                ];
            }
            BookDioCachedData::updateOrCreate(
                [
                    'year' => 0 ,
                    'dio_subject_title' => $this->subject,
                    'dio_subject_id' => $this->key
                ]
                ,
                [
                    'top_price_publishers' => $publisher
                ]
            );
        }
    }
}
