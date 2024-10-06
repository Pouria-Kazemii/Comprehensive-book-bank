<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrSubject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CreatorSubjectExport implements FromCollection ,WithHeadings
{
    private $creatorId;
    private $subjectTitle;
    private $startYear;
    private $endYear;

    public function __construct($creatorId,$subjectTitle,$startYear,$endYear)
    {
        $this->creatorId = $creatorId;
        $this->subjectTitle = $subjectTitle;
        $this->startYear = $startYear;
        $this->endYear = $endYear;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = [];

        $matchConditions = [
            ['partners.xcreator_id' => $this->creatorId] // Mandatory creator ID filter
        ];
        if ($this->subjectTitle) {
            // Search for subjects using full-text search
            $subjects = BookIrSubject::raw(function ($collection) {
                return $collection->aggregate([
                    ['$match' => ['$text' => ['$search' => $this->subjectTitle]]],
                    ['$project' => ['_id' => 1, 'score' => ['$meta' => 'textScore']]],
                    ['$sort' => ['score' => ['$meta' => 'textScore']]]
                ]);
            });
            $subjectIds = [];
            if (count($subjects) != 0) {
                foreach ($subjects as $subject) {
                    $subjectIds[] = $subject->_id;
                }
            }
            if (!empty($subjectIds)) {
                $matchConditions[] = ['subjects.xsubject_id' => ['$in' => $subjectIds]];
            }else {
                $matchConditions[] = ['subject'=> 'xzcxzc'];
            }
        }

            $matchConditions[] = ['xpublishdate_shamsi' => ['$gte' => (int)$this->startYear]];

            $matchConditions[] = ['xpublishdate_shamsi' => ['$lte' => (int)$this->endYear]];


        $pipeline = [
            ['$unwind' => '$partners'],
            ['$match' => ['$and' => $matchConditions]],
        ];

        $books = BookIrBook2::raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        foreach ($books as $book){
            $data [] = [
                "name" => $book->xname,
                "publishers" => $book->publisher[0]['xpublishername'],
                "year" => $book->xpublishdate_shamsi,
                "circulation" => priceFormat($book->xcirculation),
                'price' => priceFormat($book->xcoverprice)
            ];
        }
        return collect($data);
    }

    public function headings(): array
    {
        return [
            'عنوان',
            'ناشر',
            'سال نشر',
            'تیراژ',
            'مبلغ'
        ];
    }
}
