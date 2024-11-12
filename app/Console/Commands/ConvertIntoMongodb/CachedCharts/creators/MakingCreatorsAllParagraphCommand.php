<?php

namespace App\Console\Commands\ConvertIntoMongodb\CachedCharts\creators;

use App\Jobs\CachedData\CreatorsAllTimesCachedParagraphDataJob;
use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Console\Command;

class MakingCreatorsAllParagraphCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:creator_alltime_paragraph {id?} {--S} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cached paragraph data for creators all times';

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
        $start = microtime(true);
        $this->info('start cache paragraph data for for creators all times');
        $option = $this->option('S');
        if ($option){
            $id  = $this->argument('id');
            CreatorsAllTimesCachedParagraphDataJob::dispatch($id);
        } else {
            $row = BookIrCreator::count();
            $progressBar = $this->output->createProgressBar($row);
            $progressBar->start();
            BookIrCreator::chunk(1000, function ($creators) use ($progressBar) {
                foreach ($creators as $creator) {
                    CreatorsAllTimesCachedParagraphDataJob::dispatch($creator->_id);
                    $progressBar->advance();
                }
            });
            $progressBar->finish();
        }
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $start;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
