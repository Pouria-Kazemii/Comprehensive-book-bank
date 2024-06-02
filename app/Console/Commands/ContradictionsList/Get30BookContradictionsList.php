<?php

namespace App\Console\Commands\ContradictionsList;

use App\Models\Book30book;
use App\Models\BookirBook;
use App\Models\Crawler as CrawlerM;
use App\Models\ErshadBook;
use App\Models\UnallowableBook;
use Illuminate\Console\Command;

class Get30BookContradictionsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:30bookContradictionsList {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Contradictions List Command';

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
        $check_count = Book30book::where('check_status', 0)->where('has_permit', 0)->count();

        try {
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Contradictions-30book-' . $this->argument('crawlerId'), 'start' => '1', 'end' => $check_count, 'status' => 1));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Check  " . $this->argument('crawlerId') . "              ------------ ");
        }

        if (isset($newCrawler)) {
            $items = Book30book::where('check_status', 0)->where('has_permit', 0)->get();
            foreach ($items as $item) {
                // bookirbook with ershad book
                if (isset($item) and !empty($item)) {
                    if ((isset($item->shabak) and $item->shabak != null and !empty($item->shabak))) {
                        $this->info($item->shabak);

                        $bookiritem = BookirBook::where('xisbn', $item->shabak)->orwhere('xisbn2', $item->shabak)->orWhere('xisbn3', $item->shabak)->first();
                        if (!empty($bookiritem)) {
                            $update_data['check_status'] = 1;
                        } else {
                            $update_data['check_status'] = 2;
                        }

                        $ershad_book = ErshadBook::where('xisbn', $item->shabak)->first();
                        if (!empty($ershad_book)) {
                            $update_data['has_permit'] = 1;
                        } else {
                            $update_data['has_permit'] = 2;
                        }
                    } else {
                        $this->info('row no isbn');
                        $update_data['check_status'] = 4;
                        $update_data['has_permit'] = 4;
                    }
                    Book30book::where('id', $item->id)->update($update_data);
                }
            }

            $newCrawler->status = 2;
            $newCrawler->save();
        }

        try {
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Contradictions-UnallowableBook-30book-' . $this->argument('crawlerId'), 'status' => 1));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Check  " . $this->argument('crawlerId') . "              ------------ ");
        }

        if (isset($newCrawler)) {

            //  unallowable_book
            UnallowableBook::chunk(1, function ($items) {
                foreach ($items as $item) {
                    $this->info($item->xtitle);
                    $c_book = Book30book::select('id');
                    if (!empty($item->xtitle)) {
                        // $c_book->where('title', 'LIKE', '%' . $item->xtitle . '%');
                        $unallowableBookTitle = $item->xtitle;
                        $c_book->where(function ($query) use ($unallowableBookTitle) {
                            $query->where('title', $unallowableBookTitle);
                            $query->orwhere('title', 'like', 'کتاب ' . $unallowableBookTitle);
                        })->get();
                    }
                    // if (!empty($item->xauthor)) {
                    //     $c_book->where('partnerArray', 'LIKE', '%{"roleId":1,"name":"' . $item->xauthor . '"}%');
                    // }
                    if (!empty($item->xpublisher_name)) {
                        $c_book->where('nasher', 'LIKE', '%' . $item->xpublisher_name);
                    }
                    // if (!empty($item->xtranslator)) {
                    //     $c_book->where('partnerArray', 'LIKE', '%{"roleId":2,"name":"' . $item->xtranslator . '"}%');
                    // }
                    $c_book_books = $c_book->get();
                    foreach ($c_book_books as $c_book_book) {
                        if (isset($c_book_book) and !empty($c_book_book)) {
                            Book30book::where('id', $c_book_book->id)->update(array('unallowed' => 1));
                        }
                    }
                }
            });

            $newCrawler->status = 2;
            $newCrawler->save();
        }
    }
}
