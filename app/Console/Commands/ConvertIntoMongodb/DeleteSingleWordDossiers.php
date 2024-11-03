<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\DeleteSingleWordDossiersJob;
use App\Models\MongoDBModels\BookTempDossier2;
use Illuminate\Console\Command;

class DeleteSingleWordDossiers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:single_word_dossier';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete all dossier where they have just one character for book_names_original';

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
        // TODO : NEW
        $start = microtime(true);
        $this->info('Start to delete book-temp_dossier where they have single character');
        $dossier = BookTempDossier2::where('book_names_original', new \MongoDB\BSON\Regex('^[^ ]+$', 'i'))->count();
        $processBar = $this->output->createProgressBar($dossier);
        BookTempDossier2::chunk(1000, function ($collection) use ($processBar) {
                DeleteSingleWordDossiersJob::dispatch($collection);
                $processBar->advance();
        });
        $this->info('update book_temp2_');
        $this->newLine();
        $end = microtime(true);
        $dff = $end - $start;
        $this->newLine();
        $this->info("process finished at $dff");
        return true;
    }
}
