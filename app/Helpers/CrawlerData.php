<?php

use App\Models\AgeGroup;
use App\Models\Author;
use App\Models\BookBarkhatBook;
use App\Models\BookCover;
use App\Models\BookDigi;
use App\Models\BookDigiRelated;
use App\Models\BookFormat;
use App\Models\BookirBook;
use App\Models\BookirPartner;
use App\Models\BookirPublisher;
use App\Models\BookirRules;
use App\Models\BookirSubject;
use App\Models\BookKetabejam;
use App\Models\BookLanguage;
use App\Models\MajmaApiBook;
use App\Models\MajmaApiPublisher;
use App\Models\SiteBookLinks;
use App\Models\SiteCategories;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;


if (!function_exists('updateKetabirBook')) {
    function updateKetabirBook($recordNumber, $bookIrBook, $function_caller = NULL)
    {

        $timeout = 120;
        $url = 'http://dcapi.k24.ir/test_get_book_id_majma/' . $recordNumber . '/';
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
        $book_content = curl_exec($ch);

        if (curl_errno($ch)) {
            // $this->info(" \n ---------- Try Get BOOK " . $recordNumber . "              ---------- ");
            echo 'error:' . curl_error($ch);
            $api_status = 500;
            MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => $api_status, 'xfunction_caller' => $function_caller]);
        } else {
            // $this->info(' recordNumber : ' . $recordNumber);
            $api_status = 200;
            MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => $api_status, 'xfunction_caller' => $function_caller]);

            ////////////////////////////////////////////////// book data  ///////////////////////////////////////////////
            $book_content = json_decode($book_content);

            $book_content->title = remove_half_space_from_string($book_content->title);
            $book_content->title = convert_arabic_char_to_persian($book_content->title);

            ///////////////////////////////////////////////// book language ////////////////////////////////////////////
            if (!is_null($book_content->language) and !empty($book_content->language)) {
                BookLanguage::firstOrCreate(array('name' => $book_content->language));
            }

            ///////////////////////////////////////////////// book format ////////////////////////////////////////////
            if (!is_null($book_content->sizeType) and !empty($book_content->sizeType)) {
                BookFormat::firstOrCreate(array('name' => $book_content->sizeType));
            }

            ///////////////////////////////////////////////// book cover ////////////////////////////////////////////
            if (!is_null($book_content->coverType) and !empty($book_content->coverType)) {
                BookCover::firstOrCreate(array('name' => $book_content->coverType));
            }

            // $bookIrBook = BookirBook::where('xpageurl', 'http://ketab.ir/bookview.aspx?bookid=' . $recordNumber->orWhere('xpageurl2', 'https://ketab.ir/book/' . $book_content->uniqueId)->firstOrNew();

            // book data
            if (!is_null($book_content->bookType)) {
                $is_translate = ($book_content->bookType == 'تالیف') ? 1 : 2;
            } else {
                $is_translate = (isset($bookIrBook->is_translate)) ? $bookIrBook->is_translate : 0;
            }

            $book_content->isbn = validateIsbn($book_content->isbn);
            if (!is_null($book_content->isbn)) {

                $isbn13 = $book_content->isbn;
                $isbn13 = str_replace("-", "", str_replace("0", "", $isbn13));

                if (empty($isbn13)) {
                    $book_content->isbn = $isbn13;
                }
            }

            $book_content->isbn10 = validateIsbn($book_content->isbn10);
            if (!is_null($book_content->isbn10)) {
                $isbn10 = $book_content->isbn10;
                $isbn10 = str_replace("-", "", str_replace("0", "", $book_content->isbn));

                if (empty($isbn10)) {
                    $book_content->isbn10 = $isbn10;
                }
            }

            $bookIrBook->xpageurl = 'http://ketab.ir/bookview.aspx?bookid=' . $recordNumber;
            $bookIrBook->xpageurl2 = 'http://ketab.ir/book/' . $book_content->uniqueId;
            $bookIrBook->xname = (!is_null($book_content->title)) ? mb_substr($book_content->title, 0, 300, "UTF-8") : $bookIrBook->xname;
            $bookIrBook->xname2 = str_replace(" ", "", $bookIrBook->xname);
            $bookIrBook->xpagecount = (!is_null($book_content->pageCount)) ? $book_content->pageCount : $bookIrBook->xpagecount;
            $bookIrBook->xformat = (!is_null($book_content->sizeType)) ? $book_content->sizeType : $bookIrBook->xformat;
            $bookIrBook->xcover = (!is_null($book_content->coverType)) ? $book_content->coverType : $bookIrBook->xcover;
            $bookIrBook->xprintnumber = (!is_null($book_content->printVersion)) ? $book_content->printVersion : $bookIrBook->xprintnumber;
            $bookIrBook->xcirculation = (!is_null($book_content->circulation)) ? $book_content->circulation : $bookIrBook->xcirculation;
            $bookIrBook->xcovercount = (!is_null($book_content->volumeCount)) ? $book_content->volumeCount : $bookIrBook->xcovercount;
            $bookIrBook->xcovernumber =  (!is_null($book_content->volumeNumber)) ? $book_content->volumeNumber : $bookIrBook->xcovernumber;

            // 'xapearance'=> '' ;
            $bookIrBook->xisbn = (!is_null($book_content->isbn) && !empty($book_content->isbn)) ? $book_content->isbn : $bookIrBook->xisbn;
            $bookIrBook->xisbn3 = (!is_null($book_content->isbn) && !empty($book_content->isbn)) ? str_replace("-", "", $book_content->isbn) : substr(str_replace("-", "", $bookIrBook->xisbn), 0, 20);
            $bookIrBook->xisbn2 = (!is_null($book_content->isbn10) && !empty($book_content->isbn10)) ? str_replace("-", "", $book_content->isbn10) : $bookIrBook->xisbn2;

            $bookIrBook->xpublishdate = (!is_null($book_content->issueDate)) ? BookirBook::toGregorian(substr($book_content->issueDate, 0, 4) . '/' . substr($book_content->issueDate, 4, 2) . '/' . substr($book_content->issueDate, 6, 2), '/', '-') : $bookIrBook->xpublishdate;
            $bookIrBook->xcoverprice = (!is_null($book_content->coverPrice)) ? $book_content->coverPrice : $bookIrBook->xcoverprice;
            // 'xminprice'=>'' ;
            // 'xcongresscode'=>'' ;
            $bookIrBook->xdiocode = (!is_null($book_content->dewey)) ? $book_content->dewey : $bookIrBook->xdiocode;
            $bookIrBook->xlang = (!is_null($book_content->language)) ? $book_content->language : $bookIrBook->xlang;
            if (!is_null($book_content->publishPlace)) {
                //Replace multiple whitespace characters with a single space
                $book_content->publishPlace = preg_replace('/\s+/', ' ', $book_content->publishPlace);
            }
            $bookIrBook->xpublishplace = (!is_null($book_content->publishPlace)) ? $book_content->publishPlace : $bookIrBook->xpublishplace;
            $bookIrBook->xdescription = (!is_null($book_content->abstract)) ? $book_content->abstract : $bookIrBook->xdescription;
            // 'xweight'=>'' ;
            $bookIrBook->ximgeurl = (!is_null($book_content->imageAddress)) ? $book_content->imageAddress : $bookIrBook->ximgeurl;
            $bookIrBook->xpdfurl = (!is_null($book_content->pdfAddress)) ? $book_content->pdfAddress : $bookIrBook->xpdfurl;
            $bookIrBook->xregdate = time();
            $bookIrBook->is_translate = $is_translate;

            $bookIrBook->save();
            // $this->info('$bookIrBook->xid : ');
            // $this->info($bookIrBook->xid);

            //////////////////////////////////////////////// publisher data /////////////////////////////////////////
            $timeout = 120;
            $url = 'http://dcapi.k24.ir/test_get_publisher_id_majma/' . $book_content->publisherId;
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
            $publisher_content = curl_exec($ch);
            if (curl_errno($ch)) {
                // $this->info(" \n ---------- Try Get PUBLISHER " . $book_content->publisherId . "              ---------- ");
                echo 'error:' . curl_error($ch);
                MajmaApiPublisher::create(['xpublisher_id' => $book_content->publisherId, 'xstatus' => '500']);
            } else {
                MajmaApiPublisher::create(['xpublisher_id' => $book_content->publisherId, 'xstatus' => '200']);
                $publisher_content = json_decode($publisher_content);

                $publisher_content->title = remove_half_space_from_string($publisher_content->title);
                $publisher_content->title = convert_arabic_char_to_persian($publisher_content->title);

                $bookIrPublisher = BookirPublisher::where('xpageurl', 'http://ketab.ir//Publisherview.aspx?Publisherid=' . $publisher_content->id)->orWhere('xpageurl2', $publisher_content->url)->firstOrNew();

                // publisher data
                $publisher_manager = '';
                $publisher_manager .= (!is_null($publisher_content->managerFirstName)) ? $publisher_content->managerFirstName : '';
                $publisher_manager .= (!is_null($publisher_content->managerLastName)) ? ' ' . $publisher_content->managerLastName : '';

                $bookIrPublisher->xpageurl = 'http://ketab.ir//Publisherview.aspx?Publisherid=' . $publisher_content->id;
                $bookIrPublisher->xpageurl2 = $publisher_content->url;
                $bookIrPublisher->xpublishername = (!is_null($publisher_content->title)) ? $publisher_content->title : $bookIrPublisher->xpublishername;
                $bookIrPublisher->xmanager = (!empty($publisher_manager)) ? $publisher_manager : $bookIrPublisher->xmanager;
                // $bookIrPublisher->xactivity = '';
                if (!is_null($publisher_content->publisherPlace)) {
                    //Replace multiple whitespace characters with a single space
                    $publisher_content->publisherPlace = preg_replace('/\s+/', ' ', $publisher_content->publisherPlace);
                }
                $bookIrPublisher->xplace = (!is_null($publisher_content->publisherPlace)) ? $publisher_content->publisherPlace : $bookIrPublisher->xplace;
                $bookIrPublisher->xaddress = (!is_null($publisher_content->address)) ? $publisher_content->address : $bookIrPublisher->xaddress;
                // $bookIrPublisher->xpobox = '';
                $bookIrPublisher->xzipcode = (!is_null($publisher_content->postalCode)) ? $publisher_content->postalCode : $bookIrPublisher->xzipcode;
                $bookIrPublisher->xphone = (!is_null($publisher_content->phones)) ? implode('،', $publisher_content->phones) : $bookIrPublisher->xphone;
                $bookIrPublisher->xcellphone = (!is_null($publisher_content->mobile)) ? $publisher_content->mobile : $bookIrPublisher->xcellphone;
                $bookIrPublisher->xfax = (!is_null($publisher_content->fax)) ? $publisher_content->fax : $bookIrPublisher->xfax;
                $bookIrPublisher->xlastupdate = (!is_null($publisher_content->lastUpdateDate)) ? $publisher_content->lastUpdateDate : $bookIrPublisher->xlastupdate;
                // $bookIrPublisher->xtype = '';
                $bookIrPublisher->xpermitno = (!is_null($publisher_content->permitNumber)) ? $publisher_content->permitNumber : $bookIrPublisher->xpermitno;
                $bookIrPublisher->xemail = (!is_null($publisher_content->mail)) ? $publisher_content->mail : $bookIrPublisher->xemail;
                $bookIrPublisher->xsite = (!is_null($publisher_content->site)) ? $publisher_content->site : $bookIrPublisher->xsite;
                $bookIrPublisher->xisbnid = (!is_null($publisher_content->isbns)) ? implode(",", array_unique($publisher_content->isbns)) : $bookIrPublisher->xisbnid;
                $bookIrPublisher->xfoundingdate = (!is_null($publisher_content->foundingDate)) ? $publisher_content->foundingDate : $bookIrPublisher->xfoundingdate;
                // $bookIrPublisher->xispos = '';
                $bookIrPublisher->ximageurl = (!is_null($publisher_content->image)) ? $publisher_content->image : $bookIrPublisher->ximageurl;
                $bookIrPublisher->xregdate = time();
                $bookIrPublisher->xpublishername2 = str_replace(" ", "", $publisher_content->title);
                $bookIrPublisher->xisname = (!is_null($publisher_content->title)) ? 1 : 0;

                $bookIrPublisher->save();
                // $this->info('$bookIrPublisher->xid');
                // $this->info($bookIrPublisher->xid);

                if (isset($bookIrPublisher->xid) and !empty($bookIrPublisher->xid)) {
                    $bookIrBook->publishers()->sync($bookIrPublisher->xid);
                }
            }

            //////////////////////////////////////////////// partner data /////////////////////////////////////////////////
            unset($partner_array);
            if (!is_null($book_content->authors)) {
                foreach ($book_content->authors as $author_key => $author) {

                    $BookirPartner = BookirPartner::where('xketabir_id', $author->id)->firstOrNew();
                    $author->title = remove_half_space_from_string($author->title);
                    $author->title = convert_arabic_char_to_persian($author->title);

                    // partner data
                    if (mb_strpos($author->title, "،") > 0) {
                        $author_name = explode("،", $author->title);
                        $BookirPartner->xcreatorname = $author_name['1'] . ' ' . $author_name['0'];
                        $BookirPartner->xname2 = str_replace(" ", "", $BookirPartner->xcreatorname);
                    } else {
                        $BookirPartner->xcreatorname = $author->title;
                        $BookirPartner->xname2 = str_replace(" ", "", $author->title);
                    }

                    $BookirPartner->xketabir_id = $author->id;
                    $BookirPartner->xregdate = time();

                    $BookirPartner->save();

                    $BookirRules = BookirRules::where('xrole', $author->role)->first();
                    //rule data
                    if (empty($BookirRules)) {
                        $roleData = array(
                            'xrole' => $author->role,
                            'xregdate' => time(),
                        );
                        BookirRules::create($roleData);
                        $BookirRules = BookirRules::where('xrole', $author->role)->first();
                    }

                    $partner_array[$author_key]['xcreatorid'] = $BookirPartner->xid;
                    $partner_array[$author_key]['xroleid'] = $BookirRules->xid;

                    // $this->info('$BookirPartner->xid');
                    // $this->info($BookirPartner->xid);
                }

                if (isset($partner_array) and !empty($partner_array)) {
                    $bookIrBook->partnersRoles()->sync($partner_array);
                }
            }

            //////////////////////////////////////////////// subject data /////////////////////////////////////////////////////////
            unset($subjects_array);
            if (!is_null($book_content->parentSubject)) {

                $book_content->parentSubject = remove_half_space_from_string($book_content->parentSubject);
                $book_content->parentSubject = convert_arabic_char_to_persian($book_content->parentSubject);

                $BookirSubject = BookirSubject::where('xsubject', $book_content->parentSubject)->firstOrNew();
                $BookirSubject->xsubject = $book_content->parentSubject;
                $BookirSubject->xsubjectname2 = str_replace(" ", "", $book_content->parentSubject);
                $BookirSubject->xregdate = time();

                $BookirSubject->save();
                $subjects_array[] = $BookirSubject->xid;
                // $this->info('$BookirSubject->xid');
                // $this->info($BookirSubject->xid);
            }

            if (!is_null($book_content->subjects)) {
                foreach ($book_content->subjects as $subject) {
                    $subject = remove_half_space_from_string($subject);
                    $subject = convert_arabic_char_to_persian($subject);

                    $BookirSubject = BookirSubject::where('xsubject', $subject)->firstOrNew();
                    $BookirSubject->xsubject = $subject;
                    $BookirSubject->xsubjectname2 = str_replace(" ", "", $subject);
                    $BookirSubject->xregdate = time();

                    $BookirSubject->save();
                    // $this->info('$BookirSubject->xid');
                    // $this->info($BookirSubject->xid);
                    $subjects_array[] = $BookirSubject->xid;
                }
            }

            if (isset($subjects_array) and !empty($subjects_array)) {
                $bookIrBook->subjects()->sync($subjects_array);
            }

            //////////////////////////////////////////////// age group////////////////////////////////////////////////////////////////
            unset($ageGroup_array);
            if (!is_null($book_content->ageGroup)) {
                ($book_content->ageGroup->a == true) ? $ageGroup_array['xa'] = 1 : $ageGroup_array['xa'] = 0;
                ($book_content->ageGroup->b == true) ? $ageGroup_array['xb'] = 1 : $ageGroup_array['xb'] = 0;
                ($book_content->ageGroup->g == true) ? $ageGroup_array['xg'] = 1 : $ageGroup_array['xg'] = 0;
                ($book_content->ageGroup->d == true) ? $ageGroup_array['xd'] = 1 : $ageGroup_array['xd'] = 0;
                ($book_content->ageGroup->h == true) ? $ageGroup_array['xh'] = 1 : $ageGroup_array['xh'] = 0;

                if (isset($ageGroup_array) and !empty($ageGroup_array)) {
                    AgeGroup::updateOrCreate(
                        ['xbook_id' => $bookIrBook->xid],
                        $ageGroup_array
                    );
                }
            }
        }

        return $api_status;
    }
}


