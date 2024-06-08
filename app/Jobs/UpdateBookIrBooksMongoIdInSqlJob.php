<?php

namespace App\Jobs;

use App\Models\BookirBook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateBookIrBooksMongoIdInSqlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $book;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($books)
    {
    $this->book = $books;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
            BookirBook::find($this->book->xsqlid)
                ->update([
                    'mongo_id' => $this->book->_id
                ]);
    }
}
