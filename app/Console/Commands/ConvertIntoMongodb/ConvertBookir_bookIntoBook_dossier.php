<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\ConvertNonTranslatedBookWhitPoetIntoDossier;
use App\Jobs\ConvertNonTranslatedBookWithWriterIntoDossierJob;
use App\Jobs\ConvertTranslatedBookWhitPoetIntoDossier;
use App\Jobs\ConvertTranslatedBookWhitWriterIntoDossier;
use App\Models\MongoDBModels\BookIrBook2;
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
        $client = new Client();
        $collection = $client->datacollector->bookir_books;
        $collection->createIndex(['xname' => 'text']);
        $this::info("Start converting book_dossier table");

        $startTime = microtime(true);
//        ConvertTranslatedBookWhitPoetIntoDossier::dispatch();
        $this->info('translated books with poet done');
        ConvertTranslatedBookWhitWriterIntoDossier::dispatch();
        $this->info('translated books with writer done');
        //TODO : Non translate books have very complicate rules.
        //ConvertNonTranslatedBookWhitPoetIntoDossier::dispatch();
        $this->info('non translated books with poet done');
        //ConvertNonTranslatedBookWithWriterIntoDossierJob::dispatch();
        $this->info('non translated books with writer done');

        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
