<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\BookGisoom;
use App\Models\Author;


class GetGissomBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:gissomBook {rn}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'crawle Gisoom book from site html';

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
        $recordNumber = $this->argument('rn');
        $crawler = $client->request('GET', 'https://www.gisoom.com/book/'.$recordNumber);

        // title $crawler->filter('body div.bookinfocol div h1 a')[0]->textContent)
        // all data $crawler->filter('body div.bookinfocol div.col'))
        // image $crawler->filter('body img.cls3')->image();
        $filtered= array();
        $title = $crawler->filter('body div.bookinfocol div h1 a');
        $filtered['title'] = $title->text();

        foreach($crawler->filter('body div.bookinfocol div.col') as $col){
            if(strpos($col->textContent, 'ناشر:') !== false){
                $filtered['nasher'] =  str_replace('ناشر:','',$col->textContent);

            }
            if(strpos($col->textContent, 'ویراستار:') !== false){
                $filtered['editor'] = str_replace('ویراستار:','',$col->textContent);
            }
            if(strpos($col->textContent, 'ویراستاران:') !== false){
                $filtered['editor'] = str_replace('ویراستاران:','',$col->textContent);
            }
            if(strpos($col->textContent, 'مترجمان:') !== false || strpos($col->textContent, 'مترجم:') !== false){
                $filtered['tarjome'] = true;

            }
            if(strpos($col->textContent, 'مترجمان:') !== false || strpos($col->textContent, 'مترجم:') !== false || strpos($col->textContent, 'مؤلف:') !== false || strpos($col->textContent, 'مؤلفان:') !== false){
                $colc = new Crawler($col);
                foreach($colc->filter('a') as $link){
                    $authorObject = Author::firstOrCreate(array("d_name" => $link->textContent));
                    $authors[]=$authorObject->id;
                }
            }
            if(strpos($col->textContent, 'زبان:') !== false){
                $filtered['lang'] = str_replace('زبان:','',$col->textContent);
            }
            if(strpos($col->textContent, 'رده‌بندی دیویی:') !== false){
                $filtered['radeD'] = str_replace('رده‌بندی دیویی:','',$col->textContent);
            }
            if(strpos($col->textContent, 'سال چاپ:') !== false){
                $filtered['saleNashr'] = enNumberKeepOnly(faCharToEN(str_replace('سال چاپ:','',$col->textContent)));
            }
            if(strpos($col->textContent, 'نوبت چاپ:') !== false){
                $filtered['nobatChap'] = enNumberKeepOnly(faCharToEN(str_replace('نوبت چاپ:','',$col->textContent)));
            }
            if(strpos($col->textContent, 'تیراژ:') !== false){
                $filtered['tiraj'] = enNumberKeepOnly(faCharToEN(str_replace('تیراژ:','',$col->textContent)));
            }
            if(strpos($col->textContent, 'تعداد صفحات:') !== false){
                $filtered['tedadSafe'] = enNumberKeepOnly(faCharToEN(str_replace('تعداد صفحات:','',$col->textContent)));
            }
            if(strpos($col->textContent, 'قطع و نوع جلد:') !== false){
                $filtered['ghateChap'] = str_replace('قطع و نوع جلد:','',$col->textContent);
            }
            if(strpos($col->textContent, 'شابک 10 رقمی:') !== false){
                $filtered['shabak10'] = str_replace('شابک 10 رقمی:','',$col->textContent);
            }
            if(strpos($col->textContent, 'شابک 13 رقمی:') !== false){
                $filtered['shabak13'] = str_replace('شابک 13 رقمی:','',$col->textContent);
            }
            if(strpos($col->textContent, 'توضیح کتاب:') !== false){
                $filtered['desc'] = str_replace('توضیح کتاب:','',$col->textContent);
            }
        }

        $categories = array();
        foreach($crawler->filter("div.nav-wrapper a") as $catLinks){
            if($catLinks->textContent != 'کتاب')$categories[]= $catLinks->textContent;
        }

        $filtered['price'] = 0;
        $dibcontent = $crawler->filter('body a.iwantbook span.dib')->first()->text('');
        $dbcontent = $crawler->filter('body a.iwantbook span.db')->first()->text('');
        if($dibcontent != ''){
            $filtered['price'] = enNumberKeepOnly(faCharToEN($dibcontent));
        }elseif($dbcontent != ''){
            $filtered['price'] = enNumberKeepOnly(faCharToEN($dbcontent));
        }

        $filtered['catText'] = implode("-|-", $categories);

        $filtered['image'] = $crawler->filter('body img.cls3')->attr('src');
        $filtered['recordNumber'] = $recordNumber;

        $book = BookGisoom::firstOrCreate($filtered);
        $this->info(" \n ---------- Inserted Book   ".$recordNumber."           ---------- ");
        if(count($authors)>0){
             $book->authors()->attach($authors);
             $this->info(" \n ---------- Attach Author Book   ".$recordNumber."          ---------- ");
        }

        print_r($categories);
        print_r($authors);
        print_r($filtered);
        exit;
    }
}
