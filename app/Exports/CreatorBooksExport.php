<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CreatorBooksExport extends BookExport
{
    private string $creator;

    public function __construct($creator)
    {
        $this->creator = $creator;
    }
    public function getBooksQuery()
    {
        return BookIrBook2::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$unwind' => '$partners'
                ],
                [
                    '$match' => [
                        'partners.xcreator_id' => $this->creator
                    ]
                ]
            ]);
        });
    }
}
