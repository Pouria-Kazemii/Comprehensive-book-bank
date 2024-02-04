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

class correctXpageUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:correctXpageUrl {crawlerId} {miss?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'coorect xpageurl2 with tabble info Book Command';

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
        $correctCountBook = BookirBook::WhereNull('xpageurl2')->whereNotNull('xpageurl')->count();
        try {
            
            $startC = 1;
            $endC = $correctCountBook ;

            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-correct-xpageurl2-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 5));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }
        
        if (isset($newCrawler)) {
            $bar = $this->output->createProgressBar($correctCountBook);
            $bar->start();
                $withOutXpageUrl2books = bookirbook::where('xdocid',0)->WhereNull('xpageurl2')->whereNotNull('xpageurl')->orderBy('xid','DESC')->limit('1000')->get();
                foreach($withOutXpageUrl2books as $withOutXpageUrl2book){
                    // die($withOutXpageUrl2book);
                    $this->info($withOutXpageUrl2book->xpageurl);
                    $recordNumber = $withOutXpageUrl2book->xpageurl;
                    $recordNumber = str_replace("https://db.ketab.ir/bookview.aspx?bookid=","", $recordNumber);
                    $recordNumber = str_replace("http://ketab.ir/bookview.aspx?bookid=","",$recordNumber);
                    $this->info($recordNumber);

                    $additionalRecord = bookirbook::Where('xpageurl','LIKE','%?bookid='.$recordNumber)->whereNotNull('xpageurl2')->first();
                    if(isset($additionalRecord)and !empty($additionalRecord)){
                        // die($additionalRecord);
                        $withOutXpageUrl2book->update(['xpageurl'=>'http://ketab.ir/bookview.aspx?bookid='.$recordNumber,
                        'xpageurl2'=>$additionalRecord->xpageurl2,
                        'xpagecount'=>$additionalRecord->xpagecount,
                        'xformat'=>$additionalRecord->xformat,
                        'xcover'=>$additionalRecord->xcover,
                        'xprintnumber'=>$additionalRecord->xprintnumber,
                        'xcirculation'=>$additionalRecord->xcirculation,
                        'xisbn2'=>$additionalRecord->xisbn2,
                        'xpdfurl'=>$additionalRecord->xpdfurl,
                        'xdiocode'=>$additionalRecord->xdiocode,
                        'xdocid'=>1
                    ]);
                        $count = BookirBook::where('xpageurl','http://ketab.ir/bookview.aspx?bookid='.$recordNumber)->get()->count();
                        $this->info('$count : '.$count);
                        if($count > 1){
                           $deleted_book = BookirBook::where('xpageurl','http://ketab.ir/bookview.aspx?bookid='.$recordNumber)->where('xparent','!=','-1')->orderBy('xid','DESC')->first();
                           if(isset($deleted_book) and !empty($deleted_book)){
                            $deleted_book->delete();
                           } 
                        }
                   
                    }else{
                        $withOutXpageUrl2book->update(['xdocid'=>1]);

                    }
                   
                    // $bar->advance();*/
                    CrawlerM::where('name','Crawler-correct-xpageurl2-'.$this->argument('crawlerId'))->where('start',$startC)->update(['last'=>$recordNumber]);
                }
                
            // });
            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $bar->finish();
        }
    }



}
