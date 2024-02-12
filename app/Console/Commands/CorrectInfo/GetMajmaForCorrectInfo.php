<?php

namespace App\Console\Commands\CorrectInfo;

use App\Models\AgeGroup;
use App\Models\BookCover;
use App\Models\BookFormat;
use App\Models\BookirBook;
use App\Models\BookirPartner;
use App\Models\BookirPublisher;
use App\Models\BookirRules;
use App\Models\BookirSubject;
use App\Models\BookLanguage;
use App\Models\Crawler as CrawlerM;
use App\Models\MajmaApiBook;
use App\Models\MajmaApiPublisher;
use Goutte\Client;
use Illuminate\Console\Command;
use Symfony\Component\HttpClient\HttpClient;

class GetMajmaForCorrectInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:GetMajmaForCorrectInfo {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get majma Book Command';

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
        $function_caller = 'GetMajmaForCorrectInfo-Command';
        $total = BookirBook::WhereNull('xpageurl2')->whereNotNull('xpageurl')->where('xdocid', 1)->count();
        try {

            $startC = 1;
            $endC = $total;

            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-mamja-for-correct-info-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 5));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }

        if (isset($newCrawler)) {
            $bar = $this->output->createProgressBar($total);
            $bar->start();
            $books = bookirbook::WhereNull('xpageurl2')->whereNotNull('xpageurl')->where('xdocid', 1)->orderBy('xid', 'DESC')->limit('10000')->get();
            foreach ($books as $book) {

                //find recorNumber
                $recordNumber = $book->xpageurl;
                $recordNumber = str_replace("https://db.ketab.ir/bookview.aspx?bookid=", "", $recordNumber);
                $recordNumber = str_replace("http://ketab.ir/bookview.aspx?bookid=", "", $recordNumber);
                $this->info('recordNumber : ' . $recordNumber);

                //$bookIrBook = BookirBook::where('xpageurl', 'http://ketab.ir/bookview.aspx?bookid=' . $recordNumber)->orwhere('xpageurl', 'https://db.ketab.ir/bookview.aspx?bookid=' . $recordNumber)->orWhere('xpageurl2', 'https://ketab.ir/book/' . $book_content->uniqueId)->firstOrNew();
                $bookIrBook = BookirBook::where('xid', $book->xid)->first();
                updateBookDataWithMajmaApiInfo($recordNumber, $bookIrBook, $function_caller);
                BookirBook::where('xid', $book->xid)->update(['xdocid' => 2]);
                $bar->advance();
                $newCrawler->last = $recordNumber;
                $newCrawler->save();
            }


            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $bar->finish();
        }
    }
}
