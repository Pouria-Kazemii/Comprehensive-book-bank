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

class FirstPrintNumberBooksParagraphAccordingToDioCodesJob implements ShouldQueue
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
        $subjects = DioSubject::pluck('title', 'id_by_law');
        foreach ($subjects as $key => $subject) {
            $data = BookIrBook2::raw(function ($collection) use ($key, $subject) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'xpublishdate_shamsi' => $this->year,
                            'xtotal_pages' => [
                                '$ne' => 0
                            ],
                            'xformat' => [
                                '$ne' => ''
                            ],
                            'xprintnumber' => 1
                            ,
                            'diocode_subject' => [
                                '$elemMatch' => [
                                    $key => $subject
                                ]
                            ]
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => '$xformat',
                            'total_pages' => ['$sum' => '$xtotal_page'],
                        ]
                    ],
                ]);
            });
            $totalParagraph = 0;
            if (count($data) > 0)
                foreach ($data as $value){
                    $paragraph = takeBookParagraph($value['_id'],$value['total_pages']);
                    $totalParagraph += $paragraph;
                }
            BookDioCachedData::updateOrCreate(
                ['year' => $this->year, 'dio_subject_title' => $subject, 'dio_subject_id' => $key]
                ,
                ['first_cover_paragraph' => $totalParagraph]
            );
        }
    }
}
