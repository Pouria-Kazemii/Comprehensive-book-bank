<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Author;
use App\Models\BookShahreKetabOnline;
use App\Models\Crawler as CrawlerM;
use App\Models\SiteBookLinks;
use App\Models\siteCategories;

class GetBarKhatBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:barkhatbook {crawlerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get barkhatbook book Books from html website';

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
        /* $timeout = 120;
        $url = 'https://barkhatbook.com/api/menu';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
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
            // dd($menu_content->cats);
            foreach ($menu_content->cats as $cat) {
                siteCategories::firstOrCreate(array('domain' => 'https://barkhatbook.com/', 'cat_link' => $cat->id, 'cat_name' => $cat->name));
            }
        }*/

       /* $cats = siteCategories::where('domain', 'https://barkhatbook.com/')->get();

        foreach ($cats as $cat) {
            // find count  books for loop
            $timeout = 120;
            $url = 'https://barkhatbook.com/api/quick?page=1';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "value=".$cat->cat_link."&type=cat&sort=0&onsale=0");
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
                curl_setopt($ch, CURLOPT_POSTFIELDS, "value=".$cat->cat_link."&type=cat&sort=0&onsale=0");
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

        SiteBookLinks::where('domain','https://barkhatbook.com/')->where('status', 0)->chunk(1, function ($books) {
            foreach($books as $book){
                $client = new Client(HttpClient::create(['timeout' => 30]));
                $this->info($book->book_links);
                try {
                    $this->info(" \n ---------- Try Get BOOK " . $book->book_links . "              ---------- ");
                    $crawler = $client->request('GET', 'https://barkhatbook.com/' . $book->book_links, [
                        'headers' => [
                            'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                        ],
                    ]);
                   
                    $status_code = $client->getInternalResponse()->getStatusCode();
                } catch (\Exception $e) {
                    $crawler = null;
                    $status_code = 500;
                    $this->info(" \n ---------- Failed Get  " . $book->book_links . "              ---------=-- ");
                }
                $this->info($crawler->filterXPath('//*[@class="arm-circle"]')->count());
                $this->info($crawler->filterXPath('//nav[contains(@class, "navbar navbar-expand arm-bg-cream arm-pd-u-15 arm-fix-style")]')->count());


                if ($status_code == 200 and $crawler->filterXPath('//*[@id="arm-app"]')->count() > 0) {
                    // image arm-pointer
                    // if ($crawler->filter('body div.ProductDetails div.ProductInfo div.Image div.book-wrap img.book-image')->count() > 0) {
                    //     $book->images  = 'https://shahreketabonline.com' . $crawler->filter('body div.ProductDetails div.ProductInfo div.Image div.book-wrap img.book-image')->attr('src');
                    // }



                }



            }

        });


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
