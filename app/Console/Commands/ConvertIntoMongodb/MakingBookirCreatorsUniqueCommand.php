<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\MakingBookirCreatorsUniqueJob;
use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Console\Command;

class MakingBookirCreatorsUniqueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:bookir_creators:unique';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'making bookircreators collection unique and delete repeated creators';

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
        $this->info("Start find and deleting repeated creators");
        $startTime = microtime(true);
        $totalCreators = BookIrCreator::count();
        $progressBar = $this->output->createProgressBar($totalCreators);
        $progressBar->start();

        BookIrCreator::chunk(1000 , function ($creators) use($progressBar) {
            foreach ($creators as $creator){
                MakingBookirCreatorsUniqueJob::dispatch($creator);
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return  true;
    }
}
