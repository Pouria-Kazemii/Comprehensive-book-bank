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

class TopPriceCreatorsAccordingToDioCodeSubjectsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $year;
    private $key;
    private$subject;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($year , $key , $subject)
    {
        $this->year = $year;
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
                        'xpublishdate_shamsi' => $this->year,
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
                    '$unwind' => '$partners'
                ],
                [
                    '$group' => [
                        '_id' => [
                            'id' => '$partners.xcreator_id',
                            'name' => '$partners.xcreatorname'
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
        $creators = [];
        if ($data != null) {
            foreach ($data as $value) {
                $creators[] = [
                    'creator_id' => $value->_id['id'],
                    'creator_name' => $value->_id['name'],
                    'total_price' => $value->total_price
                ];
            }
            BookDioCachedData::updateOrCreate(
                [
                    'year' => $this->year,
                    'dio_subject_title' => $this->subject,
                    'dio_subject_id' => $this->key
                ]
                ,
                [
                    'top_price_creators' => $creators
                ]
            );
        }
    }
}
