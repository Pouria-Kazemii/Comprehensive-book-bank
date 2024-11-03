<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\ConvertNonTranslatedBookWhitPoetIntoDossierJob;
use App\Jobs\ConvertNonTranslatedBookWithWriterIntoDossierJob;
use App\Jobs\ConvertTranslatedBookWhitPoetIntoDossierJob;
use App\Jobs\ConvertTranslatedBookWhitWriterIntoDossierJob;
use App\Jobs\CreateFinalBookDossierCollectionFirstJob;
use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\BookTempDossier1;
use App\Models\MongoDBModels\BookTempDossier2;
use Illuminate\Console\Command;
use MongoDB\Client;

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
    protected $description = 'making bookdossier table from  bookirbook table';

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
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
