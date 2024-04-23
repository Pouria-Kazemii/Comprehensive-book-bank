<?php

namespace App\Console\Commands\ContradictionsList;

use App\Models\BookirBook;
use App\Models\BookShahreKetabOnline;
use App\Models\Crawler as CrawlerM;
use App\Models\ErshadBook;
use App\Models\UnallowableBook;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GetShahreketabOnlineContradictionsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:shahreKetabOnlineContradictionsList {rowId}';

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
        $check_count = BookShahreKetabOnline::where('check_status', 0)->where('has_permit', 0)->count();

        try {
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Contradictions-ShahreketabOnline-' . $this->argument('rowId'), 'start' => '1', 'end' => $check_count, 'status' => 1));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Check  " . $this->argument('rowId') . "              ------------ ");
        }

        if (isset($newCrawler)) {
            $items = BookShahreKetabOnline::where('check_status', 0)->where('has_permit', 0)->get();
            foreach ($items as $book_data) {
                if (isset($book_data) and !empty($book_data)) {
                    if ((isset($book_data->shabak) and $book_data->shabak != null and !empty($book_data->shabak))) {
                        $this->info($book_data->shabak);
                        // $this->info($book_data->saleNashr);

                        // $georgianCarbonDate=\Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $book_data->saleNashr)->toCarbon();
                        // if ($georgianCarbonDate < date('2022-03-21 00:00:00')) {
                        $bookirbook_data = BookirBook::where('xisbn', $book_data->shabak)->orwhere('xisbn2', $book_data->shabak)->orWhere('xisbn3', $book_data->shabak)->first();
                        if (!empty($bookirbook_data)) {
                            $update_data['check_status'] = 1;
                        } else {
                            $update_data['check_status'] = 2;
                        }
                        // } else {
                        //     $update_data['check_status'] = 3;
                        // }
                        // if ($georgianCarbonDate > date('2018-03-21 00:00:00')) {
                        $ershad_book = ErshadBook::where('xisbn', $book_data->shabak)->first();
                        if (!empty($ershad_book)) {
                            $update_data['has_permit'] = 1;
                        } else {
                            $update_data['has_permit'] = 2;
                        }
                        // } else {
                        //     $update_data['has_permit'] = 3;
                        // }
                    } else {
                        // if(isset($book_data->shabak) and $book_data->shabak != null and !empty($book_data->shabak)){
                        $this->info('row no isbn');
                        $update_data['check_status'] = 4;
                        $update_data['has_permit'] = 4;
                        // }
                        // }elseif((isset($book_data->saleNashr) and $book_data->saleNashr != null and !empty($book_data->saleNashr))){
                        //     $this->info('row no isbn');
                        //     $update_data['check_status'] = 5;
                        //     $update_data['has_permit'] = 5;
                        // }

                    }

                    BookShahreKetabOnline::where('id', $book_data->id)->update($update_data);
                }
                $newCrawler->last = $book_data->id;
                $newCrawler->save();
            }

            //  unallowable_book
            UnallowableBook::chunk(1, function ($items) {
                foreach ($items as $item) {
                    $this->info($item->xtitle);
                    $shahreKetabOnline = BookShahreKetabOnline::select('id');
                    if (!empty($item->xtitle)) {
                        $shahreKetabOnline->where('title', 'LIKE', '%' . $item->xtitle . '%');
                    }
                    if (!empty($item->xauthor)) {
                        $shahreKetabOnline->where('partnerArray', 'LIKE', '%{"roleId":1,"name":"' . $item->xauthor . '"}%');
                    }
                    if (!empty($item->xpublisher_name)) {
                        $shahreKetabOnline->where('nasher', 'LIKE', '%' . $item->xpublisher_name);
                    }
                    if (!empty($item->xtranslator)) {
                        $shahreKetabOnline->where('partnerArray', 'LIKE', '%{"roleId":2,"name":"' . $item->xtranslator . '"}%');
                    }
                    $shahreKetabOnline_books = $shahreKetabOnline->get();
                    foreach ($shahreKetabOnline_books as $shahreKetabOnline_book) {
                        if (isset($shahreKetabOnline_book) and !empty($shahreKetabOnline_book)) {
                            BookShahreKetabOnline::where('id', $shahreKetabOnline_book->id)->update(array('unallowed' => 1));
                        }
                    }
                }
            });

            $newCrawler->status = 2;
            $newCrawler->save();
        }
    }
}
