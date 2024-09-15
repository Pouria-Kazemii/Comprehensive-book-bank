<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\InsertDioCodeSubjectsForChildBooksJob;
use App\Jobs\InsertDioCodeSubjectsJob;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Console\Command;
use MongoDB\BSON\ObjectId;

class InsertBooksDioCodeSubjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:dio_subject {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'feel the diocode subject column in bookir_book collection';

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
        // TODO : NEW
        $this->info("Start feel diocode subject column");
        $startTime = microtime('true');
        $id = $this->argument('id');
        if ($id == null) {
            $progressBar = $this->output->createProgressBar(BookIrBook2::count());
            $progressBar->start();
            BookIrBook2::chunk(1000, function ($books) use ($progressBar) {
                foreach ($books as $book) {
                    if ($book->age_group == null){
                        InsertDioCodeSubjectsJob::dispatch($book);
                        $progressBar->advance();
                    } else {
                        InsertDioCodeSubjectsForChildBooksJob::dispatch($book);
                        $progressBar->advance();
                    }
                }
            });
            $progressBar->finish();
        } else {
            $book = BookIrBook2::where('_id', new ObjectId($id))->first();
            if ($book->age_group == null) {
                InsertDioCodeSubjectsJob::dispatch($book);
            } else {
                InsertDioCodeSubjectsForChildBooksJob::dispatch($book);
            }
        }
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
