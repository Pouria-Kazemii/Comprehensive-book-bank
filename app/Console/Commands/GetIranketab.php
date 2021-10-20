<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\BookIranketab;
use App\Models\Author;
use App\Models\Crawler as CrawlerM;

class GetIranketab extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:iranKetab {crawlerId} {miss?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get IranKetab Book Command';

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
        // if($this->argument('miss') && $this->argument('miss')==1){
        //     try{
        //         $lastCrawler = CrawlerM::where('type',2)->where('status',1)->orderBy('end', 'ASC')->first();
        //         if(isset($lastCrawler->end)){
        //             $startC = $lastCrawler->start;
        //             $endC   = $lastCrawler->end;
        //             $this->info(" \n ---------- Create Crawler  ".$this->argument('crawlerId')."     $startC  -> $endC         ---------=-- ");
        //             $newCrawler = $lastCrawler;
        //         }
        //     }catch (\Exception $e){
        //         $this->info(" \n ---------- Failed Crawler  ".$this->argument('crawlerId')."              ---------=-- ");
        //     }
        // }else{
        //     try{
        //         $lastCrawler = CrawlerM::where('type',2)->orderBy('end', 'desc')->first();
        //         if(isset($lastCrawler->end))$startC = $lastCrawler->end +1;
        //         else $startC=11044084;
        //         $endC   = $startC + CrawlerM::$crawlerSize;
        //         $this->info(" \n ---------- Create Crawler  ".$this->argument('crawlerId')."     $startC  -> $endC         ---------=-- ");
        //         $newCrawler = CrawlerM::firstOrCreate(array('name'=>'Crawler-Gisoom-'.$this->argument('crawlerId'), 'start'=>$startC, 'end'=>$endC, 'status'=>1, 'type'=>2));
        //     }catch (\Exception $e){
        //         $this->info(" \n ---------- Failed Crawler  ".$this->argument('crawlerId')."              ---------=-- ");
        //     }
        // }
        $recordNumber = $startC = $endC = 338 ;


        // if(isset($newCrawler)){

            $client = new Client(HttpClient::create(['timeout' => 30]));

            $bar = $this->output->createProgressBar(CrawlerM::$crawlerSize);
            $bar->start();

            $recordNumber = $startC;
            while ($recordNumber <= $endC){

                try {
                    $this->info(" \n ---------- Try Get BOOK ".$recordNumber."              ---------- ");
                    $crawler = $client->request('GET', 'https://www.iranketab.ir/book/'.$recordNumber);
                    $status_code = $client->getInternalResponse()->getStatusCode();
                } catch (\Exception $e) {
                    $crawler = null;
                    $status_code = 500;
                    $this->info(" \n ---------- Failed Get  ".$recordNumber."              ---------=-- ");
                }

                if($status_code == 200 &&  $crawler->filter('body')->text('')!=''){
                    $allbook = $crawler->filterXPath('//*[@itemid="'.$recordNumber.'"]')->filter('div.product-container div');
                    $refCode = md5(time());
                    foreach($allbook->filter('div.clearfix') as $book){
                        $row = new Crawler($book);
                        if($row->filter('h1.product-name')->text('')!='' || $row->filter('div.product-name')->text('')!=''){
                            $filtered= array();
                            $filtered['title']=($row->filter('h1.product-name')->text(''))?$row->filter('h1.product-name')->text(''):$row->filter('div.product-name')->text('');
                            $filtered['enTitle']=$row->filter('div.product-name-englishname')->text('');
                            $filtered['subTitle']=$row->filterXPath('//div[contains(@class, "col-md-7")]/div[3]')->text('');
                            $filtered['price']=$row->filter('span.price')->text('');
                            $filtered['nasher']=$row->filterXPath('//div[contains(@class, "prodoct-attribute-items")][1]/a')->text('');
                            $authorarray=array();
                            foreach($row->filterXPath('//div[contains(@class, "prodoct-attribute-items")][2]/a') as $authortag){
                                array_push($authorarray,$authortag->textContent);
                            }
                            $filtered['images']='';
                            foreach($row->filterXPath('//div[contains(@class, "images-container")]/div[1]/a') as $imagelink){
                                $atag=new Crawler($imagelink);
                                $filtered['images'].='https://www.iranketab.ir'.$atag->attr('href')." =|= ";
                            }
                            $filtered['refCode']=$refCode;
                            $filtered['traslate']=false;
                            $filtered['rate']=$row->filterXPath('//meta[contains(@itemprop, "ratingvalue")]')->attr('content');
                            foreach($row->filter('table.product-table tr') as $tr){
                                $trtag=new Crawler($tr);
                                $trtag->filterXPath('//td[1]')->html();
                                if(trim($trtag->filterXPath('//td[1]')->text())=='کد کتاب :')
                                    $filtered['recordNumber']=trim($trtag->filterXPath('//td[2]')->text());
                                if(trim($trtag->filterXPath('//td[1]')->text())=='مترجم :'){
                                    $filtered['traslate']=true;
                                    $partner=array();
                                    foreach($trtag->filterXPath('//td[2]/a') as $atag){
                                        array_push($partner, $atag->textContent);
                                    }
                                    $filtered['partnerArray']=serialize($partner);
                                }
                                if(trim($trtag->filterXPath('//td[1]')->text())=='شابک :')
                                    $filtered['shabak']=enNumberKeepOnly(faCharToEN($trtag->filterXPath('//td[2]')->text()));
                                if(trim($trtag->filterXPath('//td[1]')->text())=='قطع :')
                                    $filtered['ghateChap']=trim($trtag->filterXPath('//td[2]')->text());
                                if(trim($trtag->filterXPath('//td[1]')->text())=='تعداد صفحه :')
                                    $filtered['tedadSafe']=trim($trtag->filterXPath('//td[2]')->text());
                                if(trim($trtag->filterXPath('//td[1]')->text())=='سال انتشار شمسی :')
                                    $filtered['saleNashr']=trim($trtag->filterXPath('//td[2]')->text());
                                if(trim($trtag->filterXPath('//td[1]')->text())=='نوع جلد :')
                                    $filtered['jeld']=trim($trtag->filterXPath('//td[2]')->text());
                                if(trim($trtag->filterXPath('//td[1]')->text())=='سری چاپ :')
                                    $filtered['nobatChap']=trim($trtag->filterXPath('//td[2]')->text());
                            }





                            // $filtered['tags']='';
                            // $filtered['recordNumber']='';
                            // $filtered['shabak']='';
                            // $filtered['desc']='';
                            // $filtered['jeld']='';
                            // $filtered['features']='';
                            // $filtered['partsText']='';
                            // $filtered['notes']='';
                            // $filtered['prizes']='';
                            // $filtered['saveBook']='';

                            


                            var_dump($filtered);
                            exit;
                        }
                    }
                    //var_dump($filtered);
                    exit;
                }
                $bar->advance();
                $recordNumber ++;
            }
            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  ".$this->argument('crawlerId')."     $startC  -> $endC         ---------=-- ");
            $bar->finish();
        // }
    }
}
