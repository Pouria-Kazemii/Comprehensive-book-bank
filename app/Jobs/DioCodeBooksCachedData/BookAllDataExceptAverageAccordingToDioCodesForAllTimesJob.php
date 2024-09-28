<?php

namespace App\Jobs\DioCodeBooksCachedData;

use App\Models\MongoDBModels\BookDioCachedData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BookAllDataExceptAverageAccordingToDioCodesForAllTimesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
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
                        'year' => [
                            '$ne' => 0
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$dio_subject_id',
                        'dio_subject_title' => ['$first' => '$dio_subject_title'],
                        'total_circulation' => ['$sum' => '$total_circulation'],
                        'total_price' => ['$sum' => '$total_price'],
                        'total_pages' => ['$sum' => '$total_pages'],
                        'count' => ['$sum' => '$count'],
                        'paragraph' => ['$sum' => '$paragraph'],
                        'first_cover_total_circulation' => ['$sum' => '$first_cover_total_circulation'],
                        'first_cover_total_price' => ['$sum' => '$first_cover_total_price'],
                        'first_cover_total_pages' => ['$sum' => '$first_cover_total_pages'],
                        'first_cover_count' => ['$sum' => '$first_cover_count'],
                        'first_cover_paragraph' => ['$sum' => '$first_cover_paragraph']
                    ]
                ]
            ]);
        });
        if ($data != null){
            foreach ($data as $value){
                BookDioCachedData::updateOrCreate(
                   [
                       'year' => 0 ,
                       'dio_subject_id'=>$value['_id'] ,
                       'dio_subject_title' => $value['dio_subject_title']
                   ]
                   ,
                   [
                       'count' => $value['count'],
                       'paragraph' => $value['paragraph'],
                       'total_circulation' => $value['total_circulation'],
                       'total_pages' => $value['total_pages'],
                       'total_price' => $value['total_price'],
                       'first_cover_count' => $value['first_cover_count'],
                       'first_cover_paragraph' => $value['first_cover_paragraph'],
                       'first_cover_total_circulation' => $value['first_cover_total_circulation'],
                       'first_cover_total_pages' => $value['first_cover_total_pages'],
                       'first_cover_total_price' => $value['first_cover_total_price']
                   ]
                );
            }
        }
    }
}
