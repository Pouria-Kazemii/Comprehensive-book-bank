<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Models\BookirBook;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Console\Command;

class DeleteRepetedBooksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:repeated_books {skip}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete the books that are repeated in mongo';

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
        $start = microtime('true');
        $this->info('start to delete repeated books');
        $skip = $this->argument('skip');
        $books = BookIrBook2::raw(function ($collection) use ($skip){
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => '$xsqlid',
                        'count' => ['$sum' => 1],
                        'ids' => [
                            '$push' => '$_id'
                        ]
                    ]
                ],
                [
                    '$match' => [
                        'count' => ['$gt' => 1]
                    ]
                ],
                [
                    '$skip' => $skip
                ],
                [
                    '$limit' => 1000
                ]
            ]);
        });
        $totalRows = count($books);
        $progressBar = $this->output->createProgressBar($totalRows);
        $progressBar->start();
        foreach ($books as $book){
            foreach ($book->ids as $id){
                if(!BookirBook::where('xid' , $book->_id)->where('mongo_id',$id)->exists()) {
                    BookIrBook2::where('_id', $id)->delete();
                    $progressBar->advance();
                }
            }
        }
        $progressBar->finish();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $start;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
