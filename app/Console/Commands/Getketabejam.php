<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\BookKetabejam;
use App\Models\Crawler as CrawlerM;
use App\Models\SiteBookLinks;
use App\Models\SiteCategories;
use Illuminate\Support\Facades\DB;

class Getketabejam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:ketabejam {crawlerId} {runNumber?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get ketabejam book Books from html website';

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
        /*if ($this->argument('runNumber') && $this->argument('runNumber') == 1) {
            // menu
            $client = new Client(HttpClient::create(['timeout' => 30]));
            try {
                $this->info(" \n ---------- Try Get category menu             ---------- ");
                $crawler = $client->request('GET', 'https://ketabejam.com/', [
                    'headers' => [
                        'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                    ],
                ]);

                $status_code = $client->getInternalResponse()->getStatusCode();
            } catch (\Exception $e) {
                $crawler = null;
                $status_code = 500;
                $this->info(" \n ---------- Failed Get                ---------=-- ");
            }


            if ($status_code == 200 and $crawler->filterXPath('//*[@id="main-container"]')->count() > 0) {
                foreach ($crawler->filter('body header div.ct-sticky-container div.ct-container nav.header-menu-1 ul li') as $menu) {
                    $menuLi = new Crawler($menu);
                    if ($menuLi->filter('ul li div.entry-content div.wp-block-kadence-tabs div.kt-tabs-wrap div.kt-tabs-content-wrap div.wp-block-kadence-tab div.kt-tab-inner-content-inner div.wp-block-stackable-columns div.stk-inner-blocks div')->count() > 0) {
                        foreach ($menuLi->filter('ul li div.entry-content div.wp-block-kadence-tabs div.kt-tabs-wrap div.kt-tabs-content-wrap div.wp-block-kadence-tab div.kt-tab-inner-content-inner div.wp-block-stackable-columns div.stk-inner-blocks div') as $cat) {
                            $catLi = new Crawler($cat);
                            if ($catLi->filter('div.stk-column-wrapper div.stk-block-content div.wp-block-stackable-button-group div.stk-row div a')->count() > 0) {
                                foreach ($catLi->filter('div.stk-column-wrapper div.stk-block-content div.wp-block-stackable-button-group div.stk-row div a') as $tt) {
                                    $tt_li = new Crawler($tt);
                                    if ($tt_li->filter('a')->attr('href') != '' && $tt_li->filter('a')->attr('href') != 'بازی و سرگرمی‌های فکری' && $tt_li->filter('a')->attr('href') != 'مجله کتاب جم' && $tt_li->filter('a')->attr('href') != 'پیگیری سفارشات') {
                                        SiteCategories::firstOrCreate(array('domain' => 'https://ketabejam.com/', 'cat_link' => $tt_li->filter('a')->attr('href'), 'cat_name' => $tt_li->filter('a')->text()));
                                    }
                                }
                            }
                        }
                    }
                }
            }

            //category
            $cats = SiteCategories::where('domain', 'https://ketabejam.com/')->get();
            foreach ($cats as $cat) {
                // find count  books for loop
                $client = new Client(HttpClient::create(['timeout' => 30]));
                try {
                    $this->info(" \n ---------- Try Get category              ---------- ");
                    $crawler = $client->request('GET', $cat->cat_link, [
                        'headers' => [
                            'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                        ],
                    ]);

                    $status_code = $client->getInternalResponse()->getStatusCode();
                } catch (\Exception $e) {
                    $crawler = null;
                    $status_code = 500;
                    $this->info(" \n ---------- Failed Get                ---------=-- ");
                }
                if ($status_code == 200 and $crawler->filterXPath('//*[@id="main-container"]')->count() > 0) {
                    if (str_contains($result_count = $crawler->filter('body main section div.woo-listing-top p.woocommerce-result-count')->text(), 'نمایش یک نتیجه')) {
                        $cat_pages = 1;
                    } else {
                        $result_count  =  str_replace("نمایش 1–30 از", "", $result_count);
                        $total_result  =  enNumberKeepOnly(faCharToEN($result_count));
                        $this->info($total_result);
                        $cat_pages = ceil((int)$total_result / 30);
                        $this->info($cat_pages);
                    }
                }


                $x = 1;
                while ($x <= $cat_pages) {

                    $client = new Client(HttpClient::create(['timeout' => 30]));
                    try {
                        $this->info(" \n ---------- Try Get category              ---------- ");
                        $crawler = $client->request('GET', $cat->cat_link . '/page/' . $x . '/', [
                            'headers' => [
                                'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                            ],
                        ]);

                        $status_code = $client->getInternalResponse()->getStatusCode();
                    } catch (\Exception $e) {
                        $crawler = null;
                        $status_code = 500;
                        $this->info(" \n ---------- Failed Get                ---------=-- ");
                    }
                    if ($status_code == 200 and $crawler->filterXPath('//*[@id="main-container"]')->count() > 0) {
                        foreach ($crawler->filter('body main section ul li') as $book) {
                            $book_li = new Crawler($book);
                            $this->info($book_li->filter('a')->attr('href'));
                            $SiteBookLinks = SiteBookLinks::where('domain', 'https://ketabejam.com/')->where('book_links', $book_li->filter('a')->attr('href'))->first();
                            if (empty($SiteBookLinks)) {
                                SiteBookLinks::firstOrCreate(array('domain' => 'https://ketabejam.com/', 'book_links' => $book_li->filter('a')->attr('href'), 'status' => 0));
                            }
                        }
                    }
                    $x++;
                }
            }
        } else {
            //category
            $cats = SiteCategories::where('domain', 'https://ketabejam.com/')->get();
            foreach ($cats as $cat) {

                $client = new Client(HttpClient::create(['timeout' => 30]));
                try {
                    $this->info(" \n ---------- Try Get category              ---------- ");
                    $crawler = $client->request('GET', $cat->cat_link, [
                        'headers' => [
                            'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                        ],
                    ]);

                    $status_code = $client->getInternalResponse()->getStatusCode();
                } catch (\Exception $e) {
                    $crawler = null;
                    $status_code = 500;
                    $this->info(" \n ---------- Failed Get                ---------=-- ");
                }
                if ($status_code == 200 and $crawler->filterXPath('//*[@id="main-container"]')->count() > 0) {
                    foreach ($crawler->filter('body main section ul li') as $book) {
                        $book_li = new Crawler($book);
                        $this->info($book_li->filter('a')->attr('href'));
                        $SiteBookLinks = SiteBookLinks::where('domain', 'https://ketabejam.com/')->where('book_links', $book_li->filter('a')->attr('href'))->first();
                        if (empty($SiteBookLinks)) {
                            SiteBookLinks::firstOrCreate(array('domain' => 'https://ketabejam.com/', 'book_links' => $book_li->filter('a')->attr('href'), 'status' => 0));
                        }
                    }
                }
            }
        }*/

