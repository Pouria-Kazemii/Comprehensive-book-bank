<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\ConvertBookDossierJob;
use App\Jobs\ConvertTranslatedBookDossierJob;
use App\Models\MongoDBModels\BookIrBook2;
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
        $this::info("Start converting book_dossier table");

        $startTime = microtime(true);
        ConvertTranslatedBookDossierJob::dispatch();
        $this->info('translated books done');
        //TODO : Non translate books have very complicate rules.
        //ConvertBookDossierJob::dispatch();


        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
