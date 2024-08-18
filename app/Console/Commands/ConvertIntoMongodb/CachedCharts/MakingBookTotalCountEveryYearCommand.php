<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BTC_Yearly;
use Illuminate\Console\Command;

class MakingBookTotalCountEveryYearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:book_total_count_yearly {year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached data for take book total count for every year';

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
        $this->info("Start cache book total count yearly");
        $totalRows = getYearNow() - $date + 1 ;
        $progressBar = $this->output->createProgressBar($totalRows);
        $progressBar->start();
        $startTime = microtime(true);
        $data = BookIrBook2::raw(function ($collection) use($date) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'xpublishdate_shamsi' => [
                            '$gte' => $date,
                        ]

                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$xpublishdate_shamsi',
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['_id' => 1] // Sort by year
                ]
            ]);
        });

        foreach ($data as $value) {
            BTC_Yearly::where('year', $value['_id'])->updateOrCreate([
                'year' => $value['_id'],
                'count' =>$value['count']
            ]);
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
