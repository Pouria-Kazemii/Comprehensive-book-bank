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
    public function  __construct($excel_type, $excel_id)
    {
        $this->excel_type = $excel_type;
        $this->excel_id = $excel_id;
    }
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (!array_filter($row)) {
            return null;
        }
        // exceltype : unallowed , withoutIsbn ,withIsbn
        return new WebSiteBookLinksDefects([

            'siteName' => 'digikala',
            'book_links' => $row['book_links'],
            'recordNumber' => digiRecordNumberFromBookLink($row),
            'bookId' => (isset(BookDigi::where('recordNumber', digiRecordNumberFromBookLink($row))->first()->id) and !empty(BookDigi::where('recordNumber', digiRecordNumberFromBookLink($row))->first()->id)) ? BookDigi::where('recordNumber', digiRecordNumberFromBookLink($row))->first()->id : 0,
            'bugId' => siteBookLinkDefects(vaziiat_dar_khane_ketab($row), vaziiat_dar_edare_ketab($row)),
            'old_check_status' => checkStatusValue(vaziiat_dar_khane_ketab($row)),
            'old_has_permit' => hasPermitVlaue(vaziiat_dar_edare_ketab($row)),
            'old_unallowed' => ($this->excel_type == 'unallowed') ? 1 : 0,
            'excelId' => $this->excel_id,

        ]);
    }
}
