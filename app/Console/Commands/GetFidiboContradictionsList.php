<?php

namespace App\Console\Commands;

use App\Models\BookFidibo;
use App\Models\BookirBook;
use App\Models\Crawler as CrawlerM;
use App\Models\ErshadBook;
use Illuminate\Console\Command;

class GetFidiboContradictionsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:fidiboContradictionsList {rowId} {miss?}';

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
                $lastCrawler = CrawlerM::where('name', 'LIKE', 'Contradictions-Fidibo-%')->where('type', 2)->orderBy('end', 'desc')->first();
                if (isset($lastCrawler->end)) {
                    $startC = $lastCrawler->end + 1;
                } else {
                    $startC = 1;
                }

                $endC = $startC + CrawlerM::$crawlerSize;
                $this->info(" \n ---------- Check  " . $this->argument('rowId') . "     $startC  -> $endC         ---------=-- ");
                $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Contradictions-Fidibo-' . $this->argument('rowId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
            } catch (\Exception $e) {
                $this->info(" \n ---------- Check  " . $this->argument('rowId') . "              ---------=-- ");
            }
        }
        if (isset($newCrawler)) {
            $rowId = $startC;
            while ($rowId <= $endC) {
                // bookirbook with ershad book
                $book_data = BookFidibo::where('id', $rowId)->first();
                if (isset($book_data->shabak) and $book_data->shabak != null) {
                    $this->info($book_data->shabak);
                    $bookirbook_data = BookirBook::where('xisbn', $book_data->shabak)->orwhere('xisbn2', $book_data->shabak)->orWhere('xisbn3', $book_data->shabak)->first();
                    $ershad_book = ErshadBook::where('xisbn', $book_data->shabak)->first();
                    if (!empty($ershad_book) || !empty($bookirbook_data)) {
                        $update_data = array(
                            'has_permit' => 1,
                        );
                    } else {
                        $update_data = array(
                            'has_permit' => 2,
                        );
                    }
                } else {
                    $this->info('row no isbn');
                    $update_data = array(
                        'has_permit' => 3,
                    );
                }

                BookFidibo::where('id', $rowId)->update($update_data);

                /*
                //  unallowable_book
                $book_data = UnallowableBook::where('xid',$rowId)->first();
                $this->info($rowId);
                $this->info($book_data->xtitle);
                $fidibo = BookFidibo::select('id');
                if(!empty($book_data->xtitle)){
                $fidibo->where('title','LIKE','%'.$book_data->xtitle.'%');
                }
                if(!empty($book_data->xauthor)){
                $fidibo->where('partnerArray','LIKE', '%{"roleId":1,"name":"'.$book_data->xauthor.'"}%');
                }
                if(!empty($book_data->xpublisher_name)){
                $fidibo->where('nasher','LIKE','%'.$book_data->xpublisher_name);
                }
                if(!empty($book_data->xtranslator)){
                $fidibo->where('partnerArray','LIKE','%{"roleId":2,"name":"'.$book_data->xtranslator.'"}%');
                }
                $fidibo_book = $fidibo->first();
                if(isset($fidibo_book) AND !empty($fidibo_book)){
                $update_data = array(
                'has_permit'=>2
                );
                BookFidibo::where('id',$fidibo_book->id)->update($update_data);
                }*/

                $rowId++;

            }

        }
    }
}
