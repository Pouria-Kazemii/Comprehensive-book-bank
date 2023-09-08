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


                $timeout = 120;
                $url = 'https://fidibo.com/book/' . $recordNumber;
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
                        $crawler = $client->request('GET', 'https://www.fidibo.com/book/' . $recordNumber);
                        $status_code = $client->getInternalResponse()->getStatusCode();
                    } catch (\Exception $e) {
                        $crawler = null;
                        $status_code = 500;
                        $this->info(" \n ---------- Failed Get  " . $recordNumber . "              ---------=-- ");
                    }

                    if ($status_code == 200 && $crawler->filter('body main')->text('') != '' && $crawler->filterXPath('//*[@class="single2"]')->count() > 0) {

                        //BreadcrumbList
                        $BreadcrumbList = $crawler->filterXPath('//*[@style="margin-top: 7px"]')->filter('nav');
                        foreach ($BreadcrumbList->filter('ul') as $detail) {
                            unset($row);
                            $row = new Crawler($detail);
                            $tags = '';
                            for ($i = 2; $i <= 5; $i++) {
                                if ($row->filterXPath("//li[$i]")->count() > 0) {
                                    $tags = $tags . '#' . $row->filterXPath("//li[$i]/a/span")->text('');
                                    $tags = rtrim($tags, '#');
                                }
                            }
                            $filtered['tags'] = $tags;
                            // $this->info($tags);
                        }
                        //////////book image
                        if ($crawler->filter('body main div.single2 article div.container div.bov-img img')->count() > 0) {
                            $bookImage = $crawler->filter('body main div.single2 article div.container div.bov-img img')->attr('src');
                        } else {
                            $bookImage = '';
                        }
                        $filtered['images'] = str_replace('?width=200', '', $bookImage);

                        //////////book title
                        if ($crawler->filter('body main div.single2 article div.container div.book-info h1')->count() > 0) {
                            $bookTitle = $crawler->filter('body main div.single2 article div.container div.book-info h1')->text('');
                        } else {
                            $bookTitle = '';
                        }
                        // $filtered['title'] = str_replace('کتاب', '', $bookTitle);
                        $filtered['title'] = mb_substr($bookTitle, 0, 250, 'UTF-8');

                        ////////// boot creator

                        $bookCreators = $crawler->filterXPath('//*[@class="single2"]')->filter('article div.container div.book-info');
                        $partner = array();
                        $creator_counter = 0;
                        foreach ($bookCreators->filter('div.row div.col-sm-11 ul li') as $creator) {
                            unset($row);
                            $row = new Crawler($creator);
                            if($row->filterXPath('//li/span')->text('') == 'نویسنده'){
                                foreach($row->filterXPath('//li/a') as $authors){
                                    unset($authors_row);
                                    $authors_row = new Crawler($authors);

                                    $partner[$creator_counter]['roleId'] = 1;
                                    $partner[$creator_counter]['name'] = $authors_row->filterXPath('//a/span')->text('');
                                    $creator_counter++;
    
                                }
                            }elseif($row->filterXPath('//li/span')->text('') == 'مترجم'){
                                foreach($row->filterXPath('//li/a') as $authors){
                                    unset($authors_row);
                                    $authors_row = new Crawler($authors);
                                    $partner[$creator_counter]['roleId'] = 2;
                                    $partner[$creator_counter]['name'] = $authors_row->filterXPath('//a/span')->text('');
                                    $filtered['translate'] = 1;
                                    $creator_counter++;
                                }
                            }
                            
                            $filtered['partnerArray'] = json_encode($partner, JSON_UNESCAPED_UNICODE);
                        }

                        //////////book Description
                        if ($crawler->filter('body main div.single2 article section div.container div.book-description')->count() > 0) {
                            $bookDesc = $crawler->filter('body main div.single2 article section div.container div.book-description')->text('');
                        } elseif ($crawler->filter('body main div.single2 article section div.container p.book-description')->count() > 0) {
                            $bookDesc = $crawler->filter('body main div.single2 article section div.container p.book-description')->text('');
                        } else {
                            $bookDesc = '';
                        }
                        $filtered['desc'] = $bookDesc;

                        //book detail

                        $bookDetails = $crawler->filterXPath('//*[@class="single2"]')->filter('section.book-tag-section div.container div.book-tags');
                        foreach ($bookDetails->filter('ul') as $detail) {
                            unset($row);
                            $row = new Crawler($detail);
                            for ($i = 1; $i <= 7; $i++) {
                                if ($row->filterXPath("//li[$i]")->count() > 0) {
                                    if (str_contains($row->filterXPath("//li[$i]/img")->attr('src'), 'publisher.png')) {
                                        $publisher_name = $row->filterXPath("//li[$i]/a")->text('');
                                        $publisher_name = str_replace('انتشاراتی', '', $publisher_name);
                                        $publisher_name = str_replace('انتشارات', '', $publisher_name);
                                        $publisher_name = str_replace('گروه', '', $publisher_name);
                                        $publisher_name = str_replace('نشریه', '', $publisher_name);
                                        $publisher_name = str_replace('نشر', '', $publisher_name);
                                        $filtered['nasher'] =  $publisher_name;
                                    } elseif (str_contains($row->filterXPath("//li[$i]/img")->attr('src'), 'printer.png')) {
                                        $price = $row->filterXPath("//li[$i]/span")->text('');
                                        $filtered['price'] = enNumberKeepOnly(faCharToEN(trim($price)));
                                    } elseif (str_contains($row->filterXPath("//li[$i]/img")->attr('src'), 'date.png')) {
                                        $publishDate = $row->filterXPath("//li[$i]/span")->text('');
                                        $filtered['saleNashr'] = faCharToEN($publishDate);
                                    } elseif (str_contains($row->filterXPath("//li[$i]/img")->attr('src'), 'language.png')) {
                                        $language = $row->filterXPath("//li[$i]")->text('');
                                        $filtered['lang'] = $language;
                                    } elseif (str_contains($row->filterXPath("//li[$i]/img")->attr('src'), 'filesize.png')) {
                                        $file_size = $row->filterXPath("//li[$i]")->text('');
                                        $filtered['fileSize'] = $file_size;
                                    } elseif (str_contains($row->filterXPath("//li[$i]/img")->attr('src'), 'pages.png')) {
                                        $page_number = $row->filterXPath("//li[$i]")->text('');
                                        $filtered['tedadSafe'] = (enNumberKeepOnly(faCharToEN($page_number)) > 0) ? enNumberKeepOnly(faCharToEN($page_number)) : 0;
                                    } elseif (str_contains($row->filterXPath("//li[$i]/img")->attr('src'), 'isbn.png')) {
                                        $isbn = $row->filterXPath("//li[$i]/label")->text('');
                                        $filtered['shabak'] = enNumberKeepOnly(faCharToEN($isbn));
                                    }
                                }
                            }
                        }
                    } else {
                        $this->info(" \n ---------- Inappropriate Content              ---------=-- ");
                    }
                }
                
                if (isset($filtered['recordNumber']) && $filtered['recordNumber'] > 0) {
                    $selected_book = BookFidibo::where('recordNumber', $filtered['recordNumber'])->first();
                    if ($selected_book == null) {
                        try {
                            BookFidibo::create($filtered);
                            // $this->info(" \n ----------Save book info              ---------- ");
                        } catch (Exception $Exception) {
                            //throw $th;
                            $this->info(" \n ---------- Save book info exception error " . $Exception->getMessage() . "              ---------- ");
                        }
                    } else {
                        if(isset($filtered['title']) && !empty($filtered['title'])){
                            BookFidibo::updateOrCreate(
                                ['recordNumber' => $filtered['recordNumber']],
                                $filtered
                            );
                            $this->info(" \n ---------- update Book info             ---------- ");

                        }else{
                            $this->info(" \n ---------- Book info is exist   & new crawl is empty info          ---------- ");
                        }
                    }
                } else {
                    $this->info(" \n ---------- This url does not include the book             ---------- ");
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
