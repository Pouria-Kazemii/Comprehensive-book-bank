<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\publishers;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrPublisher;
use App\Models\MongoDBModels\PublisherCacheData;
use Illuminate\Console\Command;

class MakingPublishersCachedDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:publishers {year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached data for every publisher per year';

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
        $this->info("Start cache every publishers book count");
        $startTime = microtime('true');
        $year = (int)$this->argument('year');
        $currentYear = getYearNow();
        $progressBar = $this->output->createProgressBar(BookIrPublisher::count()*($currentYear-$year));
        $progressBar->start();
        while($year <= $currentYear) {

            $books = BookIrBook2::raw(function ($collection) use($year) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'publisher' => [
                                '$ne' => [],
                            ],
                                'xpublishdate_shamsi' => $year
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => '$publisher.xpublisher_id',
                            'total_circulation' => ['$sum' => '$xcirculation'],
                            'total_pages' => ['$sum' => '$xtotal_page'],
                            'total_price' => ['$sum' => '$xtotal_price'],
                            'total_book' => ['$sum' => 1],
                            'price' => ['$sum' => '$xcoverprice'],
                        ]
                    ]
                ]);
            });

            foreach ($books as $book) {
                $progressBar->advance();
                PublisherCacheData::updateOrCreate(
                    ['publisher_id' => $book['_id'][0] , 'year' => $year]
                    ,
                    [
                        'count' => $book['total_book'],
                        'total_circulation' => $book['total_circulation'],
                        'total_pages' => $book['total_pages'],
                        'total_price' => $book['total_price'],
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
        return true;
    }
}