        SiteBookLinks::where('domain', 'https://ketabejam.com/')->where('status', 0)->chunk(1, function ($bookLinks) {
            foreach ($bookLinks as $bookLink) {
                $client = new Client(HttpClient::create(['timeout' => 30]));
                $this->info($bookLink->book_links);
                try {
                    $this->info(" \n ---------- Try Get BOOK " . $bookLink->book_links . "              ---------- ");
                    $crawler = $client->request('GET', $bookLink->book_links, [
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
                // $this->info($crawler->filterXPath('//*[@class="arm-circle"]')->count());
                // $this->info($crawler->filterXPath('//nav[contains(@class, "navbar navbar-expand arm-bg-cream arm-pd-u-15 arm-fix-style")]')->count());


                if ($status_code == 200 and $crawler->filterXPath('//*[@id="main-container"]')->count() > 0) {

                    // $this->info($crawler->filter('body main article header nav.ct-breadcrumbs span')->count());
                    $book_cats = '';
                    foreach ($crawler->filter('body main article header nav.ct-breadcrumbs span a') as $nav) {
                        $breadcrumb = new Crawler($nav);
                        if (isset($book_cats) and !empty($book_cats)) {
                            $book_cats = $book_cats . "-|-" . ltrim(rtrim(self::convert_arabic_char_to_persian($breadcrumb->text())));
                        } else {
                            $book_cats = ltrim(rtrim(self::convert_arabic_char_to_persian($breadcrumb->text())));
                        }
                    }

                    if (isset($book_cats)) $cats_arr = explode('-|-', $book_cats);

                    // title
                    $title = $crawler->filter('body main article div.product div.product-entry-wrapper div.summary h1.product_title')->text();
                    // tag
                    $tags = '';
                    if ($crawler->filter('body main article div.product div.product-entry-wrapper div.summary div.product_meta span')->count() > 0) {
                        foreach ($crawler->filter('body main article div.product div.product-entry-wrapper div.summary div.product_meta span a') as $tr) {
                            $tag_tr = new Crawler($tr);
                            // $this->info($tag_tr->text() );
                            $tags = $tags . '#' . $tag_tr->text();
                        }
                        $tags = rtrim($tags, '#');
                        $tags = ltrim($tags, '#');
                    }

                    $book = BookKetabejam::where('pageUrl', $bookLink->book_links)->firstOrNew();
                    $book->pageUrl = $bookLink->book_links;
                    $book->title = $title;
                    $book->cats = $book_cats;
                    $book->tags = $tags;

                    //image
                    if ($crawler->filter('body main article div.product div.product-entry-wrapper div.woocommerce-product-gallery a.ct-image-container img')->count() > 0) {
                        $book->images  = $crawler->filter('body main article div.product div.product-entry-wrapper div.woocommerce-product-gallery a.ct-image-container img')->attr('src');
                    }

                    $book->price = enNumberKeepOnly(faCharToEN($crawler->filter('body main article div.product div.product-entry-wrapper div.summary p.price span.sale-price span.woocommerce-Price-amount bdi')->text()));
                    if ($crawler->filter('body main article div.product div.product-entry-wrapper div.summary div.woocommerce-product-details__short-description')->count() > 0) {
                        $book->desc = $crawler->filter('body main article div.product div.product-entry-wrapper div.summary div.woocommerce-product-details__short-description')->text();
                    }
                    $partner =  array();

                    foreach ($crawler->filter('table.woocommerce-product-attributes tr') as $tr) {
                        $detail_tr = new Crawler($tr);

                        if (trim($detail_tr->filterXPath('//th[1]')->text()) == 'نویسنده') {
                            $partner[0]['roleId'] = 1;
                            $partner[0]['name'] = trim($detail_tr->filterXPath('//td[1]')->text());
                        }
                        if (trim($detail_tr->filterXPath('//th[1]')->text()) == 'ناشر') {
                            $book->nasher = trim($detail_tr->filterXPath('//td[1]')->text());
                        }
                        if (trim($detail_tr->filterXPath('//th[1]')->text()) == 'مترجم') {
                            $book->translate = 1;
                            $partner[1]['roleId'] = 2;
                            $partner[1]['name'] = trim($detail_tr->filterXPath('//td[1]')->text());
                        }
                        if (trim($detail_tr->filterXPath('//th[1]')->text()) == 'دسته بندی ها') {
                            $book->tags = trim($detail_tr->filterXPath('//td[1]')->text());
                        }
                        if (trim($detail_tr->filterXPath('//th[1]')->text()) == 'شابک') {
                            $book->shabak = trim($detail_tr->filterXPath('//td[1]')->text());
                        }
                        if (trim($detail_tr->filterXPath('//th[1]')->text()) == 'قطع کتاب') {
                            $book->ghateChap = trim($detail_tr->filterXPath('//td[1]')->text());
                        }
                        if (trim($detail_tr->filterXPath('//th[1]')->text()) == 'تعداد صفحات') {
                            $book->tedadSafe = trim($detail_tr->filterXPath('//td[1]')->text());
                        }
                        if (trim($detail_tr->filterXPath('//th[1]')->text()) == 'سال انتشار') {
                            $book->saleNashr = trim($detail_tr->filterXPath('//td[1]')->text());
                        }
                        if (trim($detail_tr->filterXPath('//th[1]')->text()) == 'نوع جلد') {
                            $book->jeld = trim($detail_tr->filterXPath('//td[1]')->text());
                        }
                        if (trim($detail_tr->filterXPath('//th[1]')->text()) == 'سری (نام مجموعه)') {
                            $book->nameMajmoe = trim($detail_tr->filterXPath('//td[1]')->text());
                        }
                        if (trim($detail_tr->filterXPath('//th[1]')->text()) == 'درس') {
                            $book->dars = trim($detail_tr->filterXPath('//td[1]')->text());
                        }
                        if (trim($detail_tr->filterXPath('//th[1]')->text()) == 'مقطع تحصیلی') {
                            $book->maghtaeTahsily = trim($detail_tr->filterXPath('//td[1]')->text());
                        }
                        if (trim($detail_tr->filterXPath('//th[1]')->text()) == 'رشته تحصیلی') {
                            $book->reshteTahsily = trim($detail_tr->filterXPath('//td[1]')->text());
                        }
                        if (trim($detail_tr->filterXPath('//th[1]')->text()) == 'پایه تحصیلی') {
                            $book->payeTahsily = trim($detail_tr->filterXPath('//td[1]')->text());
                        }
                    }
                    $book->partnerArray = json_encode($partner, JSON_UNESCAPED_UNICODE);

                    // dd($book);
                    // DB::enableQueryLog();
                    $book->save();
                    SiteBookLinks::where('domain', 'https://ketabejam.com/')->where('book_links', $bookLink->book_links)->update(['status' => 1]);
                    // $query = DB::getQueryLog();
                    // $this->info($query);

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
