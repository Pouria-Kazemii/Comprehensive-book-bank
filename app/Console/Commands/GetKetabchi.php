<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Author;
use App\Models\BookShahreKetabOnline;
use App\Models\Crawler as CrawlerM;
use App\Models\SiteCategories;

class GetKetabchi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:ketabchi {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get ketabchi Books from html website';

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
        //menu
        $timeout = 120;
        $url = 'https://ketabchi.com/api/v1/menus';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        $menu_content = curl_exec($ch);

        if (curl_errno($ch)) {
            $this->info(" \n ---------- Try Get MENU              ---------- ");
            echo 'error:' . curl_error($ch);
        } else {
            $menu_content = json_decode($menu_content);
            self::give_menu($menu_content->menus);
        }

        // die('stop');
        //category
        /*$cats = SiteCategories::where('domain', 'https://ketabchi.com/')->get();
        foreach ($cats as $cat) {
            // find count  books for loop
            $timeout = 120;
            $url = 'https://ketabchi.com/api/v1/products?genre=122&from=0&count=18&orderBy=sales&compact=true';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "value=" . $cat->cat_link . "&type=cat&sort=0&onsale=0");
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            $cat_page_content = curl_exec($ch);
            // dd($cat_book_content);
            if (curl_errno($ch)) {
                $this->info(" \n ---------- Try Get MENU              ---------- ");
                echo 'error:' . curl_error($ch);
            } else {
                $cat_page_content = json_decode($cat_page_content);
                $cat_pages = $cat_page_content->books->last_page;
            }

            $x = 1;
            while ($x <= $cat_pages) {
                $timeout = 120;
                $url = 'https://barkhatbook.com/api/quick?page=' . $x;
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "value=" . $cat->cat_link . "&type=cat&sort=0&onsale=0");
                curl_setopt($ch, CURLOPT_FAILONERROR, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_ENCODING, "");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                $cat_book_content = curl_exec($ch);
                if (curl_errno($ch)) {
                    $this->info(" \n ---------- Try Get MENU              ---------- ");
                    echo 'error:' . curl_error($ch);
                } else {
                    $cat_book_content = json_decode($cat_book_content);
                    foreach ($cat_book_content->books->data as $book) {
                        SiteBookLinks::firstOrCreate(array('domain' => 'https://barkhatbook.com/', 'book_links' => 'product/bk_' . $book->code . '/' . $book->title, 'status' => 0));
                    }
                }
                $x++;
            }
        }*/

        /*if (isset($newCrawler)) {
            $client = new Client(HttpClient::create(['timeout' => 30]));

            $bar = $this->output->createProgressBar(CrawlerM::$crawlerSize);
            $bar->start();

            $recordNumber = $startC;
            while ($recordNumber <= $endC) {

                try {
                    $this->info(" \n ---------- Try Get BOOK " . $recordNumber . "              ---------- ");
                    $crawler = $client->request('GET', 'https://ketabchi.com/product/' . $recordNumber, [
                        'headers' => [
                            'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                        ],
                    ]);
                    $status_code = $client->getInternalResponse()->getStatusCode();
                } catch (\Exception $e) {
                    $crawler = null;
                    $status_code = 500;
                    $this->info(" \n ---------- Failed Get  " . $recordNumber . "              ------------ ");
                }

                $this->info($crawler->filter('body main div.container')->count());
                $this->info($crawler->filterXPath('//*[@id="announcement"]')->count());
                $this->info($crawler->filterXPath('//*[@div="container mb-5"]')->count());

                $this->info($crawler->filterXPath('//div[contains(@calss, "container mb-5")]')->count() );
                die('stop');
                if ($status_code == 200 and $crawler->filter('body div.product')->count() > 0) {
                   die('inja');
                }
                $bar->advance();
                $recordNumber++;
            }
            $newCrawler->status = 2;
            $newCrawler->save();
            $this->info(" \n ---------- Finish Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ------------ ");
            $bar->finish();
        }*/
    }

    public static function give_menu($menu){
        dd($menu['label']);
        if(isset($menu->label) AND isset($menu->link)){
            dd($menu);
            SiteCategories::firstOrCreate(array('domain' => 'https://ketabchi.com/', 'cat_link' => $menu->link, 'cat_name' => $menu->label));
        }
        if(isset($menu->children)){
            foreach($menu->children as $child){
                self::give_menu($child);
            }
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

