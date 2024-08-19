<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BTCi_Yearly;
use App\Models\MongoDBModels\BTPa_Yearly;
use Illuminate\Console\Command;

class MakingBookTotalPagesEveryYearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:book_total_pages_yearly {year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached data for take total pages for every year';

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
        $this->info("Start cache book total pages yearly");
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
                        , 'xtotal_page' => [
                            '$ne' => 0
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$xpublishdate_shamsi',
                        'total_page' => ['$sum' => '$xtotal_page'],
                    ]
                ],
                [
                    '$sort' => ['_id' => 1] // Sort by year
                ]
            ]);
        });

        foreach ($data as $value) {
            BTPa_Yearly::updateOrCreate(
                ['year' => $value['_id']],
                [
                    'total_pages' =>$value['total_page']
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
