<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\CreateFinalBookDossierCollectionFirstJob;
use App\Jobs\CreateFinalBookDossierCollectionSecondJob;
use App\Models\MongoDBModels\BookTempDossier1;
use App\Models\MongoDBModels\BookTempDossier2;
use Illuminate\Console\Command;

class ConvertBookir_bookIntoBook_dossier extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:bookdossier';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'making bookdossier collection from  book_temp_dossier_ 1 and 2 collection';

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
        //TODO : NEW
        $this::info("Start converting book_dossier table");
        $startTime = microtime(true);
        $processBar = $this->output->createProgressBar(BookTempDossier1::count());
        BookTempDossier2::chunk(1000 ,function ($dossiers) use($processBar) {
            foreach ($dossiers as $dossier) {
                CreateFinalBookDossierCollectionFirstJob::dispatch($dossier);
                $processBar->advance();
            }
        });
        BookTempDossier1::where('is_checked' , '!=' , true)->chunk(1000,function ($collection) use($processBar){
            foreach ($collection as $item){
                CreateFinalBookDossierCollectionSecondJob::dispatch($item);
                $processBar->advance();
            }
        } );
        $processBar->finish();
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
