<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Goutte\Client;

use Symfony\Component\HttpClient\HttpClient;

use Symfony\Component\DomCrawler\Crawler;

use App\Models\BookGisoom;

use App\Models\Author;

use App\Models\Crawler as CrawlerM;

class GetGissomBook extends Command

{

    /*

 The name and signature of the console command.


 @var string

*/

    protected $signature = 'get:gissomBook {crawlerId} {miss?}';

    /*

 The console command description.


 @var string

*/

    protected $description = 'crawle Gisoom book from site html';

    /*

 Create a new command instance.


 @return void

*/

    public function __construct()

    {

        parent::__construct();
    }

    /*

 Execute the console command.


 @return int

*/

    public function handle()

    {

        if ($this->argument('miss') && $this->argument('miss') == 1) {

            try {

                $lastCrawler = CrawlerM::where('name','Crawler-Gisoom-' . $this->argument('crawlerId'))->where('status', 1)->orderBy('end', 'ASC')->first();

                if (isset($lastCrawler->end)) {

                    $startC = $lastCrawler->start;

                    $endC = $lastCrawler->end;

                    $this->info(" \n ---------- Create Crawler " . $this->argument('crawlerId') . " $startC -> $endC ---------=-- ");

                    $newCrawler = $lastCrawler;
                }
            } catch (\Exception $e) {

                $this->info(" \n ---------- Failed Crawler " . $this->argument('crawlerId') . " ---------=-- ");
            }
        } else {

            try {

                $lastCrawler = CrawlerM::where('name','Crawler-Gisoom-' . $this->argument('crawlerId'))->orderBy('end', 'desc')->first();

                if (isset($lastCrawler->end)) $startC = $lastCrawler->end + 1;

                else $startC = 11000000;

                $endC = $startC + CrawlerM::$crawlerSize;

                $this->info(" \n ---------- Create Crawler " . $this->argument('crawlerId') . " $startC -> $endC ---------=-- ");

                $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-Gisoom-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
            } catch (\Exception $e) {

                $this->info(" \n ---------- Failed Crawler " . $this->argument('crawlerId') . " ---------=-- ");
            }
        }

        if (isset($newCrawler)) {
            $client = new Client(HttpClient::create(['timeout' => 30]));
            $bar = $this->output->createProgressBar(CrawlerM::$crawlerSize);
            $bar->start();
            $recordNumber = $startC;
            while ($recordNumber <= $endC) {
                try {
                    $this->info(" \n ---------- Try Get BOOK " . $recordNumber . " ---------- ");
                    // $crawler = $client->request('GET', 'http://188.253.2.66/proxy.php?url=https://www.gisoom.com/book/' . $recordNumber);
                    $crawler = $client->request('GET', 'https://www.gisoom.com/book/' . $recordNumber.'/book_name/');
                    $status_code = $client->getInternalResponse()->getStatusCode();
                } catch (\Exception $e) {
                    $crawler = null;
                    $status_code = 500;
                    $this->info(" \n ---------- Failed Get " . $recordNumber . " ---------=-- ");
                }

                if ($status_code == 200 && $crawler->filter('body')->text('') != '') {
                    $book = BookGisoom::where('recordNumber', $recordNumber)->firstOrNew();

                    $book->title = $crawler->filter('body div.bookinfocol div h1 a')->text();
                    foreach ($crawler->filter('body div.bookinfocol div.col') as $col) {
                        if (strpos($col->textContent, 'ناشر:') !== false) {
                            $book->nasher = str_replace('ناشر:', '', $col->textContent);
                        }
                        if (strpos($col->textContent, 'ویراستار:') !== false) {
                            $book->editor = str_replace('ویراستار:', '', $col->textContent);
                        }
                        if (strpos($col->textContent, 'ویراستاران:') !== false) {
                            $book->editor = str_replace('ویراستاران:', '', $col->textContent);
                        }
                        if (strpos($col->textContent, 'مترجمان:') !== false || strpos($col->textContent, 'مترجم:') !== false) {
                            $book->tarjome = true;
                        }
                        if (strpos($col->textContent, 'مترجمان:') !== false || strpos($col->textContent, 'مترجم:') !== false || strpos($col->textContent, 'مؤلف:') !== false || strpos($col->textContent, 'مؤلفان:') !== false) {
                            $colc = new Crawler($col);
                            foreach ($colc->filter('a') as $link) {
                                $authorObject = Author::firstOrCreate(array("d_name" => $link->textContent));
                                $authors[] = $authorObject->id;
                            }
                        }
                        if (strpos($col->textContent, 'زبان:') !== false) {
                            $book->lang = str_replace('زبان:', '', $col->textContent);
                        }
                        if (strpos($col->textContent, 'رده‌بندی دیویی:') !== false) {
                            $book->radeD = str_replace('رده‌بندی دیویی:', '', $col->textContent);
                        }
                        if (strpos($col->textContent, 'سال چاپ:') !== false) {
                            $book->saleNashr = enNumberKeepOnly(faCharToEN(str_replace('سال چاپ:', '', $col->textContent)));
                        }
                        if (strpos($col->textContent, 'نوبت چاپ:') !== false) {
                            $book->nobatChap = enNumberKeepOnly(faCharToEN(str_replace('نوبت چاپ:', '', $col->textContent)));
                        }
                        if (strpos($col->textContent, 'تیراژ:') !== false) {
                            $book->tiraj = enNumberKeepOnly(faCharToEN(str_replace('تیراژ:', '', $col->textContent)));
                        }
                        if (strpos($col->textContent, 'تعداد صفحات:') !== false) {
                            $book->tedadSafe = enNumberKeepOnly(faCharToEN(str_replace('تعداد صفحات:', '', $col->textContent)));
                        }
                        if (strpos($col->textContent, 'قطع و نوع جلد:') !== false) {
                            $book->ghateChap = str_replace('قطع و نوع جلد:', '', $col->textContent);
                        }

                        if (strpos($col->textContent, 'شابک 10 رقمی:') !== false) {
                            $book->shabak10 = str_replace('شابک 10 رقمی:', '', $col->textContent);
                        }
                        if (strpos($col->textContent, 'شابک 13 رقمی:') !== false) {
                            $book->shabak13 = str_replace('شابک 13 رقمی:', '', $col->textContent);
                        }
                        if (strpos($col->textContent, 'توضیح کتاب:') !== false) {
                            $book->desc = str_replace('توضیح کتاب:', '', $col->textContent);
                        }
                    }
                    $categories = array();
                    foreach ($crawler->filter("div.nav-wrapper a") as $catLinks) {
                        if ($catLinks->textContent != 'کتاب') $categories[] = $catLinks->textContent;
                    }
                    $book->price = 0;
                    $dibcontent = $crawler->filter('body a.iwantbook span.dib')->first()->text('');
                    $dbcontent = $crawler->filter('body a.iwantbook span.db')->first()->text('');
                    if ($dibcontent != '') {
                        $book->price = enNumberKeepOnly(faCharToEN($dibcontent));
                    } elseif ($dbcontent != '') {
                        $book->price = enNumberKeepOnly(faCharToEN($dbcontent));
                    }
                    $book->catText = implode("-|-", $categories);
                    $book->image = $crawler->filter('body img.cls3')->attr('src');
                    $book->recordNumber = $recordNumber;
                    $book->save();
                    $this->info(" \n ---------- Inserted Book " . $recordNumber . " ---------- ");
                    if (count($authors) > 0) {
                        $book->authors()->attach($authors);
                        $this->info(" \n ---------- Attach Author Book " . $recordNumber . " ---------- ");
                    }
                }
                $bar->advance();
                $recordNumber++;
            }
            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler " . $this->argument('crawlerId') . " $startC -> $endC ---------=-- ");
            $bar->finish();
        }
    }
}
