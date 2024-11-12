<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Jobs\FixBookPartnersJob;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Console\Command;

class FixBooksWithNoPartner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:book_partners';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix books that haven\'t partner';

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
        $this->info('start to fix bookir_book partners');
        $totalBooks = BookIrBook2::where('partners' , [])->count();
        $progressBar = $this->output->createProgressBar($totalBooks);
        $progressBar->start();
        $startTime = microtime(true);
        BookIrBook2::where('partners' , [])->chunk(1000,function ($books) use($progressBar){
            foreach ($books as $book){
                FixBookPartnersJob::dispatch($book);
                $this->line('');
                $progressBar->advance();
                $this->line('');
            }
        });
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
