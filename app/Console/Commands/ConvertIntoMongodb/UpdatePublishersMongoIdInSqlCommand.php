<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\UpdatePublishersMongoIdInSqlJob;
use App\Models\MongoDBModels\BookIrPublisher;
use Illuminate\Console\Command;

class UpdatePublishersMongoIdInSqlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:bookirpublishers_mongoid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update bookir_publisher mongo_id column in SQL';

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
        $this->info("Start update mongo_id in bookir_publishers SQL table");
        $startTime = microtime(true);
        $totalBooks = BookIrPublisher::count();
        $progressBar = $this->output->createProgressBar($totalBooks);
        $progressBar->start();
        BookIrPublisher::chunk(1000, function ($books) use($progressBar) {
            foreach ($books as $book) {
                UpdatePublishersMongoIdInSqlJob::dispatch($book);
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
