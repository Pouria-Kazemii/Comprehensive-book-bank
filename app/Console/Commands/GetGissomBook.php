<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;


class GetGissomBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:gissomBook';

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
        $recordNumber = 11000000;
        $crawler = $client->request('GET', 'https://www.gisoom.com/book/11000000/');

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
            if(strpos($col->textContent, 'مترجمان:') !== false){
                $filtered['tarjome'] = true;

            }
            if(strpos($col->textContent, 'مترجمان:') !== false || strpos($col->textContent, 'مؤلف:') !== false || strpos($col->textContent, 'مؤلفان:') !== false){
                $colc = new Crawler($col);
                foreach($colc->filter('a') as $link){
                    $clink = new Crawler($link);
                    $authors[]=$clink->link()->textContent;
                }
            }
            if(strpos($col->textContent, 'زبان:') !== false){
                $filtered['lang'] = str_replace('زبان:','',$col->textContent);
            }
            if(strpos($col->textContent, 'رده‌بندی دیویی:') !== false){
                $filtered['radeD'] = str_replace('رده‌بندی دیویی:','',$col->textContent);
            }
            if(strpos($col->textContent, 'سال چاپ:') !== false){
                $filtered['saleNashr'] = str_replace('سال چاپ:','',$col->textContent);
            }
            if(strpos($col->textContent, 'نوبت چاپ:') !== false){
                $filtered['nobatChap'] = str_replace('نوبت چاپ:','',$col->textContent);
            }
            if(strpos($col->textContent, 'تیراژ:') !== false){
                $filtered['tiraj'] = str_replace('تیراژ:','',$col->textContent);
            }
            if(strpos($col->textContent, 'تعداد صفحات:') !== false){
                $filtered['tedadSafe'] = str_replace('تعداد صفحات:','',$col->textContent);
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
        // foreach ($crawler->filter('body div.bookinfocol div.s6 a') as $link){
        //     $clink = new Crawler($link);
        //    echo "\n links : "; print_r($clink->link());
        //    echo "\n href : "; print($clink->link()->getUri());
        // }

        $filtered['image'] = $crawler->filter('body img.cls3')->attr('src');
        $filtered['recordNumber'] = $recordNumber;

        print_r($authors);
        print_r($filtered);
        exit;
    }
}
