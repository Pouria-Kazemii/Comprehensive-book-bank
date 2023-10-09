<?php

namespace App\Console\Commands;

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
    protected $signature = 'get:digiContradictionsList {rowId} {miss?}';

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
        if ($this->argument('miss') && $this->argument('miss') == 1) {
            try {
                $lastCrawler = CrawlerM::where('name', 'Contradictions-digi-' . $this->argument('rowId'))->where('status', 1)->orderBy('id', 'DESC')->first();
                if (isset($lastCrawler) AND !empty($lastCrawler)) {
                    $startC = $lastCrawler->last;
                    $endC   = $lastCrawler->end;
                    $this->info(" \n ---------- Check  " . $this->argument('rowId') . "     $startC  -> $endC         ---------=-- ");
                    $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Contradictions-digi-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));

                }
            } catch (\Exception $e) {
                $this->info(" \n ---------- Failed Crawler  " . $this->argument('rowId') . "              ---------=-- ");
            }
        } else {
            try {
                $lastCrawler = CrawlerM::where('name', 'Contradictions-digi-' . $this->argument('rowId'))->where('type', 2)->orderBy('end', 'desc')->first();
                if (isset($lastCrawler) AND !empty($lastCrawler)) {
                    $startC = $lastCrawler->end + 1;
                    $endC = $startC + CrawlerM::$crawlerSize;
                    
                } else {
                    $startC = 1;
                    $endC = $startC + CrawlerM::$crawlerSize;
                }

                $this->info(" \n ---------- Check  " . $this->argument('rowId') . "     $startC  -> $endC         ---------=-- ");
                $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Contradictions-digi-' . $this->argument('rowId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
            } catch (\Exception $e) {
                $this->info(" \n ---------- Check  " . $this->argument('rowId') . "              ---------=-- ");
            }
        }
        if (isset($newCrawler)) {
            $rowId = $startC;
            while ($rowId <= $endC) {
                // bookirbook with ershad book
                $book_data = BookDigi::where('id', $rowId)->first();
                if (isset($book_data) and !empty($book_data)) {
                    $update_data = array(
                        'check_status' => 0,
                        'has_permit' => 0,
                    );
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
                        $this->info('row no isbn');
                        $update_data['check_status'] = 4;
                        $update_data['has_permit'] = 4;
                    }

                    BookDigi::where('id', $rowId)->update($update_data);
                }

                CrawlerM::where('name', 'Contradictions-digi-' . $this->argument('rowId'))->where('start', $startC)->update(['last' => $rowId]);
                $rowId++;
            }
        }
    }
}
