<?php

namespace App\Console\Commands\BookMasterId;

use App\Models\BookDigi;
use App\Models\BookirBook;
use Illuminate\Console\Command;

class UpdateBookMasterIdDigi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:book_master_id_digi {limit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update book_master_id filed data in tables';

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

        //digi
        $limit = $this->argument('limit');
        $digi_books = BookDigi::where('book_master_id', 0)->where('shabak', '!=', null)->skip(0)->take($limit)->get();
        if ($digi_books->count() != 0) {
            foreach ($digi_books as $digi_book) {
                $search_shabak = $digi_book->shabak;
                $main_book_info = BookirBook::where('xparent', '>=', -1)
                    ->where(function ($query) use ($search_shabak) {
                        $query->where('xisbn', $search_shabak);
                        $query->orWhere('xisbn2', $search_shabak);
                    })->first();
                if (!empty($main_book_info)) {
                    if ($main_book_info->xparent == -1) {
                        $digi_book->book_master_id = $main_book_info->xid;
                    } else {
                        $digi_book->book_master_id = $main_book_info->xparent;
                    }
                } else {
                    $digi_book->book_master_id = -10;
                }

                $digi_book->update();
            }
            $this->info("successfully update book_master_id info");
        } else {
            $this->info("nothing for update");
        }
    }
}
