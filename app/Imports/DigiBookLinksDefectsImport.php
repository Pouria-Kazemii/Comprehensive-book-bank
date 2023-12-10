<?php

namespace App\Imports;

use App\Models\BookDigi;
use App\Models\BookirBook;
use App\Models\ErshadBook;
use App\Models\WebSiteBookLinksDefects;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DigiBookLinksDefectsImport implements ToModel, WithHeadingRow
{
    public function  __construct($excel_id)
    {
        $this->excel_id= $excel_id;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        
        return new WebSiteBookLinksDefects([
        //    dd($row),
            'siteName' => 'digikala' ,
            'book_links' => $row['book_links'] ,
            'recordNumber' =>digiRecordNumberFromBookLink($row),
            'bookId' => (isset(BookDigi::where('recordNumber',digiRecordNumberFromBookLink($row) )->first()->id) AND !empty(BookDigi::where('recordNumber',digiRecordNumberFromBookLink($row) )->first()->id)) ? BookDigi::where('recordNumber',digiRecordNumberFromBookLink($row) )->first()->id : 0,
            'bugId' =>siteBookLinkDefects(vaziiat_dar_khane_ketab($row),vaziiat_dar_edare_ketab($row)),
            'excelId'=>$this->excel_id,

        ]);
    }
}
