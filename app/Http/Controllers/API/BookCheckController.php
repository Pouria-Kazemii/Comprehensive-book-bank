<?php

namespace App\Http\Controllers\API;

use App\Helpers\BookMasterData;
use App\Http\Controllers\Controller;
use App\Models\BiBookBiPublisher;
use App\Models\BookirBook;
use App\Models\BookirPartner;
use App\Models\BookirPartnerrule;
use App\Models\BookirPublisher;
use App\Models\ErshadBook;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class BookCheckController extends Controller
{
    public function check_ketabir_and_ershad(Request $request){
        $shabak = $request["shabak"];
        $book_name = $request["book_name"];
        $publisher = $request["publisher"];
        $creators = $request["creators"];

        /////////////////////////////////bookirbook/////////////////////////////////////////////////
        $data = array();
        if(isset($shabak) AND !empty($shabak)){
            $bookirbook_data = BookirBook::where('xisbn', $shabak)->orwhere('xisbn2', $shabak)->orWhere('xisbn3', $shabak)->get();
            if (count($bookirbook_data)>0) {
                $data['bookir_book'] = TRUE;
            } else {
                $data['bookir_book'] = FALSE;
            }
        }else{

            // publisher
            $bookPublishers =  BookirPublisher::select('xid')->where('xpublishername',$publisher)->get();
            $publisher_ids = $bookPublishers->pluck('xid');

            if(isset($publisher_ids) AND !empty($publisher_ids)){
                $publisher_books = BiBookBiPublisher::select('bi_book_xid')->whereIN('bi_publisher_xid',$publisher_ids)->get();
                $publisher_book_ids = $publisher_books->pluck('bi_book_xid');
            }

            //creators
            // $bookPartners = BookirPartner::select('xid')->whereIN('xcreatorname',array_map('trim', explode(',', $creators)))->get();
            $bookPartners = BookirPartner::select('xid')->whereIN('xcreatorname',explode(',', $creators))->get();
            $partners_ids = $bookPartners->pluck('xid');

            if(isset($partners_ids) AND !empty($partners_ids)){
                $partner_books = BookirPartnerrule::select('xbookid')->whereIN('xcreatorid',$partners_ids)->get();
                $partner_books_ids = $partner_books->pluck('xbookid');

            }
            //
            $book_ids = $partner_books_ids->merge($publisher_book_ids);


            if(isset( $book_name) AND !empty( $book_name)){
                $bookirbook_data = BookirBook::select('xid')->where('xname', $book_name)->whereIN('xid',$book_ids )->get();
            }else{
                $bookirbook_data = BookirBook::select('xid')->whereIN('xid',$book_ids )->get();
            }

            if (count($bookirbook_data)>0) {
                $data['bookir_book'] = TRUE;
            } else {
                $data['bookir_book'] = FALSE;
            }
        }


        ////////////////////////////////////////////ershadbook//////////////////////////////////////////
        if(isset($shabak) AND !empty($shabak)){
            $ershad_book = ErshadBook::where('xisbn', $shabak)->get();
            if (count($ershad_book)>0) {
                $data['ershad_book'] = TRUE;
            } else {
                $data['ershad_book'] = FALSE;
            }
        }else{
            if(isset( $book_name) AND !empty( $book_name)){
                $ershad_book = ErshadBook::where('xpublisher_name', $publisher)->where(function($query) use ($book_name){
                    $query->where('xtitle_fa',$book_name)->orwhere('xtitle_en',$book_name);
                })->where(function($query) use ($creators){
                    $query->whereIN('xmoalefin',explode(',', $creators))->orwhereIN('xmotarjemin',explode(',', $creators));
                })->get();
            }else{
                $ershad_book = ErshadBook::where('xpublisher_name', $publisher)->where(function($query) use ($creators){
                    $query->whereIN('xmoalefin',explode(',', $creators))->orwhereIN('xmotarjemin',explode(',', $creators));
                })->get();
            }

            if (count($ershad_book)>0) {
                $data['ershad_book'] = TRUE;
            } else {
                $data['ershad_book'] = FALSE;
            }

        }

        return json_encode($data);
    }
    public function exist(Request $request){
        // return $request;
        $shabak = $request["shabak"];
        $publish_date = $request["publishdate"];
        // DB::enableQueryLog();

        if ($shabak == '' && $publish_date == ''){
            return response()->json(['error'=>'BAD REQUEST','error_code'=>'2002','result_count'=>0 , 'result'=>''], 400);
        }
        $books='';
        if ($shabak != '' && $publish_date != ''){
            $books = BookirBook::where('xpublishdate',$publish_date)
            ->where(function ($query) use ($shabak) {
                $query->where('xisbn',$shabak);
                $query->orWhere('xisbn2',$shabak);
                $query->orWhere('xisbn3',$shabak);
            })->get();
        }
        $resultArray = array();
        if($books != ''){
            foreach($books as $book){
                $temp['id'] = $book->xid;
                $temp['title'] = $book->xname;
                $resultArray[] = $temp;
            }

        }

        // $query = DB::getQueryLog();
        // dd($query);
        $resultCount = count($resultArray);

        if($resultCount == 0){
            return response()->json(['error'=>'NOT FOUND','error_code'=>'2001','result_count'=>0 , 'result'=>''], 404);
        }else{
            return response()->json(['error'=>'','result_count'=>$resultCount ,'results'=>$resultArray]);
        }


    }
    public function check()
    {
        $books = BookirBook::where('xparent', '=', '0')->where('xrequest_manage_parent','!=',1)->orderBy('xpublishdate', 'DESC')->take(1)->get();
        if($books != null)
        {
            foreach ($books as $book)
            {
                $this->initCheck($book);
            }
        }
    }

    public function checkReverse()
    {
        $books = BookirBook::where('xparent', '=', '0')->where('xrequest_manage_parent','!=',1)->orderBy('xpublishdate')->take(100)->get();
        if($books != null)
        {
            foreach ($books as $book)
            {
                $this->initCheck($book);
            }
        }
    }

    public function initCheck($book)
    {
        $id = $book->xid;
        $isbn = $book->xisbn;
        $isbn2 = $book->xisbn2;
        $isbn3 = $book->xisbn3;
        $name = $book->xname;
        $publisherIds = null;
        $where = "";
        $whereCreator = "";

        //
        $bookBiPublishers = BiBookBiPublisher::where('bi_book_xid', '=', $id)->get();
        if($bookBiPublishers != null)
        {
            foreach ($bookBiPublishers as $bookBiPublisher)
            {
                $publisherId = $bookBiPublisher->bi_publisher_xid;

                $where .= "(xname='$name' and xid In (Select bi_book_xid From bi_book_bi_publisher Where bi_publisher_xid='$publisherId')) or ";
            }
        }
        $where = ($where != "") ? "or (".rtrim($where, " or ").")" : "";
        $bookCreators = BookirPartnerrule::where('xbookid', '=', $id)->where('xroleid', '=', '1')->get();
        if($bookCreators != null)
        {
            foreach ($bookCreators as $bookCreator)
            {
                $creatorId = $bookCreator->xcreatorid;

                $whereCreator .= "(xname='$name' and xid In (Select xbookid From bookir_partnerrule Where xroleid='1' and xcreatorid='$creatorId')) or ";
            }
        }
        $whereCreator = ($whereCreator != "") ? "and (".rtrim($whereCreator, " or ").")" : "";

        //
        $similarBooks = BookirBook::whereRaw("xid!='$id' and xparent='0' and xrequest_manage_parent!='1' and ((xisbn='$isbn' or xisbn2='$isbn2' or xisbn3='$isbn3') $where)")->get();
        if($similarBooks != null)
        {
            foreach ($similarBooks as $similarBook)
            {
                BookirBook::where('xid', $similarBook->xid)->update(['xparent' => $id]);
            }
        }

        if($whereCreator != "")
        {
            $similarBooks = BookirBook::whereRaw("xid!='$id' and xparent='0' and xrequest_manage_parent!='1' $whereCreator")->get();
            if($similarBooks != null)
            {
                foreach ($similarBooks as $similarBook)
                {
                    BookirBook::where('xid', $similarBook->xid)->update(['xparent' => $id]);
                }
            }
        }

        //
//                BookirBook::where('xid', $id)->update(['xparent' => -1]);
        BookirBook::where('xid', '=', $id)->where('xparent', '=', '0')->where('xrequest_manage_parent','!=',1)->update(['xparent' => -1]);
    }

    // read & check ---> bookk24
    /*
    public function checkBookK24()
    {
        $books = BookK24::where('book_master_id', '=', '0')->take(10)->get();
        if($books != null)
        {
            foreach ($books as $book)
            {
                $bookMasterData = new BookMasterData();
                $bookMasterData->record_number = $book->recordNumber;
                $bookMasterData->shabak = $book->shabak;
                $bookMasterData->title = $book->title;
                $bookMasterData->title_en = '';
                $bookMasterData->publisher = $book->nasher;
                $bookMasterData->author = '';
                $bookMasterData->translator = '';
                $bookMasterData->language = $book->lang;
                $bookMasterData->category = $book->cats;
                $bookMasterData->weight = 0;
                $bookMasterData->book_cover_type = '';
                $bookMasterData->paper_type = '';
                $bookMasterData->type_printing = '';
                $bookMasterData->editor = '';
                $bookMasterData->first_year_publication = $book->saleNashr;
                $bookMasterData->last_year_publication = $book->saleNashr;
                $bookMasterData->count_pages = $book->tedadSafe;
                $bookMasterData->book_size = $book->ghatechap;
                $bookMasterData->print_period_count = $book->nobatChap;
                $bookMasterData->print_count = $book->printCount;
                $bookMasterData->print_location = $book->printLocation;
                $bookMasterData->translation = $book->tarjome;
                $bookMasterData->desc = $book->desc;
                $bookMasterData->image = $book->image;
                $bookMasterData->price = $book->price;
                $bookMasterData->dio_code = $book->DioCode;

                // find authors
                $authorsData = null;
                $whereAuthors = null;

                $authorsK24 = DB::table('author_book_k24')->where('book_k24_id', '=', $book->id)->get();
                if($authorsK24 != null and count($authorsK24) > 0)
                {
                    foreach ($authorsK24 as $authorK24) $whereAuthors[] = ['id', '=', $authorK24->author_id];

                    if($whereAuthors != null)
                    {
                        $authors = DB::table('author')->where($whereAuthors)->get();
                        if($authors != null and count($authors) > 0)
                        {
                            foreach ($authors as $author)
                            {
                                $authorsData[] = $author->d_name;
                            }
                        }
                    }
                }

                // call check book
                $where = [['shabak', '=', $book->shabak]];

                $bookMasterId = $this->checkBook($bookMasterData, $authorsData, $where);

                // save bookMaster id in bookk24
                $book->tmp_author = 1;
                $book->book_master_id = $bookMasterId;
                $book->save();
            }
        }

        // temp set author
        $books = BookK24::where('tmp_author', '=', '0')->where('book_master_id', '!=', '0')->take(5)->get();
        if($books != null)
        {
            foreach ($books as $book)
            {
                $bookId = $book->id;
                $bookMasterId = $book->book_master_id;
                $whereAuthors = null;
                $authorNames = "";

                $authorsK24 = DB::table('author_book_k24')->where('book_k24_id', '=', $bookId)->get();
                if($authorsK24 != null and count($authorsK24) > 0)
                {
                    foreach ($authorsK24 as $authorK24) $whereAuthors[] = ['id', '=', $authorK24->author_id];

                    if($whereAuthors != null)
                    {
                        $authors = DB::table('author')->where($whereAuthors)->get();
                        if($authors != null and count($authors) > 0)
                        {
                            foreach ($authors as $author)
                            {
//                                echo $author->d_name."<br>";

                                // save in tblPerson
                                $person = TblPerson::where('name', '=', $author->d_name)->first();
                                if($person != null)
                                {
                                    $personId = $person->id;
                                }
                                else
                                {
                                    $person = new TblPerson();
                                    $person->name = $author->d_name;
                                    $person->save();

                                    $personId = $person->id;
                                }

                                // save in tblBookMasterPerson
                                if($personId > 0)
                                {
                                    $bookMasterPerson = TblBookMasterPerson::where('book_master_id', '=', $bookMasterId)->where('person_id', '=', $personId)->first();
                                    if($bookMasterPerson == null)
                                    {
                                        $bookMasterPerson = new TblBookMasterPerson();
                                        $bookMasterPerson->book_master_id = $bookMasterId;
                                        $bookMasterPerson->person_id = $personId;
                                        $bookMasterPerson->role = 'author';
                                        $bookMasterPerson->save();

                                        $authorNames .= $author->d_name." - ";
                                    }
                                }
                            }
                        }
                    }
                }

                //
                $book->tmp_author = 1;
                $book->save();

                // save in book master
                if($authorNames != "")
                {
                    $bookMaster = TblBookMaster::where('id', '=', $bookMasterId)->first();
                    if($bookMaster != null and $bookMaster->id > 0)
                    {
                        $bookMaster->author = $bookMaster->author != "" ? $authorNames.$bookMaster->author : rtrim($authorNames, ' - ');
                        $bookMaster->save();
                    }
                }

//                echo "<hr>";
            }
        }
    }
    */

    // read & check ---> bookDigi
    /*
    public function checkBookDigi()
    {
        $books = BookDigi::where('book_master_id', '=', '0')->where('shabak', '!=', '')->take(200)->get();
        if($books != null)
        {
            foreach ($books as $book)
            {
                $bookMasterData = new BookMasterData();
                $bookMasterData->record_number = $book->recordNumber;
                $bookMasterData->shabak = faCharToEN(str_replace("-", "", $book->shabak));
                $bookMasterData->title = $book->title;
                $bookMasterData->title_en = '';
                $bookMasterData->publisher = $book->nasher;
                $bookMasterData->author = '';
                $bookMasterData->translator = '';
                $bookMasterData->language = '';
                $bookMasterData->category = $book->cat;
                $bookMasterData->weight = $book->vazn;
                $bookMasterData->book_cover_type = $book->jeld;
                $bookMasterData->paper_type = $book->noekaghaz;
                $bookMasterData->type_printing = $book->noechap;
                $bookMasterData->editor = '';
                $bookMasterData->first_year_publication = 0;
                $bookMasterData->last_year_publication = 0;
                $bookMasterData->count_pages = $book->tedadSafe;
                $bookMasterData->book_size = $book->ghatechap;
                $bookMasterData->print_period_count = 0;
                $bookMasterData->print_count = $book->count;
                $bookMasterData->print_location = '';
                $bookMasterData->translation = '';
                $bookMasterData->desc = $book->desc;
                $bookMasterData->image = $book->images;
                $bookMasterData->price = $book->price;
                $bookMasterData->dio_code = '';

                // call check book
                $where = [['shabak', '=', $book->shabak]];

                $bookMasterId = $this->checkBook($bookMasterData, $where);

                // save bookMaster id in bookk24
                $book->book_master_id = $bookMasterId;
                $book->save();
            }
        }
    }
    */

    // check & save in bookMaster
    /**
     * @param BookMasterData $bookMasterData
     * @param array $authorsData
     * @param array $where
     * @return integer $bookMasterId
     */
    /*
    private function checkBook($bookMasterData, $authorsData, $where)
    {
        $bookMasterId = 0;

        // check book exist in TblBookMaster
        $bookMaster = TblBookMaster::where($where)->first();
        if($bookMaster != null) // exist
        {
            if($bookMaster->title == "") $bookMaster->title = $bookMasterData->title;
            if($bookMaster->publisher != $bookMasterData->publisher) $bookMaster->publisher = $bookMaster->publisher.",".$bookMasterData->publisher;
            if($bookMaster->language == "") $bookMaster->language = $bookMasterData->language;
            if($bookMaster->category != $bookMasterData->category) $bookMaster->category = $bookMaster->category.",".$bookMasterData->category;
            if($bookMaster->first_year_publication > $bookMasterData->first_year_publication) $bookMaster->first_year_publication = $bookMasterData->first_year_publication;
            if($bookMaster->last_year_publication < $bookMasterData->last_year_publication) $bookMaster->last_year_publication = $bookMasterData->last_year_publication;
            if($bookMaster->count_pages == 0) $bookMaster->count_pages = $bookMasterData->count_pages;
            if($bookMaster->book_size == "") $bookMaster->book_size = $bookMasterData->book_size;
            if($bookMaster->print_period_count < $bookMasterData->print_period_count) $bookMaster->print_period_count = $bookMasterData->print_period_count;
            $bookMaster->print_count = $bookMaster->print_count + $bookMasterData->print_count;
            if($bookMaster->print_location == "") $bookMaster->print_location = $bookMasterData->print_location;
            if($bookMaster->translation == 0) $bookMaster->translation = $bookMasterData->translation;
            if($bookMaster->desc == "") $bookMaster->desc = $bookMasterData->desc;
            if($bookMaster->image == "") $bookMaster->image = $bookMasterData->image;
            if($bookMaster->price == 0) $bookMaster->price = $bookMasterData->price;
            if($bookMaster->dio_code == "") $bookMaster->dio_code = $bookMasterData->dio_code;
            $resultSave = $bookMaster->save();
        }
        else // no exist
        {
            $bookMaster = new TblBookMaster();
            $bookMaster->record_number = $bookMasterData->record_number;
            $bookMaster->shabak = $bookMasterData->shabak;
            $bookMaster->title = $bookMasterData->title;
            $bookMaster->title_en = $bookMasterData->title_en;
            $bookMaster->publisher = $bookMasterData->publisher;
            $bookMaster->author = $bookMasterData->author;
            $bookMaster->translator = $bookMasterData->translator;
            $bookMaster->language = $bookMasterData->language;
            $bookMaster->category = $bookMasterData->category;
            $bookMaster->weight = $bookMasterData->weight;
            $bookMaster->book_cover_type = $bookMasterData->book_cover_type;
            $bookMaster->paper_type = $bookMasterData->paper_type;
            $bookMaster->type_printing = $bookMasterData->type_printing;
            $bookMaster->editor = $bookMasterData->editor;
            $bookMaster->first_year_publication = $bookMasterData->first_year_publication;
            $bookMaster->last_year_publication = $bookMasterData->last_year_publication;
            $bookMaster->count_pages = $bookMasterData->count_pages;
            $bookMaster->book_size = $bookMasterData->book_size;
            $bookMaster->print_period_count = $bookMasterData->print_period_count;
            $bookMaster->print_count = $bookMasterData->print_count;
            $bookMaster->print_location = $bookMasterData->print_location;
            $bookMaster->translation = $bookMasterData->translation;
            $bookMaster->desc = $bookMasterData->desc;
            $bookMaster->image = $bookMasterData->image;
            $bookMaster->price = $bookMasterData->price;
            $bookMaster->dio_code = $bookMasterData->dio_code;
            $resultSave = $bookMaster->save();
        }

        if($resultSave)
        {
            $bookMasterId = $bookMaster->id;
            $authorNames = "";

            // save category
            $categories = $bookMasterData->category;
            $categories = explode("-", $categories);

            if($categories != null and count($categories) > 0)
            {
                foreach($categories as $categoryTitle)
                {
                    $categoryTitle = trim($categoryTitle);
                    if($categoryTitle != "" and $categoryTitle != "|")
                    {
                        $tblCategory = TblCategory::where('title', '=', $categoryTitle)->first();
                        if($tblCategory != null)
                        {
                            $categoryId = $tblCategory->id;
                        }
                        else
                        {
                            $tblCategory = new TblCategory();
                            $tblCategory->title = $categoryTitle;
                            $resultSave = $tblCategory->save();
                            if($resultSave) $categoryId = $tblCategory->id;
                        }

                        if($categoryId > 0)
                        {
                            $bookMasterCategory = TblBookMasterCategory::where('book_master_id', '=', $bookMasterId)->where('category_id', '=', $categoryId)->first();
                            if($bookMasterCategory == null)
                            {
                                $bookMasterCategory = new TblBookMasterCategory();
                                $bookMasterCategory->book_master_id = $bookMasterId;
                                $bookMasterCategory->category_id = $categoryId;
                                $bookMasterCategory->save();
                            }
                        }
                    }
                }
            }

            // save publisher
            $publisher = $bookMasterData->publisher;
            if($publisher != '')
            {
                $tblPublisher = TblPublisher::where('title', '=', $publisher)->first();
                if($tblPublisher != null)
                {
                    $publisherId = $tblPublisher->id;
                }
                else
                {
                    $tblPublisher = new TblPublisher();
                    $tblPublisher->title = $publisher;
                    $resultSave = $tblPublisher->save();
                    if($resultSave) $publisherId = $tblPublisher->id;
                }

                if($publisherId > 0)
                {
                    $bookMasterPublisher = TblBookMasterPublisher::where('book_master_id', '=', $bookMasterId)->where('publisher_id', '=', $publisherId)->first();
                    if($bookMasterPublisher == null)
                    {
                        $bookMasterPublisher = new TblBookMasterPublisher();
                        $bookMasterPublisher->book_master_id = $bookMasterId;
                        $bookMasterPublisher->publisher_id = $publisherId;
                        $bookMasterPublisher->save();
                    }
                }
            }

            // authors
            if($authorsData != null and count($authorsData) > 0)
            {
                foreach ($authorsData as $authorName)
                {
                    // save in tblPerson
                    $person = TblPerson::where('name', '=', $authorName)->first();
                    if($person != null)
                    {
                        $personId = $person->id;
                    }
                    else
                    {
                        $person = new TblPerson();
                        $person->name = $authorName;
                        $person->save();

                        $personId = $person->id;
                    }

                    // save in tblBookMasterPerson
                    if($personId > 0)
                    {
                        $bookMasterPerson = TblBookMasterPerson::where('book_master_id', '=', $bookMasterId)->where('person_id', '=', $personId)->first();
                        if($bookMasterPerson == null)
                        {
                            $bookMasterPerson = new TblBookMasterPerson();
                            $bookMasterPerson->book_master_id = $bookMasterId;
                            $bookMasterPerson->person_id = $personId;
                            $bookMasterPerson->role = 'author';
                            $bookMasterPerson->save();

                            $authorNames .= $authorName." - ";
                        }
                    }
                }
            }

            // save in book master
            if($authorNames != "")
            {
                $bookMaster->author = $bookMaster->author != "" ? $authorNames.$bookMaster->author : rtrim($authorNames, ' - ');
                $bookMaster->save();
            }
        }

        return $bookMasterId;
    }
    */
}
