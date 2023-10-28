<?php

namespace App\Console\Commands;

use App\Models\BookFidibo;
use App\Models\BookIranketab;
use App\Models\BookirBook;
use App\Models\BookTaaghche;
use Illuminate\Console\Command;

class UpdateBookMasterIdIranketab extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:book_master_id_iranketab {limit}';

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
        //iranketab
        $limit = $this->argument('limit');
        $books = BookIranketab::where('book_master_id', 0)->where('shabak', '!=', null)->skip(0)->take($limit)->get();
        if ($books->count() != 0) {
            foreach ($books as $book) {
                $search_shabak = $book->shabak;
                $main_book_info = BookirBook::where('xparent', '>=', -1)
                    ->where(function ($query) use ($search_shabak) {
                        $query->where('xisbn', $search_shabak);
                        $query->orWhere('xisbn2', $search_shabak);
                        $query->orWhere('xisbn3', $search_shabak);
                    })->first();
                if (!empty($main_book_info)) {
                    if ($main_book_info->xparent == -1) {
                        $book->book_master_id = $main_book_info->xid;
                    } else {
                        $book->book_master_id = $main_book_info->xparent;
                    }
                } else {
                    $book->book_master_id = -10;
                }

                $book->update();
            }
            $this->info("successfully update book_master_id info");
        } else {
            $this->info("nothing for update");
        }

    }
}