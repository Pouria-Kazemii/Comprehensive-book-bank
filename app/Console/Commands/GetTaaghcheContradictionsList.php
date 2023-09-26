<?php

namespace App\Console\Commands;

use App\Models\BookTaaghche;
use App\Models\BookirBook;
use App\Models\Crawler as CrawlerM;
use App\Models\ErshadBook;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GetTaaghcheContradictionsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:taaghcheContradictionsList {rowId} {miss?}';

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
                $lastCrawler = CrawlerM::where('type', 2)->where('status', 1)->orderBy('end', 'ASC')->first();
                if (isset($lastCrawler->end)) {
                    $startC = $lastCrawler->start;
                    $endC = $lastCrawler->end;
                    $this->info(" \n ---------- Check  " . $this->argument('rowId') . "     $startC  -> $endC         ---------=-- ");
                    $newCrawler = $lastCrawler;
                }
            } catch (\Exception $e) {
                $this->info(" \n ---------- Failed Crawler  " . $this->argument('rowId') . "              ---------=-- ");
            }
        } else {
            try {
                $lastCrawler = CrawlerM::where('name', 'LIKE', 'Contradictions-Taaghche-%')->where('type', 2)->orderBy('end', 'desc')->first();
                if (isset($lastCrawler->end)) {
                    $startC = $lastCrawler->end + 1;
                } else {
                    $startC = 1;
                }

                $endC = $startC + CrawlerM::$crawlerSize;
                $this->info(" \n ---------- Check  " . $this->argument('rowId') . "     $startC  -> $endC         ---------=-- ");
                $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Contradictions-Taaghche-' . $this->argument('rowId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
            } catch (\Exception $e) {
                $this->info(" \n ---------- Check  " . $this->argument('rowId') . "              ---------=-- ");
            }
        }
        if (isset($newCrawler)) {
            $rowId = $startC;
            while ($rowId <= $endC) {
                $book_data = BookTaaghche::where('id', $rowId)->first();
                if (isset($book_data) and !empty($book_data)) {
                    $update_data = array(
                        'check_status' => 0,
                        'has_permit' => 0,
                    );
                    if ((isset($book_data->shabak) and $book_data->shabak != null and !empty($book_data->shabak)) AND (isset($book_data->saleNashr) and $book_data->saleNashr != null and !empty($book_data->saleNashr))) {
                        $this->info($book_data->shabak);
                        $this->info($book_data->saleNashr);

                        $georgianCarbonDate=\Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $book_data->saleNashr)->toCarbon();
                        if ($georgianCarbonDate < date('2022-03-21 00:00:00')) {
                            $bookirbook_data = BookirBook::where('xisbn', $book_data->shabak)->orwhere('xisbn2', $book_data->shabak)->orWhere('xisbn3', $book_data->shabak)->first();
                            if (!empty($bookirbook_data) || !empty($bookirbook_data)) {
                                $update_data['check_status'] = 1;
                            } else {
                                $update_data['check_status'] = 2;
                            }
                        } else {
                            $update_data['check_status'] = 3;
                        }
                        if ($georgianCarbonDate > date('2018-03-21 00:00:00')) {
                            $ershad_book = ErshadBook::where('xisbn', $book_data->shabak)->first();
                            if (!empty($ershad_book) || !empty($bookirbook_data)) {
                                $update_data['has_permit'] = 1;
                            } else {
                                $update_data['has_permit'] = 2;
                            }
                        } else {
                            $update_data['has_permit'] = 3;
                        }
                    } else {
                        $this->info('row no isbn');
                        $update_data['check_status'] = 4;
                        $update_data['has_permit'] = 4;
                    }

                    BookTaaghche::where('id', $rowId)->update($update_data);
                }


                /*
                //  unallowable_book
                $book_data = UnallowableBook::where('xid', $rowId)->first();
                $this->info($rowId);
                $this->info($book_data->xtitle);
                $fidibo = BookTaaghche::select('id');
                if (!empty($book_data->xtitle)) {
                $fidibo->where('title', $book_data->xtitle);
                }
                if (!empty($book_data->xauthor)) {
                $fidibo->where('authorsname', 'LIKE', '%'. $book_data->xauthor . '%');
                }
                if (!empty($book_data->xpublisher_name)) {
                $fidibo->where('nasher', 'LIKE', '%' . $book_data->xpublisher_name);
                }
                if (!empty($book_data->xtranslator)) {
                $fidibo->where('authorsname', 'LIKE', '%' . $book_data->xtranslator . '%');
                }
                $fidibo_book = $fidibo->first();
                if (isset($fidibo_book) and !empty($fidibo_book)) {
                $update_data = array(
                'has_permit' => 2,
                );
                BookTaaghche::where('id', $fidibo_book->id)->update($update_data);
                }*/
                CrawlerM::where('name', 'Contradictions-Taaghche-' . $this->argument('rowId'))->where('start', $startC)->update(['last' => $rowId]);
                $rowId++;
            }
        }
    }
}
