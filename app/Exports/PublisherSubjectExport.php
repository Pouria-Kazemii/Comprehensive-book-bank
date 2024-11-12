<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrSubject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PublisherSubjectExport extends BookExport
{
    private string $publisherId;
    private string $subjectTitle;
    private int $starYear;
    private int $endYear;

    public function __construct($publisherId,$subjectTitle,$starYear,$endYear)
    {
        $this->publisherId = $publisherId;
        $this->subjectTitle = $subjectTitle;
        $starYear != 0 ?$this->starYear = $starYear : $this->starYear = 1340;
        $endYear != 0 ?$this->endYear = $endYear : $this->endYear = getYearNow();
    }

    public function getBooksQuery()
    {
        $matchConditions = [
            ['publisher.xpublisher_id' => $this->publisherId]
        ];


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

        $matchConditions[] = ['xpublishdate_shamsi' => ['$gte' => $this->starYear]];

        $matchConditions[] = ['xpublishdate_shamsi' => ['$lte' => $this->endYear]];

        $pipeline = [
            ['$match' => ['$and' => $matchConditions]],
        ];

        return BookIrBook2::raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

    }
}
