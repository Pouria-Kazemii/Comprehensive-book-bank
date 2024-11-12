<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\AddNewSubjectsJob;
use App\Models\BookirBook;
use App\Models\BookirSubject;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Console\Command;

class MatchingMongoSubjectsWithSQLCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:mongodb_subjects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create new sql subjects data in mongodb collections';

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
        $this->info('adding subjects');
        $totalSubjects = BookirBook::count()-BookIrBook2::count();
        $progressBar4 = $this->output->createProgressBar($totalSubjects);
        $lastSubjectId = \App\Models\MongoDBModels\BookIrSubject::latest('_id')->first()->_id;
        BookirSubject::where('xid' , '>' , $lastSubjectId)
            ->chunk(1000 , function ($subjects) use ($progressBar4 , &$lastProcessedId){
                foreach ($subjects as $subject){
                    AddNewSubjectsJob::dispatch($subject);
                    $progressBar4->advance();
                    $lastProcessedId = $subject->xid;
                }
            });
        $progressBar4->finish();
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Last processed ID: ' . $lastProcessedId);
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
