<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\ConvertIranketabPartnerIntoBookIrCreatorCollectionJob;
use App\Models\BookIranKetabPartner;
use Illuminate\Console\Command;

class ConvertIranketabPartnerIntoBookIrCreatorCollectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:iranketabpartner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'added some data from bookir_partnert table in sql into bookir_creators collection in mongodb';

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
        $this->info("Start converting bookir_creators table");
        $totalBooks = BookIranKetabPartner::count();
        $progressBar = $this->output->createProgressBar($totalBooks);
        $progressBar->start();
        $startTime = microtime(true);
        BookIranKetabPartner::where('partner_master_id' , '!=' , -10)->chunk(1000, function ($books) use($progressBar){
            foreach ($books as $book) {
                ConvertIranketabPartnerIntoBookIrCreatorCollectionJob::dispatch($book);
                $progressBar->advance();
            }
        });
        $progressBar->finish();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