///////////////////////////////////////////////////////////////digikala//////////////////////////////////////////
if (!function_exists('updateDigiBook')) {
    function updateDigiBook($recordNumber, $bookDigi, $function_caller = NULL)
    {

        $id = str_replace('dkp-', '', $recordNumber);
        $productUrl = "https://api.digikala.com/v2/product/" . $id . "/";
        try {
            echo " \n ---------- Try Get BOOK        " . $id . "       ---------- ";
            $json = file_get_contents($productUrl);
            $product_info =  json_decode($json);
            $headers = get_headers($productUrl);
            $api_status = $product_info->status;
            MajmaApiBook::create(['xbook_id' => $id, 'xstatus' => $api_status, 'xfunction_caller' => $function_caller]);
        } catch (\Exception $e) {
            $crawler = null;
            $api_status = 500;
            MajmaApiBook::create(['xbook_id' => $id, 'xstatus' => $api_status, 'xfunction_caller' => $function_caller]);
        }


        if ($api_status == 200) {
            // if (isset($product_info->data->product->id) and !empty($product_info->data->product->id)) {
            //     $bookDigi = BookDigi::where('recordNumber', 'dkp-' . $product_info->data->product->id)->firstOrNew();
            //     $bookDigi->recordNumber = 'dkp-' . $product_info->data->product->id;
            // }

            if (isset($product_info->data->product->title_fa) and !empty($product_info->data->product->title_fa)) {
                $bookDigi->title = $product_info->data->product->title_fa;
                $bookDigi->title = convert_arabic_char_to_persian(remove_half_space_from_string($bookDigi->title));
            }


            if (isset($product_info->data->product->rating->rate) and !empty($product_info->data->product->rating->rate)) {
                $bookDigi->rate = $product_info->data->product->rating->rate / 20;
            }

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
            if (isset($product_info->data->product->specifications['0']->title) and $product_info->data->product->specifications['0']->title == 'مشخصات') {
                foreach ($product_info->data->product->specifications['0']->attributes as $attribute) {

                    // نویسنده تو جدول author_book_digi ذخیره میشه 
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
                                $shabak = validateIsbnWithRemoveDash($shabak);
                                $shabakStr .=  $shabak . '#';
                            }
                            $bookDigi->shabak = $shabakStr;
                        } else {

                            $bookDigi->shabak = validateIsbnWithRemoveDash($attribute->values['0']);
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
                                $tedadSafeStr .=  enNumberKeepOnly($tedadSafe) . '#';
                            }
                            $bookDigi->tedadSafe = $tedadSafeStr;
                        } else {
                            $bookDigi->tedadSafe = enNumberKeepOnly($attribute->values['0']);
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
            if (isset($product_info->data->product->tags) and !empty($product_info->data->product->tags)) {
                foreach ($product_info->data->product->tags as $tag) {
                    $tag_string .= $tag->name . '#';
                }
            }


            $bookDigi->tag = $tag_string;

            $bookDigi->price = (isset($product_info->data->product->variants['0']->price->rrp_price)) ? (int)$product_info->data->product->variants['0']->price->rrp_price : 0;
            $bookDigi->desc = (isset($product_info->data->product->expert_reviews->description)) ? $product_info->data->product->expert_reviews->description : NULL;
            $bookDigi->save();
            // book author
            if (isset($authorsobj->id)) {
                $bookDigi->authors()->sync(array($authorsobj->id));
                // $this->info(" \n ---------- Attach Author Book   " . $authorsobj->id . "  To " . $id . "        ---------- ");
            }
            //book related
            if (isset($product_info->data->recommendations->related_products->title) and $product_info->data->recommendations->related_products->title == "کالاهای مشابه") {
                $related_array = array();
                foreach ($product_info->data->recommendations->related_products->products as $related_product) {
                    if (check_digi_id_is_book($related_product->id)) {
                        $related_product_digi =  BookDigi::where('recordNumber', 'dkp-' . $related_product->id)->firstOrNew();
                        $related_product_digi->recordNumber = 'dkp-' . $related_product->id;
                        $related_product_digi->save();
                        $related = bookDigiRelated::firstOrCreate(array('book_id' => $related_product->id));
                        array_push($related_array, $related->id);
                    }
                }
                $bookDigi->related()->sync($related_array);
            }
        } else {
            echo " \n ---------- Inappropriate Content              ----------- ";
        }

        return $api_status;
    }
}


