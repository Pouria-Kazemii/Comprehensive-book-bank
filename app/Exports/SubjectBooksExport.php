<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrSubject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubjectBooksExport implements FromCollection , WithHeadings
{
    private $subjectTitle;
    private $startYear;
    private $endYear;
    private $translate;
    private $authorship;

    public function __construct($subjectTitle, $startYear, $endYear, $translate, $authorship)
    {
        $this->subjectTitle = $subjectTitle;
        $this->startYear = $startYear;
        $this->endYear = $endYear;
        $this->translate = $translate;
        $this->authorship = $authorship;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = [];
        $subjects = BookIrSubject::raw(function ($collection) {
            return $collection->aggregate([
                ['$match' => ['$text' => ['$search' => $this->subjectTitle]]],
                ['$project' => ['_id' => 1, 'score' => ['$meta' => 'textScore']]],
                ['$sort' => ['score' => ['$meta' => 'textScore']]],
            ]);
        });

        $subjectIds = [];
        if ($subjects != null) {
            foreach ($subjects as $subject) {
                $subjectIds[] = $subject->_id;
            }
        }
        $matchConditions [] = ['subjects.xsubject_id' => ['$in' => $subjectIds]];

        $matchConditions[] = ['xpublishdate_shamsi' => ['$gte' => (int)$this->startYear]];

        $matchConditions[] = ['xpublishdate_shamsi' => ['$lte' => (int)$this->endYear]];

        if ((int)$this->translate == 1) {
            $matchConditions[] = ['is_translate' => 2];
        } elseif ((int)$this->authorship == 1) {
            $matchConditions[] = ['is_translate' => 1];
        }

        $pipeline = [
            ['$match' => ['$and' => $matchConditions]],
        ];
        $books = BookIrBook2::raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        if ($books != null and count($books) > 0) {
            foreach ($books as $book) {
                $data[] =
                    [
                        "name" => $book->xname,
                        "publishers" => $book->publisher[0]['xpublishername'],
                        "year" => $book->xpublishdate_shamsi,
                        "circulation" => priceFormat($book->xcirculation),
                        'price' => priceFormat($book->xcoverprice)
                    ];
            }
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
