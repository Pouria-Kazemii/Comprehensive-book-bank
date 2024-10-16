<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\MakeBookDossierTemp1Job;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Console\Command;

class MakeBookDossierTemp1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:book_dossier_temp_1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $this->info('making book dossier temp 1 collection');
        $progressBar = $this->output->createProgressBar(BookIrBook2::count());
        $progressBar->start();
        BookIrBook2::chunk(2000,function ($books) use($progressBar){
            MakeBookDossierTemp1Job::dispatch($books);
            $progressBar->advance();
        });
        $progressBar->finish();
        return true;
    }
}
