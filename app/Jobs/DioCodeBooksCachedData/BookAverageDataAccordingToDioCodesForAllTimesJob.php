<?php

namespace App\Jobs\DioCodeBooksCachedData;

use App\Models\MongoDBModels\BookDioCachedData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BookAverageDataAccordingToDioCodesForAllTimesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = BookDioCachedData::raw(function ($collection){
            return $collection->aggregate([
                [
                    '$match' => [
                        'average' => [
                            '$ne' => 0
                        ],
                        'year' => [
                            '$ne' => 0
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$dio_subject_id',
                        'dio_subject_title' => ['$first' => '$dio_subject_title'],
                        'average' => ['$sum' => '$average'] ,
                        'count' => ['$sum' => 1]
                    ]
                ]
            ]);
        });
        if ($data != null){
            foreach ($data as $value){
                BookDioCachedData::updateOrCreate(
                    [
                        'year' => 0,
                        'dio_subject_title' => $value['dio_subject_title'],
                        'dio_subject_id' => $value['_id']
                    ]
                    ,
                    [
                        'average' => round($value['average'] / $value['count'])
                    ]
                );
            }
        }
    }
}
