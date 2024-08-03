<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\AddNewSubjectsJob;
use App\Jobs\AddNewXruleJob;
use App\Jobs\ConvertBookirBookJob;
use App\Jobs\UpdateBookIrBooksMongoIdInSqlJob;
use App\Models\BookirBook;
use App\Models\BookirSubject;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Console\Command;

class MatchingMongoBookWithSQLCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:mongodb_books';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create new sql books data in mongodb collections';

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
        $lastProcessedId = null;
        $startTime = microtime(true);
        $this->info('adding books and update mongo_id');
        $books = BookirBook::where('mongo_id', null)->where('xpageurl2', '!=', null);
        $totalBooks = $books->count();
        $progressBar1 = $this->output->createProgressBar($totalBooks);
        $progressBar1->start();
        $books->chunk(1000, function ($books) use ($progressBar1 , &$lastProcessedId) {
            foreach ($books as $book) {
                ConvertBookirBookJob::dispatch($book);
                $mongoBook = BookIrBook2::where('xsqlid', $book->xid)->first();
                AddNewXruleJob::dispatch($mongoBook);
                UpdateBookIrBooksMongoIdInSqlJob::dispatch($mongoBook);
                $progressBar1->advance();
                $lastProcessedId = $book->xid;
            }
        });

        $progressBar1->finish();
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Last processed ID: ' . $lastProcessedId);
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
