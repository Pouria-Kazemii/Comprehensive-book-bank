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

class TopCirculationCreatorsAccordingToDioCodeSubjectsTotallyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $key;
    private$subject;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($key , $subject)
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
                        'xcirculation' => [
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
                        'total_page' => ['$sum' => '$xcirculation']
                    ]
                ],
                [
                    '$sort' => ['total_page' => -1] // Sort by total_price in descending order
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
                    'total_page' => $value->total_page
                ];
            }
            BookDioCachedData::updateOrCreate(
                [
                    'year' => 0,
                    'dio_subject_title' => $this->subject,
                    'dio_subject_id' => $this->key
                ]
                ,
                [
                    'top_circulation_creators' => $creators
                ]
            );
        }
    }
}
