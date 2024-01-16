<?php

namespace App\Console\Commands\ContradictionsList;

use App\Models\BookGisoom;
use App\Models\BookirBook;
use App\Models\Crawler as CrawlerM;
use App\Models\ErshadBook;
use App\Models\UnallowableBook;
use Illuminate\Console\Command;

class GetGissomContradictionsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:gissomContradictionsList {crawlerId} {miss?}';

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
        $check_count = BookGisoom::where('check_status', 0)->where('has_permit', 0)->count();
        CrawlerM::firstOrCreate(array('name' => 'Contradictions-gisoom-' . $this->argument('crawlerId'), 'start' => '1', 'end' => $check_count, 'status' => 1));

        $items = BookGisoom::where('check_status', 0)->where('has_permit', 0)->get();
        foreach ($items as $item) {
            // bookirbook with ershad book
            if (isset($item) and !empty($item)) {
                if ((isset($item->shabak10) and $item->shabak10 != null and !empty($item->shabak10)) or (isset($item->shabak13) and $item->shabak13 != null and !empty($item->shabak13))) {
                    $this->info($item->shabak10);
                    $this->info($item->shabak13);

                    $bookiritem = BookirBook::where('xisbn', $item->shabak10)->orwhere('xisbn2', $item->shabak10)->orWhere('xisbn3', $item->shabak10)->orWhere('xisbn', $item->shabak13)->orwhere('xisbn2', $item->shabak13)->orWhere('xisbn3', $item->shabak13)->first();
                    if (!empty($bookiritem)) {
                        $update_data['check_status'] = 1;
                    } else {
                        $update_data['check_status'] = 2;
                    }

                    $ershad_book = ErshadBook::where('xisbn', $item->shabak10)->orwhere('xisbn', $item->shabak13)->first();
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
                BookGisoom::where('id',$item->id)->update($update_data);
            }
        }



        //  unallowable_book
       /* UnallowableBook::chunk(1, function ($items) {
            foreach ($items as $item) {
                $this->info($item->xtitle);
                $gisoom = BookGisoom::select('id');
                if (!empty($item->xtitle)) {
                    $gisoom->where('title', 'LIKE', '%' . $item->xtitle . '%');
                }
                if (!empty($item->xauthor)) {
                    $gisoom->where('partnerArray', 'LIKE', '%{"roleId":1,"name":"' . $item->xauthor . '"}%');
                }
                if (!empty($item->xpublisher_name)) {
                    $gisoom->where('nasher', 'LIKE', '%' . $item->xpublisher_name);
                }
                if (!empty($item->xtranslator)) {
                    $gisoom->where('partnerArray', 'LIKE', '%{"roleId":2,"name":"' . $item->xtranslator . '"}%');
                }
                $gisoom_books = $gisoom->get();
                foreach ($gisoom_books as $gisoom_book) {
                    if (isset($gisoom_book) and !empty($gisoom_book)) {
                        Bookgisoom::where('id', $gisoom_book->id)->update(array('unallowed' => 1));
                    }
                }
            }
        }); */
    }
}
