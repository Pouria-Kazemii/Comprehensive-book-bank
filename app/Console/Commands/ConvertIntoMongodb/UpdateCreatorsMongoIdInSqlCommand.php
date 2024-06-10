<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\UpdateCreatorsMongoIdInSqlJob;
use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Console\Command;

class UpdateCreatorsMongoIdInSqlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:bookircreators_mongoid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update bookir_partner mongo_id column in SQL';

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
        $this->info("Start update mongo_id in bookir_partners SQL table");
        $startTime = microtime(true);
        $totalBooks = BookIrCreator::count();
        $progressBar = $this->output->createProgressBar($totalBooks);
        $progressBar->start();
        BookIrCreator::chunk(1000, function ($books) use($progressBar) {
            foreach ($books as $book) {
                UpdateCreatorsMongoIdInSqlJob::dispatch($book);
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
