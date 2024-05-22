<?php

namespace App\Console\Commands\CrawlerSites;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\BookDigi;
use App\Models\BookDigiRelated;
use App\Models\Author;
use App\Models\Crawler as CrawlerM;

class GetDigiRelation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:digirelation {crawlerId} {miss?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get DigiKala Book Relation Command';

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
        if($this->argument('miss') && $this->argument('miss')==1){
            try{
                $lastCrawler = CrawlerM::where('type',6)->where('status',1)->orderBy('end', 'ASC')->first();
                if(isset($lastCrawler->end)){
                    $startC = $lastCrawler->start;
                    $endC   = $lastCrawler->end;
                    $this->info(" \n ---------- Create Crawler  ".$this->argument('crawlerId')."     $startC  -> $endC         ------------ ");
                    $newCrawler = $lastCrawler;
                }
            }catch (\Exception $e){
                $this->info(" \n ---------- Failed Crawler  ".$this->argument('crawlerId')."              ------------ ");
            }
        }else{
            try{
                $lastCrawler = CrawlerM::where('type',6)->orderBy('end', 'desc')->first();
                if(isset($lastCrawler->end))$startC = $lastCrawler->end +1;
                else $startC=1;
                $endC   = $startC + 10;
                $this->info(" \n ---------- Create Crawler  ".$this->argument('crawlerId')."     $startC  -> $endC         ------------ ");
                $newCrawler = CrawlerM::firstOrCreate(array('name'=>'Crawler-digiRel-'.$this->argument('crawlerId'), 'start'=>$startC, 'end'=>$endC, 'status'=>1, 'type'=>6));
            }catch (\Exception $e){
                $this->info(" \n ---------- Failed Crawler  ".$this->argument('crawlerId')."              ------------ ");
            }
        }
        // $recordNumber = $startC = $endC = 338 ;
        


