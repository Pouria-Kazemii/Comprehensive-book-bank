<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\ConvertPublishersJob;
use App\Jobs\UpdatePublishersMongoIdInSqlJob;
use App\Models\BookirPublisher;
use Illuminate\Console\Command;

class MatchingMongoPublisherWithSQLCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:mongodb_publishers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create new sql publishers data in mongodb collections';

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
        $this->info('adding publishers and update mongo_id');
        $publishers = BookirPublisher::where('mongo_id' , '0')->where('xpageurl2', '!=' , null);
        $totalPublishers = $publishers->count();
        $progressBar3 = $this->output->createProgressBar($totalPublishers);
        $progressBar3->start();
        $publishers->chunk(1000 , function ($publishers) use ($progressBar3 , &$lastProcessedId){
            foreach ($publishers as $publisher){
                ConvertPublishersJob::dispatch($publisher);
                $mongoPublisher = \App\Models\MongoDBModels\BookIrPublisher::where('xsqlid' , $publisher->xid)->first();
                UpdatePublishersMongoIdInSqlJob::dispatch($mongoPublisher);
                $progressBar3->advance();
                $lastProcessedId = $publisher->xid;
            }
        });
        $progressBar3->finish();
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Last processed ID: ' . $lastProcessedId);
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
