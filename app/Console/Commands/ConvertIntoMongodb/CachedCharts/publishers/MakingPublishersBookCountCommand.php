<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\publishers;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrPublisher;
use App\Models\MongoDBModels\PublisherTotalCount;
use Illuminate\Console\Command;

class MakingPublishersBookCountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:publisher:book_total_count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached data for take total_book_count for every publisher';

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
        $progressBar = $this->output->createProgressBar(BookIrPublisher::count());
        $books = BookIrBook2::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'publisher' => [
                            '$ne' => [],
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$publisher.xpublisher_id',
//                    'total_circulation' => ['$sum' => '$xcirculation'],
//                    'total_pages' => ['$sum' => '$total_page'],
//                    'total_price' => ['$sum' => '$total_price'],
                        'total_book' => ['$sum' => 1]
                    ]
                ]
            ]);
        });

        $progressBar->start();
        $startTime = microtime('true');
        foreach ($books as $book){
            PublisherTotalCount::updateOrCreate(
                ['publisher_id' => $book['_id'][0]]
                ,
                [
                    'count' => $book['total_book']
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
