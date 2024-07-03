<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\MakeCreatorsNameCleanInBookIrBooksCollectionJob;
use App\Jobs\MakeCreatorsNameCleanInBookIrCreatorsCollectionJob;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Console\Command;

class MakeCreatorsNameCleanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:creators:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'making name of creators clean in bookir_books and bookir_creators';

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
        //first part
        $this->info("Start cleaning creators name in bookir_books collection");
        $totalBooks = BookIrBook2::count();
        $progressBar = $this->output->createProgressBar($totalBooks);
        $progressBar->start();
        $startTime = microtime(true);
        BookIrBook2::chunk(500 , function ($books) use($progressBar) {
            foreach ($books as $book) {
                MakeCreatorsNameCleanInBookIrBooksCollectionJob::dispatch($book);
                $progressBar->advance();
            }
        });
        $progressBar->finish();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        $this->line('');
        //second part
        $this->info("Start cleaning creators name in bookir_creators collection");
        $totalBooks = BookIrCreator::count();
        $progressBar = $this->output->createProgressBar($totalBooks);
        $progressBar->start();
        $startTime = microtime(true);
        BookIrCreator::chunk(500 , function ($creators) use($progressBar) {
            foreach ($creators as $creator) {
                MakeCreatorsNameCleanInBookIrCreatorsCollectionJob::dispatch($creator);
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
