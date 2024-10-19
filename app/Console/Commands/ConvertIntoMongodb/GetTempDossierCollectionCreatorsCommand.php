<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\TakeCreatorOfTempDossierCollectionJob;
use App\Models\MongoDBModels\BookTempDossier1;
use Illuminate\Console\Command;

class GetTempDossierCollectionCreatorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:temp_dossier_creators';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this will take creator for each document in book_dossier_temp_1 collection';

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
         $this->info('start to take creator of this book temp dossier 1');
         $processBar = $this->output->createProgressBar(BookTempDossier1::count());
         $processBar->start();
         BookTempDossier1::chunk(1000,function ($docs) use($processBar){
             foreach ($docs as $doc){
                 TakeCreatorOfTempDossierCollectionJob::dispatch($doc);
                 $processBar->advance();
             }
         });
         $processBar->finish();
         $this->info('process successfully ended');
         return true;
    }
}
