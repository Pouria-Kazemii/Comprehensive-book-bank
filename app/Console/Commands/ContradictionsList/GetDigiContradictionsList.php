<?php

namespace App\Console\Commands\ContradictionsList;

use App\Models\BookDigi;
use App\Models\BookirBook;
use App\Models\Crawler as CrawlerM;
use App\Models\ErshadBook;
use Illuminate\Console\Command;

class GetDigiContradictionsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:digiContradictionsList {rowId}';

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
        $check_count = BookDigi::where('check_status', 0)->where('has_permit', 0)->count();

        try {
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Contradictions-digi-' . $this->argument('rowId'), 'start' => '1', 'end' => $check_count, 'status' => 1));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Check  " . $this->argument('rowId') . "              ------------ ");
        }

        if (isset($newCrawler)) {
            $items = BookDigi::where('check_status', 0)->where('has_permit', 0)->get();
            foreach ($items as $book_data) {
                // bookirbook with ershad book
                if (isset($book_data) and !empty($book_data)) {
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

                    BookDigi::where('id', $book_data->id)->update($update_data);
                }
                $newCrawler->last = $book_data->id;
                $newCrawler->save();
            }

            $newCrawler->status = 2;
            $newCrawler->save();
        }
    }
}
