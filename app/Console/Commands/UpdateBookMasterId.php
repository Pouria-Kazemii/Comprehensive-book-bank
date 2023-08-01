<?php

namespace App\Console\Commands;

use App\Models\Book30book;
use App\Models\BookDigi;
use App\Models\BookFidibo;
use App\Models\BookGisoom;
use App\Models\BookirBook;
use App\Models\BookK24;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateBookMasterId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:book_master_id {limit}';

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
        //gisoom table
            $limit = $this->argument('limit');
            $gisoom_books = BookGisoom::where('book_master_id', 0)->where('shabak10', '!=', NULL)->where('shabak13', '!=', NULL)->skip(0)->take($limit)->get();
            if ($gisoom_books->count() != 0) {
                foreach ($gisoom_books as $gisoom_book) {
                    $search_shabak = $gisoom_book->shabak10;
                    $search_shabak1 = $gisoom_book->shabak13;
                    $main_book_info = BookirBook::where('xparent', '>=', -1)
                        ->where(function ($query) use ($search_shabak, $search_shabak1) {
                            $query->where('xisbn', $search_shabak);
                            $query->orWhere('xisbn2', $search_shabak);
                            $query->orWhere('xisbn3', $search_shabak);
                            $query->orWhere('xisbn', $search_shabak1);
                            $query->orWhere('xisbn2', $search_shabak1);
                            $query->orWhere('xisbn3', $search_shabak1);
                        })->first();
                    if (!empty($main_book_info)) {
                        if ($main_book_info->xparent == -1) {
                            $gisoom_book->book_master_id = $main_book_info->xid;
                        } else {
                            $gisoom_book->book_master_id = $main_book_info->xparent;
                        }
                    } else {
                        $gisoom_book->book_master_id = -10;
                    }

                    $gisoom_book->update();
                }
                $this->info("successfully update book_master_id info");
            } else {
                $this->info("nothing for update");
            }

        //digi
            /* $limit = $this->argument('limit');
            $digi_books = BookDigi::where('book_master_id', 0)->where('shabak','!=',NULL)->skip(0)->take($limit)->get();
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
            } */
        // 30book
            /*$limit = $this->argument('limit');
            $c_books = Book30book::where('book_master_id', 0)->where('shabak','!=',NULL)->skip(0)->take($limit)->get();
            if ($c_books->count() != 0) {
                foreach ($c_books as $c_book) {
                        $search_shabak = $c_book->shabak;
                        $main_book_info = BookirBook::where('xparent', '>=', -1)
                            ->where(function ($query) use ($search_shabak) {
                                $query->where('xisbn', $search_shabak);
                                $query->orWhere('xisbn2', $search_shabak);
                            })->first();
                        if (!empty($main_book_info)) {
                            if ($main_book_info->xparent == -1) {
                                $c_book->book_master_id = $main_book_info->xid;
                            } else {
                                $c_book->book_master_id = $main_book_info->xparent;
                            }
                        } else {
                            $c_book->book_master_id = -10;
                        }
                    
                    $c_book->update();
                }
                $this->info("successfully update book_master_id info");
            } else {
                $this->info("nothing for update");
            } */

        //fidibo
        $limit = $this->argument('limit');
        $fidibo_books = BookFidibo::where('book_master_id', 0)->where('shabak', '!=', NULL)->skip(0)->take($limit)->get();
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
