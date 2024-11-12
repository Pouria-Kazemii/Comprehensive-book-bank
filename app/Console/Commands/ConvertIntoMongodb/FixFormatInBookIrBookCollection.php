<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\FixFormatPartInBookIrBookCoolectionJob;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Console\Command;

class FixFormatInBookIrBookCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:format';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'trim format part in bookirbook collection';

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
        $start = microtime(true);
        $this->info('start fixing format part in bookirbook');
        $progressBar = $this->output->createProgressBar(BookIrBook2::count());
        $progressBar->start();
        BookIrBook2::chunk(1000,function ($books) use($progressBar){
            foreach ($books as $book){
                FixFormatPartInBookIrBookCoolectionJob::dispatch($book);
                $progressBar->advance();
            }
        });
        $progressBar->finish();
        $end = microtime(true);
        $processTime = $end-$start;
        $this->info("process finish at $processTime");
        return true;
    }
}
