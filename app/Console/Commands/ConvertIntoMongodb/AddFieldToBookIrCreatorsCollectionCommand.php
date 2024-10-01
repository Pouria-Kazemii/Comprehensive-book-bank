<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Console\Command;

class AddFieldToBookIrCreatorsCollectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:no_space_creators';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'add a field named xcreatorname2 in bookir_creators collection';

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
        $this->info("Start add new field bookir_creators collection");
        $totalBooks = BookIrCreator::count();
        $progressBar = $this->output->createProgressBar($totalBooks);
        $progressBar->start();
        $startTime = microtime(true);
        BookIrCreator::chunk(1000 , function ($creators) use ($progressBar){
            foreach ($creators as $creator){
                $noSpace = preg_replace('/\s+/', '', $creator->xcreatorname);
                $creator->update([
                    'xcreatorname2' => $noSpace
                ]);
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
