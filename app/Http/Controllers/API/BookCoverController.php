<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookCover;
use Illuminate\Http\Request;

class BookCoverController extends Controller
{
    public function list(Request $request)
    {
        $bookCovers = BookCover::get();
        // $result = array();
        // foreach($bookCovers as $key=>$item){
        //     $result[$key]['id'] =$item['id'];
        //     $result[$key]['value'] =$item['name'];
        // }
        // return response()->json($result);
        return response()->json($bookCovers->pluck('name')->all());
    }
}
