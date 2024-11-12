<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrSubject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubjectBooksExport extends BookExport
{
    private string $subjectTitle;
    private int $startYear;
    private int $endYear;
    private int $translate;
    private int $authorship;

    public function __construct($subjectTitle, $startYear, $endYear, $translate, $authorship)
    {
        $this->subjectTitle = $subjectTitle;
        $startYear != 0 ? $this->startYear = $startYear : $this->startYear = 1340;
        $endYear != 0 ? $this->endYear = $endYear : $this->endYear = getYearNow();
        $this->translate = $translate;
        $this->authorship = $authorship;
    }

    public function getBooksQuery()
    {
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

        $matchConditions[] = ['xpublishdate_shamsi' => ['$gte' => $this->startYear]];

        $matchConditions[] = ['xpublishdate_shamsi' => ['$lte' => $this->endYear]];

        if ($this->translate == 1) {
            $matchConditions[] = ['is_translate' => 2];
        } elseif ($this->authorship == 1) {
            $matchConditions[] = ['is_translate' => 1];
        }

        $pipeline = [
            ['$match' => ['$and' => $matchConditions]],
        ];
        return BookIrBook2::raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });
    }
}
