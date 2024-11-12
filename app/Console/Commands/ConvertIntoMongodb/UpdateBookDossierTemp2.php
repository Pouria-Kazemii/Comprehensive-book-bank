<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\UpdateSecondBookTempDossierJob;
use App\Models\MongoDBModels\BookTempDossier2;
use Illuminate\Console\Command;

class UpdateBookDossierTemp2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:book_dossier_temp_second';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this will update the book dossier temp 2 and fix bug of create this collection';

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
     *\
     * @return Bool
     */
    public function handle()
    {
        // TODO : NEW
        $start = microtime(true);
        $this->info('Start to update book-temp_dossier');
        $dossier =BookTempDossier2::count() ;
        $processBar = $this->output->createProgressBar($dossier);
        BookTempDossier2::chunk(1,function ($collection) use ($processBar){
            foreach ($collection as $item){
                UpdateSecondBookTempDossierJob::dispatch($item);
                $processBar->advance();
            }
        });
        $this->info('update book_temp2_');
        $this->newLine();
        $end = microtime(true);
        $dff = $end-$start;
        $this->newLine();
        $this->info("process finished at $dff");
        return true;
    }
}
