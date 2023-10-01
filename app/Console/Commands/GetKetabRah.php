<?php

namespace App\Console\Commands;

use Goutte\Client;
use App\Models\Author;
use App\Models\BookIranketab;
use App\Models\BookirPartner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\BookIranKetabPartner;
use App\Models\BookKetabrah;
use App\Models\Crawler as CrawlerM;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Exception;

class GetKetabRah extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:ketabRah {crawlerId} {miss?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get KetabRah Book Command';

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
                $lastCrawler = CrawlerM::where('name', 'Crawler-KetabRah-' . $this->argument('crawlerId'))->where('status', 1)->orderBy('id', 'DESC')->first();
                if (isset($lastCrawler) AND !empty($lastCrawler)) {
                    $startC = $lastCrawler->last;
                    $endC   = $lastCrawler->end;
                    $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
                    $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-KetabRah-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
                }
            } catch (\Exception $e) {
                $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
            }
        } else {
            try {
                $lastCrawler = CrawlerM::where('name', 'Crawler-KetabRah-' . $this->argument('crawlerId'))->where('status', 2)->orderBy('id', 'desc')->first();
                if (isset($lastCrawler) AND !empty($lastCrawler)) {
                    $startC = $lastCrawler->end + 1;
                    $endC = $startC + CrawlerM::$crawlerSize;
                    
                } else {
                    $startC = 1;
                    $endC = $startC + CrawlerM::$crawlerSize;
                }

                $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
                $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-KetabRah-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
            } catch (\Exception $e) {
                $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
            }
        }
        if (isset($newCrawler)) {

            $client = new Client(HttpClient::create(['timeout' => 30]));

            $bar = $this->output->createProgressBar(CrawlerM::$crawlerSize);
            $bar->start();

            $recordNumber = $startC;
            while ($recordNumber <= $endC) {
                $timeout = 120;
                $url = 'https://www.ketabrah.ir/book_name/book/' . $recordNumber;
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
                $content = curl_exec($ch);
                if (curl_errno($ch)) {
                    $this->info(" \n ---------- Try Get BOOK " . $recordNumber . "              ---------- ");
                    echo 'error:' . curl_error($ch);
                } else {
                    try {
                        $this->info(" \n ---------- Try Get BOOK " . $recordNumber . "              ---------- ");
                        $crawler = $client->request('GET', 'https://www.ketabrah.ir/book_name/book/' . $recordNumber);
                        $status_code = $client->getInternalResponse()->getStatusCode();
                    } catch (\Exception $e) {
                        $crawler = null;
                        $status_code = 500;
                        $this->info(" \n ---------- Failed Get  " . $recordNumber . "              ---------=-- ");
                    }

                    if ($status_code == 200 &&  $crawler->filter('body')->text('') != '' and $crawler->filterXPath('//div[contains(@id, "InternalPageContents")]')->count() > 0) {
                        $this->info($status_code);
                        $BookKetabrah = BookKetabrah::where('recordNumber', $recordNumber)->firstOrNew();
                        $BookKetabrah->recordNumber = $recordNumber;

                        if ($crawler->filter('article')->count() > 0) {
                            //tag 
                            if($crawler->filterXPath('//div[contains(@class, "book")]')->filter('div.breadcrumb-container div.breadcrumb-ol')->count() > 0){
                                $tagStr = '';
                                foreach ($crawler->filterXPath('//div[contains(@class, "book")]')->filter('div.breadcrumb-container div.breadcrumb-ol ol li') as $key=>$cat) {
                                    unset($row);
                                    $row = new Crawler($cat);
                                    if($key !=0 AND $key !=3){
                                        if( $row->filter('a span')->text() != ''){
                                            $tagStr .= $row->filter('a span')->text().'#';
                                        }
                                    }
                                   
                                }
                                $tagStr = rtrim($tagStr,'#');
                                $BookKetabrah->tags = $tagStr;
                            }

                            // image
                            if($crawler->filterXPath('//div[contains(@class, "book")]')->filter('div.book-main-info-cover a')->count() > 0){
                                $BookKetabrah->images = $crawler->filterXPath('//div[contains(@class, "book")]')->filter('div.book-main-info-cover a')->attr('href');
                            }

                            // Desc
                            if($crawler->filterXPath('//div[contains(@id, "BookIntroduction")]')->count() > 0){
                                $BookKetabrah->desc = $crawler->filterXPath('//div[contains(@id, "BookIntroduction")]')->html();
                            }
                           
                            // detail
                            if($crawler->filterXPath('//div[contains(@class, "book-description-content")]')->filter('div.book-details table')->count() > 0){
                                $partner = array();
                                $partnerCount = 0;
                                foreach ($crawler->filterXPath('//div[contains(@class, "book-description-content")]')->filter('div.book-details table tr') as $item) {
                                   
                                    unset($row);
                                    $row = new Crawler($item);
                                    if( $row->filterXPath('//td[1]')->text() == 'نام کتاب'){
                                        $title = self::convert_arabic_char_to_persian($row->filterXPath('//td[2]')->text());
                                        $title = str_replace('کتاب','',$title);
                                        $title = str_replace('صوتی','',$title);
                                        $title = str_replace('الکترونیکی','',$title);
                                        $BookKetabrah->title = $title;
                                        $BookKetabrah->title2 = str_replace(' ','',$title);
                                    }

                                    if( $row->filterXPath('//td[1]')->text() == 'نویسنده'){
                                        $authors = explode("،",$row->filterXPath('//td[2]')->text());
                                        foreach($authors as $author){
                                            $partner[$partnerCount]['roleId'] = 1;
                                            $partner[$partnerCount]['name'] = $author;
                                            $partnerCount++;
                                        }
                                       
                                    }

                                    if( $row->filterXPath('//td[1]')->text() == 'مترجم'){
                                        $translators = explode("،",$row->filterXPath('//td[2]')->text());
                                        foreach($translators as $translator){
                                            $partner[$partnerCount]['roleId'] = 2;
                                            $partner[$partnerCount]['name'] = $translator;
                                            $partnerCount++;
                                        }
                                        $BookKetabrah->translate = 1;
                                    }
                                    if( $row->filterXPath('//td[1]')->text() == 'گوینده'){
                                        $speakers = explode("،",$row->filterXPath('//td[2]')->text());
                                        foreach($speakers as $speaker){
                                            $partner[$partnerCount]['roleId'] = 38;
                                            $partner[$partnerCount]['name'] = $speaker;
                                            $partnerCount++;
                                        }
                                    }
                                    $BookKetabrah->partnerArray = json_encode($partner, JSON_UNESCAPED_UNICODE);

                                    if( $row->filterXPath('//td[1]')->text() == 'موضوع کتاب'){
                                        $catStr = str_replace('،','#',$row->filterXPath('//td[2]')->text());
                                        $catStr = rtrim($catStr,'#');
                                        $BookKetabrah->cat = $catStr;
                                    }

                                    if( $row->filterXPath('//td[1]')->text() == 'ناشر چاپی' OR $row->filterXPath('//td[1]')->text() =='ناشر صوتی'){
                                        $publisher_name = str_replace('انتشاراتی', '', $row->filterXPath('//td[2]')->text());
                                        $publisher_name = str_replace('انتشارات', '', $publisher_name);
                                        $publisher_name = str_replace('گروه', '', $publisher_name);
                                        $publisher_name = str_replace('نشریه', '', $publisher_name);
                                        $publisher_name = str_replace('نشر', '', $publisher_name);
                                        $BookKetabrah->nasher = $publisher_name;
                                    }

                                    if( $row->filterXPath('//td[1]')->text() == 'سال انتشار'){
                                        $BookKetabrah->saleNashr = enNumberKeepOnly(faCharToEN($row->filterXPath('//td[2]')->text()));
                                    }

                                    if( $row->filterXPath('//td[1]')->text() == 'فرمت کتاب'){
                                        $BookKetabrah->format = $row->filterXPath('//td[2]')->text();
                                    }
                                
                                    if( $row->filterXPath('//td[1]')->text() == 'تعداد صفحات'){
                                        $BookKetabrah->tedadSafe = enNumberKeepOnly(faCharToEN($row->filterXPath('//td[2]')->text()));
                                    }

                                    if( $row->filterXPath('//td[1]')->text() == 'زبان'){
                                        $BookKetabrah->lang =self::convert_arabic_char_to_persian($row->filterXPath('//td[2]')->text());
                                    }

                                    if( $row->filterXPath('//td[1]')->text() == 'شابک'){
                                        $BookKetabrah->shabak = self::validateIsbn($row->filterXPath('//td[2]')->text());
                                    }
                                }
                            }

                            // price 
                            //
                            if($crawler->filterXPath('//div[contains(@class, "book-description-content")]')->filter('div.book-details div.book-page-price-table div.book-price')->count() > 0){

                                $prices = $crawler->filterXPath('//div[contains(@class, "book-description-content")]')->filter('div.book-details div.book-page-price-table div.book-price span')->text();
                                $price = explode('-',$prices);
                                $BookKetabrah->price = enNumberKeepOnly(faCharToEN($price[0]));
                                $this->info(enNumberKeepOnly(faCharToEN($price[0])));
                            }
                            $BookKetabrah->save();
                        } 
                    } else {
                        $this->info(" \n ---------- Inappropriate Content              ---------=-- ");
                    }
                }

                // $bar->advance();
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
