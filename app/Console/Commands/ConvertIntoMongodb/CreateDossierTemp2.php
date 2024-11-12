<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\CreateDossierTemp2Job;
use App\Models\MongoDBModels\BookTempDossier1;
use Illuminate\Console\Command;

class CreateDossierTemp2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:temp_dossier_2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create second temp dossier collection';

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
        // TODO : NEW
        $start = microtime(true);
        $this->info('start to create second dossier temp dossier');
        $dossier = BookTempDossier1::where('is_delete',false);
        $progressBar = $this->output->createProgressBar($dossier->count());
        $dossier->chunk(1000,function ($dossiers) use($progressBar){
            foreach ($dossiers as $dossier){
                CreateDossierTemp2Job::dispatch($dossier);
                $progressBar->advance();
            }
        });
        $progressBar->finish();
        $end = microtime(true);
        $processTime = $end - $start;
        $this->newLine();
        $this->info("process finished in : $processTime");
        return true;
    }
}
