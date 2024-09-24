<?php

namespace App\Jobs\DioCodeBooksCachedData;

use App\Models\MongoDBModels\BookDioCachedData;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\DioSubject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BookTotalCirculationAccordinToDioCodesJob implements ShouldQueue
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
        $subjects = DioSubject::pluck('title','id_by_law');
        foreach ($subjects as $key => $subject){
            $data = BookIrBook2::raw(function ($collection) use($key,$subject){
                return $collection->aggregate([
                    [
                        '$match' => [
                            'xpublishdate_shamsi' => $this->year,
                            'xcirculation' =>[
                                '$ne' => 0
                            ],
                            'diocode_subject' => [
                                '$elemMatch' =>[
                                    $key => $subject
                                ]
                            ]
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => '$xpublishdate_shamsi',
                            'total_circulation' => ['$sum' => '$xcirculation'],
                        ]
                    ],
                    [
                        '$sort' => ['_id' => 1]
                    ]
                ]);
            });
            if (count($data) == 1)
            BookDioCachedData::updateOrCreate(
                ['year' => $data[0]['_id'] , 'dio_subject_title' => $subject , 'dio_subject_id' => $key]
                ,
                ['total_circulation' => $data[0]['total_circulation'] ]
            );
        }
    }
}
