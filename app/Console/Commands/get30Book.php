<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class get30Book extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:30book {Id}';

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
        $client = new Client(HttpClient::create(['timeout' => 30]));
        $recordNumber = $this->argument('Id');

        try {
            $this->info(" \n ---------- Try Get BOOK ".$recordNumber."              ---------- ");
            $crawler = $client->request('GET', 'https://www.30book.com/Book/'.$recordNumber);
            $status_code = $client->getInternalResponse()->getStatusCode();
        }catch (\Exception $e) {
            $crawler = null;
            $status_code = 500;
            $this->info(" \n ---------- Failed Get  ".$recordNumber."              ---------=-- ");
        }

        if($status_code == 200){

            $filtered= array();

            foreach ($crawler->filter('body div.body-content a.indigo') as $cat){
                if(isset($filtered['cats']))$filtered['cats']= $filtered['cats']."-|-".$cat->textContent;
                else $filtered['cats']= $cat->textContent;
            }
            $cats = explode('-|-', $filtered['cats']);


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
                                $authors[] = $link->textContent;
                            }
                        }
                    break;
                    case 'مترجم':
                        if($trObj->filter('td')->nextAll()->text('') != ''){
                            $filtered['tarjome'] = true;
                            foreach($trObj->filter('a') as $link){
                                $authors[] = $link->textContent;
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
            if((!in_array('کودک و نوجوان', $cats) && !in_array('بازی و اسباب بازی', $cats) && !in_array('سرگرمی', $cats) && !in_array('کالای فرهنگی', $cats)) || $save){

                print_r($authors);
                print_r($filtered);
            }
        }
    }
}
