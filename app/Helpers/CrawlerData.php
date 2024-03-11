<?php

use App\Models\AgeGroup;
use App\Models\Author;
use App\Models\BookCover;
use App\Models\BookDigi;
use App\Models\BookDigiRelated;
use App\Models\BookFormat;
use App\Models\BookirBook;
use App\Models\BookirPartner;
use App\Models\BookirPublisher;
use App\Models\BookirRules;
use App\Models\BookirSubject;
use App\Models\BookLanguage;
use App\Models\MajmaApiBook;
use App\Models\MajmaApiPublisher;

if (!function_exists('updateBookDataWithKetabirApiInfo')) {
    /**
     * Change number to price format
     *
     * @param string $priceNumber
     * @return string $price
     */

    function updateBookDataWithKetabirApiInfo($recordNumber, $bookIrBook, $function_caller = NULL)
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

            // $bookIrBook = BookirBook::where('xpageurl', 'http://ketab.ir/bookview.aspx?bookid=' . $recordNumber)->orwhere('xpageurl', 'https://db.ketab.ir/bookview.aspx?bookid=' . $recordNumber)->orWhere('xpageurl2', 'https://ketab.ir/book/' . $book_content->uniqueId)->firstOrNew();

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


if (!function_exists('returnBookDataFromKetabirApi')) {
    function returnBookDataFromKetabirApi($recordNumber, $function_caller = null)
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
            MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => '500', 'xfunction_caller' => $function_caller]);

            return False;
        } else {
            MajmaApiBook::create(['xbook_id' => $recordNumber, 'xstatus' => '200', 'xfunction_caller' => $function_caller]);

            $book_content = json_decode($book_content);
            return $book_content;
        }
    }
}


if (!function_exists('updateBookDigi')) {
    function updateBookDigi($recordNumber,$bookDigi,$function_caller = NULL)
    {

        $id = str_replace('dkp-','',$recordNumber);
        $productUrl = "https://api.digikala.com/v2/product/" . $id . "/";
        try {
            // $this->info(" \n ---------- Try Get BOOK        " . $id . "       ---------- ");
            $json = file_get_contents($productUrl);
            $product_info =  json_decode($json);
            $headers = get_headers($productUrl);
            $api_status = $product_info->status;
            MajmaApiBook::create(['xbook_id' => $id, 'xstatus' => $api_status, 'xfunction_caller' => $function_caller]);

        } catch (\Exception $e) {
            $crawler = null;
            $api_status = 500;
            MajmaApiBook::create(['xbook_id' => $id, 'xstatus' => $api_status, 'xfunction_caller' => $function_caller]);

            // $this->info(" \n ---------- Failed Get  " . $id . "              ---------=-- ");
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
                    if(check_digi_id_is_book($related_product->id)){
                        $related_product_digi =  BookDigi::where('recordNumber', 'dkp-' . $related_product->id)->firstOrNew();
                        $related_product_digi->recordNumber = 'dkp-' . $related_product->id;
                        $related_product_digi->save();
                        $related = bookDigiRelated::firstOrCreate(array('book_id' => $related_product->id));
                        array_push($related_array, $related->id);
                    }
                }
                $bookDigi->related()->sync($related_array);
            }
        }

        return $api_status;
    }
}


if(!function_exists('check_digi_book_status')){
    function check_digi_book_status($recordNumber)
    {
        $id = str_replace('dkp-','',$recordNumber);
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
            // $this->info(" \n ---------- Failed Get  " . $id . "              ---------=-- ");
        }


        if ($api_status == 200) {
            if(isset($product_info->data->product->is_inactive) AND $product_info->data->product->is_inactive == true){ 
                return 'is_inactive';
            }else{
                if (isset($product_info->data->product->breadcrumb) and !empty($product_info->data->product->breadcrumb)) {
                    if($product_info->data->product->breadcrumb['1']->url->uri == '/main/book-and-media/'){
                        return 'is_book';
                    }else{
                        return 'is_not_book';
                    }
                }else{
                    return 'unknown';
                }
            }
        }
    }
}


if(!function_exists('check_digi_id_is_book')){
    function check_digi_id_is_book($recordNumber)
    {
        $result_check_digi_book_status = check_digi_book_status('dkp-' .$recordNumber);
        if($result_check_digi_book_status == 'is_book'){
            return TRUE;
        }else{
            return FALSE;
        }
    }
}