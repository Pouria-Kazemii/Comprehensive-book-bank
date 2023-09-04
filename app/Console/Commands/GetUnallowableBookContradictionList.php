<?php

namespace App\Console\Commands;

use App\Models\BookDigi;
use App\models\BookFidibo;
use App\Models\BookirBook;
use App\Models\BookTaaghche;
use App\Models\Crawler as CrawlerM;
use App\Models\ErshadBook;
use App\Models\UnallowableBook;
use Exception;
use Goutte\Client;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Illuminate\Support\Facades\DB;


class GetUnallowableBookContradictionList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:ContradictionsList {rowId} {miss?}';

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
                $lastCrawler = CrawlerM::where('name', 'LIKE', 'Contradictions-Unallowable-%')->where('type', 2)->orderBy('end', 'desc')->first();
                if (isset($lastCrawler->end)) {
                    $startC = $lastCrawler->end + 1;
                } else {
                    $startC = 1;
                }

                $endC = $startC + CrawlerM::$crawlerSize;
                $this->info(" \n ---------- Check  " . $this->argument('rowId') . "     $startC  -> $endC         ---------=-- ");
                $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Contradictions-Unallowable-' . $this->argument('rowId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
            } catch (\Exception $e) {
                $this->info(" \n ---------- Check  " . $this->argument('rowId') . "              ---------=-- ");
            }
        }
        if (isset($newCrawler)) {
            $rowId = $startC;
            while ($rowId <= $endC) {
                // DB::enableQueryLog();

                $book_data = UnallowableBook::where('xid',$rowId)->first();
                if(isset($book_data->xtitle) AND !empty($book_data->xtitle) AND $book_data->xtitle != NULL ){
                    $this->info($book_data->xtitle);

                    $book_data->xtitle = ltrim($book_data->xtitle);
                    $book_data->xtitle = rtrim($book_data->xtitle);
                    // bookFidibo::where('title',$book_data->xtitle)->update(['has_permit'=>2]);
                    bookFidibo::where('title','like', "%$book_data->xtitle%")->update(['has_permit'=>2]);
                    // BookTaaghche::where('title',$book_data->xtitle)->update(['has_permit'=>2]);
                    BookTaaghche::where('title','like', "%$book_data->xtitle%")->update(['has_permit'=>2]);
                    // BookDigi::where('title',$book_data->xtitle)->update(['has_permit'=>2]);
                    // BookDigi::where('title','like', "%$book_data->xtitle%")->update(['has_permit'=>2]);
                }

                // $query = DB::getQueryLog();
                //         var_dump($query);
                /*
                $book_data = BookFidibo::where('id',$rowId)->first();
                if(isset($book_data->shabak) AND $book_data->shabak != NULL){
                    $this->info($book_data->shabak);
                    $bookirbook_data = BookirBook::where('xisbn',$book_data->shabak)->orwhere('xisbn2',$book_data->shabak)->orWhere('xisbn3',$book_data->shabak)->first();
                    $ershad_book = ErshadBook::where('xisbn',$book_data->shabak)->first();
                    if( $ershad_book->count() > 0 ||  $bookirbook_data->count() > 0){
                        $update_data = array(
                            'has_permit'=>1
                        );
                    }else{
                        $update_data = array(
                            'has_permit'=>2
                        );
                    }
                }else{
                    $this->info('row no isbn');
                    $update_data = array(
                        'has_permit'=>3
                    );
                }

                BookFidibo::where('id',$rowId)->update($update_data);*/
                $rowId++;

            }

        }
    }
}