if (!function_exists('check_digi_book_status')) {
    function check_digi_book_status($recordNumber)
    {
        $id = str_replace('dkp-', '', $recordNumber);
        $productUrl = "https://api.digikala.com/v2/product/" . $id . "/";
        try {
            // $this->info(" \n ---------- Try Get BOOK        " . $id . "       ---------- ");
            $json = file_get_contents($productUrl);
            $product_info =  json_decode($json);
            $headers = get_headers($productUrl);
            $api_status = $product_info->status;
            // MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => $api_status, 'xfunction_caller' => $function_caller]);

        } catch (\Exception $e) {
            $crawler = null;
            $api_status = 500;
            // MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => $api_status, 'xfunction_caller' => $function_caller]);
            // $this->info(" \n ---------- Failed Get  " . $id . "              ------------ ");
        }


        if ($api_status == 200) {
            if (isset($product_info->data->product->is_inactive) and $product_info->data->product->is_inactive == true) {
                return 'is_inactive';
            } else {
                if (isset($product_info->data->product->breadcrumb) and !empty($product_info->data->product->breadcrumb)) {
                    if ($product_info->data->product->breadcrumb['1']->url->uri == '/main/book-and-media/') {
                        return 'is_book';
                    } else {
                        return 'is_not_book';
                    }
                } else {
                    return 'unknown';
                }
            }
        }
    }
}


