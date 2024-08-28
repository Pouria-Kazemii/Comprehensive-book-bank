<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\creators;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\CreatorCacheData;
use Illuminate\Console\Command;

class MakingCreatorsCachedDataSecondCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:creators_average {year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached average data for every creator per year';

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
     * @return Bool
     */
    public function handle()
    {
        $this->info("Start cache average price data every creators per year");
        $startTime = microtime('true');
        $year = (int)$this->argument('year');
        $currentYear = getYearNow();
        $progressBar = $this->output->createProgressBar(BookIrCreator::count()*($currentYear-$year));
        $progressBar->start();
        while($year <= $currentYear) {

            $books = BookIrBook2::raw(function ($collection) use($year) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'partners' => [
                                '$ne' => [],
                            ],
                            'xcoverprice' => [
                                '$ne' => 0
                            ],
                            'xpublishdate_shamsi' => $year
                        ]
                    ],
                    [
                      '$unwind' => '$partners'
                    ],
                    [
                        '$group' => [
                            '_id' => '$partners.xcreator_id',
                            'total_book' => ['$sum' => 1],
                            'price' => ['$sum' => '$xcoverprice'],
                        ]
                    ]
                ]);
            });

            foreach ($books as $book) {
                $progressBar->advance();
                CreatorCacheData::updateOrCreate(
                    ['creator_id' => $book['_id'] , 'year' => $year]
                    ,
                    [
                        'average' => round($book['price']/$book['total_book'])
                    ]
                );
            }
            $year++;
        }
        $progressBar->finish();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;    }
}
