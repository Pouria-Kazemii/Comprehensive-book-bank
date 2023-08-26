<?php

namespace App\Console\Commands;

use App\Models\BookDigi;
use Illuminate\Console\Command;
use App\Models\BookirBook;
use App\Models\ErshadBook;

class CheckHasPermitBookDigi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkHasPermitBookDigi {limit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check book digi isbn with ershad book and ketab.ir';

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
        $limit = $this->argument('limit');
        $books = BookDigi::where('has_permit',0)->skip(0)->take($limit)->get();
        if($books->count() != 0){
            foreach($books as $book){
                if(empty($book->shabak) || is_null($book->shabak)){
                    $book->has_permit  = 3;
                }else{
                    $ershad_books = ErshadBook::where('xisbn',$book->shabak)->get();
                    $book_ir_books = BookirBook::where('xisbn',$book->shabak )->get();
                    $book_ir_books2 = BookirBook::where('xisbn2',$book->shabak )->get();
                    $book_ir_books3 = BookirBook::where('xisbn3',$book->shabak )->get();
                    
                    if( $ershad_books->count() > 0 ||  $book_ir_books->count() > 0 || $book_ir_books2->count() > 0 || $book_ir_books3->count() > 0 ){
                        $book->has_permit  = 1;
                    }else{
                        $book->has_permit  = 2;
                    }
                }
                $book->update();
            }
        $this->info("successfully update has_permit  info");
        } else {
            $this->info("nothing for update");
        }
    }
}