if (!function_exists('check_digi_id_is_book')) {
    function check_digi_id_is_book($recordNumber)
    {
        $result_check_digi_book_status = check_digi_book_status('dkp-' . $recordNumber);
        if ($result_check_digi_book_status == 'is_book') {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}


////////////////////////////////////////////////////////fidibo///////////////////////////////////////////////////

if (!function_exists('updateFidiboBook')) {
    function updateFidiboBook($recordNumber, $bookFidibo, $function_caller = NULL)
    {
        try {
            $timeout = 120;
            $pageUrl = 'https://api.fidibo.com/flex/page?pageName=BOOK_OVERVIEW&bookId=' . $recordNumber . '&page=1&limit=1000';
            $json = file_get_contents($pageUrl);
            $page_info = json_decode($json);
            if ($page_info->data->result != null) {
                $status_code = 200;
            } else {
                $status_code = 500;
            }
            MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => $status_code, 'xfunction_caller' => $function_caller]);

        } catch (\Exception $e) {
            $status_code = 500;
            MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => $status_code, 'xfunction_caller' => $function_caller]);

        }
        if ($status_code == "200") {


            ///////////////////////////////////////book//////////////////////////
            try {
                $timeout = 120;
                $bookUrl = 'https://api.fidibo.com/flex/book/item/' . $recordNumber;
                $json = file_get_contents($bookUrl);
                $book_info = json_decode($json);
                if ($book_info->data->result != null) {
                    $book_status_code = 200;
                } else {
                    $book_status_code = 500;
                }
            } catch (\Exception $e) {
                $book_status_code = 500;
                // $this->info(" \n ---------- Failed Get  Image" . $recordNumber . "              ------------ ");
            }

            if ($book_status_code == "200") {
                if (isset($book_info->data->result) and !empty($book_info->data->result)) {
                    foreach ($book_info->data->result as $book_result) {
                        $bookFidibo->images = (isset($book_result->cover->image))? $book_result->cover->image: NULL;
                        if(isset($book_result->breadcrumb) AND !empty($book_result->breadcrumb)){
                            $tagStr = '';
                            foreach ($book_result->breadcrumb  as $tag) {
                                $tagStr .= $tag->name . '#';
                            }
                            $tagStr = rtrim($tagStr, '#');
                            $bookFidibo->tags = $tagStr;
                        }
                    }
                }
            }
            ///////////////////////////////////////////////////////////////////////

            $bookFidibo->recordNumber = $recordNumber;
            if (isset($page_info->data->result) and !empty($page_info->data->result)) {
                foreach ($page_info->data->result as $result) {
                    if (isset($result->subtitle) and $result->subtitle == 'معرفی') {
                        $book_title  = ltrim($result->items['0']->introduction->title,'درباره');
                        $book_title = ltrim($book_title, 'کتاب');
                        $bookFidibo->title = $book_title;
                        $bookFidibo->desc = (isset($result->items['0']->introduction->description)) ? $result->items['0']->introduction->description : NULL;
                        // $this->info( $bookFidibo->desc );
                       
                    }


                    if (isset($result->title) and $result->title == 'شناسنامه') {
                        $partner = array();
                        $partnerCount = 0;
                        foreach ($result->items['0']->specifications as $attribute) {


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
                                // $this->info($attribute->sub_title);
                            }

                            if ($attribute->title == 'مترجم') {
                                $partner[$partnerCount]['roleId'] = 2;
                                $partner[$partnerCount]['name'] = $attribute->sub_title;
                                $bookFidibo->translate = 1;
                                // $this->info($attribute->sub_title);
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
                                $bookFidibo->title_en = str_replace('کتاب', '', $attribute->sub_title);
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
        

    }
}


///////////////////////////////////////////////////gissom//////////////////////////////////////////////////////
if (!function_exists('updateGisoomBook')) {
    function updateGisoomBook($recordNumber, $bookGissom, $function_caller = NULL)
    {
        $client = new Client(HttpClient::create(['timeout' => 30]));

        try {
            echo " \n ---------- Try Get BOOK " . $recordNumber . " ---------- ";
            // $crawler = $client->request('GET', 'http://188.253.2.66/proxy.php?url=https://www.gisoom.com/book/' . $recordNumber);
            // $crawler = $client->request('GET', 'https://www.gisoom.com/book/' . $recordNumber . '/book_name/');
            $crawler = $client->request('GET', 'http://asr.dmedia.ir/getgisoom/' . $recordNumber . '/');
            $status_code = $client->getInternalResponse()->getStatusCode();
            MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => $status_code, 'xfunction_caller' => $function_caller]);

        } catch (\Exception $e) {
            $crawler = null;
            $status_code = 500;
            MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => $status_code, 'xfunction_caller' => $function_caller]);

        }
        // dd($crawler->filter('body div.bookinfocol')->count());
        if ($status_code == 200 && $crawler->filter('body')->text('') != '' && $crawler->filter('body div.bookinfocol')->count() > 0) {
            $authors = array();

            $bookGissom->title = $crawler->filter('body div.bookinfocol div h1 a')->text();
            foreach ($crawler->filter('body div.bookinfocol div.col') as $col) {
                if (strpos($col->textContent, 'ناشر:') !== false) {
                    $bookGissom->nasher = str_replace('ناشر:', '', $col->textContent);
                }
                if (strpos($col->textContent, 'ویراستار:') !== false) {
                    $bookGissom->editor = str_replace('ویراستار:', '', $col->textContent);
                }
                if (strpos($col->textContent, 'ویراستاران:') !== false) {
                    $bookGissom->editor = str_replace('ویراستاران:', '', $col->textContent);
                }
                if (strpos($col->textContent, 'مترجمان:') !== false || strpos($col->textContent, 'مترجم:') !== false) {
                    $bookGissom->tarjome = true;
                }
                if (strpos($col->textContent, 'پدیدآوران:') !== false || strpos($col->textContent, 'مترجمان:') !== false || strpos($col->textContent, 'مترجم:') !== false || strpos($col->textContent, 'مؤلف:') !== false || strpos($col->textContent, 'مؤلفان:') !== false) {
                    $colc = new Crawler($col);
                    foreach ($colc->filter('a') as $link) {
                        $authorObject = Author::firstOrCreate(array("d_name" => $link->textContent));
                        $authors[] = $authorObject->id;
                    }
                }
                if (strpos($col->textContent, 'زبان:') !== false) {
                    $bookGissom->lang = str_replace('زبان:', '', $col->textContent);
                }
                if (strpos($col->textContent, 'رده‌بندی دیویی:') !== false) {
                    $bookGissom->radeD = str_replace('رده‌بندی دیویی:', '', $col->textContent);
                }
                if (strpos($col->textContent, 'سال چاپ:') !== false) {
                    $bookGissom->saleNashr = enNumberKeepOnly(faCharToEN(str_replace('سال چاپ:', '', $col->textContent)));
                }
                if (strpos($col->textContent, 'نوبت چاپ:') !== false) {
                    $bookGissom->nobatChap = enNumberKeepOnly(faCharToEN(str_replace('نوبت چاپ:', '', $col->textContent)));
                }
                if (strpos($col->textContent, 'تیراژ:') !== false) {
                    $bookGissom->tiraj = enNumberKeepOnly(faCharToEN(str_replace('تیراژ:', '', $col->textContent)));
                }
                if (strpos($col->textContent, 'تعداد صفحات:') !== false) {
                    $bookGissom->tedadSafe = enNumberKeepOnly(faCharToEN(str_replace('تعداد صفحات:', '', $col->textContent)));
                }
                if (strpos($col->textContent, 'قطع و نوع جلد:') !== false) {
                    $bookGissom->ghateChap = str_replace('قطع و نوع جلد:', '', $col->textContent);
                }

                if (strpos($col->textContent, 'شابک 10 رقمی:') !== false) {
                    $bookGissom->shabak10 = str_replace('شابک 10 رقمی:', '', $col->textContent);
                }
                if (strpos($col->textContent, 'شابک 13 رقمی:') !== false) {
                    $bookGissom->shabak13 = str_replace('شابک 13 رقمی:', '', $col->textContent);
                }
                if (strpos($col->textContent, 'شابک:') !== false) {
                    $shabak = str_replace('شابک:', '', $col->textContent);
                    if (strlen($shabak) >= 13) {
                        $bookGissom->shabak13 =  $shabak;
                    } else {
                        $bookGissom->shabak10 =  $shabak;
                    }
                }
                if (strpos($col->textContent, 'توضیح کتاب:') !== false) {
                    $bookGissom->desc = str_replace('توضیح کتاب:', '', $col->textContent);
                }
            }
            $categories = array();
            foreach ($crawler->filter("div.nav-wrapper a") as $catLinks) {
                if ($catLinks->textContent != 'کتاب') $categories[] = $catLinks->textContent;
            }
            $bookGissom->price = 0;
            $dibcontent = $crawler->filter('body a.iwantbook span.dib')->first()->text('');
            $dbcontent = $crawler->filter('body a.iwantbook span.db')->first()->text('');
            if ($dibcontent != '') {
                $bookGissom->price = enNumberKeepOnly(faCharToEN($dibcontent));
            } elseif ($dbcontent != '') {
                $bookGissom->price = enNumberKeepOnly(faCharToEN($dbcontent));
            }
            $bookGissom->catText = implode("-|-", $categories);
            $bookGissom->image = $crawler->filter('body img.cls3')->attr('src');
            $bookGissom->recordNumber = $recordNumber;
            $bookGissom->save();
            // $this->info(" \n ---------- Inserted Book " . $recordNumber . " ---------- ");
            // dd($authors);
            // if (count($authors) > 0) {
            $bookGissom->authors()->sync($authors);
            // $this->info(" \n ---------- sync Author Book " . $recordNumber . " ---------- ");
            // }
        }
    }
}

