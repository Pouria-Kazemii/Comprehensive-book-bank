<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Author;
use App\Models\BookShahreKetabOnline;
use App\Models\Crawler as CrawlerM;

class GetShahreKetabOnline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:shahreketabonline {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get shahreketabonline Books from html website';

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
        $Last_id = (isset(BookShahreKetabOnline::whereNotNull('title')->orderBy('recordNumber', 'DESC')->first()->recordNumber)) ? BookShahreKetabOnline::whereNotNull('title')->orderBy('recordNumber', 'DESC')->first()->recordNumber : 0;
        try {
            $startC = $Last_id + 1;
            $endC = $startC + 100;
            $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-shahreketabonline-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 3));
        } catch (\Exception $e) {
            $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
        }

        if (isset($newCrawler)) {
            $client = new Client(HttpClient::create(['timeout' => 30]));

            $bar = $this->output->createProgressBar(CrawlerM::$crawlerSize);
            $bar->start();

            $recordNumber = $startC;
            while ($recordNumber <= $endC) {

                try {
                    $this->info(" \n ---------- Try Get BOOK " . $recordNumber . "              ---------- ");
                    $crawler = $client->request('GET', 'https://shahreketabonline.com/Products/Details/' . $recordNumber, [
                        'headers' => [
                            'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                        ],
                    ]);
                    $status_code = $client->getInternalResponse()->getStatusCode();
                } catch (\Exception $e) {
                    $crawler = null;
                    $status_code = 500;
                    $this->info(" \n ---------- Failed Get  " . $recordNumber . "              ---------=-- ");
                }

                if ($status_code == 200 and $crawler->filter('body div.ProductDetails')->count() > 0) {
                    $book_cats = '';
                    foreach ($crawler->filter('body ol.breadcrumb li a') as $cat) {
                        if (isset($book_cats) and !empty($book_cats)) {
                            $book_cats = $book_cats . "-|-" . ltrim(rtrim(self::convert_arabic_char_to_persian($cat->textContent)));
                        } else {
                            $book_cats = ltrim(rtrim(self::convert_arabic_char_to_persian($cat->textContent)));
                        }
                    }
                    if (isset($book_cats)) $cats_arr = explode('-|-', $book_cats);

                    if (!in_array('نوشت افزار', $cats_arr) && !in_array('محصولات فرهنگی', $cats_arr) && !in_array('صنایع دستی', $cats_arr) && !in_array('هنری', $cats_arr)) {

                        $book = BookShahreKetabOnline::where('recordNumber', $recordNumber)->firstOrNew();

                        $book->recordNumber = $recordNumber;
                        $book->cats = $book_cats;


                        // image
                        if ($crawler->filter('body div.ProductDetails div.ProductInfo div.Image div.book-wrap img.book-image')->count() > 0) {
                            $book->images  = 'https://shahreketabonline.com' . $crawler->filter('body div.ProductDetails div.ProductInfo div.Image div.book-wrap img.book-image')->attr('src');
                        }
                        if ($crawler->filter('body div.ProductDetails div.ProductInfo div.Image div.book-wrap div.OtherImages')->count() > 0) {
                            $book->images =  $book->images . '=|=' . 'https://shahreketabonline.com' . $crawler->filter('body div.ProductDetails div.ProductInfo div.Image div.book-wrap div.OtherImages a img')->attr('src');
                        }

                        // price 
                        if ($crawler->filter('body div.ProductDetails div.ProductInfo div.AddProductToCart div.Price')->count() > 0) {
                            $price  = enNumberKeepOnly(faCharToEN($crawler->filter('body div.ProductDetails div.ProductInfo div.AddProductToCart div.Price')->text()));
                            if (strlen($price) < 10) {
                                $book->price = $price;
                            }
                        }

                        // desc 
                        if ($crawler->filter('body div.ProductDetails div.description')->count() > 0) {
                            $book->Desc  = $crawler->filter('body div.ProductDetails div.description')->text();
                        }

                        // details
                        $book->title  = $crawler->filter('body div.ProductDetails div.ProductInfo div.Details h1')->text();

                        $partner =  array();
                        foreach ($crawler->filter("body div.ProductDetails div.ProductInfo div.Details div.mt-1 div.Attributes div.Attribute") as $trTable) {
                            $trObj = new Crawler($trTable);

                            switch ($trObj->filter('div.LightText')->first()->text()) {
                                case 'شابک:':
                                    $book->shabak = $trObj->filter('div.LightText')->nextAll()->text();
                                    break;
                                case 'موضوع:':
                                    $book->subject = $trObj->filter('div.LightText')->nextAll()->text();
                                    break;
                                case 'نویسنده:':
                                    if ($trObj->filter('div.LightText')->nextAll()->text() != '') {
                                        // $this->info($trObj->filter('div.LightText')->nextAll()->text());
                                        // foreach($trObj->filter('a div') as $link){
                                        // $authorObject = Author::firstOrCreate(array("d_name" => $trObj->filter('div.LightText')->nextAll()->text()));
                                        // $authors[] = $authorObject->id;

                                        $partner[0]['roleId'] = 1;
                                        $partner[0]['name'] = $trObj->filter('div.LightText')->nextAll()->text();
                                        // }
                                    }
                                    break;
                                case 'مترجم:':
                                    if ($trObj->filter('div.LightText')->nextAll()->text() != '') {
                                        $book->translate = 1;
                                        // foreach($trObj->filter('a') as $link){
                                        // $authorObject = Author::firstOrCreate(array("d_name" => $trObj->filter('div.LightText')->nextAll()->text()));
                                        // $authors[] = $authorObject->id;
                                        $partner[1]['roleId'] = 2;
                                        $partner[1]['name'] = $trObj->filter('div.LightText')->nextAll()->text();
                                        // }
                                    }
                                    break;
                                case 'انتشارات:':
                                    $book->nasher = $trObj->filter('div.LightText')->nextAll()->text();
                                    break;
                                    // case 'نوبت چاپ':
                                    //     $book->nobatChap = $trObj->filter('div.LightText')->nextAll()->text();
                                    //     break;
                                case 'شماره چاپ:':
                                    $book->nobatChap = $trObj->filter('div.LightText')->nextAll()->text();
                                    break;
                                case 'زبان:':
                                    $book->lang = $trObj->filter('div.LightText')->nextAll()->text();
                                    break;
                                case 'قطع:':
                                    $book->ghateChap = $trObj->filter('div.LightText')->nextAll()->text();
                                    break;
                                case 'جلد:':
                                    $book->jeld = $trObj->filter('div.LightText')->nextAll()->text();
                                    break;
                                case 'تعداد صفحه:':
                                    $book->tedadSafe = enNumberKeepOnly(faCharToEN($trObj->filter('div.LightText')->nextAll()->text()));
                                    break;
                                case 'طول:':
                                    $book->length = faCharToEN($trObj->filter('div.LightText')->nextAll()->text());
                                    break;
                                case 'عرض:':
                                    $book->width = faCharToEN($trObj->filter('div.LightText')->nextAll()->text());
                                    break;
                                case 'ارتفاع:':
                                    $book->height = faCharToEN($trObj->filter('div.LightText')->nextAll()->text());
                                    break;
                                case 'وزن:':
                                    $book->vazn = enNumberKeepOnly(faCharToEN($trObj->filter('div.LightText')->nextAll()->text()));
                                    break;
                            }
                        }

                        $book->partnerArray = json_encode($partner, JSON_UNESCAPED_UNICODE);


                        //tags
                        if ($crawler->filter("body div.ProductDetails div.Tags")->count() > 0) {
                            foreach ($crawler->filter("body div.ProductDetails div.Tags a") as $tag) {
                                $tagObj = new Crawler($tag);
                                $book->tags = $book->tags . $tagObj->filter('div.Tag')->text();
                                // $this->info($tagObj->filter('div.Tag')->text());
                            }
                        }


                        $book->save();
                    } else {
                        $this->info(" \n ---------- Rejected Book   " . $recordNumber . "           ---------- ");
                    }
                }
                $bar->advance();
                $recordNumber++;
            }
            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
            $bar->finish();
        }
    }
    public static function convert_arabic_char_to_persian($string)
    {
        $string = str_replace("ي", "ی", $string);
        $string = str_replace("ك", "ک", $string);
        $string = str_replace("ة", "ه", $string);
        return $string;
    }

    /*  delete name space */
    public static function remove_half_space_from_string($string)
    {
        $string = urlencode($string);
        $string = str_replace('%E2%80%8C', ' ', $string);
        $string = urldecode($string);
        return $string;
    }

    public static function convert_arabic_num_to_english($string)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];

        $num = range(0, 9);
        $convertedPersianNums = str_replace($persian, $num, $string);
        $englishNumbersOnly = str_replace($arabic, $num, $convertedPersianNums);

        return $englishNumbersOnly;
    }

    public static function validateIsbn($isbn) //correction  isbn
    {
        $isbn = self::convert_arabic_num_to_english($isbn);
        $isbn = trim($isbn, ' ');
        $isbn = rtrim($isbn, ' ');
        $isbn = ltrim($isbn, ' ');

        $isbn = trim($isbn, '');
        $isbn = rtrim($isbn, '');
        $isbn = ltrim($isbn, '');

        $isbn = trim($isbn, '.');
        $isbn = rtrim($isbn, '.');

        $isbn = ltrim($isbn, ',');
        $isbn = ltrim($isbn, ',');

        $isbn = ltrim($isbn, '.');
        $isbn = ltrim($isbn, '"');

        $isbn = str_replace(" ", "", $isbn);
        $isbn = str_replace(".", "", $isbn);
        $isbn = str_replace("،", "", $isbn);
        // $isbn = str_replace("-", "", $isbn);
        $isbn = str_replace("+", "", $isbn);

        $isbn = str_replace(",", "", $isbn);
        $isbn = str_replace("،", "", $isbn);
        $isbn = str_replace("#", "", $isbn);
        $isbn = str_replace('"', "", $isbn);

        return $isbn;
    }
}
