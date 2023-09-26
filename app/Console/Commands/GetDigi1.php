<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\BookDigi;
use App\Models\Author;
use App\Models\BookDigiRelated;
use App\Models\Crawler as CrawlerM;

class GetDigi1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:digiCategoryPrintedBookArtAndEntertainment {crawlerId} {miss?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get DigiKala Book Command';

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
        if ($this->argument('miss') && $this->argument('miss') == 1) {
            try {
                $lastCrawler = CrawlerM::where('name', 'Crawler-digi-category-printed-book-of-art-and-entertainment-' . $this->argument('crawlerId'))->where('type', 5)->where('status', 1)->orderBy('end', 'ASC')->first();
                if (isset($lastCrawler->end)) {
                    $startC = $lastCrawler->start;
                    $endC   = $lastCrawler->end;
                    $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
                    $newCrawler = $lastCrawler;
                }
            } catch (\Exception $e) {
                $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
            }
        } else {
            try {
                $lastCrawler = CrawlerM::where('name', 'Crawler-digi-category-printed-book-of-art-and-entertainment' . $this->argument('crawlerId'))->where('type', 5)->orderBy('end', 'desc')->first();
                if (isset($lastCrawler->end)) {
                    $startC = $lastCrawler->last + 1;
                } else {
                    $startC = 1;
                }
                $endC = $startC + CrawlerM::$crawlerSize;

                $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
                $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-digi-category-printed-book-of-art-and-entertainment' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 5));
            } catch (\Exception $e) {
                $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
            }
        }
        if (isset($newCrawler)) {

            $client = new Client(HttpClient::create(['timeout' => 30]));


            $recordNumber = $startC;

            while ($recordNumber <= $endC) {
                $bar = $this->output->createProgressBar(36);
                $bar->start();
                try {
                    $pageUrl = 'https://www.digikala.com/ajax/search/category-printed-book-of-art-and-entertainment/?pageno=' . $recordNumber . '&sortby=1';
                    $this->info(" \n ---------- Page URL  " . $pageUrl . "              ---------=-- ");
                    $json = file_get_contents($pageUrl);
                    $headers = get_headers($pageUrl);
                    $status_code = substr($headers[0], 9, 3);
                } catch (\Exception $e) {
                    $crawler = null;
                    $status_code = 500;
                    //$this->info(" \n ---------- Failed Get  ".$recordNumber."              ---------=-- ");
                }
                $this->info(" \n ---------- STATUS Get  " . $status_code . "              ---------=-- ");

                if ($status_code == "200") {

                    $products_all = json_decode($json);
                    foreach ($products_all->data->trackerData->products as $pp) {

                        $productUrl = "https://api.digikala.com/v1/product/" . $pp->product_id . "/";
                        try {
                            $this->info(" \n ---------- Try Get BOOK        " . $pp->product_id . "       ---------- ");
                            $json = file_get_contents($productUrl);
                            $product_info =  json_decode($json);
                            $headers = get_headers($pageUrl);
                            $status_code = $product_info->status;
                        } catch (\Exception $e) {
                            $crawler = null;
                            $status_code = 500;
                            $this->info(" \n ---------- Failed Get  " . $pp->product_id . "              ---------=-- ");
                        }

                        if ($status_code == 200) {
                            $bookDigi = BookDigi::where('recordNumber', 'dkp-' . $product_info->data->product->id)->firstOrNew();
                            $bookDigi->recordNumber = 'dkp-' . $product_info->data->product->id;

                            $bookDigi->title = str_replace('کتاب', '', $product_info->data->product->title_fa);

                            // اثرمرکب
                            /*if(mb_strpos($bookDigi->title,'اثر') > 0){
                                $bookDigi->title = mb_substr($bookDigi->title,0,mb_strpos($bookDigi->title,'اثر'), "UTF-8");
                            }
                            if(mb_strpos($bookDigi->title,'نشر')){
                                $bookDigi->title = mb_substr($bookDigi->title,0,mb_strpos($bookDigi->title,'نشر'), "UTF-8");
                            }
                            if(mb_strpos($bookDigi->title,'انتشارات')){
                                $bookDigi->title = mb_substr($bookDigi->title,0,mb_strpos($bookDigi->title,'انتشارات'), "UTF-8");
                            }
                            if(mb_strpos($bookDigi->title,'جلد')){
                                $bookDigi->title = mb_substr($bookDigi->title,0,mb_strpos($bookDigi->title,'جلد'), "UTF-8");
                            }
                            if(mb_strpos($bookDigi->title,'چاپ')){
                                $bookDigi->title = mb_substr($bookDigi->title,0,mb_strpos($bookDigi->title,'چاپ'), "UTF-8");
                            }*/

                            $bookDigi->title = self::convert_arabic_char_to_persian(self::remove_half_space_from_string($bookDigi->title));
                            $bookDigi->rate = $product_info->data->product->rating->rate / 20;
                            if (isset($product_info->data->product->images->list) and !empty($product_info->data->product->images->list)) {
                                $image_str = '';
                                foreach ($product_info->data->product->images->list as $image) {
                                    if (isset($image->webp_url['0'])) {
                                        $image_str .= $image->webp_url['0'] . '#';
                                    }
                                }
                                $bookDigi->images = $image_str;
                            }

                            $authorsobj = array();
                            if ($product_info->data->product->specifications['0']->title == 'مشخصات') {
                                foreach ($product_info->data->product->specifications['0']->attributes as $attribute) {

                                    if ($attribute->title == 'نویسنده') {
                                        $authorsobj = Author::firstOrCreate(array("d_name" => $attribute->values['0']));
                                    }
                                    if ($attribute->title == 'مترجم') {
                                        $bookDigi->partnerArray = $attribute->values['0'];
                                    }

                                    if ($attribute->title == 'شابک') {
                                        if (strpos($attribute->values['0'], ' - ') > 0) {
                                            $shabakStr = '';
                                            $shabaks = explode(' - ', $attribute->values['0']);
                                            foreach ($shabaks as $shabak) {
                                                $shabakStr .= self::validateIsbn($shabak) . '#';
                                            }
                                            $bookDigi->shabak = $shabakStr;
                                        } else {
                                            $bookDigi->shabak = self::validateIsbn($attribute->values['0']);
                                        }
                                    }
                                    if ($attribute->title == 'ناشر') {
                                        $bookDigi->nasher = $attribute->values['0'];
                                    }
                                    if ($attribute->title == 'موضوع') {
                                        $subject_str = '';
                                        foreach ($attribute->values as $value) {
                                            $subject_str .= $value . '#';
                                        }
                                        $bookDigi->subject = $subject_str;
                                    }
                                    if ($attribute->title == 'قطع') {
                                        $bookDigi->ghatechap = $attribute->values['0'];
                                    }
                                    if ($attribute->title == 'نوع جلد') {
                                        $bookDigi->jeld = $attribute->values['0'];
                                    }
                                    if ($attribute->title == 'نوع کاغذ') {
                                        $bookDigi->noekaghaz = $attribute->values['0'];
                                    }

                                    if ($attribute->title == 'تعداد جلد') {
                                        $jeld = str_replace('جلد', '', $attribute->values['0']);
                                        $jeld = trim($jeld);
                                        $bookDigi->count = (!empty(enNumberKeepOnly(faCharToEN($jeld)))) ? enNumberKeepOnly(faCharToEN($jeld)) : 1;
                                    }
                                    if ($attribute->title == 'تعداد صفحه') {
                                        if (strpos($attribute->values['0'], ' - ') > 0) {
                                            $tedadSafeStr = '';
                                            $tedadSafes = explode(' - ', $attribute->values['0']);
                                            foreach ($tedadSafes as $tedadSafe) {
                                                $tedadSafeStr .=  $tedadSafe . '#';
                                            }
                                            $bookDigi->tedadSafe = $tedadSafeStr;
                                        } else {
                                            $bookDigi->tedadSafe = $attribute->values['0'];
                                        }
                                    }
                                    $ageGroup_str = '';
                                    if ($attribute->title == 'گروه سنی') {
                                        foreach ($attribute->values as $value) {
                                            $ageGroup_str .= $value . '#';
                                        }
                                        $bookDigi->ageGroup = $ageGroup_str;
                                    }
                                    if ($attribute->title == 'وزن') {
                                        $bookDigi->vazn = $attribute->values['0'];
                                    }
                                    if ($attribute->title == 'رده‌بندی کتاب') {
                                        $bookDigi->cat = $attribute->values['0'];
                                    }
                                    if ($attribute->title == 'اقلام همراه' || $attribute->title == 'سایر توضیحات') {
                                        $bookDigi->features = $attribute->values['0'];
                                    }
                                }
                            }

                            $tag_string = '';
                            foreach ($product_info->data->product->tags as $tag) {
                                $tag_string .= $tag->name . '#';
                            }
                            $bookDigi->tag = $tag_string;

                            $bookDigi->price = (isset($product_info->data->product->variants['0']->price->rrp_price)) ? (int)$product_info->data->product->variants['0']->price->rrp_price : 0;
                            $bookDigi->desc = $product_info->data->product->expert_reviews->description;
                            $bookDigi->save();
                            // book author
                            if (isset($authorsobj->id)) {
                                $bookDigi->authors()->sync(array($authorsobj->id));
                                $this->info(" \n ---------- Attach Author Book   " . $authorsobj->id . "  To " . $pp->product_id . "        ---------- ");
                            }
                            //book related
                            if ($product_info->data->recommendations->related_products->title == "کالاهای مشابه") {
                                $related_array = array();
                                foreach ($product_info->data->recommendations->related_products->products as $related_product) {
                                    $related_product_digi =  BookDigi::where('recordNumber', 'dkp-' . $related_product->id)->firstOrNew();
                                    $related_product_digi->recordNumber = 'dkp-' . $related_product->id;
                                    $related_product_digi->save();
                                    $related = bookDigiRelated::firstOrCreate(array('book_id' => $related_product->id));
                                    array_push($related_array, $related->id);
                                }
                                $bookDigi->related()->sync($related_array);
                            }
                        }
                        $bar->advance();
                    }
                }

                CrawlerM::where('name', 'Crawler-digi-category-printed-book-of-art-and-entertainment' . $this->argument('crawlerId'))->where('start', $startC)->update(['last' => $recordNumber]);
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
        $isbn = str_replace("-", "", $isbn);
        $isbn = str_replace("+", "", $isbn);

        $isbn = str_replace(",", "", $isbn);
        $isbn = str_replace("،", "", $isbn);
        $isbn = str_replace("#", "", $isbn);
        $isbn = str_replace('"', "", $isbn);

        $isbn = str_replace("-", "", str_replace("0", "", $isbn));
        return $isbn;
    }
}