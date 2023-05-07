<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BiBookBiSubject;
use App\Models\BookirBook;
use App\Models\BookirPartner;
use App\Models\BookirPartnerrule;
use App\Models\BookirPublisher;
use App\Models\BookirRules;
use App\Models\BookirSubject;
use App\Models\PublisherLinks;
use Goutte\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class CrawlerKetabirController extends Controller
{
    public function crawler_ketabir_with_circulation($id, Request $request) // without publisher info

    {

        DB::statement("SET sql_mode=''");
        $bookSelectedInfo = BookirBook::findOrFail($id);
        $bookId = str_replace('Book-', '', $request->id);
        $page_url = $request->url;
        $book_publisher = $request->book_publisher;

        $book_title = trim($request->book_title);
        if (empty($book_title)) {
            $bookSelectedInfo->xisname = 0;
        } else {
            $bookSelectedInfo->xisname = 1;
        }
        $bookSelectedInfo->xname2 = str_replace(' ', '', $book_title);
        $bookSelectedInfo->xcirculation = $request->circulation;
        $bookSelectedInfo->xpagecount = $request->book_page_count;
        $bookSelectedInfo->xcover = $request->book_cover_type;
        $bookSelectedInfo->xcoverprice = $request->book_cover_price;
        $bookSelectedInfo->xprintnumber = $request->book_print_version;
        $bookSelectedInfo->ximgeurl = $request->image;
        $bookSelectedInfo->xispublisher = (!empty($book_publisher) ? 1 : 0);
        // $request->book_publisher');
        // ;

        // $bookData['entity_type'] =  $book_items['entity_type'];
        // $bookData['book_publisher'] = $book_items['book_publisher'];

        if (isset($page_url) and !empty($page_url)) {
            unset($partner_array);
            $partner_array = array();

            ////////////////////////////book page info ////////////////////////////////////////
            if (isset($page_url) and !empty($page_url)) {
                $bookCrawlUrl = 'https://ketab.ir/book/' . $page_url;
                $bookSelectedInfo->xpageurl2 = $bookCrawlUrl;
                try {
                    $crawler = $client->request('GET', $bookCrawlUrl);
                    $status_code = $client->getInternalResponse()->getStatusCode();
                } catch (\Exception $e) {
                    $crawler = null;
                    $status_code = 500;
                }
                if ($status_code == 200 and $crawler->filterXPath('//main[contains(@class, "container")]')->count() >= 0) {

                    if ($crawler->filter('div.col-md-9 div.card-body a')->count() > 0) {
                        $bookSelectedInfo->xpdfurl = $crawler->filter('div.col-md-9 div.card-body a')->attr('href');
                    } else {
                        $bookSelectedInfo->xpdfurl = '';
                    }

                    //////////////////////////////////book subject ///////////////////////////////////
                    if ($crawler->filter('small.text-muted')->count() > 0) {
                        $book_subject = $crawler->filter('small.text-muted');
                    }

                    if ($crawler->filter('table.table-striped tr')->count() > 0) {
                        foreach ($crawler->filter('table.table-striped tr') as $tr) {
                            $tr_crawler = new Crawler($tr);

                            ///////////////////////////  book partner ///////////////////////////////
                            if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'پدیدآور') {
                                $book_partner = trim($tr_crawler->filterXPath('//td[2]')->text());
                            }
                            if (isset($book_partner) and !empty($book_partner)) {
                                unset($partner_info);
                                $partner_info = array();
                                if (str_contains($book_partner, '-')) { // چندتا
                                    $book_partner_list = explode('-', $book_partner);
                                    foreach ($book_partner_list as $partner_key => $partner_items) {
                                        if (str_contains($partner_items, ':')) {
                                            $partner_info_array = explode(':', $partner_items);
                                            $partner_info[$partner_key]['role'] = trim($partner_info_array['0']);
                                            $partner_info[$partner_key]['name'] = trim($partner_info_array['1']);
                                        }
                                    }
                                } else { // یکی
                                    if (str_contains($book_partner, ':')) {
                                        $partner_info_array = explode(':', $book_partner);
                                        $partner_info[0]['role'] = trim($partner_info_array['0']);
                                        $partner_info[0]['name'] = trim($partner_info_array['1']);
                                    }
                                }
                            }
                            //////////////////////////////////////////////////////////////////////////
                            if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'شابک') {
                                $bookSelectedInfo->xisbn = trim($tr_crawler->filterXPath('//td[2]')->text());
                            }
                            if (isset($bookSelectedInfo->xisbn) and !empty($bookSelectedInfo->xisbn)) {
                                if (strlen(str_replace('-', '', $bookSelectedInfo->xisbn) <= 10)) {
                                    $bookSelectedInfo->xisbn2 = str_replace('-', '', $bookSelectedInfo->xisbn);
                                }
                                if (strlen(str_replace('-', '', $bookSelectedInfo->xisbn) > 10)) {
                                    $bookSelectedInfo->xisbn3 = str_replace('-', '', $bookSelectedInfo->xisbn);
                                }
                            }
                            if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'تاریخ نشر') {
                                $publish_date = trim($tr_crawler->filterXPath('//td[2]')->text());
                                $jalali_publish_date = mb_substr($publish_date, 0, 4) . '/' . mb_substr($publish_date, 4, 2) . '/' . mb_substr($publish_date, 6, 2);
                                $bookSelectedInfo->xpublishdate = BookirBook::toGregorian($jalali_publish_date, '/', '-');
                            }
                            if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'کد دیویی') {
                                $bookSelectedInfo->xdiocode = trim($tr_crawler->filterXPath('//td[2]')->text());
                            }
                            if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'زبان کتاب') {
                                $bookSelectedInfo->xlang = trim($tr_crawler->filterXPath('//td[2]')->text());
                            }
                            if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'محل نشر') {
                                $bookSelectedInfo->xpublishplace = trim($tr_crawler->filterXPath('//td[2]')->text());
                            }
                            if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'توضیحات') {
                                if (str_contains(trim($tr_crawler->filterXPath('//td[2]')->text()), 'ترجمه')) {
                                    $bookSelectedInfo->is_translate = 2;
                                } elseif (str_contains(trim($tr_crawler->filterXPath('//td[2]')->text()), 'تالیف')) {
                                    $bookSelectedInfo->is_translate = 1;
                                }
                            }
                        }
                    }
                    if ($crawler->filter('div.col-md-12 div.card-body p')->count() > 0) {
                        $bookSelectedInfo->xdescription = $crawler->filter('div.col-md-12 div.card-body p')->text();
                    } else {
                        $bookSelectedInfo->xdescription = '';
                    }

                    $bookSelectedInfo->xregdate = time();
                }
            }

            ///////////////////////////////////////////subject///////////////////////////////////////////////////

            unset($book_subject_id);
            if (isset($book_subject) and !empty($book_subject)) {
                foreach ($book_subject->filter('span') as $subject_items) {
                    unset($row);
                    $row = new Crawler($subject_items);
                    $book_subject_item = $row->filter('span')->text('');

                    $bookSubjectData['xsubject'] = $book_subject_item;
                    $bookSubjectData['xsubjectname2'] = str_replace(' ', '', $book_subject_item);
                    $bookSubjectData['xregdate'] = time();
                    $BookSubjectSelectedInfo = BookirSubject::where('xsubject', $book_subject_item)->first();
                    if (empty($BookSubjectSelectedInfo)) {
                        BookirSubject::create($bookSubjectData);
                        $BookSubjectSelectedInfo = BookirSubject::where('xsubject', $book_subject_item)->first();
                    }
                    if (isset($BookSubjectSelectedInfo->xid) and !empty($BookSubjectSelectedInfo->xid)) {
                        $book_subject_id[] = $BookSubjectSelectedInfo->xid;
                    }
                }
                $bookSelectedInfo->xissubject = 1;
            } else {
                $biBookSubjectSelected = BiBookBiSubject::where('bi_book_xid', $bookSelectedInfo->xid)->first();
                if (empty($biBookSubjectSelected)) {
                    $bookSelectedInfo->xissubject = 0;
                } else {
                    $bookSelectedInfo->xissubject = 1;
                }
            }

            ///////////////////////////////////////  partner ///////////////////////////////////////////////
            if (isset($partner_info) and !empty($partner_info)) {
                foreach ($partner_info as $partner_key => $partner_item) {
                    /// partner role
                    $role_info = BookirRules::where('xrole', $partner_item['role'])->first();
                    if (empty($role_info)) {
                        $roleData = array(
                            'xrole' => $partner_item['role'],
                            'xregdate' => time(),
                        );
                        BookirRules::create($roleData);
                        $role_info = BookirRules::where('xrole', $partner_item['role'])->first();
                    }
                    /// partner name
                    if (str_contains($partner_item['name'], '،')) {
                        $author_arr = explode('،', $partner_item['name']);
                        $authorName = rtrim(ltrim($author_arr[1] . ' ' . $author_arr[0]));
                    } else {
                        $authorName = $partner_item['name'];
                    }

                    $bookAuthorData['xcreatorname'] = $authorName;
                    $bookAuthorData['xname2'] = str_replace(' ', '', $authorName);
                    $bookAuthorData['xregdate'] = time();
                    $bookAuthorData['xstatus'] = -10;
                    $bookAuthorSelectedInfo = BookirPartner::where('xcreatorname', $authorName)->where('xstatus', -10)->first(); // این اشتباه است و باید پدیدآورنده دقیق سلکت زده بشه
                    if (empty($bookAuthorSelectedInfo)) {
                        BookirPartner::create($bookAuthorData);
                        $bookAuthorSelectedInfo = BookirPartner::where('xcreatorname', $authorName)->where('xstatus', -10)->first(); // این اشتباه است و باید پدیدآورنده دقیق سلکت زده بشه
                    }
                    $partner_array[$partner_key]['xcreatorid'] = $bookAuthorSelectedInfo->xid;
                    $partner_array[$partner_key]['xroleid'] = $role_info->xid;
                    $bookSelectedInfo->xiscreator = 1;
                }
            } else {
                $bookAuthorSelectedInfo = BookirPartnerrule::where('xbookid', $bookSelectedInfo->xid)->first();
                if (empty($bookAuthorSelectedInfo)) {
                    $bookSelectedInfo->xiscreator = 0;
                } else {
                    $bookSelectedInfo->xiscreator = 1;
                }
            }

            ///////////////////////////////////////////////////////////////////////////////////////////

            if (isset($publisherTableId) and !empty($publisherTableId)) {
                $bookSelectedInfo->publishers()->sync($publisherTableId);
            }
            // sync book and subject
            if (isset($book_subject_id) and !empty($book_subject_id)) {
                $bookSelectedInfo->subjects()->sync($book_subject_id);
            }

            if (isset($partner_array) and !empty($partner_array)) {
                $bookSelectedInfo->partnersRoles()->sync($partner_array);
            }
            $bookSelectedInfo->check_circulation = 0;
            return $bookSelectedInfo;
            if($bookSelectedInfo->xisbn != ''){
                $bookSelectedInfo->update();
            }
           
            return 'ok';

        } else {
            return 'bad request';
        }
    }

    public function publisher_list()
    {
        DB::statement("SET sql_mode=''");

        echo 'start : ' . date("H:i:s", time()) . '</br>';
        $crawlerSize = 1;

        $publisherSelected = PublisherLinks::where('xcheck_status', 0)->orderBy('idd', 'desc')->get();
        // $publisherList = $publisherSelected->pluck('pub_name')->all();
        foreach ($publisherSelected as $publisherItem) {
            PublisherLinks::where('idd', $publisherItem->idd)->update(['xcheck_status' => 2]);
            echo 'publisher id : ' . $publisherItem->idd;
            echo '</br>';
            // $publisherName = '%DA%86%D8%B4%D9%85%D9%87';
            // https://msapi.ketab.ir/search/?query=%DB%8C%D9%88%D8%B4%DB%8C%D8%AA%D8%A7&user-id=7c670b656dcf818b70166e2a98aa2d6d&limit=14
            $publisherName = urlencode($publisherItem->pub_name);
            // die('stop');
            $userId = '7c670b656dcf818b70166e2a98aa2d6d';
            $from = 0;
            $limit = 14;
            $url = "https://msapi.ketab.ir/search/?query=$publisherName&user-id=$userId&limit=1&from=0";
            $client = new Client(HttpClient::create(['timeout' => 120, 'max_redirects' => 10]));

            if ($this->get_http_response_code($url) != "200") {
                echo "no url : " . $url . '</br>';
                PublisherLinks::where('idd', $publisherItem->idd)->update(['xcheck_status' => 3]);

            } else {
                $response = @file_get_contents($url);
                if (isset($response) and !empty($response)) {
                    $response = json_decode($response, true);
                    if (isset($response['result']['groups']['printableBook']['total']) and !empty($response['result']['groups']['printableBook']['total'])) {
                        $total_book = $response['result']['groups']['printableBook']['total'];
                        for ($start = 0; $start <= $total_book; $start += $limit) {
                            echo $newUrl = "https://msapi.ketab.ir/search/?query=$publisherName&user-id=$userId&limit=$limit&from=$start";
                            PublisherLinks::where('idd', $publisherItem->idd)->update(['offset_crawler' => $start]);
                            echo '</br>';
                            // $response = file_get_contents($newUrl);
                            $curl_handle = curl_init();
                            curl_setopt($curl_handle, CURLOPT_URL, $newUrl);
                            curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
                            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                            $response = curl_exec($curl_handle);
                            curl_close($curl_handle);

                            if (isset($response) and !empty($response)) {
                                $response = json_decode($response, true);

                                // book info //
                                if (isset($response['result']['groups']['printableBook']['items']) and !empty($response['result']['groups']['printableBook']['items'])) {

                                    foreach ($response['result']['groups']['printableBook']['items'] as $key_book => $book_items) {
                                        unset($partner_array);
                                        $partner_array = array();

                                        $bookId = str_replace('Book-', '', $book_items['id']);

                                        if ((isset($book_items['url']) and !empty($book_items['url'])) or isset($book_items['id']) and !empty($book_items['id'])) {
                                            $bookCrawlUrl = 'https://ketab.ir/book/' . $book_items['url'];
                                            $bookSelectedInfo = BookirBook::where('xpageurl', 'like', "%=$bookId%")->orWhere('xpageurl2', $bookCrawlUrl)->firstOrNew();

                                            $bookSelectedInfo->xpageurl2 = $bookCrawlUrl;
                                            $bookSelectedInfo->xname = trim($book_items['book_title']);
                                            if (empty($bookSelectedInfo->xname)) {
                                                $bookSelectedInfo->xisname = 0;
                                            } else {
                                                $bookSelectedInfo->xisname = 1;
                                            }
                                            $bookSelectedInfo->xname2 = str_replace(' ', '', $book_items['book_title']);
                                            $bookSelectedInfo->xpagecount = $book_items['book_page_count'];
                                            $bookSelectedInfo->xcover = $book_items['book_cover_type'];
                                            $bookSelectedInfo->xcoverprice = $book_items['book_cover_price'];
                                            $bookSelectedInfo->xprintnumber = $book_items['book_print_version'];
                                            $bookSelectedInfo->ximgeurl = $book_items['image'];

                                            // $bookData['entity_type'] =  $book_items['entity_type'];
                                            $book_publisher = $book_items['book_publisher'];

                                            ////////////////////////////book page info ////////////////////////////////////////
                                            if (isset($book_items['url']) and !empty($book_items['url'])) {
                                                try {
                                                    $crawler = $client->request('GET', $bookCrawlUrl);
                                                    $status_code = $client->getInternalResponse()->getStatusCode();
                                                } catch (\Exception $e) {
                                                    $crawler = null;
                                                    $status_code = 500;
                                                }

                                                if ($status_code == 200 and $crawler->filterXPath('//main[contains(@class, "container")]')->count() >= 0) {
                                                    if ($crawler->filter('div.col-md-9 div.card-body a')->count() > 0) {
                                                        $bookSelectedInfo->xpdfurl = $crawler->filter('div.col-md-9 div.card-body a')->attr('href');
                                                    } else {
                                                        $bookSelectedInfo->xpdfurl = '';
                                                    }

                                                    //////////////////////////////////book subject ///////////////////////////////////
                                                    if ($crawler->filter('small.text-muted')->count() > 0) {
                                                        $book_subject = $crawler->filter('small.text-muted');
                                                    }

                                                    if ($crawler->filter('table.table-striped tr')->count() > 0) {
                                                        foreach ($crawler->filter('table.table-striped tr') as $tr) {
                                                            $tr_crawler = new Crawler($tr);

                                                            ///////////////////////////  book partner ///////////////////////////////
                                                            if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'پدیدآور') {
                                                                $book_partner = trim($tr_crawler->filterXPath('//td[2]')->text());
                                                            }
                                                            if (isset($book_partner) and !empty($book_partner)) {
                                                                unset($partner_info);
                                                                $partner_info = array();
                                                                if (str_contains($book_partner, '-')) { // چندتا
                                                                    $book_partner_list = explode('-', $book_partner);
                                                                    foreach ($book_partner_list as $partner_key => $partner_items) {
                                                                        if (str_contains($partner_items, ':')) {
                                                                            $partner_info_array = explode(':', $partner_items);
                                                                            $partner_info[$partner_key]['role'] = trim($partner_info_array['0']);
                                                                            $partner_info[$partner_key]['name'] = trim($partner_info_array['1']);
                                                                        }
                                                                    }
                                                                } else { // یکی
                                                                    if (str_contains($book_partner, ':')) {
                                                                        $partner_info_array = explode(':', $book_partner);
                                                                        $partner_info[0]['role'] = trim($partner_info_array['0']);
                                                                        $partner_info[0]['name'] = trim($partner_info_array['1']);
                                                                    }
                                                                }
                                                            }
                                                            //////////////////////////////////////////////////////////////////////////
                                                            if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'شابک') {
                                                                $bookSelectedInfo->xisbn = trim($tr_crawler->filterXPath('//td[2]')->text());
                                                            }
                                                            if (isset($bookSelectedInfo->xisbn) and !empty($bookSelectedInfo->xisbn)) {
                                                                if (strlen(str_replace('-', '', $bookSelectedInfo->xisbn) <= 10)) {
                                                                    $bookSelectedInfo->xisbn2 = str_replace('-', '', $bookSelectedInfo->xisbn);
                                                                }
                                                                if (strlen(str_replace('-', '', $bookSelectedInfo->xisbn) > 10)) {
                                                                    $bookSelectedInfo->xisbn3 = str_replace('-', '', $bookSelectedInfo->xisbn);
                                                                }
                                                            }
                                                            if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'تاریخ نشر') {
                                                                $publish_date = trim($tr_crawler->filterXPath('//td[2]')->text());
                                                                $jalali_publish_date = mb_substr($publish_date, 0, 4) . '/' . mb_substr($publish_date, 4, 2) . '/' . mb_substr($publish_date, 6, 2);
                                                                $bookSelectedInfo->xpublishdate = BookirBook::toGregorian($jalali_publish_date, '/', '-');
                                                            }
                                                            if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'کد دیویی') {
                                                                $bookSelectedInfo->xdiocode = trim($tr_crawler->filterXPath('//td[2]')->text());
                                                            }
                                                            if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'زبان کتاب') {
                                                                $bookSelectedInfo->xlang = trim($tr_crawler->filterXPath('//td[2]')->text());
                                                            }
                                                            if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'محل نشر') {
                                                                $bookSelectedInfo->xpublishplace = trim($tr_crawler->filterXPath('//td[2]')->text());
                                                            }
                                                            if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'توضیحات') {
                                                                if (str_contains(trim($tr_crawler->filterXPath('//td[2]')->text()), 'ترجمه')) {
                                                                    $bookSelectedInfo->is_translate = 2;
                                                                } elseif (str_contains(trim($tr_crawler->filterXPath('//td[2]')->text()), 'تالیف')) {
                                                                    $bookSelectedInfo->is_translate = 1;
                                                                }
                                                            }
                                                        }
                                                    }
                                                    if ($crawler->filter('div.col-md-12 div.card-body p')->count() > 0) {
                                                        $bookSelectedInfo->xdescription = $crawler->filter('div.col-md-12 div.card-body p')->text();
                                                    } else {
                                                        $bookSelectedInfo->xdescription = '';
                                                    }
                                                    $bookSelectedInfo->xregdate = time();
                                                }

                                            }

                                            // publisher info ///
                                            if (isset($response['result']['groups']['publisher']['items']) and !empty($response['result']['groups']['publisher']['items'])) {
                                                $publisherTableId = $this->find_publisher($response['result']['groups']['publisher']['items'],$book_publisher, $client, $userId, $limit, $from);
                                            }

                                            $bookSelectedInfo->xispublisher = 1;
                                           
                                            ///////////////////////////////////////////subject///////////////////////////////////////////////////

                                            unset($book_subject_id);
                                            if (isset($book_subject) and !empty($book_subject)) {
                                                foreach ($book_subject->filter('span') as $subject_items) {
                                                    unset($row);
                                                    $row = new Crawler($subject_items);
                                                    $book_subject_item = $row->filter('span')->text('');

                                                    $bookSubjectData['xsubject'] = $book_subject_item;
                                                    $bookSubjectData['xsubjectname2'] = str_replace(' ', '', $book_subject_item);
                                                    $bookSubjectData['xregdate'] = time();
                                                    $BookSubjectSelectedInfo = BookirSubject::where('xsubject', $book_subject_item)->first();
                                                    if (empty($BookSubjectSelectedInfo)) {
                                                        BookirSubject::create($bookSubjectData);
                                                        $BookSubjectSelectedInfo = BookirSubject::where('xsubject', $book_subject_item)->first();
                                                    }
                                                    if (isset($BookSubjectSelectedInfo->xid) and !empty($BookSubjectSelectedInfo->xid)) {
                                                        $book_subject_id[] = $BookSubjectSelectedInfo->xid;
                                                    }
                                                }
                                                $bookSelectedInfo->xissubject = 1;
                                            } else {
                                                $biBookSubjectSelected = BiBookBiSubject::where('bi_book_xid', $bookSelectedInfo->xid)->first();
                                                if (empty($biBookSubjectSelected)) {
                                                    $bookSelectedInfo->xissubject = 0;
                                                } else {
                                                    $bookSelectedInfo->xissubject = 1;
                                                }
                                            }
                                            /*
                                            if (isset($book_items['book_subject']) and !empty($book_items['book_subject'])) {
                                                foreach ($book_items['book_subject'] as $book_subject_items) {
                                                    $bookSubjectData['xsubject'] = $book_subject_items;
                                                    $bookSubjectData['xsubjectname2'] = str_replace(' ', '', $book_subject_items);
                                                    $bookSubjectData['xregdate'] = time();
                                                    $BookSubjectSelectedInfo = BookirSubject::where('xsubject', $book_subject_items)->first();
                                                    if (empty($BookSubjectSelectedInfo)) {
                                                        BookirSubject::create($bookSubjectData);
                                                        $BookSubjectSelectedInfo = BookirSubject::where('xsubject', $book_subject_items)->first();
                                                    }
                                                    $bookData['xissubject'] = 1;
                                                    }
                                                    } else {
                                                    $biBookSubjectSelected = BiBookBiSubject::where('bi_book_xid', $bookSelectedInfo->xid)->first();
                                                    if (empty($biBookSubjectSelected)) {
                                                    $bookData['xissubject'] = 0;
                                                    } else {
                                                    $bookData['xissubject'] = 1;
                                                }
                                            }
                                             */
                                            ///////////////////////////////////////  partner ///////////////////////////////////////////////
                                            if (isset($partner_info) and !empty($partner_info)) {
                                                foreach ($partner_info as $partner_key => $partner_item) {
                                                    /// partner role
                                                    $role_info = BookirRules::where('xrole', $partner_item['role'])->first();
                                                    if (empty($role_info)) {
                                                        $roleData = array(
                                                            'xrole' => $partner_item['role'],
                                                            'xregdate' => time(),
                                                        );
                                                        BookirRules::create($roleData);
                                                        $role_info = BookirRules::where('xrole', $partner_item['role'])->first();
                                                    }
                                                    /// partner name
                                                    if (str_contains($partner_item['name'], '،')) {
                                                        $author_arr = explode('،', $partner_item['name']);
                                                        $authorName = rtrim(ltrim($author_arr[1] . ' ' . $author_arr[0]));
                                                    } else {
                                                        $authorName = $partner_item['name'];
                                                    }

                                                    $bookAuthorData['xcreatorname'] = $authorName;
                                                    $bookAuthorData['xname2'] = str_replace(' ', '', $authorName);
                                                    $bookAuthorData['xregdate'] = time();
                                                    $bookAuthorData['xstatus'] = -10;
                                                    $bookAuthorSelectedInfo = BookirPartner::where('xcreatorname', $authorName)->where('xstatus', -10)->first(); // این اشتباه است و باید پدیدآورنده دقیق سلکت زده بشه
                                                    if (empty($bookAuthorSelectedInfo)) {
                                                        BookirPartner::create($bookAuthorData);
                                                        $bookAuthorSelectedInfo = BookirPartner::where('xcreatorname', $authorName)->where('xstatus', -10)->first(); // این اشتباه است و باید پدیدآورنده دقیق سلکت زده بشه
                                                    }
                                                    $partner_array[$partner_key]['xcreatorid'] = $bookAuthorSelectedInfo->xid;
                                                    $partner_array[$partner_key]['xroleid'] = $role_info->xid;
                                                    $bookSelectedInfo->xiscreator = 1;
                                                }
                                            } else {
                                                $bookAuthorSelectedInfo = BookirPartnerrule::where('xbookid', $bookSelectedInfo->xid)->first();
                                                if (empty($bookAuthorSelectedInfo)) {
                                                    $bookSelectedInfo->xiscreator = 0;
                                                } else {
                                                    $bookSelectedInfo->xiscreator = 1;
                                                }
                                            }

                                            ///////////////////////////////////////////////////////////////////////////////////////////

                                            
                                            $bookSelectedInfo->check_circulation = 0;
                                            // echo '<pre>'; print_r($bookSelectedInfo);
                                            $bookSelectedInfo->save();
                                            if (isset($publisherTableId) and !empty($publisherTableId)) {
                                                $bookSelectedInfo->publishers()->sync($publisherTableId);
                                            }
                                            // sync book and subject
                                            if (isset($book_subject_id) and !empty($book_subject_id)) {
                                                $bookSelectedInfo->subjects()->sync($book_subject_id);
                                            }

                                            if (isset($partner_array) and !empty($partner_array)) {
                                                $bookSelectedInfo->partnersRoles()->sync($partner_array);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                PublisherLinks::where('idd', $publisherItem->idd)->update(['xcheck_status' => 1]);
            }

        }
        echo 'start : ' . date("H:i:s", time()) . '</br>';

    }
    public function get_http_response_code($url)
    {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }

    public function find_publisher($response, $book_publisher, $client, $userId, $limit, $from)
    {
        $find_publisher = false;
        foreach ($response as $publisher_items) {
            if ($publisher_items['publisher_title'] == $book_publisher) {
                $publisherId = str_replace('Publisher-', '', $publisher_items['id']);
                $publisherTableId = $this->save_publisher_info($publisherId, $publisher_items['image'], $publisher_items['publisher_manager_fullname'], $publisher_items['publisher_title'], $publisher_items['url']);
                $find_publisher = true;
            }
        }

        if (!$find_publisher) {
            $publisher_table_info = BookirPublisher::select('xid')->where('xpublishername', $book_publisher)->first();
            if (isset($publisher_table_info->xid) and !empty($publisher_table_info->xid)) {
                $publisherTableId = $publisher_table_info->xid;
            }
        }

        if (!$find_publisher) {
            $book_publisher_encoded = urlencode($book_publisher);
            $url = "https://msapi.ketab.ir/search/?query=$book_publisher_encoded&user-id=$userId&limit=$limit&from=$from";
            /* $response = file_get_contents($url);
            $publisher_response = json_decode($response, true);*/
            // echo '<pre>'; print_r($publisher_response);
            // die($publisher_response);
            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, $url);
            curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
            $publisher_response = curl_exec($curl_handle);
            curl_close($curl_handle);

            $publisher_response = json_decode($publisher_response, true);

            if (isset($publisher_response['result']['groups']['publisher']['items']) and !empty($publisher_response['result']['groups']['publisher']['items'])) {
                $json_publisher_items = $publisher_response['result']['groups']['publisher']['items'];
                foreach ($json_publisher_items as $publisher_items) {
                    if ($publisher_items['publisher_title'] == $book_publisher) {
                        $publisherId = str_replace('Publisher-', '', $publisher_items['id']);
                        $publisherTableId = $this->save_publisher_info($publisherId, $publisher_items['image'], $publisher_items['publisher_manager_fullname'], $publisher_items['publisher_title'], $publisher_items['url']);
                        $find_publisher = true;
                    }
                }
            }
        }

        if (isset($publisherTableId)) {
            return $publisherTableId;
        } else {
            return -1;
        }
    }

    public function save_publisher_info($publisherId, $publisher_image, $publisher_manager_fullname, $publisher_title, $publisher_url)
    {
        $client = new Client(HttpClient::create(['timeout' => 120, 'max_redirects' => 10]));
        unset($publisherData);
        $publisherData = array();
        $publisherData['ximageurl'] = $publisher_image;
        $publisherData['xmanager'] = trim($publisher_manager_fullname);
        $publisherData['xpublishername'] = trim($publisher_title);
        $publisherData['xpublishername2'] = str_replace(' ', '', $publisher_title);
        if (isset($publisher_url) and !empty($publisher_url)) {
            $timeout = 120;
            $publisherCrawlUrl = 'https://ketab.ir/Publisher/' . $publisher_url;
            try {
                $publisherCrawlUrl = 'https://ketab.ir/Publisher/' . $publisher_url;
                $crawler = $client->request('GET', $publisherCrawlUrl);
                $status_code = $client->getInternalResponse()->getStatusCode();
            } catch (\Exception $e) {
                $crawler = null;
                $status_code = 500;
            }
            $publisherData['xpageurl2'] = $publisherCrawlUrl;
            // $data['text']=$crawler->filter('body main.container')->text();
            //    Test::create($data);
            // $bookDesc = $crawler->filter('body main.container')->text();

            // echo '</pre>'; print_r($crawler);

            if ($status_code == 200 and $crawler->filterXPath('//main[contains(@class, "container")]')->count() >= 0) {

                if ($crawler->filter('div.col-md-9 div.card-body table')->count() > 0) {
                    $publisherData['xsave'] = '<table class="table table-striped table-hover"><tbody>' . $crawler->filter('div.col-md-9 div.card-body table')->html() . '</tbody></table>';
                } else {
                    $publisherData['xsave'] = '';
                }
                if ($crawler->filter('table.table-striped tr')->count() > 0) {
                    foreach ($crawler->filter('table.table-striped tr') as $tr) {
                        $tr_crawler = new Crawler($tr);
                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'استان - شهرستان') {
                            $publisherData['xplace'] = trim($tr_crawler->filterXPath('//td[2]')->text());
                        }
                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'نشانی پستی') {
                            $publisherData['xaddress'] = trim($tr_crawler->filterXPath('//td[2]')->text());
                        }
                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'صندوق پستی') {
                            $publisherData['xzipcode'] = str_replace('-', '', trim($tr_crawler->filterXPath('//td[2]')->text()));
                        }
                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'تلفن') {
                            $publisherData['xphone'] = str_replace(' ', '', trim($tr_crawler->filterXPath('//td[2]')->text()));
                        }
                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'تاریخ آخرین روزآوری اطلاعات') {
                            $publisherData['xlastupdate'] = str_replace('/', '', trim($tr_crawler->filterXPath('//td[2]')->text()));
                        }
                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'دورنگار') {
                            $publisherData['xfax'] = trim($tr_crawler->filterXPath('//td[2]')->text());
                        }
                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'شماره پروانه نشر') {
                            $publisherData['xpermitno'] = trim($tr_crawler->filterXPath('//td[2]')->text());
                        }
                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'پست الکترونیک') {
                            $publisherData['xemail'] = trim($tr_crawler->filterXPath('//td[2]')->text());
                        }
                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'وب سایت') {
                            $publisherData['xsite'] = trim($tr_crawler->filterXPath('//td[2]')->text());
                        }
                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'شناسه شابک') {
                            $publisherData['xisbnid'] = trim($tr_crawler->filterXPath('//td[2]')->text());
                        }
                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'تاریخ تاسیس') {
                            $publisherData['xfoundingdate'] = trim($tr_crawler->filterXPath('//td[2]')->text());
                        }
                    }
                }
            }

            // DB::enableQueryLog();
            $publisherSelectedInfo = BookirPublisher::where('xpageurl', 'like', "%?Publisherid=$publisherId")->orWhere('xpageurl2', $publisherData['xpageurl2'])->first();

            if (empty($publisherSelectedInfo)) {
                BookirPublisher::create($publisherData);
                $publisherSelectedInfo = BookirPublisher::where('xpageurl', 'like', "%?Publisherid=$publisherId")->orWhere('xpageurl2', $publisherData['xpageurl2'])->first();
            } else {
                unset($publisherUpdateData);
                $publisherUpdateData = array();
                if (!empty($publisherData['xpageurl2']) and (empty($publisherSelectedInfo['xpageurl2']) or $publisherSelectedInfo['xpageurl2'] == null or $publisherSelectedInfo['xpageurl2'] == 0)) {
                    $publisherUpdateData['xpageurl2'] = $publisherData['xpageurl2'];
                }
                if (!empty($publisherData['xpublishername']) and (empty($publisherSelectedInfo['xpublishername']) or $publisherSelectedInfo['xpublishername'] == null or $publisherSelectedInfo['xpublishername'] == 0)) {
                    $publisherUpdateData['xpublishername'] = $publisherData['xpublishername'];
                }
                if (!empty($publisherData['xmanager']) and (empty($publisherSelectedInfo['xmanager']) or $publisherSelectedInfo['xmanager'] == null or $publisherSelectedInfo['xmanager'] == 0)) {
                    $publisherUpdateData['xmanager'] = $publisherData['xmanager'];
                }
                if (!empty($publisherData['xplace']) and (empty($publisherSelectedInfo['xplace']) or $publisherSelectedInfo['xplace'] == null or $publisherSelectedInfo['xplace'] == 0)) {
                    $publisherUpdateData['xplace'] = $publisherData['xplace'];
                }
                if (!empty($publisherData['xaddress']) and (empty($publisherSelectedInfo['xaddress']) or $publisherSelectedInfo['xaddress'] == null or $publisherSelectedInfo['xaddress'] == 0)) {
                    $publisherUpdateData['xaddress'] = $publisherData['xaddress'];
                }
                if (!empty($publisherData['xzipcode']) and (empty($publisherSelectedInfo['xzipcode']) or $publisherSelectedInfo['xzipcode'] == null or $publisherSelectedInfo['xzipcode'] == 0)) {
                    $publisherUpdateData['xzipcode'] = $publisherData['xzipcode'];
                }
                if (!empty($publisherData['xphone']) and (empty($publisherSelectedInfo['xphone']) or $publisherSelectedInfo['xphone'] == null or $publisherSelectedInfo['xphone'] == 0)) {
                    $publisherUpdateData['xphone'] = $publisherData['xphone'];
                }
                if (!empty($publisherData['xfax']) and (empty($publisherSelectedInfo['xfax']) or $publisherSelectedInfo['xfax'] == null or $publisherSelectedInfo['xfax'] == 0)) {
                    $publisherUpdateData['xfax'] = $publisherData['xfax'];
                }
                if (!empty($publisherData['xlastupdate']) and (empty($publisherSelectedInfo['xlastupdate']) or $publisherSelectedInfo['xlastupdate'] == null or $publisherSelectedInfo['xlastupdate'] == 0)) {
                    $publisherUpdateData['xlastupdate'] = $publisherData['xlastupdate'];
                }
                if (!empty($publisherData['xpermitno']) and (empty($publisherSelectedInfo['xpermitno']) or $publisherSelectedInfo['xpermitno'] == null or $publisherSelectedInfo['xpermitno'] == 0)) {
                    $publisherUpdateData['xpermitno'] = $publisherData['xpermitno'];
                }
                if (!empty($publisherData['xemail']) and (empty($publisherSelectedInfo['xemail']) or $publisherSelectedInfo['xemail'] == null or $publisherSelectedInfo['xemail'] == 0)) {
                    $publisherUpdateData['xemail'] = $publisherData['xemail'];
                }
                if (!empty($publisherData['xsite']) and (empty($publisherSelectedInfo['xsite']) or $publisherSelectedInfo['xsite'] == null or $publisherSelectedInfo['xsite'] == 0)) {
                    $publisherUpdateData['xsite'] = $publisherData['xsite'];
                }
                if (!empty($publisherData['xisbnid']) and (empty($publisherSelectedInfo['xisbnid']) or $publisherSelectedInfo['xisbnid'] == null or $publisherSelectedInfo['xisbnid'] == 0)) {
                    $publisherUpdateData['xisbnid'] = $publisherData['xisbnid'];
                }
                if (!empty($publisherData['xfoundingdate']) and (empty($publisherSelectedInfo['xfoundingdate']) or $publisherSelectedInfo['xfoundingdate'] == null or $publisherSelectedInfo['xfoundingdate'] == 0)) {
                    $publisherUpdateData['xfoundingdate'] = $publisherData['xfoundingdate'];
                }
                if (!empty($publisherData['ximageurl']) and (empty($publisherSelectedInfo['ximageurl']) or $publisherSelectedInfo['ximageurl'] == null or $publisherSelectedInfo['ximageurl'] == 0)) {
                    $publisherUpdateData['ximageurl'] = $publisherData['ximageurl'];
                }
                if (!empty($publisherData['xpublishername2']) and (empty($publisherSelectedInfo['xpublishername2']) or $publisherSelectedInfo['xpublishername2'] == null or $publisherSelectedInfo['xpublishername2'] == 0)) {
                    $publisherUpdateData['xpublishername2'] = $publisherData['xpublishername2'];
                }
                if (!empty($publisherData['xsave']) and (empty($publisherSelectedInfo['xsave']) or $publisherSelectedInfo['xsave'] == null or $publisherSelectedInfo['xsave'] == 0)) {
                    $publisherUpdateData['xsave'] = $publisherData['xsave'];
                }
                if (isset($publisherUpdateData) and !empty($publisherUpdateData)) {
                    BookirPublisher::where('xid', $publisherSelectedInfo['xid'])->update($publisherUpdateData);
                }
            }
            // $queryyyy = DB::getQueryLog();
            // dd($queryyyy);
            $publisherTableId = $publisherSelectedInfo->xid;
            return $publisherTableId;
        }
    }
}
