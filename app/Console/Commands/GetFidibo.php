<?php

namespace App\Console\Commands;

use App\models\BookFidibo;
use App\Models\Crawler as CrawlerM;
use Exception;
use Goutte\Client;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class GetFidibo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:fidibo {crawlerId} {miss?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get fidibo Book Command';

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
                $lastCrawler = CrawlerM::where('type', 2)->where('status', 1)->orderBy('end', 'ASC')->first();
                if (isset($lastCrawler->end)) {
                    $startC = $lastCrawler->start;
                    $endC = $lastCrawler->end;
                    $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
                    $newCrawler = $lastCrawler;
                }
            } catch (\Exception $e) {
                $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
            }
        } else {
            try {
                $lastCrawler = CrawlerM::where('name', 'LIKE', 'Crawler-Fidibo-%')->where('type', 2)->orderBy('end', 'desc')->first();
                if (isset($lastCrawler->end)) {
                    $startC = $lastCrawler->end + 1;
                } else {
                    $startC = 100;
                }

                $endC = $startC + CrawlerM::$crawlerSize;
                $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
                $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-Fidibo-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
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
                unset($filtered);
                $filtered = array();
                $filtered['recordNumber'] = $recordNumber;

                try {
                    $timeout = 120;
                    $pageUrl = 'https://api.fidibo.com/flex/page?pageName=BOOK_OVERVIEW&bookId=' . $recordNumber . '&page=1&limit=1000';
                    $json = file_get_contents($pageUrl);
                    $book_info = json_decode($json);
                    if ($book_info->data->result != null) {
                        $status_code = 200;
                    }else{
                        $status_code = 500;
                    }
                } catch (\Exception $e) {
                    $status_code = 500;
                    $this->info(" \n ---------- Failed Get  " . $recordNumber . "              ---------=-- ");
                }
                if ($status_code == "200") {
                    $bookFidibo = BookFidibo::where('recordNumber', $recordNumber)->firstOrNew();
                    $bookFidibo->recordNumber = $recordNumber;
                    if(isset($book_info->data->result) AND !empty($book_info->data->result)){
                        foreach($book_info->data->result as $result ){

                            if(isset($result->subtitle) AND $result->subtitle == 'معرفی'){
                                $this->info(str_replace('درباره ','',$result->items['0']->introduction->title));
                                $bookFidibo->title = str_replace('درباره ','',$result->items['0']->introduction->title);
                                $bookFidibo->desc = (isset($result->items['0']->introduction->description)) ? $result->items['0']->introduction->description : NULL;
                                // $this->info( $bookFidibo->desc );
                                $tagStr = '';
                                foreach($result->items['0']->categories as $category){
                                    $tagStr .= $category->name.'#';
                                }
                                $tagStr = rtrim($tagStr, '#');
                                $bookFidibo->tags = $tagStr;
                            }


                            if(isset($result->title) AND $result->title == 'شناسنامه'){
                                $partner = array();
                                $partnerCount = 0;
                                foreach($result->items['0']->specifications as $attribute){
                                    
                                    
                                    if ($attribute->title == 'تعداد صفحات') {
                                        $tedadSafe = str_replace('صفحه', '', $attribute->sub_title);
                                        $tedadSafe = trim($tedadSafe);
                                        $bookFidibo->tedadSafe = (enNumberKeepOnly(faCharToEN($tedadSafe)) > 0) ? enNumberKeepOnly(faCharToEN($tedadSafe)) : 0;
                                        // $this->info($bookFidibo->tedadSafe );
                                    }


                                    if ($attribute->title == 'نویسنده') {
                                        $partner[$partnerCount]['roleId'] = 1;
                                        $partner[$partnerCount]['name'] = $attribute->sub_title;
                                        $partnerCount++;
                                        $this->info(  $attribute->sub_title );

                                    }

                                    if ($attribute->title == 'مترجم') {
                                        $partner[$partnerCount]['roleId'] = 2;
                                        $partner[$partnerCount]['name'] = $attribute->sub_title;
                                        $bookFidibo->translate = 1;
                                        $this->info(  $attribute->sub_title );
                                    }
                                    // var_dump($partner);
        
                                    $bookFidibo->partnerArray = json_encode($partner, JSON_UNESCAPED_UNICODE);
                                    if ($attribute->title == 'ناشر') {
                                        $publisher_name = str_replace('انتشاراتی', '', $attribute->sub_title);
                                        $publisher_name = str_replace('انتشارات', '', $publisher_name);
                                        $publisher_name = str_replace('گروه', '', $publisher_name);
                                        $publisher_name = str_replace('نشریه', '', $publisher_name);
                                        $publisher_name = str_replace('نشر', '', $publisher_name);
                                        $bookFidibo->nasher =  $publisher_name;
                                        // $this->info(  $bookFidibo->nasher );

                                    }
        
                                    if ($attribute->title == 'زبان') {
                                        $bookFidibo->lang = $attribute->sub_title;
                                        // $this->info(  $bookFidibo->lang );

                                    }
        
                                    if ($attribute->title == 'عنوان انگلیسی') {
                                        $bookFidibo->title_en = str_replace('کتاب','',$attribute->sub_title);
                                        // $this->info(  $bookFidibo->title_en );

                                    }
        
                                    if ($attribute->title == 'تاریخ انتشار') {
                                        $bookFidibo->saleNashr = faCharToEN($attribute->sub_title);
                                        // $this->info(  $bookFidibo->saleNashr );
                                    }
        
                                    if ($attribute->title == 'قیمت چاپی') {
                                        $price  =  str_replace('تومان', '', $attribute->sub_title);
                                        $bookFidibo->price = enNumberKeepOnly(faCharToEN(trim($price)));
                                        // $this->info(  $bookFidibo->price );
                                    }
        
                                    if ($attribute->title == 'حجم') {
                                        $bookFidibo->fileSize = faCharToEN($attribute->sub_title);
                                        // $this->info(  $bookFidibo->fileSize );
                                    }
                                }

                            }

                            $bookFidibo->save();
                        }
                    }
                   
                }
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
