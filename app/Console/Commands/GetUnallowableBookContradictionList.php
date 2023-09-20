<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\AuthorBook30book;
use App\Models\Book30book;
use App\Models\BookDigi;
use App\models\BookFidibo;
use App\Models\BookGisoom;
use App\Models\BookIranketab;
use App\Models\BookirBook;
use App\Models\BookTaaghche;
use App\Models\Crawler as CrawlerM;
use App\Models\ErshadBook;
use App\Models\UnallowableBook;
use Exception;
use Goutte\Client;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Illuminate\Support\Facades\DB;


class GetUnallowableBookContradictionList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:ContradictionsList {rowId} {miss?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Contradictions List Command';

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
                    $this->info(" \n ---------- Check  " . $this->argument('rowId') . "     $startC  -> $endC         ---------=-- ");
                    $newCrawler = $lastCrawler;
                }
            } catch (\Exception $e) {
                $this->info(" \n ---------- Failed Crawler  " . $this->argument('rowId') . "              ---------=-- ");
            }
        } else {
            try {
                $lastCrawler = CrawlerM::where('name', 'LIKE', 'Contradictions-Unallowable-%')->where('type', 2)->orderBy('end', 'desc')->first();
                if (isset($lastCrawler->end)) {
                    $startC = $lastCrawler->end + 1;
                } else {
                    $startC = 1;
                }

                $endC = $startC + CrawlerM::$crawlerSize;
                $this->info(" \n ---------- Check  " . $this->argument('rowId') . "     $startC  -> $endC         ---------=-- ");
                $newCrawler = CrawlerM::firstOrCreate(array('name' => 'Contradictions-Unallowable-' . $this->argument('rowId'), 'start' => $startC, 'end' => $endC, 'status' => 1, 'type' => 2));
            } catch (\Exception $e) {
                $this->info(" \n ---------- Check  " . $this->argument('rowId') . "              ---------=-- ");
            }
        }
        if (isset($newCrawler) or true) {
            $rowId = $startC;
            while ($rowId <= $endC) {

                $book_data = UnallowableBook::where('xid', $rowId)->first();
                $this->info($rowId);
                if (isset($book_data->xtitle) and !empty($book_data->xtitle) and $book_data->xtitle != NULL) {
                    $this->info($book_data->xtitle);

                    $book_data->xtitle = ltrim($book_data->xtitle);
                    $book_data->xtitle = rtrim($book_data->xtitle);
                    /////////////////////////////////////////////fidibo///////////////////////////////////////////
                    //fidibo 
                    $fidibo_query = bookFidibo::where(function($fidibo_query) use ($book_data){
                        $fidibo_query->where('title',"$book_data->xtitle");
                        $fidibo_query->orwhere('title','like','کتاب '."$book_data->xtitle");
                        $fidibo_query->orwhere('title','like','کتاب صوتی '."$book_data->xtitle");
                        $fidibo_query->orwhere('title','like','مجموعه '."$book_data->xtitle");
                        $fidibo_query->orwhere('title','like','مجله '."$book_data->xtitle");
                    });
                    if ($book_data->xpublisher_name != NULL) {
                        $fidibo_query->where('nasher', $book_data->xpublisher_name);
                    }
                    if ($book_data->xauthor != NULL) {
                        $fidibo_query->where('partnerArray', 'like', "%$book_data->xauthor%");
                    }
                    if ($book_data->xtranslator) {
                        $fidibo_query->where('partnerArray', 'like', "%$book_data->xtranslator%");
                    }
                    $fidibo_query->update(['has_permit' => 2]);

                    // fidibo like 
                    if($book_data->xpublisher_name != NULL || $book_data->xauthor != NULL || $book_data->xtranslator){
                        $fidibo_query = bookFidibo::where('title','like',"%$book_data->xtitle%");
                        if ($book_data->xpublisher_name != NULL) {
                            $fidibo_query->where('nasher', $book_data->xpublisher_name);
                        }
                        if ($book_data->xauthor != NULL) {
                            $fidibo_query->where('partnerArray', 'like', "%$book_data->xauthor%");
                        }
                        if ($book_data->xtranslator) {
                            $fidibo_query->where('partnerArray', 'like', "%$book_data->xtranslator%");
                        }
                        $fidibo_query->where('has_permit',0)->update(['has_permit' => 3]);
                    }


                    ///////////////////////////////////////taaghche /////////////////////////////////////////
                    // taaghche
                   /* $taaghche_query = BookTaaghche::where(function($taaghche_query) use ($book_data){
                        $taaghche_query->where('title',"$book_data->xtitle");
                        $taaghche_query->orwhere('title','like','کتاب '."$book_data->xtitle");
                    });
                    if ($book_data->xpublisher_name != NULL) {
                        $taaghche_query->where('nasher', $book_data->xpublisher_name);
                    }
                    if ($book_data->xauthor != NULL) {
                        $taaghche_query->where('authorsname', 'like', "%$book_data->xauthor%");
                    }
                    if ($book_data->xtranslator) {
                        $taaghche_query->where('authorsname', 'like', "%$book_data->xtranslator%");
                    }
                    $taaghche_query->update(['has_permit' => 2]);*/


                    
                    //taaghche like 
                    if($book_data->xpublisher_name != NULL || $book_data->xauthor != NULL || $book_data->xtranslator){
                        $taaghche_query = BookTaaghche::where('title','like',"%$book_data->xtitle%");
                        if ($book_data->xpublisher_name != NULL) {
                            $taaghche_query->where('nasher', $book_data->xpublisher_name);
                        }
                        if ($book_data->xauthor != NULL) {
                            $taaghche_query->where('authorsname', 'like', "%$book_data->xauthor%");
                        }
                        if ($book_data->xtranslator) {
                            $taaghche_query->where('authorsname', 'like', "%$book_data->xtranslator%");
                        }
                        $taaghche_query->where('has_permit',0)->update(['has_permit' => 3]);
                    }

                    //////////////////////////////////////digikala /////////////////////////////////
                    // BookDigi::where('title',$book_data->xtitle)->update(['has_permit'=>2]);
                    // BookDigi::where('title','like', "%$book_data->xtitle%")->update(['has_permit'=>2]);

                    ///////////////////////////////////////iranketab/////////////////////////////////
                    //iranketab
                    $iranKetab_query = BookIranketab::where(function($iranKetab_query) use ($book_data){
                        $iranKetab_query->where('title',"$book_data->xtitle");
                        $iranKetab_query->orwhere('title','like','کتاب '."$book_data->xtitle");
                    });
                    if($book_data->	xpublisher_name != NULL){
                        $iranKetab_query->where('nasher',$book_data->	xpublisher_name);
                    }
                    if($book_data->xauthor != NULL){
                        $iranKetab_query->where('partnerArray','like', "%$book_data->xauthor%");
                    }
                    if($book_data->xtranslator){
                        $iranKetab_query->where('partnerArray','like', "%$book_data->xtranslator%");
                    }
                    $iranKetab_query->update(['has_permit'=>2]);

                    // iranketab like 
                    if($book_data->xpublisher_name != NULL || $book_data->xauthor != NULL || $book_data->xtranslator){
                        $iranKetab_query = BookIranketab::where('title','like',"%$book_data->xtitle%");
                        if($book_data->	xpublisher_name != NULL){
                            $iranKetab_query->where('nasher',$book_data->	xpublisher_name);
                        }
                        if($book_data->xauthor != NULL){
                            $iranKetab_query->where('partnerArray','like', "%$book_data->xauthor%");
                        }
                        if($book_data->xtranslator){
                            $iranKetab_query->where('partnerArray','like', "%$book_data->xtranslator%");
                        }
                        $iranKetab_query->where('has_permit',0)->update(['has_permit'=>3]);
                    }


                    /////////////////////////////////////////////////////////30book/////////////////////////////////////////////////
                    // 30book
                    /* $author_book30book_status = FALSE;
                    $translator_book30book_status = FALSE;
                    

                    if($book_data->	xpublisher_name != NULL){
                        
                        $book30book_info = Book30book::where('nasher',$book_data->xpublisher_name)->where(function($query) use ($book_data){
                           $query->where('title',"$book_data->xtitle");
                           $query->orwhere('title','like','کتاب '."$book_data->xtitle");
                        })->get();
                    }else{
                        $book30book_info = Book30book::where('title',"$book_data->xtitle")->orwhere('title','like','کتاب '."$book_data->xtitle")->get();
                    }
                    // if($book30book_info->count() >0){
                        $this->info('1');
                        foreach($book30book_info as $info){
                            $this->info('xauthor : '.$book_data->xauthor);
                            if($book_data->xauthor != NULL){
                                $author_info = Author::where('d_name',"$book_data->xauthor")->first();
                                if(!empty($author_info)){
                                    
                                    $author_book30book = AuthorBook30book::where('book30book_id',$info->id)->where('author_id',$author_info->id)->get();
                                    
                                    $author_book30book_status = ($author_book30book->count() > 0) ? TRUE : FALSE;
                                    if( $author_book30book_status){
                                        $this->info('author_book30book_status : ' . 'true');
                                    }else{
                                        $this->info('author_book30book_status : ' . 'false');
                                    }
                                   
                                }
                            }
                            $this->info('xtranslator : '.$book_data->xtranslator);
                            if($book_data->xtranslator != NULL){
                                $translator_info = Author::where('d_name',"$book_data->xtranslator")->first();
                                if(!empty($translator_info)){
                                    $author_book30book = AuthorBook30book::where('book30book_id',$info->id)->where('author_id',$translator_info->id)->get();
                                    $translator_book30book_status = ($author_book30book->count() > 0) ? TRUE : FALSE;
                                    if( $translator_book30book_status){
                                        $this->info('translator_book30book_status : ' . 'true');
                                    }else{
                                        $this->info('translator_book30book_status : ' . 'false');
                                    }
                                   
                                }
                            }
                            if($author_book30book_status AND  $translator_book30book_status){
                                $this->info('update');
                                Book30book::where('id',$info->id)->update(['has_permit'=>3]);
                            }
                            $this->info('-------------------------------------------------------');
                        }
                        
                        
                    // }*/


                    /*$query = Book30book::with(['authors'])
                    ->whereHas('authors', function($query) use($book_data) {
                        // Query the name field in status table
                        if($book_data->xauthor != NULL){
                            $query->where('d_name',"$book_data->xauthor");
                        }
                        if($book_data->xtranslator != NULL){
                            $query->where('d_name', "$book_data->xtranslator");
                        }
                    })
                    // ->where('title','like', "%$book_data->xtitle");
                    ->where('title',"$book_data->xtitle");

                    if($book_data->	xpublisher_name != NULL){
                        $query->where('nasher',$book_data->	xpublisher_name);
                    }
                    $query->update(['has_permit'=>2]);*/

                }

                /////////////////////////////check with ketab.ir and ershad books
                 /*
                    $book_data = BookFidibo::where('id',$rowId)->first();
                    if(isset($book_data->shabak) AND $book_data->shabak != NULL){
                        $this->info($book_data->shabak);
                        $bookirbook_data = BookirBook::where('xisbn',$book_data->shabak)->orwhere('xisbn2',$book_data->shabak)->orWhere('xisbn3',$book_data->shabak)->first();
                        $ershad_book = ErshadBook::where('xisbn',$book_data->shabak)->first();
                        if( $ershad_book->count() > 0 ||  $bookirbook_data->count() > 0){
                            $update_data = array(
                                'has_permit'=>1
                            );
                        }else{
                            $update_data = array(
                                'has_permit'=>2
                            );
                        }
                    }else{
                        $this->info('row no isbn');
                        $update_data = array(
                            'has_permit'=>3
                        );
                    }

                    BookFidibo::where('id',$rowId)->update($update_data);*/

               
                $rowId++;
            }
        }
    }
}
