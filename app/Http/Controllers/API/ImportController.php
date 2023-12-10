<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ErshadBookRequest;
use App\Http\Requests\UnallowableBookRequest;
use App\Http\Controllers\Controller;
use App\Imports\DigiBookLinksDefectsImport;
use App\Imports\ErshadBookImport;
use App\Imports\UnallowableBookImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ContradictionsExcelExport;


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

    public function importUnallowableBooks(UnallowableBookRequest $request){
        set_time_limit(0);
        Excel::import(new UnallowableBookImport, $request->file('File')->store('public/excel'));
        return 'true';

    }


    public function importDigiExcel($excel_name){
        set_time_limit(0);
        // $excel_name = 'ketabhaye_gheyre_mojaz_digikala_8_mehr.xlsx';
        // $excel_name = 'list_ba_shabak_digikala_17_mehr.xlsx';
        // $excel_name = 'list_bedune_shabak_digikala_17_mehr.xlsx';
        $contents = file_get_contents('https://manvaketab.com/public/files/datacollector/'. $excel_name);
        Storage::disk('local')->put($excel_name, $contents);
        $contradictionsExcelExport = ContradictionsExcelExport::create(array('title'=>$excel_name));
        $excel_id = $contradictionsExcelExport->id;
        Excel::import(new DigiBookLinksDefectsImport($excel_id), storage_path('app/'.$excel_name));
    }
}