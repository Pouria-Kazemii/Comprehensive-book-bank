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
    protected $signature = 'update:book_dossier_temp';

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
        BookTempDossier1::where('is_delete','exists', false)->chunk(1, function ($docs) use ($processBar) {
            foreach ($docs as $doc) {
                if ($doc->creator !== null and $doc->creator !== 'multi' and  $doc->creator !== '') {
                    $xbooks = $doc->book_names ?? [];
                    if (count(array_unique($xbooks)) === 1) {
                        UpdateBookDossierTemp1Job::dispatch($doc);
                        $processBar->advance();
                    } else {
                        $doc->update([
                            'is_delete' => true
                        ]);
                        $processBar->advance();
                    }
                } else {
                    $doc->update([
                        'is_delete' => true
                    ]);
                }

            }
        });
        $processBar->finish();
        $this->info('process successfully ended');
        return true;
    }
}
