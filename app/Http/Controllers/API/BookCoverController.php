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
        return response()->json($bookCovers->pluck('name')->all());
    }
}