        if(isset($newCrawler)){

            $client = new Client(HttpClient::create(['timeout' => 30]));

                $recordNumber = $startC;

                $books = BookDigi::where('id','>',$startC)->where('id','<',$endC)->orderBy('id', 'DESC')->get();
                $bar = $this->output->createProgressBar($books->count());
                $bar->start();
                
                foreach($books as $book){
                    //$book->recordNumber = 'dkp-1111023';
                    $productUrl="https://www.digikala.com/product/".$book->recordNumber."/";
                    //$productUrl="https://www.digikala.com/product/dkp-5547149/%DA%A9%D8%AA%D8%A7%D8%A8-%D9%85%D8%B9%D8%AC%D8%B2%D9%87-%D8%B4%DA%A9%D8%B1%DA%AF%D8%B2%D8%A7%D8%B1%DB%8C-%D8%A7%D8%AB%D8%B1-%D8%B1%D8%A7%D9%86%D8%AF%D8%A7-%D8%A8%D8%B1%D9%86-%D8%A7%D9%86%D8%AA%D8%B4%D8%A7%D8%B1%D8%A7%D8%AA-%D9%86%DA%AF%DB%8C%D9%86-%D8%A7%DB%8C%D8%B1%D8%A7%D9%86";

                    try {
                        $this->info(" \n ---------- Try Get BOOK        ".$book->recordNumber."       ---------- ");
                        $crawler = $client->request('GET', $productUrl);
                        $status_code = $client->getInternalResponse()->getStatusCode();
                    } catch (\Exception $e) {
                        $crawler = null;
                        $status_code = 500;
                        $this->info(" \n ---------- Failed Get  ".$book->recordNumber."              ------------ ");
                    }

                    if($status_code == 200 ){
                        $related_array = array();
                        foreach($crawler->filterXPath('//div[contains(@class, "c-carousel--horizontal-general")]') as $div){
                            $divobj=new Crawler($div);                            
                            //var_dump($divobj->filterXPath('//div[contains(@class, "swiper-slide")]'));exit;
                            foreach($divobj->filterXPath('//div[contains(@class, "swiper-wrapper")]/div') as $divi){
                                $divitem=new Crawler($divi);
                                $this->info("\n-- Title : ".$divitem->filter('div.c-product-box__title.js-ab-not-app-incredible-product')->text('')."--".strpos($divitem->filter('div.c-product-box__title.js-ab-not-app-incredible-product')->text(''), 'کتاب'));
                                if(strpos($divitem->filter('div.c-product-box__title.js-ab-not-app-incredible-product')->text(''), 'کتاب')==0){
                                    $this->info('\n --- DIGI ID : '.$divitem->attr('data-id'));
				                    $relatedBook = BookDigi::where('recordNumber', 'dkp-'.$divitem->attr('data-id'))->first();
                                    if(!isset($relatedBook->id)){
                                           $relatedBook = $this->createDigiBook($divitem->attr('data-id'));            
                                    }
                                    $related = bookDigiRelated::firstOrCreate(array('book_id'=>$relatedBook->id));
                                    array_push($related_array, $related->id);
                                    $this->info(" \n ---------- ‌‌book  ".$book->recordNumber." : Related : ".$relatedBook->id."              ------------ ");
                                }
                            }
                        }
                        $book->related()->attach($related_array);
                    }

                    $bar->advance();
                    $recordNumber ++;
                }

            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  ".$this->argument('crawlerId')."     $startC  -> $endC         ------------ ");
            $bar->finish();
        }
    }

    public function createDigiBook($id)
    {
        $client = new Client(HttpClient::create(['timeout' => 30]));
        $productUrl="https://www.digikala.com/product/dkp-".$id."/";
        //$productUrl="https://www.digikala.com/product/dkp-5547149/%DA%A9%D8%AA%D8%A7%D8%A8-%D9%85%D8%B9%D8%AC%D8%B2%D9%87-%D8%B4%DA%A9%D8%B1%DA%AF%D8%B2%D8%A7%D8%B1%DB%8C-%D8%A7%D8%AB%D8%B1-%D8%B1%D8%A7%D9%86%D8%AF%D8%A7-%D8%A8%D8%B1%D9%86-%D8%A7%D9%86%D8%AA%D8%B4%D8%A7%D8%B1%D8%A7%D8%AA-%D9%86%DA%AF%DB%8C%D9%86-%D8%A7%DB%8C%D8%B1%D8%A7%D9%86";

        try {
            $this->info(" \n ---------- Try Get BOOK        ".$id."       ---------- ");
            $crawler = $client->request('GET', $productUrl);
            $status_code = $client->getInternalResponse()->getStatusCode();
        } catch (\Exception $e) {
            $crawler = null;
            $status_code = 500;
            $this->info(" \n ---------- Failed Get  ".$id."   $productUrl   ".$e->getMessage()."        ------------ ");
        }

        if($status_code == 200 ){
            $row = $crawler->filter('body');
            if($row->filter('h1.c-product__title')->text('')!=''){
                $authorsobj= array();
                $filtered= array();
                $filtered['title']=$row->filter('h1.c-product__title')->text('');
                $filtered['rate']=(int)$row->filter('div.c-product__engagement-rating')->text('');
                $filtered['price']=(int)$row->filter('div.c-product__seller-price-pure')->text('');
                $filtered['desc']=$row->filter('div.c-mask__text--product-summary')->text('');
                $filtered['images']=$row->filter('img.js-gallery-img')->attr('data-src');
                $filtered['recordNumber']='dkp-'.$row->filter('div.js-product-page')->attr('data-product-id');
                $filtered['features']="";
                //sellers   c-table-suppliers__body
                if($row->filter('div.c-c-table-suppliers__body')->text('')!=''){
                    $filtered['sellers'] = '';
                    foreach($row->filterXPath('//div[contains(@class, "c-c-table-suppliers__body")]/div') as $div){
                        $divobj=new Crawler($div);
                        if($divobj->filter('div.c-seller-rating__title')->text('')!=''){
                            $filtered['sellers'].=$divobj->filter('div.c-seller-rating__title')->text('')." :|: ";
                        }
                    }
                }

                foreach($row->filterXPath('//ul[contains(@class, "c-params__list")]/li') as $li){
                    $litag=new Crawler($li);
                    if($litag->filter('div.c-params__list-key')->text('')=='سایر توضیحات' || $litag->filter('div.c-params__list-key')->text('|')==''){
                        $filtered['features'].=$litag->filter('div.c-params__list-value')->text('')." :|: ";
                    }
                    if($litag->filter('div.c-params__list-key')->text('')=='وزن'){
                        $filtered['vazn']=$litag->filter('div.c-params__list-value')->text('');
                    }
                    if($litag->filter('div.c-params__list-key')->text('')=='نویسنده'){
                        $authorsobj = Author::firstOrCreate(array("d_name" => $litag->filter('div.c-params__list-value')->text('')));
                    }
                    if($litag->filter('div.c-params__list-key')->text('')=='قطع'){
                        $filtered['ghateChap']=$litag->filter('div.c-params__list-value')->text('');
                    }
                    if($litag->filter('div.c-params__list-key')->text('')=='نوع جلد'){
                        $filtered['jeld']=$litag->filter('div.c-params__list-value')->text('');
                    }
                    if($litag->filter('div.c-params__list-key')->text('')=='نوع کاغذ'){
                        $filtered['noekaghaz']=$litag->filter('div.c-params__list-value')->text('');
                    }
                    if($litag->filter('div.c-params__list-key')->text('')=='ناشر'){
                        $filtered['nasher']=$litag->filter('div.c-params__list-value')->text('');
                    }
                    if($litag->filter('div.c-params__list-key')->text('')=='مترجم'){
                        $filtered['partnerArray']=$litag->filter('div.c-params__list-value')->text('');
                    }
                    if($litag->filter('div.c-params__list-key')->text('')=='تعداد صفحه'){
                        $filtered['tedadSafe']=(int)$litag->filter('div.c-params__list-value')->text('');
                    }
                    if($litag->filter('div.c-params__list-key')->text('')=='شابک'){
                        $filtered['shabak']=$litag->filter('div.c-params__list-value')->text('');
                    }
                    if($litag->filter('div.c-params__list-key')->text('')=='رده‌بندی کتاب'){
                        $filtered['cat']=$litag->filter('div.c-params__list-value')->text('');
                    }
                    if($litag->filter('div.c-params__list-key')->text('')=='تعداد جلد'){
                        $filtered['count']=(int)$litag->filter('div.c-params__list-value')->text('');
                    }
                    if($litag->filter('div.c-params__list-key')->text('')=='نوع چاپ'){
                        $filtered['noechap']=$litag->filter('div.c-params__list-value')->text('');
                    }
                    
                    

                }
                $book = BookDigi::firstOrCreate($filtered);
                
                if(isset($authorsobj->id)){
                    $book->authors()->attach(array($authorsobj->id));
                    $this->info(" \n ---------- Attach Author Book   ".$authorsobj->id."  To ".$id."        ---------- ");
                }                  
            return $book;
            }
        }
        return false;
    }
}