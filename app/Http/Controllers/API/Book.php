<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Book as BookM;
use App\Models\Book30book as B30BookM;
use App\Models\BookGisoom as GBookM;
use Hamcrest\Type\IsObject;

class Book extends Controller
{
    //
    public function find(Request $request){

        $source = (int)($request->input('source',0));

        if ($request->input('shabak','') == '' && $request->input('title','') == ''){
            return response()->json(['error'=>'BAD REQUEST','error_code'=>'2002','result_count'=>0 , 'result'=>''], 400);
        }
        $books='';
        if ($request->input('shabak','') != ''){
            switch($source){
                case 0: // nahad ketabkhaneha
                    $books = array(BookM::with(['authors', 'libraries'])->where('shabak',$request->input('shabak'))->first());
                break;
                case 1: // Gisoom
                    if(strlen($request->input('shabak'))>10) $books = array(GBookM::with(['authors'])->where('shabak13',$request->input('shabak'))->first());
                    else $books = array(GBookM::with(['authors'])->where('shabak10',$request->input('shabak'))->first());
                break;
                case 2: // 30Book
                    $books = array(B30BookM::with(['authors'])->where('shabak',$request->input('shabak'))->first());
                break;
            }

        }
        if(!is_array($books)){
            if($request->input('title','')!=''){
                switch($source){
                    case 0: // nahad ketabkhaneha
                        $qBooks = BookM::with(['authors', 'libraries'])->where('Title','LIKE','%'.$request->input('title').'%');
                    break;
                    case 1: // Gisoom
                        $qBooks = GBookM::with(['authors'])->where('title','LIKE','%'.$request->input('title').'%');
                    break;
                    case 2: // 30Book
                        $qBooks = B30BookM::with(['authors'])->where('title','LIKE','%'.$request->input('title').'%');
                    break;
                }
            }
            if($request->input('nasher','')!=''){
               if($source == 0) $qBooks->Where('Nasher','LIKE','%'.$request->input('nasher').'%');
               else $qBooks->Where('nasher','LIKE','%'.$request->input('nasher').'%');
            }
            if($request->input('mahalenashr','')!=''){
                if($source == 0)$qBooks->Where('MahalNashr','LIKE','%'.$request->input('mahalenashr').'%');
            }
            if($request->input('salenashr','')!=''){
                $qBooks->Where('saleNashr','LIKE','%'.$request->input('salenashr').'%');
            }
            if($request->input('tedadsafe','')!=''){
                if($source == 0)$qBooks->Where('TedadSafhe','LIKE','%'.$request->input('tedadsafe').'%');
                else $qBooks->Where('tedadSafhe','LIKE','%'.$request->input('tedadsafe').'%');
            }
            if($request->input('nevisande','')!=''){
                $qBooks->whereHas('authors', function ($query){
                                              global $request;
                                              $query->where('d_name','LIKE', '%'.$request->input('nevisande').'%');
                                            });
            }

            if($source == 0)$books= $qBooks->orderBy('lastCheckLibraries', 'desc')->take(10)->get();
            else $books= $qBooks->orderBy('id', 'desc')->take(10)->get();
        }
        $resultArray = array();
        if($books != ''){
            foreach($books as $book){
                if($source == 0)$temp['title'] = $book->Title;
                else $temp['title'] = $book->title;
                if($source == 0)$temp['nasher'] = $book->Nasher;
                else $temp['nasher'] = $book->nasher;
                if($source == 1){
                    $temp['shabak'] = $book->shabak13;
                    $temp['shabak10'] = $book->shabak10;
                }else{
                    $temp['shabak'] = $book->shabak;
                }

                if($source == 0)$temp['barcode'] = $book->barcode;

                switch($source){
                    case 0: // nahad ketabkhaneha
                        $temp['mozoe'] = $book->mozoe;
                    break;
                    case 1: // Gisoom
                        $cats = explode('-|-',$book->catText);
                        $last = count($cats)-1;
                        $temp['mozoe'] = $cats[$last];
                    break;
                    case 2: // 30Book
                        $cats = explode('-|-',$book->cats);
                        $last = count($cats)-1;
                        $temp['mozoe'] = $cats[$last];
                    break;
                }

                $temp['salenashr'] = $book->saleNashr;
                if($source == 0)$temp['mahalnashr'] = $book->MahalNashr;
                if($source == 0)$temp['tedadsafhe'] = $book->TedadSafhe;
                else $temp['tedadsafhe'] = $book->tedadSafhe;
                if($source == 0)$temp['image'] = $book->Image_Address;
                else $temp['image'] = $book->image;
                if($source == 0)$temp['lang'] = $book->langName;
                else $temp['lang'] = $book->lang;
                if($source == 0)$temp['radeasliD'] = $book->RadeAsliD;
                elseif($source == 1) $temp['radeasliD'] = $book->radeD;
                if($source == 0)$temp['radefareiD'] = $book->RadeFareiD;
                if($source == 0)$temp['katerD'] = $book->ShomareKaterD;
                if($source == 0)$temp['pishrade'] = $book->PishRade;
                $temp['authors'] = array();
                foreach($book->authors as $author){
                    $temp['authors'][] = $author->d_name;
                }
                $temp['libraries'] = array();
                if($source == 0){
                    foreach($book->libraries as $key=>$library){
                        $temp['libraries'][$key]['code']      = $library->libraryCode;
                        $temp['libraries'][$key]['name']      = $library->libraryName;
                        $temp['libraries'][$key]['address']   = $library->address;
                        $temp['libraries'][$key]['postcode']  = $library->postCode;
                        $temp['libraries'][$key]['phone']     = $library->phone;
                        $temp['libraries'][$key]['state']     = $library->state->stateName;
                        $temp['libraries'][$key]['city']      = $library->city->townshipName;
                    }
                }



                $resultArray[] = $temp;


            }
        }


        $resultCount = count($resultArray);
        if($resultCount == 0){
            return response()->json(['error'=>'NOT FOUND','error_code'=>'2001','result_count'=>0 , 'result'=>''], 404);
        }else{
            return response()->json(['error'=>'','result_count'=>$resultCount ,'results'=>$resultArray]);
        }

    }
}
