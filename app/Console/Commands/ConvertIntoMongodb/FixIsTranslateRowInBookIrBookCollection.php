<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Console\Command;

class FixIsTranslateRowInBookIrBookCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:is_translate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check and fix the book is translate or not';

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
        $start = microtime('true');
        $this->info('start fix is translate row in bookir_book collection');
        $total = BookIrBook2::count();
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();
        BookIrBook2::chunk(1000 , function ($books) use($progressBar){
           foreach ($books as $book){
               $is_translate = 1;

               if ($book->partners == []){
                   $is_translate = 3;
               }

               foreach ($book->partners as $partner){
                   if ($partner['xrule'] == 'مترجم'
                       or $partner['xrule'] == 'ترجمه مقدمه'
                       or $partner['xrule'] == 'ترجمه به شعر'
                       or $partner['xrule'] == 'ترجمه انگليسي'
                       or $partner['xrule'] == 'ترجمه انگلیسی')
                   {
                       $is_translate = 2;
                   }

                   $book->update([
                       'is_translate' => $is_translate
                   ]);

               }
               $progressBar->advance();
           }
        });
        $progressBar->finish();
        $this->line('');
        $endTime = microtime(true);
        $duration = $endTime - $start;
        $this->info('Process completed in ' . number_format($duration, 2) . ' seconds.');
        return true;
    }
}
