<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubjectBookExport extends BookExport
{
    private int $subject;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function getBooksQuery()
    {
        return BookIrBook2::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$unwind' => '$subjects'
                ],
                [
                    '$match' => [
                        'subjects.xsubject_id' => $this->subject
                    ]
                ]
            ]);
        });
    }
}
