<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book as BookM;

class Book extends Controller
{
    //
    public function find(Request $request){

        if ($request->input('shabak','') != ''){
            $books = BookM::where('shabak',$request->input('shabak'))->first();
        }
        if(!isset($books)){
            if($request->input('title','')!=''){
                $qBooks = BookM::where('Title','LIKE','%'.$request->input('title').'%');
            }
            if($request->input('nasher','')!=''){
                $qBooks->Where('nasher','LIKE','%'.$request->input('nasher').'%');
            }
            if($request->input('mahalenashr','')!=''){
                $qBooks->Where('MahalNashr','LIKE','%'.$request->input('mahalenashr').'%');
            }
            if($request->input('salenashr','')!=''){
                $qBooks->Where('saleNashr','LIKE','%'.$request->input('salenashr').'%');
            }
            if($request->input('tedadsafe','')!=''){
                $qBooks->Where('TedadSafhe','LIKE','%'.$request->input('tedadsafe').'%');
            }

            $books= $qBooks->orderBy('lastCheckLibraries', 'desc')->take(10)->get();
        }
        $resultArray = array();
        foreach($books as $book){
            $temp['title'] = $book->Title;
            $temp['nasher'] = $book->Nasher;
            $temp['shabak'] = $book->shabak;
            $temp['barcode'] = $book->barcode;
            $temp['mozoe'] = $book->mozoe;
            $temp['salenashr'] = $book->saleNashr;
            $temp['mahalnashr'] = $book->MahalNashr;
            $temp['tedadsafhe'] = $book->TedadSafhe;
            $temp['image'] = $book->Image_Address;
            $temp['lang'] = $book->langName;
            $temp['radeasliD'] = $book->RadeAsliD;
            $temp['radefareiD'] = $book->RadeFareiD;
            $temp['katerD'] = $book->ShomareKaterD;
            $temp['pishrade'] = $book->PishRade;

            print_r($book);//print_r($book->libraries());
            exit;

            foreach($book->authors() as $author){
                $temp['authors'][] = $author->d_name;
            }
            foreach($book->libraries() as $key=>$library){
                $temp['authors'][$key]['code']      = $library->libraryCode;
                $temp['authors'][$key]['name']      = $library->libraryName;
                $temp['authors'][$key]['address']   = $library->address;
                $temp['authors'][$key]['postcode']  = $library->postCode;
                $temp['authors'][$key]['phone']     = $library->phone;
                $temp['authors'][$key]['state']     = $library->state();
                $temp['authors'][$key]['city']      = $library->city();
            }


            $resultArray[] = $temp;


        }

        $resultCount = count($resultArray);
        if($resultCount == 0){
            response()->json(['error'=>'NOT FOUND','error_code'=>'2001','result_count'=>0 , 'result'=>''], 404);
        }else{
            response()->json(['error'=>'','result_count'=>$resultCount ,'results'=>$resultArray]);
        }

    }
}
