<?php

namespace App\Jobs\HomePageCachedData;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BPA_Yearly;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BookAveragePriceYearlyJob implements ShouldQueue
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
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$xpublishdate_shamsi',
                        'count' => ['$sum' => 1],
                        'price' => ['$sum' => '$xcoverprice'],
                    ]
                ],
                [
                    '$sort' => ['_id' => 1] // Sort by year
                ]
            ]);
        });

        foreach ($data as $value) {
            BPA_Yearly::updateOrCreate(
                ['year' =>$value['_id']],
                [
                    'price' => $value['price'],
                    'count' => $value['count'],
                    'average' => round($value['price'] / $value['count'])
                ]
            );
        }
    }
}
