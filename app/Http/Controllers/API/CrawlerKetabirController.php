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
use App\Models\Test;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Goutte\Client;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use App\Models\Crawler as CrawlerM;

class CrawlerKetabirController extends Controller
{

    public function publisher_list()
    {
        $crawlerSize = 1;

        $lastCrawler = CrawlerM::where('name', 'LIKE', 'Crawler-Ketabir-%')->where('type', 2)->orderBy('end', 'desc')->first();
        if (isset($lastCrawler->end)) $startC = $lastCrawler->end + 1;
        else $startC = 1;
        $endC   = $startC + $crawlerSize;
        CrawlerM::firstOrCreate(array('name' => 'Crawler-Ketabir-' . $crawlerSize, 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));



        $publisherList = PublisherLinks::skip($startC)->take($crawlerSize)->get();
        $publisherList = $publisherList->pluck('pub_name')->all();
        foreach ($publisherList as $publisherName) {
            // $publisherName = '%DA%86%D8%B4%D9%85%D9%87';
            // https://msapi.ketab.ir/search/?query=%DB%8C%D9%88%D8%B4%DB%8C%D8%AA%D8%A7&user-id=7c670b656dcf818b70166e2a98aa2d6d&limit=14
            $publisherName = urlencode($publisherName);
            // die('stop');
            $userId = '7c670b656dcf818b70166e2a98aa2d6d';
            $from = 0;
            $limit = 14;
            $url = "https://msapi.ketab.ir/search/?query=$publisherName&user-id=$userId&limit=$limit&from=$from";
            $client = new Client(HttpClient::create(['timeout' => 120, 'max_redirects' => 10]));
            $response = file_get_contents($url);
            $response = json_decode($response, true);

            $countLoop =  $response['result']['groups']['printableBook']['total'] / $limit;
            for ($start = 0; $start <= $countLoop; $start++) {
                echo  $newUrl = "https://msapi.ketab.ir/search/?query=$publisherName&user-id=$userId&limit=$limit&from=$start";
                echo '</br>';
                $response = file_get_contents($newUrl);
                $response = json_decode($response, true);

                // publisher info ///
                if ($start == 0) {
                    foreach ($response['result']['groups']['publisher']['items'] as $publisherKey => $publisher_items) {
                        if ($publisherKey < 1) {
                            $publisherId = str_replace('Publisher-', '', $publisher_items['id']);
                            unset($publisherData);
                            $publisherData = array();
                            $publisherData['ximageurl'] =  $publisher_items['image'];
                            $publisherData['xmanager'] = trim($publisher_items['publisher_manager_fullname']);
                            $publisherData['xpublishername'] =  trim($publisher_items['publisher_title']);
                            $publisherData['xpublishername2'] =  str_replace(' ', '', $publisher_items['publisher_title']);
                            if (isset($publisher_items['url']) and !empty($publisher_items['url'])) {
                                $timeout = 120;
                                $publisherCrawlUrl = 'https://db.ketab.ir/Publisher/' . $publisher_items['url'];
                                try {
                                    $publisherCrawlUrl = 'https://db.ketab.ir/Publisher/' . $publisher_items['url'];
                                    $crawler = $client->request('GET', $publisherCrawlUrl);
                                    $status_code = $client->getInternalResponse()->getStatusCode();
                                } catch (\Exception $e) {
                                    $crawler = null;
                                    $status_code = 500;
                                }
                                // $data['text']=$crawler->filter('body main.container')->text();
                                //    Test::create($data);
                                // $bookDesc = $crawler->filter('body main.container')->text();

                                // echo '</pre>'; print_r($crawler);

                                if ($status_code == 200 and $crawler->filterXPath('//main[contains(@class, "container")]')->count() >= 0) {
                                    $publisherData['xpageurl2'] =  $publisherCrawlUrl;
                                    $publisherData['xsave'] = '<table class="table table-striped table-hover"><tbody>' . $crawler->filter('div.col-md-9 div.card-body table')->html() . '</tbody></table>';
                                    foreach ($crawler->filter('table.table-striped tr') as $tr) {
                                        $tr_crawler = new Crawler($tr);
                                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'استان - شهرستان') {
                                            $publisherData['xplace'] =  trim($tr_crawler->filterXPath('//td[2]')->text());
                                        }
                                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'نشانی پستی') {
                                            $publisherData['xaddress'] =  trim($tr_crawler->filterXPath('//td[2]')->text());
                                        }
                                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'صندوق پستی') {
                                            $publisherData['xzipcode'] =  str_replace('-', '', trim($tr_crawler->filterXPath('//td[2]')->text()));
                                        }
                                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'تلفن') {
                                            $publisherData['xphone'] =  str_replace(' ', '', trim($tr_crawler->filterXPath('//td[2]')->text()));
                                        }
                                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'تاریخ آخرین روزآوری اطلاعات') {
                                            $publisherData['xlastupdate'] =  str_replace('/', '', trim($tr_crawler->filterXPath('//td[2]')->text()));
                                        }
                                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'دورنگار') {
                                            $publisherData['xfax'] =  trim($tr_crawler->filterXPath('//td[2]')->text());
                                        }
                                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'شماره پروانه نشر') {
                                            $publisherData['xpermitno'] =  trim($tr_crawler->filterXPath('//td[2]')->text());
                                        }
                                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'پست الکترونیک') {
                                            $publisherData['xemail'] =  trim($tr_crawler->filterXPath('//td[2]')->text());
                                        }
                                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'وب سایت') {
                                            $publisherData['xsite'] =  trim($tr_crawler->filterXPath('//td[2]')->text());
                                        }
                                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'شناسه شابک') {
                                            $publisherData['xisbnid'] =  trim($tr_crawler->filterXPath('//td[2]')->text());
                                        }
                                        if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'تاریخ تاسیس') {
                                            $publisherData['xfoundingdate'] =  trim($tr_crawler->filterXPath('//td[2]')->text());
                                        }
                                    }
                                }
                            }
                            // echo '</pre>'; print_r($publisherData);
                            $publisherSelectedInfo = BookirPublisher::where('xpageurl', 'like', "%=$publisherId%")->orWhere('xpageurl2', $publisherData['xpageurl2'])->first();
                            if (empty($publisherSelectedInfo)) {
                                BookirPublisher::create($publisherData);
                                $publisherSelectedInfo = BookirPublisher::where('xpageurl', 'like', "%=$publisherId%")->orWhere('xpageurl2', $publisherData['xpageurl2'])->first();
                            } else {
                                unset($publisherUpdateData);
                                $publisherUpdateData = array();
                                if (!empty($publisherData['xpageurl2']) and (empty($publisherSelectedInfo['xpageurl2']) or  $publisherSelectedInfo['xpageurl2'] == NULL or $publisherSelectedInfo['xpageurl2'] == 0)) {
                                    $publisherUpdateData['xpageurl2'] = $publisherData['xpageurl2'];
                                }
                                if (!empty($publisherData['xpublishername']) and (empty($publisherSelectedInfo['xpublishername']) or  $publisherSelectedInfo['xpublishername'] == NULL or $publisherSelectedInfo['xpublishername'] == 0)) {
                                    $publisherUpdateData['xpublishername'] = $publisherData['xpublishername'];
                                }
                                if (!empty($publisherData['xmanager']) and (empty($publisherSelectedInfo['xmanager']) or  $publisherSelectedInfo['xmanager'] == NULL or $publisherSelectedInfo['xmanager'] == 0)) {
                                    $publisherUpdateData['xmanager'] = $publisherData['xmanager'];
                                }
                                if (!empty($publisherData['xplace']) and (empty($publisherSelectedInfo['xplace']) or  $publisherSelectedInfo['xplace'] == NULL or $publisherSelectedInfo['xplace'] == 0)) {
                                    $publisherUpdateData['xplace'] = $publisherData['xplace'];
                                }
                                if (!empty($publisherData['xaddress']) and (empty($publisherSelectedInfo['xaddress']) or  $publisherSelectedInfo['xaddress'] == NULL or $publisherSelectedInfo['xaddress'] == 0)) {
                                    $publisherUpdateData['xaddress'] = $publisherData['xaddress'];
                                }
                                if (!empty($publisherData['xzipcode']) and (empty($publisherSelectedInfo['xzipcode']) or  $publisherSelectedInfo['xzipcode'] == NULL or $publisherSelectedInfo['xzipcode'] == 0)) {
                                    $publisherUpdateData['xzipcode'] = $publisherData['xzipcode'];
                                }
                                if (!empty($publisherData['xphone']) and (empty($publisherSelectedInfo['xphone']) or  $publisherSelectedInfo['xphone'] == NULL or $publisherSelectedInfo['xphone'] == 0)) {
                                    $publisherUpdateData['xphone'] = $publisherData['xphone'];
                                }
                                if (!empty($publisherData['xfax']) and (empty($publisherSelectedInfo['xfax']) or  $publisherSelectedInfo['xfax'] == NULL or $publisherSelectedInfo['xfax'] == 0)) {
                                    $publisherUpdateData['xfax'] = $publisherData['xfax'];
                                }
                                if (!empty($publisherData['xlastupdate']) and (empty($publisherSelectedInfo['xlastupdate']) or  $publisherSelectedInfo['xlastupdate'] == NULL or $publisherSelectedInfo['xlastupdate'] == 0)) {
                                    $publisherUpdateData['xlastupdate'] = $publisherData['xlastupdate'];
                                }
                                if (!empty($publisherData['xpermitno']) and (empty($publisherSelectedInfo['xpermitno']) or  $publisherSelectedInfo['xpermitno'] == NULL or $publisherSelectedInfo['xpermitno'] == 0)) {
                                    $publisherUpdateData['xpermitno'] = $publisherData['xpermitno'];
                                }
                                if (!empty($publisherData['xemail']) and (empty($publisherSelectedInfo['xemail']) or  $publisherSelectedInfo['xemail'] == NULL or $publisherSelectedInfo['xemail'] == 0)) {
                                    $publisherUpdateData['xemail'] = $publisherData['xemail'];
                                }
                                if (!empty($publisherData['xsite']) and (empty($publisherSelectedInfo['xsite']) or  $publisherSelectedInfo['xsite'] == NULL or $publisherSelectedInfo['xsite'] == 0)) {
                                    $publisherUpdateData['xsite'] = $publisherData['xsite'];
                                }
                                if (!empty($publisherData['xisbnid']) and (empty($publisherSelectedInfo['xisbnid']) or  $publisherSelectedInfo['xisbnid'] == NULL or $publisherSelectedInfo['xisbnid'] == 0)) {
                                    $publisherUpdateData['xisbnid'] = $publisherData['xisbnid'];
                                }
                                if (!empty($publisherData['xfoundingdate']) and (empty($publisherSelectedInfo['xfoundingdate']) or  $publisherSelectedInfo['xfoundingdate'] == NULL or $publisherSelectedInfo['xfoundingdate'] == 0)) {
                                    $publisherUpdateData['xfoundingdate'] = $publisherData['xfoundingdate'];
                                }
                                if (!empty($publisherData['ximageurl']) and (empty($publisherSelectedInfo['ximageurl']) or  $publisherSelectedInfo['ximageurl'] == NULL or $publisherSelectedInfo['ximageurl'] == 0)) {
                                    $publisherUpdateData['ximageurl'] = $publisherData['ximageurl'];
                                }
                                if (!empty($publisherData['xpublishername2']) and (empty($publisherSelectedInfo['xpublishername2']) or  $publisherSelectedInfo['xpublishername2'] == NULL or $publisherSelectedInfo['xpublishername2'] == 0)) {
                                    $publisherUpdateData['xpublishername2'] = $publisherData['xpublishername2'];
                                }
                                if (!empty($publisherData['xsave']) and (empty($publisherSelectedInfo['xsave']) or  $publisherSelectedInfo['xsave'] == NULL or $publisherSelectedInfo['xsave'] == 0)) {
                                    $publisherUpdateData['xsave'] = $publisherData['xsave'];
                                }
                                if (isset($publisherUpdateData) and !empty($publisherUpdateData)) {
                                    BookirPublisher::where('xid', $publisherSelectedInfo['xid'])->update($publisherUpdateData);
                                }
                            }
                            $publisherTableId = $publisherSelectedInfo->xid;
                            // dd($publisherData);
                        }
                    }
                }


                // book info //
                foreach ($response['result']['groups']['printableBook']['items'] as $key_book => $book_items) {
                    unset($bookData);
                    $bookData = array();
                    $bookId =  str_replace('Book-', '', $book_items['id']);
                    $bookData['xname'] =  trim($book_items['book_title']);
                    if (empty($bookData['xname'])) {
                        $bookData['xisname'] = 0;
                    } else {
                        $bookData['xisname'] = 1;
                    }
                    $bookData['xname2'] =  str_replace(' ', '', $book_items['book_title']);
                    $bookData['xpagecount'] =  $book_items['book_page_count'];
                    $bookData['xcover'] =  $book_items['book_cover_type'];
                    $bookData['xcoverprice'] =  $book_items['book_cover_price'];
                    $bookData['xprintnumber'] =  $book_items['book_print_version'];
                    $bookData['ximgeurl'] =  $book_items['image'];

                    // $bookData['entity_type'] =  $book_items['entity_type'];
                    $bookData['book_publisher'] =  $book_items['book_publisher'];

                    ////////////////////////////book page info ////////////////////////////////////////
                    if (isset($book_items['url']) and !empty($book_items['url'])) {
                        echo $bookCrawlUrl = 'https://db.ketab.ir/book/' . $book_items['url'];
                        echo '</br>';
                        $bookData['xpageurl2'] =  $bookCrawlUrl;
                        try {
                            $crawler = $client->request('GET', $bookCrawlUrl);
                            $status_code = $client->getInternalResponse()->getStatusCode();
                        } catch (\Exception $e) {
                            $crawler = null;
                            $status_code = 500;
                        }

                        if ($status_code == 200 and $crawler->filterXPath('//main[contains(@class, "container")]')->count() >= 0) {

                            $bookData['xpdfurl'] = $crawler->filter('div.col-md-9 div.card-body a')->attr('href');

                            //////////////////////////////////book subject ///////////////////////////////////
                            $book_subject = $crawler->filter('small.text-muted');

                            foreach ($crawler->filter('table.table-striped tr') as $tr) {
                                $tr_crawler = new Crawler($tr);

                                ///////////////////////////  book partner ///////////////////////////////
                                if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'پدیدآور') {
                                    $book_partner =  trim($tr_crawler->filterXPath('//td[2]')->text());
                                }
                                if (isset($book_partner) and !empty($book_partner)) {
                                    $book_partner_list =  explode('-', $book_partner);
                                    foreach ($book_partner_list as $partner_key => $partner_items) {
                                        $partner_info_array =  explode(' : ', $partner_items);
                                        $partner_info[$partner_key]['role'] = $partner_info_array['0'];
                                        $partner_info[$partner_key]['name'] = $partner_info_array['1'];
                                    }
                                }
                                //////////////////////////////////////////////////////////////////////////
                                if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'شابک') {
                                    $bookData['xisbn'] =  trim($tr_crawler->filterXPath('//td[2]')->text());
                                }
                                if (isset($bookData['xisbn']) and !empty($bookData['xisbn'])) {
                                    if (strlen(str_replace('-', '', $bookData['xisbn']) <= 10)) {
                                        $bookData['xisbn2'] = str_replace('-', '', $bookData['xisbn']);
                                    }
                                    if (strlen(str_replace('-', '', $bookData['xisbn']) > 10)) {
                                        $bookData['xisbn3'] = str_replace('-', '', $bookData['xisbn']);
                                    }
                                }
                                if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'تاریخ نشر') {
                                    $publish_date =  trim($tr_crawler->filterXPath('//td[2]')->text());
                                    $jalali_publish_date = mb_substr($publish_date, 0, 4) . '/' . mb_substr($publish_date, 4, 2) . '/' . mb_substr($publish_date, 6, 2);
                                    $bookData['xpublishdate'] =  BookirBook::toGregorian($jalali_publish_date, '/', '-');
                                }
                                if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'کد دیویی') {
                                    $bookData['xdiocode'] =  trim($tr_crawler->filterXPath('//td[2]')->text());
                                }
                                if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'زبان کتاب') {
                                    $bookData['xlang'] =  trim($tr_crawler->filterXPath('//td[2]')->text());
                                }
                                if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'محل نشر') {
                                    $bookData['xpublishplace'] =  trim($tr_crawler->filterXPath('//td[2]')->text());
                                }
                                if (trim($tr_crawler->filterXPath('//td[1]')->text()) == 'توضیحات') {
                                    if (str_contains(trim($tr_crawler->filterXPath('//td[2]')->text()), 'ترجمه')) {
                                        $bookData['is_translate'] = 2;
                                    } elseif (str_contains(trim($tr_crawler->filterXPath('//td[2]')->text()), 'تالیف')) {
                                        $bookData['is_translate'] = 1;
                                    }
                                }
                            }
                            $bookData['xdescription'] = $crawler->filter('div.col-md-12 div.card-body p')->text();
                            $bookData['xregdate'] = time();
                        }
                    }
                    $bookData['xispublisher'] = 1;
                    $bookSelectedInfo = BookirBook::where('xpageurl', 'like', "%=$bookId%")->orWhere('xpageurl2', $bookData['xpageurl2'])->first();
                    if (empty($bookSelectedInfo)) {
                        bookirbook::create($bookData);
                        $bookSelectedInfo = BookirBook::where('xpageurl', 'like', "%=$bookId%")->orWhere('xpageurl2', $bookData['xpageurl2'])->first();
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
                            $book_subject_id[] = $BookSubjectSelectedInfo->xid;
                        }
                        $bookData['xissubject'] = 1;
                    } else {
                        $biBookSubjectSelected = BiBookBiSubject::where('bi_book_xid', $bookSelectedInfo->xid)->first();
                        if (empty($biBookSubjectSelected)) {
                            $bookData['xissubject'] = 0;
                        } else {
                            $bookData['xissubject'] = 1;
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
                                    'xregdate' => time()
                                );
                                BookirRules::create($roleData);
                                $role_info = BookirRules::where('xrole', $partner_item['role'])->first();
                            }
                            /// partner name
                            $author_arr = explode('،', $partner_item['name']);
                            $authorName = rtrim(ltrim($author_arr[1] . ' ' . $author_arr[0]));
                            $bookAuthorData['xcreatorname'] = $authorName;
                            $bookAuthorData['xname2'] = str_replace(' ', '', $authorName);
                            $bookAuthorData['xregdate'] = time();
                            $bookAuthorSelectedInfo = BookirPartner::where('xcreatorname', $authorName)->first(); // این اشتباه است و باید پدیدآورنده دقیق سلکت زده بشه 
                            if (empty($bookAuthorSelectedInfo)) {
                                BookirPartner::create($bookAuthorData);
                                $bookAuthorSelectedInfo = BookirPartner::where('xcreatorname', $authorName)->first(); // این اشتباه است و باید پدیدآورنده دقیق سلکت زده بشه 
                            }
                            $partner_array[$partner_key]['xcreatorid'] = $bookAuthorSelectedInfo->xid;
                            $partner_array[$partner_key]['xroleid'] = $role_info->xid;
                            $bookData['xiscreator'] = 1;
                        }
                    } else {
                        $bookAuthorSelectedInfo = BookirPartnerrule::where('xbookid', $bookSelectedInfo->xid)->first();
                        if (empty($bookAuthorSelectedInfo)) {
                            $bookData['xiscreator'] = 0;
                        } else {
                            $bookData['xiscreator'] = 1;
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

                    $bookSelectedInfo->update($bookData);
                }
            }
            die('stop');
        }
    }
}
