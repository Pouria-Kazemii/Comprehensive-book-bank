<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookFormat;
use Illuminate\Http\Request;

class BookFormatController extends Controller
{
    public function list(Request $request)
    {
        $bookFormats = BookFormat::get();
        return response()->json($bookFormats->pluck('name')->all());
    }
}
