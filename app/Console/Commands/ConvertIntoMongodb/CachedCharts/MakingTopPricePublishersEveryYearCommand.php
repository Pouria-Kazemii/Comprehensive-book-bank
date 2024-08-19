<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\TPP_Yearly;
use Illuminate\Console\Command;

class MakingTopPricePublishersEveryYearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:top_price_publishers_yearly {year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached data for taking top price publishers for every year';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = (int)$this->argument('year');
        $this->info("Start cache top price publishers yearly");
        $totalRows = getYearNow() - $date + 1;
        $progressBar = $this->output->createProgressBar($totalRows);
        $progressBar->start();
        $startTime = microtime(true);
        for ($i=$date ; $i<=getYearNow() ; $i++) {
            $data = BookIrBook2::raw(function ($collection) use ($i) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'xpublishdate_shamsi' => $i
                            ,
                            'xtotal_price' => [
                                '$ne' => 0 // Ensure xcoverprice is not equal to 0
                            ]
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
            $publishers = [];
            foreach ($data as $value) {
                $publishers[] = [
                    'publisher_id' => $value->_id['id'],
                    'publisher_name' => $value->_id['name'],
                    'total_price' => $value->total_price
                ];
            }
            TPP_Yearly::updateOrCreate(
                ['year' => $i],
                [
                'publishers' => $publishers,
                ]
            );
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
