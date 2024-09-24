<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\AddNewSubjectsJob;
use App\Jobs\AddNewXruleJob;
use App\Jobs\ConvertBookirBookJob;
use App\Jobs\ConvertCreatorsJob;
use App\Jobs\ConvertPublishersJob;
use App\Jobs\UpdateBookIrBooksMongoIdInSqlJob;
use App\Jobs\UpdateCreatorsMongoIdInSqlJob;
use App\Jobs\UpdatePublishersMongoIdInSqlJob;
use App\Models\BookirBook;
use App\Models\BookirPartner;
use App\Models\BookirPublisher;
use App\Models\BookirSubject;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Console\Command;
use function Symfony\Component\String\u;

class MatchingMongoDBDataWhitMySQLData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:mongodb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create new sql data in mongodb collections';

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
        $startTime = microtime(true);
        //------------------------------------------ Subjects ------------------------------------------//
        $this->info('start create new sql data');
        $this->info('adding subjects');
        $totalSubjects = BookirBook::count()-BookIrBook2::count();
        $progressBar4 = $this->output->createProgressBar($totalSubjects);
        $lastSubjectId = \App\Models\MongoDBModels\BookIrSubject::latest('_id')->first()->_id;
        BookirSubject::where('xid' , '>' , $lastSubjectId)
            ->chunk(1000 , function ($subjects) use ($progressBar4){
                foreach ($subjects as $subject){
                    AddNewSubjectsJob::dispatch($subject);
                    $progressBar4->advance();
                }
            });
        $progressBar4->finish();
        $this->line('');

        //------------------------------------------ Creators ------------------------------------------//
        $this->info('adding creators and update mongo_id');
        $creators = BookirPartner::where('mongo_id' , '0')->where('xcreatorname' , '!=' , null);
        $totalCreators = $creators->count();
        $progressBar2 = $this->output->createProgressBar($totalCreators);
        $progressBar2->start();
        $creators->chunk(1000 , function ($creators) use ($progressBar2){
            foreach ($creators as $creator){
                ConvertCreatorsJob::dispatch($creator);
                $mongoCreator = BookIrCreator::where('xsqlid' ,$creator->xid)->first() ;
                UpdateCreatorsMongoIdInSqlJob::dispatch($mongoCreator);
                $progressBar2->advance();
            }
        });

        $progressBar2->finish();
        $this->line('');
        //------------------------------------------ Publishers ------------------------------------------//
        $this->info('adding publishers and update mongo_id');
        $publishers = BookirPublisher::where('mongo_id' , '0')->where('xpageurl2', '!=' , null);
        $totalPublishers = $publishers->count();
        $progressBar3 = $this->output->createProgressBar($totalPublishers);
        $progressBar3->start();
        $publishers->chunk(1000 , function ($publishers) use ($progressBar3){
            foreach ($publishers as $publisher){
                ConvertPublishersJob::dispatch($publisher);
                $mongoPublisher = \App\Models\MongoDBModels\BookIrPublisher::where('xsqlid' , $publisher->xid)->first();
                UpdatePublishersMongoIdInSqlJob::dispatch($mongoPublisher);
                $progressBar3->advance();
            }
        });

        $progressBar3->finish();
        $this->line('');
        //------------------------------------------ books ------------------------------------------//

        $this->info('adding books and update mongo_id');
        $books = BookirBook::where('mongo_id', null)->where('xpageurl2', '!=' , null);
        $totalBooks = $books->count();
        $progressBar1 = $this->output->createProgressBar($totalBooks);
        $progressBar1->start();
        $books->chunk(1000,function ($books) use ($progressBar1){
            foreach ($books as $book){
                ConvertBookirBookJob::dispatch($book);
                $mongoBook = BookIrBook2::where('xsqlid' , $book->xid)->first();
                AddNewXruleJob::dispatch($mongoBook);
                UpdateBookIrBooksMongoIdInSqlJob::dispatch($mongoBook);
                $progressBar1->advance();
            }
        });

        $progressBar1->finish();
        $this->line('');
        //------------------------------------------ End Process ------------------------------------------//

        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
