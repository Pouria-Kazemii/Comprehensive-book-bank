<?php

namespace App\Jobs\HomePageCachedData;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BTP_Yearly;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BookTotalPriceJob implements ShouldQueue
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
        $data = BookIrBook2::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'xpublishdate_shamsi' => $this->year
                        ,
                        'xtotal_price' => [
                            '$ne' => 0
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$xpublishdate_shamsi',
                        'total_price' => ['$sum' => '$xtotal_price'],
                    ]
                ],
                [
                    '$sort' => ['_id' => 1] // Sort by year
                ]
            ]);
        });

        foreach ($data as $value) {
            BTP_Yearly::updateOrCreate(
                ['year' => $value['_id']],
                [
                    'price' => $value['total_price']
                ]
            );
        }
    }
}
