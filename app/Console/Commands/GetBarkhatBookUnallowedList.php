<?php

namespace App\Console\Commands;

use App\Models\BookBarkhatBook;
use App\Models\BookDigi;
use App\Models\BookirBook;
use App\Models\Crawler as CrawlerM;
use App\Models\ErshadBook;
use Illuminate\Console\Command;

class GetBarkhatBookUnallowedList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:barkhatBookUnallowedList';

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
        $count_check = BookBarkhatBook::where('check_status', 0)->where('has_permit', 0)->get()->count();
        if ($count_check) {
            try {
                $startC = 1;
                $endC = $count_check;
                $this->info(" \n ---------- Check      $startC  -> $endC         ------------ ");
                $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Contradictions-barkhat', 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
            } catch (\Exception $e) {
                $this->info(" \n ---------- Failed Crawler                ------------ ");
            }
        }


        if (isset($newCrawler)) {
            $rowId = $startC;
            while ($rowId <= $endC) {
                // bookirbook with ershad book
                $book_data = BookBarkhatBook::where('check_status', 0)->where('has_permit', 0)->first();
                if (isset($book_data) and !empty($book_data)) {
                    // $update_data = array(
                    //     'check_status' => 0,
                    //     'has_permit' => 0,
                    // );
                    if ((isset($book_data->shabak) and $book_data->shabak != null and !empty($book_data->shabak))/* AND (isset($book_data->saleNashr) and $book_data->saleNashr != null and !empty($book_data->saleNashr))*/) {
                        $this->info($book_data->shabak);
                        $this->info($book_data->saleNashr);

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
                        // if ($georgianCarbonDate > date('2024-03-29 00:00:00')) {
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
                        $this->info('row no isbn');
                        $update_data['check_status'] = 4;
                        $update_data['has_permit'] = 4;
                    }

                    BookBarkhatBook::where('id', $book_data->id)->update($update_data);
                }

                CrawlerM::where('name', 'Contradictions-barkhat')->where('start', $startC)->update(['last' => $rowId]);
                $rowId++;
            }
        }
    }
}
