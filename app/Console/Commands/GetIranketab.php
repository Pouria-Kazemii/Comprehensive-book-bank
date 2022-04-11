<?php

namespace App\Console\Commands;

use Goutte\Client;
use App\Models\Author;
use App\Models\BookIranketab;
use App\Models\BookirPartner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\BookIranKetabPartner;
use App\Models\Crawler as CrawlerM;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Exception;
class GetIranketab extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:iranKetab {crawlerId} {miss?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get IranKetab Book Command';

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
        $illegal_characters = explode(",", "ç,Ç,æ,œ,À,Á,Â,Ã,Ä,Å,Æ,á,È,É,Ê,Ë,é,í,Ì,Í,Î,Ï,ð,Ñ,ñ,Ò,Ó,Ô,Õ,Ö,ó,ú,Ù,Ú,Û,Ü,à,ã,è,ì,ò,õ,ō,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
        $Allowed_characters = explode(",", "c,C,ae,oe,A,A,A,A,A,A,AE,a,E,E,E,E,e,i,I,I,I,I,o,N,n,O,O,O,O,O,o,u,U,U,U,U,a,a,e,i,o,o,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");

        if ($this->argument('miss') && $this->argument('miss') == 1) {
            try {
                $lastCrawler = CrawlerM::where('type', 2)->where('status', 1)->orderBy('end', 'ASC')->first();
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
                $lastCrawler = CrawlerM::where('name', 'LIKE', 'Crawler-IranKetab-%')->where('type', 2)->orderBy('end', 'desc')->first();
                if (isset($lastCrawler->end)) $startC = $lastCrawler->end + 1;
                else $startC = 24;
                $endC   = $startC + CrawlerM::$crawlerSize;
                $this->info(" \n ---------- Create Crawler  " . $this->argument('crawlerId') . "     $startC  -> $endC         ---------=-- ");
                $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Crawler-IranKetab-' . $this->argument('crawlerId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
            } catch (\Exception $e) {
                $this->info(" \n ---------- Failed Crawler  " . $this->argument('crawlerId') . "              ---------=-- ");
            }
        }

        // $recordNumber = $startC = $endC = 10159;


        if (isset($newCrawler)) {

            $client = new Client(HttpClient::create(['timeout' => 30]));

            $bar = $this->output->createProgressBar(CrawlerM::$crawlerSize);
            $bar->start();

            $recordNumber = $startC;
            while ($recordNumber <= $endC) {

                try {
                    $this->info(" \n ---------- Try Get BOOK " . $recordNumber . "              ---------- ");
                    $crawler = $client->request('GET', 'https://www.iranketab.ir/book/' . $recordNumber);
                    $status_code = $client->getInternalResponse()->getStatusCode();
                } catch (\Exception $e) {
                    $crawler = null;
                    $status_code = 500;
                    $this->info(" \n ---------- Failed Get  " . $recordNumber . "              ---------=-- ");
                }

                if ($status_code == 200 &&  $crawler->filter('body')->text('') != '' && $crawler->filterXPath('//*[@itemid="' . $recordNumber . '"]')->count() > 0) {

                    //tags
                    $bookDesc = $crawler->filter('body div.product-description')->text();
                    $bookTags = '';
                    foreach ($crawler->filter('body div.product-tags h5 a') as $tag) {
                        $bookTags .= '#' . $tag->textContent;
                    }
                    //note
                    $noteCounter = 0;
                    $noteResult = array();
                    foreach ($crawler->filterXPath('//div[contains(@class, "middle-bar")][1]')->filter('div') as $note) {
                        if ($noteCounter != 0) {
                            if (fmod($noteCounter, 3) == 1) {
                                $noteResult[($noteCounter - 1) / 3]['en'] = trim(preg_replace("/\r|\n/", " ", $note->textContent), " ");
                            }
                            if (fmod($noteCounter, 3) == 2) {
                                $noteResult[($noteCounter - 1) / 3]['fa'] = trim(preg_replace("/\r|\n/", " ", $note->textContent), " ");
                            }
                            if (fmod($noteCounter, 3) == 0) {
                                $noteResult[($noteCounter - 1) / 3]['writer'] = trim(preg_replace("/\r|\n/", " ", $note->textContent), " ");
                            }
                        }
                        $noteCounter++;
                    }
                    $bookNotes = json_encode($noteResult, JSON_UNESCAPED_UNICODE);
                    //part text
                    $partTextCounter = 0;
                    $partTextResult = array();
                    foreach ($crawler->filterXPath('//div[contains(@class, "middle-bar")][2]')->filter('div.persian-bar') as $partTexts) {
                        $partTextResult[$partTextCounter] = trim(preg_replace("/\r|\n/", " ", $partTexts->textContent), " ");
                        $partTextCounter++;
                    }
                    $bookPartText = json_encode($partTextResult, JSON_UNESCAPED_UNICODE);
                    //features
                    $featurerCounter = 0;
                    $featuresResult = array();
                    foreach ($crawler->filterXPath('//div[contains(@class, "product-features")]')->filter('h4') as $features) {
                        $featuresResult[$featurerCounter] = trim(preg_replace("/\r|\n/", " ", $features->textContent), " ");
                        $featurerCounter++;
                    }
                    $bookFeature = json_encode($featuresResult, JSON_UNESCAPED_UNICODE);
                    /////////////////////////////////////////////////////////////////////////////////////
                    $allbook = $crawler->filterXPath('//*[@itemid="' . $recordNumber . '"]')->filter('div.product-container div.clearfix');
                    $refCode = md5(time());
                    foreach ($allbook->filter('div.clearfix') as $book) {
                        unset($row);
                        $row = new Crawler($book);
                        unset($filtered);
                        $partner = array();
                        if ($row->filter('h1.product-name')->text('') != '' || $row->filter('div.product-name')->text('') != '') {
                            $filtered = array();
                            $filtered['title'] = ($row->filter('h1.product-name')->text('')) ? $row->filter('h1.product-name')->text('') : $row->filter('div.product-name')->text('');
                            $filtered['enTitle'] = $row->filter('div.product-name-englishname')->text('');
                            $filtered['subTitle'] = $row->filterXPath('//div[contains(@class, "col-md-7")]/div[3]')->text('');
                            $filtered['price'] = str_replace(",", "", $row->filter('span.price')->text(''));
                            if ($row->filterXPath('//div[contains(@class, "prodoct-attribute-items")]')->count() >= 1) {
                                if ($row->filterXPath('//div[contains(@class, "prodoct-attribute-items")][1]/span')->text('') == 'انتشارات:') {
                                    $filtered['nasher'] = $row->filterXPath('//div[contains(@class, "prodoct-attribute-items")][1]/a')->text('');
                                }
                            }
                            if ($row->filterXPath('//div[contains(@class, "prodoct-attribute-items")]')->count() >= 2) {
                                $authorarray = array();
                                if ($row->filterXPath('//div[contains(@class, "prodoct-attribute-items")][2]/span')->text() == 'نویسنده:') {
                                    foreach ($row->filterXPath('//div[contains(@class, "prodoct-attribute-items")][2]/a') as $authortag) {
                                        array_push($authorarray, $authortag->textContent);
                                        $authorLink = new Crawler($authortag);
                                        //crawler auther
                                        try {
                                            $this->info(" \n ---------- Try Get author " . $authorLink->attr('href') . "              ---------- ");
                                            $author_crawler = $client->request('GET', 'https://www.iranketab.ir/' . $authorLink->attr('href'));
                                            $author_status_code = $client->getInternalResponse()->getStatusCode();
                                        } catch (\Exception $e) {
                                            $author_crawler = null;
                                            $author_status_code = 500;
                                            $this->info(" \n ---------- Failed Get author " . $authorLink->attr('href') . "              ---------=-- ");
                                        }
                                        if ($author_status_code == 200 &&  $author_crawler->filter('body')->text('') != '') {
                                            unset($authorData);
                                            $authorData = array();
                                            $authorLinkArray = explode("-", $authorLink->attr('href'));
                                            $authorData['roleId'] = 1;
                                            $authorData['partnerId'] = intval(str_replace("/profile/", "", array_shift($authorLinkArray)));
                                            $partnerEnName = urldecode(implode("-", $authorLinkArray));
                                            $partnerEnName = str_replace($illegal_characters, $Allowed_characters, $partnerEnName);
                                            $authorData['partnerEnName'] = trim(preg_replace("/\r|\n/", " ", mb_strtolower($partnerEnName, 'UTF-8')), " ");
                                            $authorData['partnerDesc'] = $author_crawler->filter('body div.container div.container-fluid h5')->text();
                                            $authorData['partnerImage'] = $author_crawler->filter('body div.container div.container-fluid img.img-responsive')->attr('src');
                                            $authorData['partnerName'] = trim(preg_replace("/\r|\n/", " ", $author_crawler->filter('body div.container div.container-fluid h1')->text()), " ");

                                            $author_info = BookIranKetabPartner::where('partnerId', $authorData['partnerId'])->first();
                                            if (empty($author_info)) {
                                                BookIranKetabPartner::create($authorData);
                                            }
                                            $partner[0]['id'] = $authorData['partnerId'];
                                            $partner[0]['roleId'] = $authorData['roleId'];
                                            $partner[0]['en_name'] = $authorData['partnerEnName'];
                                            $partner[0]['name'] = $authorData['partnerName'];
                                        }
                                        //end crwaler author
                                    }
                                }
                            }
                            foreach ($row->filterXPath('//div[contains(@class, "images-container")]/div[1]/a') as $imagelink) {
                                $filtered['images'] = '';
                                $atag = new Crawler($imagelink);
                                $filtered['images'] .= 'https://www.iranketab.ir' . $atag->attr('href') . " =|= ";
                            }
                            $filtered['refCode'] = $refCode;
                            $filtered['traslate'] = false;
                            // $filtered['rate']=$row->filterXPath('//meta[contains(@itemprop, "ratingvalue")]')->attr('content');
                            $filtered['rate'] =  floatval($row->filterXPath('//div[contains(@class, "my-rating")]')->attr('data-rating'));
                            foreach ($row->filter('table.product-table tr') as $tr) {
                                $trtag = new Crawler($tr);
                                $trtag->filterXPath('//td[1]')->html();
                                if (trim($trtag->filterXPath('//td[1]')->text()) == 'کد کتاب :' && empty($filtered['recordNumber'])) {
                                    $filtered['recordNumber'] = trim($trtag->filterXPath('//td[2]')->text());
                                    if ($filtered['recordNumber'] == $recordNumber) {
                                        $filtered['desc'] = $bookDesc;
                                        $filtered['partsText'] = $bookPartText;
                                        $filtered['notes'] = $bookNotes;
                                        $filtered['tags'] = $bookTags;
                                        $filtered['features'] = $bookFeature;
                                    }
                                    $filtered['parentId'] = $recordNumber;
                                }
                                if (trim($trtag->filterXPath('//td[1]')->text()) == 'مترجم :' ) {
                                    $filtered['traslate'] = true;
                                    if($trtag->filterXPath('//td[2]/a')->count() > 0){
                                        foreach ($trtag->filterXPath('//td[2]/a') as $atag) {
                                            $translatorLink = new Crawler($atag);
                                            //crawler translator
                                            try {
                                                $this->info(" \n ---------- Try Get translator " . $translatorLink->attr('href') . "              ---------- ");
                                                $translator_crawler = $client->request('GET', 'https://www.iranketab.ir/' . $translatorLink->attr('href'));
                                                $translator_status_code = $client->getInternalResponse()->getStatusCode();
                                            } catch (\Exception $e) {
                                                $translator_crawler = null;
                                                $translator_status_code = 500;
                                                $this->info(" \n ---------- Failed Get translator " . $translatorLink->attr('href') . "              ---------=-- ");
                                            }
                                            if ($translator_status_code == 200 &&  $translator_crawler->filter('body')->text('') != '') {
    
                                                unset($translatorData);
                                                $translatorData = array();
                                                $translatorLinkArray = explode("-", $translatorLink->attr('href'));
                                                $translatorData['roleId'] = 2;
                                                $translatorData['partnerId'] = intval(str_replace("/profile/", "", array_shift($translatorLinkArray)));
                                                $partnerEnName = urldecode(implode("-", $translatorLinkArray));
                                                $partnerEnName = str_replace($illegal_characters, $Allowed_characters, $partnerEnName);
                                                $translatorData['partnerEnName'] = trim(preg_replace("/\r|\n/", " ", mb_strtolower($partnerEnName, 'UTF-8')), " ");
                                                $translatorData['partnerDesc'] = $translator_crawler->filter('body div.container div.container-fluid h5')->text();
                                                $translatorData['partnerImage'] = $translator_crawler->filter('body div.container div.container-fluid img.img-responsive')->attr('src');
                                                $translatorData['partnerName'] = trim(preg_replace("/\r|\n/", " ", $translator_crawler->filter('body div.container div.container-fluid h1')->text()), " ");
    
                                                $translatorData_info = BookIranKetabPartner::where('partnerId', $translatorData['partnerId'])->first();
                                                if (empty($translatorData_info)) {
                                                    BookIranKetabPartner::create($translatorData);
                                                }
                                                $index_key = array_key_last($partner);
                                                $partner[$index_key + 1]['id'] = $translatorData['partnerId'];
                                                $partner[$index_key + 1]['roleId'] = $translatorData['roleId'];
                                                $partner[$index_key + 1]['en_name'] = $translatorData['partnerEnName'];
                                                $partner[$index_key + 1]['name'] = $translatorData['partnerName'];
                                            }
                                            //end crwaler translator
                                        }
                                    }else{
                                        foreach ($trtag->filterXPath('//td[2]/span') as $atag) {
                                            $index_key = array_key_last($partner);
                                            $partner[$index_key + 1]['id'] = 0;
                                            $partner[$index_key + 1]['roleId'] = 2;
                                            $partner[$index_key + 1]['en_name'] = '';
                                            $partner[$index_key + 1]['name'] = $atag->textContent;
                                        } 
                                    }
                                   
                                }
                                $filtered['partnerArray'] = json_encode($partner, JSON_UNESCAPED_UNICODE);
                                if (trim($trtag->filterXPath('//td[1]')->text()) == 'شابک :' && empty($filtered['shabak']))
                                    $filtered['shabak'] = enNumberKeepOnly(faCharToEN($trtag->filterXPath('//td[2]')->text()));
                                if (trim($trtag->filterXPath('//td[1]')->text()) == 'قطع :' && empty($filtered['ghateChap']))
                                    $filtered['ghateChap'] = trim($trtag->filterXPath('//td[2]')->text());
                                if (trim($trtag->filterXPath('//td[1]')->text()) == 'تعداد صفحه :' && empty($filtered['tedadSafe']))
                                    $filtered['tedadSafe'] = trim($trtag->filterXPath('//td[2]')->text());
                                if (trim($trtag->filterXPath('//td[1]')->text()) == 'سال انتشار شمسی :' && empty($filtered['saleNashr']))
                                    $filtered['saleNashr'] = trim($trtag->filterXPath('//td[2]')->text());
                                if (trim($trtag->filterXPath('//td[1]')->text()) == 'نوع جلد :' && empty($filtered['jeld']))
                                    $filtered['jeld'] = trim($trtag->filterXPath('//td[2]')->text());
                                if (trim($trtag->filterXPath('//td[1]')->text()) == 'سری چاپ :' && empty($filtered['nobatChap']))
                                    $filtered['nobatChap'] = trim($trtag->filterXPath('//td[2]')->text());
                            }

                            // $filtered['prizes']='';
                            // $filtered['saveBook']='';
                            $selected_book = BookIranketab::where('recordNumber', $filtered['recordNumber'])->first();
                            if ($selected_book == null) {
                                try {
                                    BookIranketab::create($filtered);
                                    $this->info(" \n ----------Save book info              ---------- ");

                                } catch (Exception $Exception) {
                                    //throw $th;
                                    $this->info(" \n ---------- Save book info exception error " . $Exception->getMessage() . "              ---------- ");

                                    
                                }
                               
                            }
                        }
                        
                    }
                    //var_dump($filtered);
                    // exit;
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
}
