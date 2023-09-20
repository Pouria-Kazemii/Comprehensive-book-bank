<?php

namespace App\Imports;

use App\Models\UnallowableBook;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UnallowableBookImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new UnallowableBook([
            'xtitle' => mb_substr($row['title'], 0, 250, 'UTF-8') ,
            'xauthor' => mb_substr($row['author'], 0, 250, 'UTF-8') ,
            // 'xpublish_date' => $row['publishdate'],
            'xpublisher_name' => $row['publishername'],
            'xtranslator' => $row['translator'],
        ]);
    }
}
