<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\UpdateBookDossierTemp1Job;
use App\Models\MongoDBModels\BookTempDossier1;
use Illuminate\Console\Command;

class UpdateBookDossierTemp1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uppdate:book_dossier_temp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this will update book dossier temp according to name and creator';

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
        $this->info('start to update book dossier temp 1');
        $processBar = $this->output->createProgressBar(BookTempDossier1::count());
        $processBar->start();
        BookTempDossier1::chunk(1000,function ($docs) use ($processBar){
            foreach ($docs as $doc){
                UpdateBookDossierTemp1Job::dispatch($doc);
                $processBar->advance();
            }
        });
        $processBar->finish();
        $this->info('process successfully ended');
        return true;
    }
}
