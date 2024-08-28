<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\creators;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\CreatorCacheData;
use Illuminate\Console\Command;

class MakingCreatorsCachedDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:creators {year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached all data except average for every creator per year';

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
        $this->info("Start cache every creator data");
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
                            'xpublishdate_shamsi' => $year
                        ]
                    ],
                    [
                        '$unwind' => '$partners'
                    ],
                    [
                        '$group' => [
                            '_id' => '$partners.xcreator_id',
                            'total_circulation' => ['$sum' => '$xcirculation'],
                            'total_pages' => ['$sum' => '$xtotal_page'],
                            'total_price' => ['$sum' => '$xtotal_price'],
                            'total_book' => ['$sum' => 1],
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
                        'count' => $book['total_book'],
                        'total_circulation' => $book['total_circulation'],
                        'total_pages' => $book['total_pages'],
                        'total_price' => $book['total_price'],
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
