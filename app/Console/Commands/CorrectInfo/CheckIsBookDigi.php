<?php

namespace App\Console\Commands\CorrectInfo;

use App\Models\BookDigi;
use App\Models\Crawler as CrawlerM;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckIsBookDigi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:CheckIsBookDigi {crawlerId} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check is book digi id Command';

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
        $total = BookDigi::where('is_book', 0)->whereNull('shabak')->count();
        try {
            $startC = 0;
            $endC   = $total;
            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Check-Is-Book_Digi-Books-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }

        if (isset($newCrawler)) {


            $bar = $this->output->createProgressBar($total);
            $bar->start();

            BookDigi::where('is_book', 0)->whereNull('shabak')->orderby('id', 'DESC')->chunk(200, function ($books) use ($bar, $newCrawler) {
                foreach ($books as $book) {
                    $book_digi = BookDigi::where('id',$book->id)->first();
                    $this->info($book->recordNumber);
                    $this->info(check_digi_book_status($book->recordNumber));
                    $result_check_digi_id_is_book = check_digi_book_status($book->recordNumber);
                    if( $result_check_digi_id_is_book == 'is_book'){
                        $book_digi->is_book = 1;
                    } elseif($result_check_digi_id_is_book == 'is_not_book') {
                        $book_digi->is_book = 2;
                    }elseif($result_check_digi_id_is_book == 'unknown') {
                        $book_digi->is_book = 3;
                    }elseif($result_check_digi_id_is_book == 'is_inactive') {
                        $book_digi->is_book = 4;
                    }
                    $book_digi->save();

                    $bar->advance();
                    $newCrawler->last = $book->id;
                    $newCrawler->save();
                }
            });



            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $bar->finish();
        }
    }
}
