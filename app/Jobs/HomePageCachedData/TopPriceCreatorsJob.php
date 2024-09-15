<?php

namespace App\Jobs\HomePageCachedData;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\TPC_Yearly;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TopPriceCreatorsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private$year;
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
        $data = BookIrBook2::raw(function ($collection)  {
            return $collection->aggregate([
                [
                    '$match' => [
                        'xpublishdate_shamsi' => $this->year
                        ,
                        'xtotal_price' => [
                            '$ne' => 0 // Ensure xcoverprice is not equal to 0
                        ]
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
        foreach ($data as $value) {
            $creators[] = [
                'creator_id' => $value->_id['id'],
                'creator_name' => $value->_id['name'],
                'total_price' => $value->total_price
            ];
        }
        TPC_Yearly::updateOrCreate(
            ['year' => $this->year],
            [
                'creators' => $creators,
            ]
        );
    }
}
