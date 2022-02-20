<?php

namespace App\Console\Commands;

use App\Models\BookirBook;
use App\Models\BookirPartnerrule;
use App\Models\BookirRules;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateIsTranslateDataInBookirBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'is_translate:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update is_translate filed data in bookir_book table';

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
        $books = BookirBook::where('is_translate',0)->get();
        if($books->count() != 0){
            foreach($books as $book){
                $result = BookirPartnerrule::where('xbookid',$book->xid)->where('xroleid',BookirRules::where('xrole','مترجم')->first()->xid)->get();
                if( $result->count() > 0){
                    $book->is_translate = 2;
                }else{
                    $book->is_translate = 1;
                }
                $book->update();
            }
        $this->info("successfully update is_translate info");
        } else {
            $this->info("nothing for update");
        }

    }
}
