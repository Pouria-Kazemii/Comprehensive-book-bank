<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ErshadBookRequest;
use App\Http\Controllers\Controller;
use App\Imports\ErshadBookImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;


class ImportController extends Controller
{
    /**
     * create a new instance of the class
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function importErshadBooks(ErshadBookRequest $request){
        set_time_limit(0);
        Excel::import(new ErshadBookImport, $request->file('File')->store('public/excel'));
        return 'true';

    }
}