///////////////////////////////////////////////barkhat book/////////////////////////////////////////////////////////
if (!function_exists('updateBarKhatBookCategoriesAllBooks')) {
    function updateBarKhatBookCategoriesAllBooks()
    {
        $cats = SiteCategories::where('domain', 'https://barkhatbook.com/')->get();
        foreach ($cats as $cat) {
            // find count  books for loop
            $timeout = 120;
            $url = 'https://barkhatbook.com/api/quick?page=1';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "value=" . $cat->cat_link . "&type=cat&sort=0&onsale=0");
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            $cat_page_content = curl_exec($ch);
            // dd($cat_book_content);
            if (curl_errno($ch)) {
                echo 'error:' . curl_error($ch);
            } else {
                $cat_page_content = json_decode($cat_page_content);
                $cat_pages = $cat_page_content->books->last_page;
            }

            $x = 1;
            while ($x <= $cat_pages) {
                $timeout = 120;
                $url = 'https://barkhatbook.com/api/quick?page=' . $x;
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "value=" . $cat->cat_link . "&type=cat&sort=0&onsale=0");
                curl_setopt($ch, CURLOPT_FAILONERROR, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_ENCODING, "");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                $cat_book_content = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'error:' . curl_error($ch);
                } else {
                    $cat_book_content = json_decode($cat_book_content);
                    foreach ($cat_book_content->books->data as $book) {
                        // echo 'product/bk_' . $book->code . '/' . $book->title . '<br>';
                        $book->title = str_replace("#", "", $book->title);
                        $book->title = str_replace("?", "", $book->title);
                        $book->title = str_replace("/", "", $book->title);
                        $book->title = (empty($book->title)) ?  'book_name' : $book->title;
                        SiteBookLinks::firstOrCreate(array('domain' => 'https://barkhatbook.com/', 'book_links' => 'product/bk_' . $book->code . '/' . $book->title));
                    }
                }
                $x++;
            }
        }
    }
}

if (!function_exists('updateBarKhatBookCategoriesFirstPageBooks')) {
    function updateBarKhatBookCategoriesFirstPageBooks()
    {
        $cats = SiteCategories::where('domain', 'https://barkhatbook.com/')->get();
        foreach ($cats as $cat) {
            // find count  books for loop
            $timeout = 120;
            $url = 'https://barkhatbook.com/api/quick?page=1';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "value=" . $cat->cat_link . "&type=cat&sort=0&onsale=0");
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            $cat_book_content = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'error:' . curl_error($ch);
            } else {
                $cat_book_content = json_decode($cat_book_content);
                foreach ($cat_book_content->books->data as $book) {
                    // echo 'product/bk_' . $book->code . '/' . $book->title . '<br>';
                    $book->title = str_replace("#", "", $book->title);
                    $book->title = str_replace("?", "", $book->title);
                    $book->title = str_replace("/", "", $book->title);
                    $book->title = (empty($book->title)) ?  'book_name' : $book->title;
                    SiteBookLinks::firstOrCreate(array('domain' => 'https://barkhatbook.com/', 'book_links' => 'product/bk_' . $book->code . '/' . $book->title));
                }
            }
        }
    }
}


if (!function_exists('updateBarKhatBookCategories')) {
    function updateBarKhatBookCategories()
    {
        $timeout = 120;
        $url = 'https://barkhatbook.com/api/menu';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        $menu_content = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'error:' . curl_error($ch);
        } else {
            $menu_content = json_decode($menu_content);
            foreach ($menu_content->cats as $cat) {
                // echo $cat->name . '</br>';
                SiteCategories::firstOrCreate(array('domain' => 'https://barkhatbook.com/', 'cat_link' => $cat->id, 'cat_name' => $cat->name));
            }
        }
    }
}

