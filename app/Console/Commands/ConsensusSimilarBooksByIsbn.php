<?php

namespace App\Console\Commands;

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
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpClient\HttpClient;

class ConsensusSimilarBooksByIsbn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:ConsensusSimilarBooksByIsbn {crawlerId} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consensus Similar Books By Isbn in bookirbook table';

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
        $countBook = BookirBook::where('xparent', 0)->whereNotNull('xisbn3')->whereNotNull('xisbn2')->where('xisbn3','!=','N')->where('xisbn3','!=','0')->where('xisbn3','!=','-')->count();
        try {

            $startC = 1;
            $endC = $countBook;

            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ------------ ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-consensus-similar-books-by-isbn-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 5));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ------------ ");
        }

        if (isset($newCrawler)) {

            // DB::enableQueryLog();
            // 0 نیاشه
            // - نباشه
            // خالی نیاشه
            // N نباشه
            // ز نباشه
            // ت نباشه
            // dossier by same isbn
            bookirbook::where('xparent', 0)->whereNotNull('xisbn3')->whereNotNull('xisbn2')->where('xisbn3','!=','N')->where('xisbn3','!=','0')->where('xisbn3','!=','-')->orderBy('xid', 'DESC')->chunk(1, function ($books,$startC) {
                foreach ($books as $book) {
                    $this->info($book->xisbn3);
                    $same_books = BookirBook::where('xisbn3', $book->xisbn3)->orwhere('xisbn2', $book->xisbn2)->get();
                    if (isset($same_books) and !empty($same_books)) {
                        $old_book_version = BookirBook::where('xisbn3', $book->xisbn3)->orwhere('xisbn2', $book->xisbn2)->orderBy('xpublishdate', 'ASC')->orderBy('xprintnumber', 'ASC')->first();
                        $this->info($old_book_version->xid);
                        $this->info(count($same_books));
                        if (count($same_books) > 1) {
                            foreach ($same_books as $dossier_book) {
                                BookirBook::where('xid', $dossier_book->xid)->update(['xparent' => $old_book_version->xid]);
                            }
                        }
                        BookirBook::where('xid', $old_book_version->xid)->update(['xparent' => -1]);
                    }
                    // $query = DB::getQueryLog();
                    // dd($query);
                    // CrawlerM::where('name', 'Crawler-consensus-similar-books-by-isbn-' . $this->argument('crawlerId'))->where('start', $startC)->update(['last' => $book->xisbn3]);
                }
            });

            $newCrawler->status = 2;
            $newCrawler->save();
        }
    }
}
