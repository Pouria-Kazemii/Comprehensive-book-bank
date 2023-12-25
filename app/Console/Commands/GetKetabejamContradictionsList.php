<?php

namespace App\Console\Commands;

use App\Models\BookirBook;
use App\Models\BookKetabejam;
use App\Models\Crawler as CrawlerM;
use App\Models\ErshadBook;
use App\Models\UnallowableBook;
use Illuminate\Console\Command;

class GetKetabejamContradictionsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:ketabejamContradictionsList {crawlerId} {miss?}';

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
        $check_count = BookKetabejam::where('check_status', 0)->where('has_permit', 0)->count();
        CrawlerM::firstOrCreate(array('name' => 'Contradictions-Ketabejam-' . $this->argument('crawlerId'), 'start' => '1', 'end' => $check_count, 'status' => 1));

        $items = BookKetabejam::where('check_status', 0)->where('has_permit', 0)->get();
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
                BookKetabejam::where('id',$item->id)->update($update_data);
            }
        }



        //  unallowable_book
        UnallowableBook::chunk(1, function ($items) {
            foreach ($items as $item) {
                $this->info($item->xtitle);
                $Ketabejam = BookKetabejam::select('id');
                if (!empty($item->xtitle)) {
                    $Ketabejam->where('title', 'LIKE', '%' . $item->xtitle . '%');
                }
                if (!empty($item->xauthor)) {
                    $Ketabejam->where('partnerArray', 'LIKE', '%{"roleId":1,"name":"' . $item->xauthor . '"}%');
                }
                if (!empty($item->xpublisher_name)) {
                    $Ketabejam->where('nasher', 'LIKE', '%' . $item->xpublisher_name);
                }
                if (!empty($item->xtranslator)) {
                    $Ketabejam->where('partnerArray', 'LIKE', '%{"roleId":2,"name":"' . $item->xtranslator . '"}%');
                }
                $Ketabejam_books = $Ketabejam->get();
                foreach ($Ketabejam_books as $Ketabejam_book) {
                    if (isset($Ketabejam_book) and !empty($Ketabejam_book)) {
                        BookKetabejam::where('id', $Ketabejam_book->id)->update(array('unallowed' => 1));
                    }
                }
            }
        });
    }
}