if (!function_exists('updateBarkhatBook')) {
    function updateBarkhatBook($bookLink, $function_caller = NULL)
    {
        $client = new Client(HttpClient::create(['timeout' => 30]));
        try {
            echo " \n ---------- Try Get BOOK " . $bookLink->book_links . "              ---------- ";

            $crawler = $client->request('GET', 'https://barkhatbook.com/' . $bookLink->book_links, [
                'headers' => [
                    'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                ],
            ]);

            $status_code = $client->getInternalResponse()->getStatusCode();
            MajmaApiBook::create(['xbook_id' => $bookLink->id, 'xstatus' => $status_code, 'xfunction_caller' => $function_caller]);
        } catch (\Exception $e) {
            $crawler = null;
            $status_code = 500;
            MajmaApiBook::create(['xbook_id' => $bookLink->id, 'xstatus' => $status_code, 'xfunction_caller' => $function_caller]);
        }
        if ($status_code == 200 and $crawler->filter('body div.container-fluid single-product')->count() > 0) {
            $book_json_info = $crawler->filter('body div.container-fluid single-product')->attr('v-bind:product_lv');
            $book_info = json_decode($book_json_info);

            if (isset($book_info) and !empty($book_info)) {
                $partner =  array();
                $book = BookBarkhatBook::where('recordNumber', $book_info->product->code)->firstOrNew();
                $book->recordNumber = $book_info->product->code;

                //  = $book_info->product->id;
                // = $book_info->product->title;
                $book->weight  = $book_info->product->weight;
                //  = $book_info->product->productTypeId;
                //  = $book_info->product->code;
                $book->price  = $book_info->product->price;
                //  = $book_info->product->discount;
                //  = $book_info->product->specialEnd;
                //  = $book_info->product->isSpecial;
                //  = $book_info->product->catId;
                //   = $book_info->product->bio;
                //  = $book_info->product->onSale;
                //  = $book_info->product->scoreCount;
                //  = $book_info->product->score;
                //  = $book_info->product->hasSendFree;
                //  = $book_info->product->audio_url;
                //  = $book_info->product->pdf_url;
                if (isset($book_info->product->category) and !empty($book_info->product->category)) {
                    $book_cats = '';
                    foreach ($book_info->product->category as $category) {
                        if (isset($category->name) and !empty($category->name)) {
                            $book_cats = $book_cats . "-|-" . $category->name;
                        }
                    }
                    $book->cats = $book_cats;
                }
                //  = $book_info->product->tags;
                //  = $book_info->product->category->id;
                //  = $book_info->product->category->name;
                //  = $book_info->product->books->id;
                if (isset($book_info->product->books) and !empty($book_info->product->books)) {
                    foreach ($book_info->product->books as $book_item) {
                        $book->title  = $book_item->title;
                        $book->saleNashr = $book_item->year;
                        $book->nobatChap  = $book_item->published;
                        $book->shabak  = $book_item->isbn;
                        $book->desc  = $book_item->mainSubject;
                        $book->subTopic = $book_item->subTopic;
                        if (isset($book_item->authorName) and !empty($book_item->authorName)) {
                            $partner[0]['roleId'] = 1;
                            $partner[0]['name'] = $book_item->authorName;
                        }
                        if (isset($book_item->translatorName) and !empty($book_item->translatorName)) {
                            $book->translate = 1;
                            $partner[1]['roleId'] = 2;
                            $partner[1]['name'] = $book_item->translatorName;
                        }
                        if (isset($book_item->publisherName) and !empty($book_item->publisherName)) {
                            $book->nasher = $book_item->publisherName;
                        }

                        //  = $book_item->onSale;
                        //  = $book_item->availableCount;
                        //  = $book_item->productId;
                        $book->tedadSafe  = $book_item->pages;
                        $book->jeld  = $book_item->bookCover;
                        $book->ghateChap  = $book_item->bookSize;
                    }
                }

                if (isset($book_info->product->images) and !empty($book_info->product->images)) {
                    foreach ($book_info->product->images as $image) {
                        $book->images . '=|=' . $image->imageUrl;
                    }
                }
                //  = $book_info->product->images->id;
                //  = $book_info->product->images->imageUrl;
                //  = $book_info->product->images->alt;
                //  = $book_info->product->images->productId;
                $book->partnerArray = json_encode($partner, JSON_UNESCAPED_UNICODE);
                $book->save();
                SiteBookLinks::where('domain', 'https://barkhatbook.com/')->where('book_links', $bookLink->book_links)->update(['status' => 1]);
            }
        } else {
            echo " \n ---------- Inappropriate Content              ----------- ";
        }
        return $status_code;
    }
}

//////////////////////////////////////////////////////ketabejam///////////////////////////////////////////////////
if (!function_exists('updateKetabejamCategoriesAllBooks')) {
    function updateKetabejamCategoriesAllBooks()
    {
        //category
        $cats = SiteCategories::where('domain', 'https://ketabejam.com/')->get();
        foreach ($cats as $cat) {
            // find count  books for loop
            $client = new Client(HttpClient::create(['timeout' => 30]));
            try {
                $crawler = $client->request('GET', $cat->cat_link, [
                    'headers' => [
                        'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                    ],
                ]);

                $status_code = $client->getInternalResponse()->getStatusCode();
            } catch (\Exception $e) {
                $crawler = null;
                $status_code = 500;
            }
            if ($status_code == 200 /*and $crawler->filterXPath('//*[@id="main-container"]')->count() > 0*/) {
                if (str_contains($result_count = $crawler->filter('body main section div.woo-listing-top p.woocommerce-result-count')->text(), 'نمایش یک نتیجه')) {
                    $cat_pages = 1;
                } else {
                    $result_count  =  str_replace("نمایش 1–30 از", "", $result_count);
                    $total_result  =  enNumberKeepOnly(faCharToEN($result_count));
                    $cat_pages = ceil((int)$total_result / 30);
                }
            }


            $x = 1;
            while ($x <= $cat_pages) {

                $client = new Client(HttpClient::create(['timeout' => 30]));
                try {
                    $crawler = $client->request('GET', $cat->cat_link . '/page/' . $x . '/', [
                        'headers' => [
                            'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                        ],
                    ]);

                    $status_code = $client->getInternalResponse()->getStatusCode();
                } catch (\Exception $e) {
                    $crawler = null;
                    $status_code = 500;
                }
                if ($status_code == 200 /*and $crawler->filterXPath('//*[@id="main-container"]')->count() > 0*/) {
                    foreach ($crawler->filter('body main section ul li') as $book) {
                        $book_li = new Crawler($book);
                        $SiteBookLinks = SiteBookLinks::where('domain', 'https://ketabejam.com/')->where('book_links', $book_li->filter('a')->attr('href'))->first();
                        if (empty($SiteBookLinks)) {
                            SiteBookLinks::firstOrCreate(array('domain' => 'https://ketabejam.com/', 'book_links' => $book_li->filter('a')->attr('href')));
                        }
                    }
                }
                $x++;
            }
        }
    }
}

if (!function_exists('updateKetabejamCategoriesFirstPageBooks')) {
    function updateKetabejamCategoriesFirstPageBooks()
    {
        //category
        $cats = SiteCategories::where('domain', 'https://ketabejam.com/')->get();
        foreach ($cats as $cat) {

            $client = new Client(HttpClient::create(['timeout' => 30]));
            try {
                $crawler = $client->request('GET', $cat->cat_link, [
                    'headers' => [
                        'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                    ],
                ]);

                $status_code = $client->getInternalResponse()->getStatusCode();
            } catch (\Exception $e) {
                $crawler = null;
                $status_code = 500;
            }
            if ($status_code == 200 and $crawler->filterXPath('//*[@id="main-container"]')->count() > 0) {
                foreach ($crawler->filter('body main section ul li') as $book) {
                    $book_li = new Crawler($book);
                    $SiteBookLinks = SiteBookLinks::where('domain', 'https://ketabejam.com/')->where('book_links', $book_li->filter('a')->attr('href'))->first();
                    if (empty($SiteBookLinks)) {
                        SiteBookLinks::firstOrCreate(array('domain' => 'https://ketabejam.com/', 'book_links' => $book_li->filter('a')->attr('href')));
                    }
                }
            }
        }
    }
}


if (!function_exists('updateKetabejamCategories')) {
    function updateKetabejamCategories()
    {
        // menu
        $client = new Client(HttpClient::create(['timeout' => 30]));
        try {
            $crawler = $client->request('GET', 'https://ketabejam.com/', [
                'headers' => [
                    'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                ],
            ]);

            $status_code = $client->getInternalResponse()->getStatusCode();
        } catch (\Exception $e) {
            $crawler = null;
            $status_code = 500;
        }

        if ($status_code == 200 /*and $crawler->filterXPath('//*[@id="main-container"]')->count() > 0*/) {
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
    }
}


if (!function_exists('updateKetabejamBook')) {
    function updateKetabejamBook($bookLink, $function_caller = NULL)
    {
        $client = new Client(HttpClient::create(['timeout' => 30]));
        try {
            echo " \n ---------- Try Get BOOK " . $bookLink->book_links . "              ---------- ";
            $crawler = $client->request('GET', $bookLink->book_links, [
                'headers' => [
                    'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                ],
            ]);

            $status_code = $client->getInternalResponse()->getStatusCode();
            MajmaApiBook::create(['xbook_id' => $bookLink->id, 'xstatus' => $status_code, 'xfunction_caller' => $function_caller]);
        } catch (\Exception $e) {
            $crawler = null;
            $status_code = 500;
            MajmaApiBook::create(['xbook_id' => $bookLink->id, 'xstatus' => $status_code, 'xfunction_caller' => $function_caller]);
        }
        // $this->info($crawler->filterXPath('//*[@class="arm-circle"]')->count());
        // $this->info($crawler->filterXPath('//nav[contains(@class, "navbar navbar-expand arm-bg-cream arm-pd-u-15 arm-fix-style")]')->count());


        if ($status_code == 200 and $crawler->filterXPath('//*[@id="main-container"]')->count() > 0) {

            // $this->info($crawler->filter('body main article header nav.ct-breadcrumbs span')->count());
            $book_cats = '';
            foreach ($crawler->filter('body main article header nav.ct-breadcrumbs span a') as $nav) {
                $breadcrumb = new Crawler($nav);
                if (isset($book_cats) and !empty($book_cats)) {
                    $book_cats = $book_cats . "-|-" . ltrim(rtrim(convert_arabic_char_to_persian($breadcrumb->text())));
                } else {
                    $book_cats = ltrim(rtrim(convert_arabic_char_to_persian($breadcrumb->text())));
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

            if ($crawler->filter('body main article div.product div.product-entry-wrapper div.summary p.price span.sale-price span.woocommerce-Price-amount bdi')->count() > 0) {
                $book->price = enNumberKeepOnly(faCharToEN($crawler->filter('body main article div.product div.product-entry-wrapper div.summary p.price span.sale-price span.woocommerce-Price-amount bdi')->text()));
            }

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
                    $book->tedadSafe = enNumberKeepOnly(faCharToEN(trim($detail_tr->filterXPath('//td[1]')->text())));
                }
                if (trim($detail_tr->filterXPath('//th[1]')->text()) == 'سال انتشار') {
                    $book->saleNashr = enNumberKeepOnly(faCharToEN(trim($detail_tr->filterXPath('//td[1]')->text())));
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

            $book->save();
            SiteBookLinks::where('domain', 'https://ketabejam.com/')->where('book_links', $bookLink->book_links)->update(['status' => 1]);
        } else {
            echo " \n ---------- Inappropriate Content              ----------- ";
        }
        return $status_code;
    }
}

///////////////////////////////////////////////////shahre onlne ketab//////////////////////////////////////////////
if (!function_exists('updateShahreketabonlineBook')) {
    function updateShahreketabonlineBook($recordNumber, $book, $function_caller = NULL)
    {
        $client = new Client(HttpClient::create(['timeout' => 30]));

        try {
            echo " \n ---------- Try Get BOOK " . $recordNumber . "              ---------- ";
            $crawler = $client->request('GET', 'https://shahreketabonline.com/Products/Details/' . $recordNumber, [
                'headers' => [
                    'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36',
                ],
            ]);
            $status_code = $client->getInternalResponse()->getStatusCode();
            MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => $status_code, 'xfunction_caller' => $function_caller]);
        } catch (\Exception $e) {
            $crawler = null;
            $status_code = 500;
            MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => $status_code, 'xfunction_caller' => $function_caller]);
        }

        if ($status_code == 200 and $crawler->filter('body div.ProductDetails')->count() > 0) {
            $book_cats = '';
            foreach ($crawler->filter('body ol.breadcrumb li a') as $cat) {
                if (isset($book_cats) and !empty($book_cats)) {
                    $book_cats = $book_cats . "-|-" . ltrim(rtrim(convert_arabic_char_to_persian($cat->textContent)));
                } else {
                    $book_cats = ltrim(rtrim(convert_arabic_char_to_persian($cat->textContent)));
                }
            }
            if (isset($book_cats)) $cats_arr = explode('-|-', $book_cats);

            if (!in_array('نوشت افزار', $cats_arr) && !in_array('محصولات فرهنگی', $cats_arr) && !in_array('صنایع دستی', $cats_arr) && !in_array('هنری', $cats_arr)) {


                $book->cats = $book_cats;


                // image
                if ($crawler->filter('body div.ProductDetails div.ProductInfo div.Image div.book-wrap img.book-image')->count() > 0) {
                    $book->images  = 'https://shahreketabonline.com' . $crawler->filter('body div.ProductDetails div.ProductInfo div.Image div.book-wrap img.book-image')->attr('src');
                }
                if ($crawler->filter('body div.ProductDetails div.ProductInfo div.Image div.book-wrap div.OtherImages')->count() > 0) {
                    $book->images =  $book->images . '=|=' . 'https://shahreketabonline.com' . $crawler->filter('body div.ProductDetails div.ProductInfo div.Image div.book-wrap div.OtherImages a img')->attr('src');
                }

                // price 
                if ($crawler->filter('body div.ProductDetails div.ProductInfo div.AddProductToCart div.Price')->count() > 0) {
                    $price  = enNumberKeepOnly(faCharToEN($crawler->filter('body div.ProductDetails div.ProductInfo div.AddProductToCart div.Price')->text()));
                    if (strlen($price) < 10) {
                        $book->price = $price;
                    }
                }

                // desc 
                if ($crawler->filter('body div.ProductDetails div.description')->count() > 0) {
                    $book->Desc  = $crawler->filter('body div.ProductDetails div.description')->text();
                }

                // details
                $book->title  = $crawler->filter('body div.ProductDetails div.ProductInfo div.Details h1')->text();

                $partner =  array();
                foreach ($crawler->filter("body div.ProductDetails div.ProductInfo div.Details div.mt-1 div.Attributes div.Attribute") as $trTable) {
                    $trObj = new Crawler($trTable);

                    switch ($trObj->filter('div.LightText')->first()->text()) {
                        case 'شابک:':
                            $book->shabak = $trObj->filter('div.LightText')->nextAll()->text();
                            break;
                        case 'موضوع:':
                            $book->subject = $trObj->filter('div.LightText')->nextAll()->text();
                            break;
                        case 'نویسنده:':
                            if ($trObj->filter('div.LightText')->nextAll()->text() != '') {
                                // $this->info($trObj->filter('div.LightText')->nextAll()->text());
                                // foreach($trObj->filter('a div') as $link){
                                // $authorObject = Author::firstOrCreate(array("d_name" => $trObj->filter('div.LightText')->nextAll()->text()));
                                // $authors[] = $authorObject->id;

                                $partner[0]['roleId'] = 1;
                                $partner[0]['name'] = $trObj->filter('div.LightText')->nextAll()->text();
                                // }
                            }
                            break;
                        case 'مترجم:':
                            if ($trObj->filter('div.LightText')->nextAll()->text() != '') {
                                $book->translate = 1;
                                // foreach($trObj->filter('a') as $link){
                                // $authorObject = Author::firstOrCreate(array("d_name" => $trObj->filter('div.LightText')->nextAll()->text()));
                                // $authors[] = $authorObject->id;
                                $partner[1]['roleId'] = 2;
                                $partner[1]['name'] = $trObj->filter('div.LightText')->nextAll()->text();
                                // }
                            }
                            break;
                        case 'انتشارات:':
                            $book->nasher = $trObj->filter('div.LightText')->nextAll()->text();
                            break;
                            // case 'نوبت چاپ':
                            //     $book->nobatChap = $trObj->filter('div.LightText')->nextAll()->text();
                            //     break;
                        case 'شماره چاپ:':
                            $book->nobatChap = $trObj->filter('div.LightText')->nextAll()->text();
                            break;
                        case 'زبان:':
                            $book->lang = $trObj->filter('div.LightText')->nextAll()->text();
                            break;
                        case 'قطع:':
                            $book->ghateChap = $trObj->filter('div.LightText')->nextAll()->text();
                            break;
                        case 'جلد:':
                            $book->jeld = $trObj->filter('div.LightText')->nextAll()->text();
                            break;
                        case 'تعداد صفحه:':
                            $book->tedadSafe = (enNumberKeepOnly(faCharToEN($trObj->filter('div.LightText')->nextAll()->text()))) ? enNumberKeepOnly(faCharToEN($trObj->filter('div.LightText')->nextAll()->text())) : 0;
                            $book->tedadSafe = ($book->tedadSafe != $book->shabak) ? $book->tedadSafe : 0;
                            break;
                        case 'طول:':
                            $book->length = faCharToEN($trObj->filter('div.LightText')->nextAll()->text());
                            break;
                        case 'عرض:':
                            $book->width = faCharToEN($trObj->filter('div.LightText')->nextAll()->text());
                            break;
                        case 'ارتفاع:':
                            $book->height = faCharToEN($trObj->filter('div.LightText')->nextAll()->text());
                            break;
                        case 'وزن:':
                            $book->vazn = enNumberKeepOnly(faCharToEN($trObj->filter('div.LightText')->nextAll()->text()));
                            break;
                    }
                }

                $book->partnerArray = json_encode($partner, JSON_UNESCAPED_UNICODE);


                //tags
                if ($crawler->filter("body div.ProductDetails div.Tags")->count() > 0) {
                    foreach ($crawler->filter("body div.ProductDetails div.Tags a") as $tag) {
                        $tagObj = new Crawler($tag);
                        $book->tags = $book->tags . $tagObj->filter('div.Tag')->text();
                        // $this->info($tagObj->filter('div.Tag')->text());
                    }
                }


                $book->save();
            } else {
                echo " \n ---------- Inappropriate Content              ----------- ";
            }
        } else {
            echo " \n ---------- Inappropriate Content              ----------- ";
        }
        return $status_code;
    }
}


//////////////////////////////////////////////////////ketabrah//////////////////////////////////////////////////
if (!function_exists('updateKetabrahBook')) {
    function updateKetabrahBook($BookKetabrah, $recordNumber, $function_caller = NULL)
    {
        $client = new Client(HttpClient::create(['timeout' => 30]));
        try {
            echo " \n ---------- Try Get BOOK " . $recordNumber . "              ---------- ";
            $crawler = $client->request('GET', 'https://www.ketabrah.ir/book_name/book/' . $recordNumber);
            $status_code = $client->getInternalResponse()->getStatusCode();
            MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => $status_code, 'xfunction_caller' => $function_caller]);
        } catch (\Exception $e) {
            $crawler = null;
            $status_code = 500;
            MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => $status_code, 'xfunction_caller' => $function_caller]);
        }

        if ($status_code == 200 &&  $crawler->filter('body')->text('') != '' and $crawler->filterXPath('//div[contains(@id, "InternalPageContents")]')->count() > 0) {
            if ($crawler->filter('article')->count() > 0) {
                //tag 
                if ($crawler->filterXPath('//div[contains(@class, "book")]')->filter('div.breadcrumb-container div.breadcrumb-ol')->count() > 0) {
                    $tagStr = '';
                    foreach ($crawler->filterXPath('//div[contains(@class, "book")]')->filter('div.breadcrumb-container div.breadcrumb-ol ol li') as $key => $cat) {
                        unset($row);
                        $row = new Crawler($cat);
                        if ($key != 0 and $key != 3) {
                            if ($row->filter('a span')->text() != '') {
                                $tagStr .= $row->filter('a span')->text() . '#';
                            }
                        }
                    }
                    $tagStr = rtrim($tagStr, '#');
                    $BookKetabrah->tags = $tagStr;
                }

                // image
                if ($crawler->filterXPath('//div[contains(@class, "book")]')->filter('div.book-main-info-cover a')->count() > 0) {
                    $BookKetabrah->images = $crawler->filterXPath('//div[contains(@class, "book")]')->filter('div.book-main-info-cover a')->attr('href');
                }

                // Desc
                if ($crawler->filterXPath('//div[contains(@id, "BookIntroduction")]')->count() > 0) {
                    $BookKetabrah->desc = $crawler->filterXPath('//div[contains(@id, "BookIntroduction")]')->html();
                }

                // detail
                if ($crawler->filterXPath('//div[contains(@class, "book-description-content")]')->filter('div.book-details table')->count() > 0) {
                    $partner = array();
                    $partnerCount = 0;
                    foreach ($crawler->filterXPath('//div[contains(@class, "book-description-content")]')->filter('div.book-details table tr') as $item) {

                        unset($row);
                        $row = new Crawler($item);
                        if ($row->filterXPath('//td[1]')->text() == 'نام کتاب') {
                            $title = convert_arabic_char_to_persian($row->filterXPath('//td[2]')->text());
                            $title = str_replace('کتاب', '', $title);
                            $title = str_replace('صوتی', '', $title);
                            $title = str_replace('الکترونیکی', '', $title);
                            $BookKetabrah->title = $title;
                            $BookKetabrah->title2 = str_replace(' ', '', $title);
                        }

                        if ($row->filterXPath('//td[1]')->text() == 'نویسنده') {
                            $authors = explode("،", $row->filterXPath('//td[2]')->text());
                            foreach ($authors as $author) {
                                $partner[$partnerCount]['roleId'] = 1;
                                $partner[$partnerCount]['name'] = $author;
                                $partnerCount++;
                            }
                        }

                        if ($row->filterXPath('//td[1]')->text() == 'مترجم') {
                            $translators = explode("،", $row->filterXPath('//td[2]')->text());
                            foreach ($translators as $translator) {
                                $partner[$partnerCount]['roleId'] = 2;
                                $partner[$partnerCount]['name'] = $translator;
                                $partnerCount++;
                            }
                            $BookKetabrah->translate = 1;
                        }
                        if ($row->filterXPath('//td[1]')->text() == 'گوینده') {
                            $speakers = explode("،", $row->filterXPath('//td[2]')->text());
                            foreach ($speakers as $speaker) {
                                $partner[$partnerCount]['roleId'] = 38;
                                $partner[$partnerCount]['name'] = $speaker;
                                $partnerCount++;
                            }
                        }
                        $BookKetabrah->partnerArray = json_encode($partner, JSON_UNESCAPED_UNICODE);

                        if ($row->filterXPath('//td[1]')->text() == 'موضوع کتاب') {
                            $catStr = str_replace('،', '#', $row->filterXPath('//td[2]')->text());
                            $catStr = rtrim($catStr, '#');
                            $BookKetabrah->cat = $catStr;
                        }

                        if ($row->filterXPath('//td[1]')->text() == 'ناشر چاپی') {
                            $publisher_name = str_replace('انتشاراتی', '', $row->filterXPath('//td[2]')->text());
                            $publisher_name = str_replace('انتشارات', '', $publisher_name);
                            $publisher_name = str_replace('گروه', '', $publisher_name);
                            $publisher_name = str_replace('نشریه', '', $publisher_name);
                            $publisher_name = str_replace('نشر', '', $publisher_name);
                            $BookKetabrah->nasher = $publisher_name;
                        }
                        if ($row->filterXPath('//td[1]')->text() == 'ناشر صوتی') {
                            $audio_publisher_name = str_replace('انتشاراتی', '', $row->filterXPath('//td[2]')->text());
                            $audio_publisher_name = str_replace('انتشارات', '', $audio_publisher_name);
                            $audio_publisher_name = str_replace('گروه', '', $audio_publisher_name);
                            $audio_publisher_name = str_replace('نشریه', '', $audio_publisher_name);
                            $audio_publisher_name = str_replace('نشر', '', $audio_publisher_name);
                            $BookKetabrah->nasherSouti = $audio_publisher_name;
                        }

                        if ($row->filterXPath('//td[1]')->text() == 'سال انتشار') {
                            $BookKetabrah->saleNashr = enNumberKeepOnly(faCharToEN($row->filterXPath('//td[2]')->text()));
                        }

                        if ($row->filterXPath('//td[1]')->text() == 'فرمت کتاب') {
                            $BookKetabrah->format = $row->filterXPath('//td[2]')->text();
                        }

                        if ($row->filterXPath('//td[1]')->text() == 'تعداد صفحات') {
                            $BookKetabrah->tedadSafe = enNumberKeepOnly(faCharToEN($row->filterXPath('//td[2]')->text()));
                        }

                        if ($row->filterXPath('//td[1]')->text() == 'زبان') {
                            $BookKetabrah->lang = convert_arabic_char_to_persian($row->filterXPath('//td[2]')->text());
                        }

                        if ($row->filterXPath('//td[1]')->text() == 'شابک') {
                            $BookKetabrah->shabak = validateIsbnWithRemoveDash($row->filterXPath('//td[2]')->text());
                        }
                    }
                }

                // price 
                if ($crawler->filterXPath('//div[contains(@class, "book-description-content")]')->filter('div.book-details div.book-page-price-table div.book-price')->count() > 0) {

                    $prices = $crawler->filterXPath('//div[contains(@class, "book-description-content")]')->filter('div.book-details div.book-page-price-table div.book-price span')->text();
                    $price = explode('-', $prices);
                    $BookKetabrah->price = (isset($price[0]) and enNumberKeepOnly(faCharToEN($price[0])) > 0) ? enNumberKeepOnly(faCharToEN($price[0])) : 0;
                }
                $BookKetabrah->save();
            }
        } else {
            echo " \n ---------- Inappropriate Content              ----------- ";
        }
        return $status_code;
    }
}
