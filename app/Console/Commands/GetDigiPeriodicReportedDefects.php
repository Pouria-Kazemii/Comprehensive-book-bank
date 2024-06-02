<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\BookDigi;
use App\Models\Author;
use App\Models\BookDigiRelated;
use App\Models\BookirBook;
use App\Models\Crawler as CrawlerM;
use App\Models\ErshadBook;
use App\Models\UnallowableBook;
use App\Models\WebSiteBookLinksDefects;

class GetDigiPeriodicReportedDefects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:digiPeriodicReportedDefects {crawlerId}';

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

        $client = new HttpBrowser(HttpClient::create(['timeout' => 30]));


        $items = WebSiteBookLinksDefects::where('siteName', 'digikala')->WhereNull('crawlerStatus')->WhereNotNull('book_links')->get();
        if (isset($items) and !empty($items)) {
            foreach ($items as $item) {
                // check bookirbook and ershad and witout isbn 
                $product_id = str_replace("dkp-", "", $item->recordNumber);
                $productUrl = "https://api.digikala.com/v1/product/" . $product_id . "/";
                try {
                    $this->info(" \n ---------- Try Get BOOK        " . $product_id . "       ---------- ");
                    $json = file_get_contents($productUrl);
                    $product_info =  json_decode($json);
                    $headers = get_headers($productUrl);
                    $status_code = $product_info->status;
                } catch (\Exception $e) {
                    $crawler = null;
                    $status_code = 500;
                    $this->info(" \n ---------- Failed Get  " . $product_id . "              ------------ ");
                }


                if ($status_code == 200) {
                    // start crawler site
                        $CrawlerData = array();
                        if (isset($product_info->data->product->id) and !empty($product_info->data->product->id)) {
                            $CrawlerData['recordNumber'] = 'dkp-' . $product_info->data->product->id;
                        }

                        if (isset($product_info->data->product->title_fa) and !empty($product_info->data->product->title_fa)) {
                            $CrawlerData['title'] = $product_info->data->product->title_fa;
                            $CrawlerData['title'] = self::convert_arabic_char_to_persian(self::remove_half_space_from_string($CrawlerData['title']));
                        }


                        if (isset($product_info->data->product->rating->rate) and !empty($product_info->data->product->rating->rate)) {
                            $CrawlerData['rate'] = $product_info->data->product->rating->rate / 20;
                        }

                        if (isset($product_info->data->product->images->list) and !empty($product_info->data->product->images->list)) {
                            $image_str = '';
                            foreach ($product_info->data->product->images->list as $image) {
                                if (isset($image->webp_url['0'])) {
                                    $image_str .= $image->webp_url['0'] . '#';
                                }
                            }
                            $CrawlerData['image'] = $image_str;
                        }

                        if (isset($product_info->data->product->specifications['0']->title) and $product_info->data->product->specifications['0']->title == 'مشخصات') {
                            foreach ($product_info->data->product->specifications['0']->attributes as $attribute) {

                                if ($attribute->title == 'نویسنده') {
                                    $CrawlerData['authors'] = Author::firstOrCreate(array("d_name" => $attribute->values['0']));
                                }
                                if ($attribute->title == 'مترجم') {
                                    $CrawlerData['partnerArray'] = $attribute->values['0'];
                                }

                                if ($attribute->title == 'شابک') {
                                    if (strpos($attribute->values['0'], ' - ') > 0) {
                                        $shabakStr = '';
                                        $shabaks = explode(' - ', $attribute->values['0']);
                                        foreach ($shabaks as $shabak) {
                                            $shabakStr .= self::validateIsbn($shabak) . '#';
                                        }
                                        $CrawlerData['shabak'] = $shabakStr;
                                    } else {
                                        $CrawlerData['shabak'] = self::validateIsbn($attribute->values['0']);
                                    }
                                }
                                if ($attribute->title == 'ناشر') {
                                    $CrawlerData['nasher'] = $attribute->values['0'];
                                }
                                if ($attribute->title == 'موضوع') {
                                    $subject_str = '';
                                    foreach ($attribute->values as $value) {
                                        $subject_str .= $value . '#';
                                    }
                                    $CrawlerData['subject'] = $subject_str;
                                }
                                if ($attribute->title == 'قطع') {
                                    $CrawlerData['ghatechap'] = $attribute->values['0'];
                                }
                                if ($attribute->title == 'نوع جلد') {
                                    $CrawlerData['jeld'] = $attribute->values['0'];
                                }
                                if ($attribute->title == 'نوع کاغذ') {
                                    $CrawlerData['noekaghaz'] = $attribute->values['0'];
                                }

                                if ($attribute->title == 'تعداد جلد') {
                                    $jeld = str_replace('جلد', '', $attribute->values['0']);
                                    $jeld = trim($jeld);
                                    $CrawlerData['count'] = (!empty(enNumberKeepOnly(faCharToEN($jeld)))) ? enNumberKeepOnly(faCharToEN($jeld)) : 1;
                                }
                                if ($attribute->title == 'تعداد صفحه') {
                                    if (strpos($attribute->values['0'], ' - ') > 0) {
                                        $tedadSafeStr = '';
                                        $tedadSafes = explode(' - ', $attribute->values['0']);
                                        foreach ($tedadSafes as $tedadSafe) {
                                            $tedadSafeStr .=  enNumberKeepOnly($tedadSafe) . '#';
                                        }
                                        $CrawlerData['tedadSafe'] = $tedadSafeStr;
                                    } else {
                                        $CrawlerData['tedadSafe'] = enNumberKeepOnly($attribute->values['0']);
                                    }
                                }
                                $ageGroup_str = '';
                                if ($attribute->title == 'گروه سنی') {
                                    foreach ($attribute->values as $value) {
                                        $ageGroup_str .= $value . '#';
                                    }
                                    $CrawlerData['ageGroup'] = $ageGroup_str;
                                }
                                if ($attribute->title == 'وزن') {
                                    $CrawlerData['vazn'] = $attribute->values['0'];
                                }
                                if ($attribute->title == 'رده‌بندی کتاب') {
                                    $CrawlerData['cat'] = $attribute->values['0'];
                                }
                                if ($attribute->title == 'اقلام همراه' || $attribute->title == 'سایر توضیحات') {
                                    $CrawlerData['features'] = $attribute->values['0'];
                                }
                            }
                        }

                        $tag_string = '';
                        if (isset($product_info->data->product->tags) and !empty($product_info->data->product->tags)) {
                            foreach ($product_info->data->product->tags as $tag) {
                                $tag_string .= $tag->name . '#';
                            }
                        }

                        $CrawlerData['tag'] = $tag_string;
                        $CrawlerData['price'] = (isset($product_info->data->product->variants['0']->price->rrp_price)) ? (int)$product_info->data->product->variants['0']->price->rrp_price : 0;
                        $CrawlerData['desc'] = (isset($product_info->data->product->expert_reviews->description)) ? $product_info->data->product->expert_reviews->description : NULL;
                        $item->crawlerInfo = json_encode($CrawlerData);
                    //end crawler site
                    // check status and has permit 
                    if ($item->old_unallowed == 1) { // کتاب های غیر مجاز
                        if (isset($product_info->data->product->is_inactive) and $product_info->data->product->is_inactive == true) {
                            $item->new_check_status = 9;
                            $item->new_has_permit = 9;
                            $item->new_unallowed = 9;
                        } else {
                            $item->new_check_status = 10;
                            $item->new_has_permit = 10;
                            $item->new_unallowed = 10;
                        }
                    } else {
                        if (isset($CrawlerData['shabak']) and !empty($CrawlerData['shabak'])) {
                            $bookirbookInfo = BookirBook::where('xisbn', $CrawlerData['shabak'])->orwhere('xisbn2', $CrawlerData['shabak'])->orwhere('xisbn3', $CrawlerData['shabak'])->first();
                            if (isset($bookirbookInfo->xid) and !empty($bookirbookInfo->xid)) {
                                $item->new_check_status = '1';
                            } else {
                                $item->new_check_status = '4';
                            }
                            $ershadbookinfo = ErshadBook::where('xisbn', $CrawlerData['shabak'])->first();
                            if (isset($ershadbookinfo->xid) and !empty($ershadbookinfo->xid)) {
                                $item->new_has_permit = '1';
                            } else {
                                $item->new_has_permit = '4';
                            }
                        } else {
                            $item->new_check_status = '4';
                            $item->new_has_permit = '4';
                        }
                    }
                    BookDigi::where('recordNumber', $item->recordNumber)->update(array('check_status' => $item->new_check_status, 'has_permit' => $item->new_has_permit,'unallowed'=>$item->new_unallowed));
                    $item->result = siteBookLinkDefects(checkStatusTitle($item->new_check_status), hasPermitTitle($item->new_has_permit));
                }
                $item->crawlerStatus = $status_code;
                $item->crawlerTime = date("Y-m-d h:i:s");
                $item->save();
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
        $isbn = str_replace("-", "", $isbn);
        $isbn = str_replace("+", "", $isbn);

        $isbn = str_replace(",", "", $isbn);
        $isbn = str_replace("،", "", $isbn);
        $isbn = str_replace("#", "", $isbn);
        $isbn = str_replace('"', "", $isbn);

        return $isbn;
    }
}
