<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Book as BookM;
use Hamcrest\Type\IsObject;

class Book extends Controller
{
    //
    public function find(Request $request){

        if ($request->input('shabak','') == '' && $request->input('title','') == ''){
            return response()->json(['error'=>'BAD REQUEST','error_code'=>'2002','result_count'=>0 , 'result'=>''], 400);
        }

        if ($request->input('shabak','') != ''){
            $books = BookM::where('shabak',$request->input('shabak'))->first();
        }
        if(!is_object($books)){
            if($request->input('title','')!=''){
                $qBooks = BookM::with(['authors', 'libraries'])->where('Title','LIKE','%'.$request->input('title').'%');
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
        if(is_object($books)){
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

                foreach($book->authors as $author){
                    $temp['authors'][] = $author->d_name;
                }
                foreach($book->libraries as $key=>$library){
                    $temp['libraries'][$key]['code']      = $library->libraryCode;
                    $temp['libraries'][$key]['name']      = $library->libraryName;
                    $temp['libraries'][$key]['address']   = $library->address;
                    $temp['libraries'][$key]['postcode']  = $library->postCode;
                    $temp['libraries'][$key]['phone']     = $library->phone;
                    $temp['libraries'][$key]['state']     = $library->state->stateName;
                    $temp['libraries'][$key]['city']      = $library->city->townshipName;
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
