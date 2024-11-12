<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\ConvertPublishersJob;
use App\Models\BookirPublisher;
use Illuminate\Console\Command;

class ConvertPublishersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:publishers';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert publishers table into mongodb';

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
            $this->info('All Mongo data Deleted');
            $this->info("Start converting bookir_publishers table");
            $startTime = microtime(true);
            $totalPublisher = BookirPublisher::count();
            $progressBar = $this->output->createProgressBar($totalPublisher);
            $progressBar->start();
            BookirPublisher::chunk(1000, function ($books) use($progressBar) {
                foreach ($books as $book) {
                    ConvertPublishersJob::dispatch($book);
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
