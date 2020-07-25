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
            $qBooks = BookM::where('shabak',$request->input('shabak'));
        }
        if($request->input('title','')!=''){
            $qBooks = BookM::where('Title','LIKE','%'.$request->input('title').'%');
        }
        if($request->input('nasher','')!=''){
            $qBooks->orWhere('nasher','LIKE','%'.$request->input('nasher').'%');
        }
        if($request->input('mahalenashr','')!=''){
            $qBooks->orWhere('MahalNashr','LIKE','%'.$request->input('mahalenashr').'%');
        }
        if($request->input('salenashr','')!=''){
            $qBooks->orWhere('saleNashr','LIKE','%'.$request->input('salenashr').'%');
        }
        if($request->input('tedadsafe','')!=''){
            $qBooks->orWhere('TedadSafhe','LIKE','%'.$request->input('tedadsafe').'%');
        }

        print_r($qBooks->orderBy('lastCheckLibraries', 'desc')->take(10)->get());
    }
}
