<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\ConvertBookirBookJob;
use App\Models\BookirBook;
use Illuminate\Console\Command;

class ConvertBookIr_bookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:bookirbook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert bookirbook table into mongodb';

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
        $this::info("Start converting bookir_books table");

        $startTime = microtime(true);
        BookirBook::chunk(1000, function ($books) {
            foreach ($books as $book) {
                ConvertBookirBookJob::dispatch($book);
            }
        });
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
    }
}
