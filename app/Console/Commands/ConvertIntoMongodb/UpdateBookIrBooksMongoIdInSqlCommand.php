<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\UpdateBookIrBooksMongoIdInSqlJob;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Console\Command;

class UpdateBookIrBooksMongoIdInSqlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:bookirbook_mongoid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update bookir_book mongo_id column in SQL';

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
        //DONE!!!!
        $this->info("Start update mongo_id in bookir_book SQL table");
        $startTime = microtime(true);
        $totalBooks = BookIrBook2::count();
        $progressBar = $this->output->createProgressBar($totalBooks);
        $progressBar->start();
        BookIrBook2::chunk(1000, function ($books) use($progressBar) {
            foreach ($books as $book) {
                UpdateBookIrBooksMongoIdInSqlJob::dispatch($book);
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
