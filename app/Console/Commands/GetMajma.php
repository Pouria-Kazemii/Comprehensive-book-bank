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
use Goutte\Client;
use Illuminate\Console\Command;
use Symfony\Component\HttpClient\HttpClient;

class Getmajma extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:majma {crawlerId}';

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
        $total = MajmaApiBook::where('xfunction_caller','GetMajmaLastDays-Command')->where('xstatus',0)->count();
        try {
            $startC = 0;
            $endC = $total;

            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-Majma-New-Books' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }
        

        if (isset($newCrawler)) {

            $client = new Client(HttpClient::create(['timeout' => 30]));

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            MajmaApiBook::where('xfunction_caller','GetMajmaLastDays-Command')->where('xstatus',0)->orderBy('xbook_id', 'DESC')->chunk(2000, function ($books) use ($bar, $newCrawler) {
                foreach($books as $book){
                    $this->info($book->xbook_id);
                    $bookIrBook = BookirBook::where('xpageurl', 'http://ketab.ir/bookview.aspx?bookid=' . $book->xbook_id)->orwhere('xpageurl', 'https://db.ketab.ir/bookview.aspx?bookid=' . $book->xbook_id)->first();
                    $api_status = updateBookDataWithMajmaApiInfo($book->xbook_id,$bookIrBook);
                    $this->info($api_status);
                    MajmaApiBook::where('xbook_id',$book->xbook_id)->update(['xstatus'=>$api_status]);
                    $bar->advance();
                    $newCrawler->last = $book->xbook_id;
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
