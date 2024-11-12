<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\ConvertCreatorsJob;
use App\Jobs\UpdateCreatorsMongoIdInSqlJob;
use App\Models\BookirPartner;
use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Console\Command;

class MatchingMongoCreatorsWithSQLCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:mongodb_creators';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create new sql creators data in mongodb collections';

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
        $this->info('adding creators and update mongo_id');
        $creators = BookirPartner::where('mongo_id' , '0')->where('xcreatorname' , '!=' , null);
        $totalCreators = $creators->count();
        $progressBar2 = $this->output->createProgressBar($totalCreators);
        $progressBar2->start();
        $creators->chunk(1000 , function ($creators) use ($progressBar2 , &$lastProcessedId){
            foreach ($creators as $creator){
                ConvertCreatorsJob::dispatch($creator);
                $mongoCreator = BookIrCreator::where('xsqlid' ,$creator->xid)->first() ;
                UpdateCreatorsMongoIdInSqlJob::dispatch($mongoCreator);
                $progressBar2->advance();
                $lastProcessedId = $creator->xid;
            }
        });
        $progressBar2->finish();
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Last processed ID: ' . $lastProcessedId);
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
