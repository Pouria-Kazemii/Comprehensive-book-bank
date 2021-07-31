<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\BookK24;
use App\Models\Author;
use App\Models\Crawler as CrawlerM;

class get30Book extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:k24book {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get K24 Books from html website';

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
        try{
            $lastCrawler = CrawlerM::where('type',3)->orderBy('end', 'desc')->first();
            if(isset($lastCrawler->end))$startC = $lastCrawler->end +1;
            else $startC=1;
            $endC   = $startC + CrawlerM::$crawlerSize;
            $this->info(" \n ---------- Create Crawler  ".$this->argument('crawlerId')."     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name'=>'Crawler-30book-'.$this->argument('crawlerId'), 'start'=>$startC, 'end'=>$endC, 'status'=>1, 'type'=>3));
        }catch (\Exception $e){
            $this->info(" \n ---------- Failed Crawler  ".$this->argument('crawlerId')."              ---------=-- ");
        }

        $client = new Client(HttpClient::create(['timeout' => 30]));

        $bar = $this->output->createProgressBar(CrawlerM::$crawlerSize);
        $bar->start();

        $recordNumber = $startC;
        while ($recordNumber <= $endC){

            try {
                $this->info(" \n ---------- Try Get BOOK ".$recordNumber."              ---------- ");
                $crawler = $client->request('GET', 'http://k24.ir/v1/getbookbyid&bookid='.$recordNumber."/" , [
                    'headers' => [
                        'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                    ],
                ]);
                $status_code = $client->getInternalResponse()->getStatusCode();
            }catch (\Exception $e) {
                $crawler = null;
                $status_code = 500;
                $this->info(" \n ---------- Failed Get  ".$recordNumber."              ---------=-- ");
            }

            if($status_code == 200){

                $filtered= array();
                $cats= array();
                $content = $crawler->getContent();
                $content = $crawler->toArray();

                $filtered['title']  = $content['Title'];
                foreach($content['PublisherList'] as $pub){
                    if (isset($filtered['nasher'])){
                        $filtered['nasher'] .= " -|- ". $pub['PublisherName'];
                    }else{
                        $filtered['nasher'] = $pub['PublisherName'];
                    }

                }
                $filtered['lang']  = $content['Lang'];
                foreach($content['SubjectArray'] as $pub){
                    if (isset($filtered['cats'])){
                        $filtered['cats'] .= " -|- ". $pub;
                    }else{
                        $filtered['cats'] = $pub;
                    }
                }
                $filtered['saleNashr']  = $content['PubDate'];
                $filtered['nobatChap']  = $content['PrintNumber'];
                $filtered['recordNumber']  = $recordNumber;
                $filtered['tedadSafe']  = $content['PageCount'];
                $filtered['ghateChap']  = $content['Format'];
                $filtered['shabak']  = enNumberKeepOnly($content['ISBN']);
                $filtered['tarjome']  = ($content['Title']!='فارسی')? True:False;
                $filtered['desc']  = $content['Context'];
                $filtered['image']  = $content['PicAddress'];
                $filtered['price']  = $content['Price'];
                $filtered['DioCode']  = $content['DioCode'];
                $filtered['printCount']  = $content['Circulation'];
                $filtered['printLocation']  = $content['PubPlace'];
                $partners=array();
                foreach($content['CreatorList'] as $creator){
                    if($creator['CreatorRole']=="نویسنده"){
                        $authorObject = Author::firstOrCreate(array("d_name" => $creator['CreatorName']));
                        $authors[]=$authorObject->id;
                    }else{
                        $partners[]=array("role"=>$creator['CreatorRole'], "name"=>$creator['CreatorName']);
                    }
                }
                $filtered['partnerArray'] = json_encode($partners);

                $book = BookK24::firstOrCreate($filtered);
                $this->info(" \n ---------- Inserted Book   ".$recordNumber."           ---------- ");
                if(count($authors)>0){
                    $book->authors()->attach($authors);
                    $this->info(" \n ---------- Attach Author Book   ".$recordNumber."          ---------- ");
                }

            }else{
                    $this->info(" \n ---------- Rejected Book   ".$recordNumber."           ---------- ");
            }
            
            $bar->advance();
            $recordNumber ++;
        }
        $newCrawler->status = 2;
        $newCrawler->save();
        $this->info(" \n ---------- Finish Crawler  ".$this->argument('crawlerId')."     $startC  -> $endC         ---------=-- ");
        $bar->finish();
    }
}
