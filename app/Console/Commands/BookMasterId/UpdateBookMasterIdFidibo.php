<?php

namespace App\Console\Commands\BookMasterId;

use App\Models\BookFidibo;
use App\Models\BookirBook;
use Illuminate\Console\Command;

class UpdateBookMasterIdFidibo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:book_master_id_fidibo {limit}';

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
        //fidibo
        $limit = $this->argument('limit');
        $fidibo_books = BookFidibo::where('book_master_id', 0)->where('shabak', '!=', null)->skip(0)->take($limit)->get();
        if ($fidibo_books->count() != 0) {
            foreach ($fidibo_books as $fidibo_book) {
                $search_shabak = $fidibo_book->shabak;
                $main_book_info = BookirBook::where('xparent', '>=', -1)
                    ->where(function ($query) use ($search_shabak) {
                        $query->where('xisbn', $search_shabak);
                        $query->orWhere('xisbn2', $search_shabak);
                        $query->orWhere('xisbn3', $search_shabak);
                    })->first();
                if (!empty($main_book_info)) {
                    if ($main_book_info->xparent == -1) {
                        $fidibo_book->book_master_id = $main_book_info->xid;
                    } else {
                        $fidibo_book->book_master_id = $main_book_info->xparent;
                    }
                } else {
                    $fidibo_book->book_master_id = -10;
                }

                $fidibo_book->update();
            }
            $this->info("successfully update book_master_id info");
        } else {
            $this->info("nothing for update");
        }

    }
}
