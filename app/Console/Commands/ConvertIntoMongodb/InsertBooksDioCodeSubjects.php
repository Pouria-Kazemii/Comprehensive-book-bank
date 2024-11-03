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
    protected $signature = 'add:dio_subject {id?} {--F} ';

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
        $this->info("Start feel diocode subject column");
        $startTime = microtime('true');
        $id = $this->argument('id');
        $option = $this->option('F');
        if ($id == null and !$option) {
            $progressBar = $this->output->createProgressBar(BookIrBook2::count());
            $progressBar->start();
            BookIrBook2::chunk(1000, function ($books) use ($progressBar) {
                foreach ($books as $book) {
                    $condition = $this->getBookCategory($book);
                    if (!$condition) {
                        InsertDioCodeSubjectsJob::dispatch($book);
                        $progressBar->advance();

                    } else {
                        InsertDioCodeSubjectsForChildBooksJob::dispatch($book);
                        $progressBar->advance();
                    }
                }
            });
            $progressBar->finish();
        } elseif ($option) {
            $progressBar = $this->output->createProgressBar(BookIrBook2::where('diocode_subject', '=', [])->count());
            $progressBar->start();
            $books = BookIrBook2::where('diocode_subject', [])->get();
            foreach ($books as $book) {
                $condition = $this->getBookCategory($book);
                if (!$condition) {
                    InsertDioCodeSubjectsJob::dispatch($book);
                    $progressBar->advance();

                } else {
                    InsertDioCodeSubjectsForChildBooksJob::dispatch($book);
                    $progressBar->advance();
                }
            }
        } else {
            $book = BookIrBook2::where('_id', new ObjectId($id))->first();
            $condition = $this->getBookCategory($book);
            if (!$condition) {
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

    private function getBookCategory($book)
    {
        $result = false;
        if ($book->age_group != null) {
            foreach ($book->age_group as $value) {
                if ($value['xa'] == 1 or $value['xb'] == 1 or $value['xg'] == 1 or $value['xd'] == 1 or $value['xh'] == 1) {
                    $result = true;
                }
            }
        }
        if (!$result) {
            if ($book->subjects != null) {
                foreach ($book->subjects as $subject) {
                    if ($subject['xsubject_id'] == 187748) {
                        $result = true;
                    }
                }
            }
        }
        return $result;
    }
}
