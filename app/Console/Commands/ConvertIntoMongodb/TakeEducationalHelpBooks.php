<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\UpdateEducationalHelpBooksJob;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Console\Command;

class TakeEducationalHelpBooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'take:educational_help_books {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'take and slice books with subject of educational help ';

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
        $this->info('start to find educational books and update them inside bookir_book collection');
        $startTime = microtime('true');
        $id = $this->argument('id');
        if ($id == null) {
            $books = BookIrBook2::raw(function ($collection) {
                return $collection->aggregate([
                    [
                        '$unwind' => '$subjects'
                    ]
                    ,
                    [
                        '$match' => [
                            '$or' => [
                                ['subjects.xsubject_id' => 260442],
                                ['subjects.xsubject_id' => 260471]
                            ]
                        ]
                    ]
                ]);
            });
            $progressBar = $this->output->createProgressBar(count($books));
            $progressBar->start();
            foreach ($books as $book) {
                UpdateEducationalHelpBooksJob::dispatch($book);
                $progressBar->advance();
            }
        } else {
            $book = BookIrBook2::where('_id', $id)->first();
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->start();
            UpdateEducationalHelpBooksJob::dispatch($book);
        }
        $progressBar->finish();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');

        return true;
    }
}
