<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\AddXroleArrayToBookIrCreatorsJob;
use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Console\Command;

class AddXroleArrayToBookIrCreatorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:bookir_creators:role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'add a array of roles for every creator';

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
        //DONE!?!?
        $this->info("Start adding roles array to creators");
        $totalBooks = BookIrCreator::count();
        $progressBar = $this->output->createProgressBar($totalBooks);
        $progressBar->start();
        $startTime = microtime(true);
       BookIrCreator::chunk(500 , function ($creators) use($progressBar) {
           foreach ($creators as $creator) {
                AddXroleArrayToBookIrCreatorsJob::dispatch($creator);
               $progressBar->advance();
           }
       });
        $progressBar->finish();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
