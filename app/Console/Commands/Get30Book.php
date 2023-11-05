<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Book30book;
use App\Models\Author;
use App\Models\Crawler as CrawlerM;

class get30Book extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:30book {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get 30book Books from html website';

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
        $Last_id = Book30book::whereNotNull('title')->orderBy('recordNumber','DESC')->first()->recordNumber;
        try{
            $startC = $Last_id + 1;
            $endC = $startC + 100;
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
                $crawler = $client->request('GET', 'https://www.30book.com/Book/'.$recordNumber , [
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

                foreach ($crawler->filter('body div.body-content a.indigo') as $cat){
                    if(isset($filtered['cats']))$filtered['cats']= $filtered['cats']."-|-".$cat->textContent;
                    else $filtered['cats']= $cat->textContent;
                }
                if(isset($filtered['cats']))$cats = explode('-|-', $filtered['cats']);

                $filtered['title']  = $crawler->filter('body div.body-content h1')->text('');
                $filtered['nasher'] = $crawler->filter('body div.body-content h2 a.site-c')->text('');
                if($crawler->filter('body div.body-content span.price-slash')->count() > 0)$filtered['price']  = enNumberKeepOnly(faCharToEN($crawler->filter('body div.body-content span.price-slash')->text('')));
                if(!isset($filtered['price'])){
                    if(strpos($crawler->filter('body div.body-content span.red-text')->text(''), 'ریال') !== false){
                        $filtered['price'] = enNumberKeepOnly(faCharToEN($crawler->filter('body div.body-content span.red-text')->text('')));
                    }
                }
                $filtered['image']  = 'https://www.30book.com'.$crawler->filter('body div.body-content div.card img.rounded')->attr('src');
                $filtered['desc']  = $crawler->filter('body div.body-content p.line-h-2')->text('');
                $filtered['recordNumber']  = $recordNumber;

                $authors = array();
                $koodat = false;
                $save = false;
                foreach ($crawler->filter("body div.body-content table.table-striped tr") as $trTable){
                    $trObj = new Crawler($trTable);

                    switch($trObj->filter('td')->first()->text('')){
                        case 'شابک':
                            $filtered['shabak'] = $trObj->filter('td')->nextAll()->text('');
                        break;
                        case 'دسته بندی':
                            if($trObj->filter('td')->nextAll()->text('') == 'کودک و نوجوان')$koodat = true;
                        break;
                        case 'موضوع فرعی':
                            if(strpos($trObj->filter('td')->nextAll()->text(''), 'داستان')!==false  && $koodat )$save = true;
                            if(strpos($trObj->filter('td')->nextAll()->text(''), 'کمیک و داستان مصور')!==false  && $koodat )$save = true;
                            if(strpos($trObj->filter('td')->nextAll()->text(''), 'قصه و شعر')!==false  && $koodat )$save = true;
                            if(strpos($trObj->filter('td')->nextAll()->text(''), 'علمی تخیلی')!==false  && $koodat )$save = true;
                        break;
                        case 'نویسنده':
                            if($trObj->filter('td')->nextAll()->text('') != ''){
                                foreach($trObj->filter('a') as $link){
                                    $authorObject = Author::firstOrCreate(array("d_name" => $link->textContent));
                                    $authors[]=$authorObject->id;
                                }
                            }
                        break;
                        case 'مترجم':
                            if($trObj->filter('td')->nextAll()->text('') != ''){
                                $filtered['tarjome'] = true;
                                foreach($trObj->filter('a') as $link){
                                    $authorObject = Author::firstOrCreate(array("d_name" => $link->textContent));
                                    $authors[]=$authorObject->id;
                                }
                            }
                        break;
                        case 'سال انتشار':
                            $filtered['saleNashr'] = $trObj->filter('td')->nextAll()->text('');
                        break;
                        case 'نوبت چاپ':
                            $filtered['nobatChap'] = $trObj->filter('td')->nextAll()->text('');
                        break;
                        case 'زبان کتاب':
                            $filtered['lang'] = $trObj->filter('td')->nextAll()->text('');
                        break;
                        case 'قطع کتاب':
                            $filtered['ghateChap'] = $trObj->filter('td')->nextAll()->text('');
                        break;
                        case 'جلد کتاب':
                            $filtered['jeld'] = $trObj->filter('td')->nextAll()->text('');
                        break;
                        case 'تعداد صفحه':
                            $filtered['tedadSafe'] = enNumberKeepOnly(faCharToEN($trObj->filter('td')->nextAll()->text('')));
                        break;
                        case 'وزن':
                            $filtered['vazn'] = enNumberKeepOnly(faCharToEN($trObj->filter('td')->nextAll()->text('')));
                        break;

                    }
                }

                foreach ($crawler->filter("body div.body-content li.breadcrumb-item a") as $linkcat){
                    if($linkcat->textContent != 'خانه'){
                        if(isset($filtered['catPath']))$filtered['catPath'] = $filtered['catPath']."-|-".$linkcat->textContent;
                        else $filtered['catPath'] = $linkcat->textContent;
                    }
                }
                if($filtered['title'] =='')$save =false;

                if((!in_array('کودک و نوجوان', $cats) && !in_array('بازی و اسباب بازی', $cats) && !in_array('سرگرمی', $cats) && !in_array('کالای فرهنگی', $cats)) || $save){

                    $book = Book30book::firstOrCreate($filtered);
                    $this->info(" \n ---------- Inserted Book   ".$recordNumber."           ---------- ");
                    if(count($authors)>0){
                        $book->authors()->attach($authors);
                        $this->info(" \n ---------- Attach Author Book   ".$recordNumber."          ---------- ");
                    }

                }else{
                    $this->info(" \n ---------- Rejected Book   ".$recordNumber."           ---------- ");
                }
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
