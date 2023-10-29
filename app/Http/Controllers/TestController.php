<?php

namespace App\Http\Controllers;


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

class TestController extends Controller
{
    public function test_majma_api()
    {
        $timeout = 120;
        $url = 'https://core.ketab.ir/api/Majma/get-books/?MaxResultCount=200&SkipCount=0&From=2023-08-18&To=2023-08-20';
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
        var_dump($content);
    }
    public function test_get_books_majma($from_date, $to_date, $from, $result_count)
    {

        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );

        // $url = 'https://core.ketab.ir/api/Majma/get-books/?MaxResultCount=200&SkipCount=0&From=2023-02-13&To=2023-02-15';
        $url = 'https://core.ketab.ir/api/Majma/get-books/?MaxResultCount=' . $result_count . '&SkipCount=' . $from . '&From=' . $from_date . '&To=' . $to_date;
        $response = file_get_contents($url, false, stream_context_create($arrContextOptions));
        die($response);
    }

    public function test_get_book_id_majma($book_id)
    {
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );


        $url = 'https://core.ketab.ir/api/Majma/get-book/' . $book_id;
        $response = file_get_contents($url, false, stream_context_create($arrContextOptions));
        die($response);
    }



    public function test_get_publishers_majma($from, $result_count)
    {
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );


        $url = 'https://core.ketab.ir/api/Majma/get-publishers/?MaxResultCount=' . $result_count . '&SkipCount=' . $from;
        $response = file_get_contents($url, false, stream_context_create($arrContextOptions));
        die($response);
    }

    public function test_get_publisher_id_majma($publisher_id)
    {
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );


        $url = 'https://core.ketab.ir/api/Majma/get-publisher/' . $publisher_id;
        $response = file_get_contents($url, false, stream_context_create($arrContextOptions));
        die($response);
    }

    public function test_get_authors_majma($from, $result_count)
    {
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );


        $url = 'https://core.ketab.ir/api/Majma/get-authors/?MaxResultCount=' . $result_count . '&SkipCount=' . $from;
        $response = file_get_contents($url, false, stream_context_create($arrContextOptions));
        die($response);
    }

    public function test_get_iranketab(){
        // BookIranketab::select(DB::raw('count(parentId) as count_group'),'parentId')->where('recheck_info',0)->groupBy('parentId')->having('count_group','>',1)->orderBy('count_group','DESC')->chunk(1000, function($check_books) { 
        $check_books = BookIranketab::select(DB::raw('count(parentId) as count_group'),'parentId')->where('recheck_info',0)->groupBy('parentId')->having('count_group','>',1)->orderBy('count_group','DESC')->limit('1')->get();
            foreach ($check_books as $check_book) {
                $client = new Client(HttpClient::create(['timeout' => 30]));
                $illegal_characters = explode(",", "ç,Ç,æ,œ,À,Á,Â,Ã,Ä,Å,Æ,á,È,É,Ê,Ë,é,í,Ì,Í,Î,Ï,ð,Ñ,ñ,Ò,Ó,Ô,Õ,Ö,ó,ú,Ù,Ú,Û,Ü,à,ã,è,ì,ò,õ,ō,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
                $Allowed_characters = explode(",", "c,C,ae,oe,A,A,A,A,A,A,AE,a,E,E,E,E,e,i,I,I,I,I,o,N,n,O,O,O,O,O,o,u,U,U,U,U,a,a,e,i,o,o,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
        
                echo 'recordNumber : '.$check_book->recordNumber.'parentId: '.$check_book->parentId.' count:' .$check_book->count_group.'</br>';
                $recordNumber = $check_book->parentId ;
                $timeout= 120;
                $url = 'https://kandoonews.com/iranketab/' . $recordNumber.'/';
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_FAILONERROR, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
                curl_setopt($ch, CURLOPT_ENCODING, "" );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
                curl_setopt($ch, CURLOPT_AUTOREFERER, true );
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout );
                // curl_setopt($ch, CURLOPT_TIMEOUT, $timeout );
                // curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
                $content = curl_exec($ch);
                $redirectedUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                curl_close($ch);

                // if(curl_errno($ch))
                // {
                //     echo(" \n ---------- Try Get BOOK " . $recordNumber . "              ---------- ");
                //     echo '</br>';
                //     echo 'error:' . curl_error($ch);
                //     echo '</br>';
                // }
                // else
                // {
                    try {
                        echo(" \n ---------- Try Get BOOK " . $recordNumber . "              ---------- ");
                        echo '</br>';
                        $crawler = $client->request('GET', 'https://kandoonews.com/iranketab/' . $recordNumber.'/');
                        $status_code = $client->getInternalResponse()->getStatusCode();
                    } catch (\Exception $e) {
                        $crawler = null;
                        $status_code = 500;
                        echo(" \n ---------- Failed Get  " . $recordNumber . "              ---------=-- ");
                        echo '</br>';
                    }
    
                    echo($url);
                    echo '</br>';
                    echo($redirectedUrl);
                    echo '</br>';

                    $redirectedUrl = (str_contains($redirectedUrl,"https://kandoonews.com/iranketab/")) ? str_replace("https://kandoonews.com/iranketab/", '', $redirectedUrl) : $redirectedUrl;
                    $redirectedUrl = (str_contains($redirectedUrl,"/")) ? str_replace("/", '', $redirectedUrl) : $redirectedUrl;
                    $slug_array  = explode("-", $redirectedUrl);
                    echo($slug_array[0]);
                    echo '</br>';
                    $parentNumber = $slug_array[0];

                    echo($parentNumber);
                    echo '</br>';
                    echo($crawler->filterXPath('//*[@itemid="' . $recordNumber . '"]')->count());
                    echo '</br>';
                    if ($status_code == 200 &&  $crawler->filter('body')->text('') != ''  && $crawler->filterXPath('//*[@itemid="' . $parentNumber . '"]')->count() > 0 ) {
                        echo('in body');
                        echo '</br>';
                        //tags
                        if($crawler->filter('body div.product-description')->count() > 0){
                            $bookDesc = $crawler->filter('body div.product-description')->text();
                        }else{
                            $bookDesc ='';  
                        }
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
                        $allbook = $crawler->filterXPath('//*[@itemid="' . $parentNumber . '"]')->filter('div.product-container div.clearfix');
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
                                                echo(" \n ---------- Try Get author " . $authorLink->attr('href') . "              ---------- ");
                                                echo '</br>';
                                                $author_crawler = $client->request('GET', 'https://www.iranketab.ir/' . $authorLink->attr('href'));
                                                $author_status_code = $client->getInternalResponse()->getStatusCode();
                                            } catch (\Exception $e) {
                                                $author_crawler = null;
                                                $author_status_code = 500;
                                                echo(" \n ---------- Failed Get author " . $authorLink->attr('href') . "              ---------=-- ");
                                                echo '</br>';
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
                                                $authorData['partnerImage'] = 'https://www.iranketab.ir' .$author_crawler->filter('body div.container div.container-fluid img.img-responsive')->attr('src');
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
                                                    echo(" \n ---------- Try Get translator " . $translatorLink->attr('href') . "              ---------- ");
                                                    echo '</br>';
                                                    $translator_crawler = $client->request('GET', 'https://www.iranketab.ir/' . $translatorLink->attr('href'));
                                                    $translator_status_code = $client->getInternalResponse()->getStatusCode();
                                                } catch (\Exception $e) {
                                                    $translator_crawler = null;
                                                    $translator_status_code = 500;
                                                    echo(" \n ---------- Failed Get translator " . $translatorLink->attr('href') . "              ---------=-- ");
                                                    echo '</br>';
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
                                if(isset($filtered['recordNumber']) && $filtered['recordNumber'] >0){
                                    $filtered['recheck_info'] = 1;
                                    $selected_book = BookIranketab::where('recordNumber', $filtered['recordNumber'])->firstOrNew();
                                    $selected_book->fill($filtered);
                                    // dd($selected_book);
                                    $selected_book->save();
                                    /*if ($selected_book == null) {
                                        try {
                                            BookIranketab::create($filtered);
                                            echo(" \n ----------Save book info              ---------- ");
        
                                        } catch (Exception $Exception) {
                                            //throw $th;
                                            echo(" \n ---------- Save book info exception error " . $Exception->getMessage() . "              ---------- ");
                                        }
                                    }else{
                                        BookIranketab::update($filtered);
                                        echo(" \n ---------- Book info is exist             ---------- ");
            
                                    }*/
                                }else{
                                    echo(" \n ---------- This url does not include the book             ---------- ");
                                    echo '</br>';
                                }
                                
                            }
                            
                        }
                        //var_dump($filtered);
                        // exit;
                    }else{
                        echo(" \n ---------- Inappropriate Content              ---------=-- ");
                    }    

                // }
            }
        // });
    }
}
