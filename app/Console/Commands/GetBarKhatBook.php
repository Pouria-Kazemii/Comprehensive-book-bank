<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\BookBarkhatBook;
use App\Models\Crawler as CrawlerM;
use App\Models\SiteBookLinks;
use App\Models\SiteCategories;

class GetBarKhatBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:barkhatbook {crawlerId} {runNumber?}';

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
        if ($this->argument('runNumber') && $this->argument('runNumber') == 1) {
            $timeout = 120;
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
                    SiteCategories::firstOrCreate(array('domain' => 'https://barkhatbook.com/', 'cat_link' => $cat->id, 'cat_name' => $cat->name));
                }
            }

            $cats = SiteCategories::where('domain', 'https://barkhatbook.com/')->get();
            foreach ($cats as $cat) {
                // find count  books for loop
                $timeout = 120;
                $url = 'https://barkhatbook.com/api/quick?page=1';
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
            }
        } else {


            $cats = SiteCategories::where('domain', 'https://barkhatbook.com/')->get();

            foreach ($cats as $cat) {
                // find count  books for loop
                $timeout = 120;
                $url = 'https://barkhatbook.com/api/quick?page=1';
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
            }
        }

        SiteBookLinks::where('domain', 'https://barkhatbook.com/')->where('status', 0)->chunk(1, function ($bookLinks) {
            foreach ($bookLinks as $bookLink) {
                $client = new Client(HttpClient::create(['timeout' => 30]));
                $this->info($bookLink->book_links);
                try {
                    $this->info(" \n ---------- Try Get BOOK " . $bookLink->book_links . "              ---------- ");
                    $crawler = $client->request('GET', 'https://barkhatbook.com/' . $bookLink->book_links, [
                        'headers' => [
                            'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                        ],
                    ]);

                    $status_code = $client->getInternalResponse()->getStatusCode();
                } catch (\Exception $e) {
                    $crawler = null;
                    $status_code = 500;
                    $this->info(" \n ---------- Failed Get  " . $bookLink->book_links . "              ---------=-- ");
                }
                if ($status_code == 200 and $crawler->filter('body div.container-fluid single-product')->count() > 0) {
                    $book_json_info = $crawler->filter('body div.container-fluid single-product')->attr('v-bind:product_lv');
                    $book_info = json_decode($book_json_info);

                    if (isset($book_info) and !empty($book_info)) {
                        $partner =  array();
                        $book = BookBarkhatBook::where('recordNumber', $book_info->product->code)->firstOrNew();
                        $book->recordNumber = $book_info->product->code;

                        //  = $book_info->product->id;
                        // = $book_info->product->title;
                        $book->weight  = $book_info->product->weight;
                        //  = $book_info->product->productTypeId;
                        //  = $book_info->product->code;
                        $book->price  = $book_info->product->price;
                        //  = $book_info->product->discount;
                        //  = $book_info->product->specialEnd;
                        //  = $book_info->product->isSpecial;
                        //  = $book_info->product->catId;
                        //   = $book_info->product->bio;
                        //  = $book_info->product->onSale;
                        //  = $book_info->product->scoreCount;
                        //  = $book_info->product->score;
                        //  = $book_info->product->hasSendFree;
                        //  = $book_info->product->audio_url;
                        //  = $book_info->product->pdf_url;
                        if (isset($book_info->product->category) and !empty($book_info->product->category)) {
                            $book_cats = '';
                            foreach ($book_info->product->category as $category) {
                                if (isset($category->name) and !empty($category->name)) {
                                    $book_cats = $book_cats . "-|-" . $category->name;
                                }
                            }
                            $book->cats = $book_cats;
                        }
                        //  = $book_info->product->tags;
                        //  = $book_info->product->category->id;
                        //  = $book_info->product->category->name;
                        //  = $book_info->product->books->id;
                        if (isset($book_info->product->books) and !empty($book_info->product->books)) {
                            foreach ($book_info->product->books as $book_item) {
                                $book->title  = $book_item->title;
                                $book->saleNashr = $book_item->year;
                                $book->nobatChap  = $book_item->published;
                                $book->shabak  = $book_item->isbn;
                                $book->desc  = $book_item->mainSubject;
                                $book->subTopic = $book_item->subTopic;
                                if (isset($book_item->authorName) and !empty($book_item->authorName)) {
                                    $partner[0]['roleId'] = 1;
                                    $partner[0]['name'] = $book_item->authorName;
                                }
                                if (isset($book_item->translatorName) and !empty($book_item->translatorName)) {
                                    $book->translate = 1;
                                    $partner[1]['roleId'] = 2;
                                    $partner[1]['name'] = $book_item->translatorName;
                                }
                                if (isset($book_item->publisherName) and !empty($book_item->publisherName)) {
                                    $book->nasher = $book_item->publisherName;
                                }

                                //  = $book_item->onSale;
                                //  = $book_item->availableCount;
                                //  = $book_item->productId;
                                $book->tedadSafe  = $book_item->pages;
                                $book->jeld  = $book_item->bookCover;
                                $book->ghateChap  = $book_item->bookSize;
                            }
                        }

                        if (isset($book_info->product->images) and !empty($book_info->product->images)) {
                            foreach ($book_info->product->images as $image) {
                                $book->images . '=|=' . $image->imageUrl;
                            }
                        }
                        //  = $book_info->product->images->id;
                        //  = $book_info->product->images->imageUrl;
                        //  = $book_info->product->images->alt;
                        //  = $book_info->product->images->productId;
                        $book->partnerArray = json_encode($partner, JSON_UNESCAPED_UNICODE);
                        $book->save();
                        SiteBookLinks::where('domain', 'https://barkhatbook.com/')->where('book_links', $bookLink->book_links)->update(['status' => 1]);
                    }
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
