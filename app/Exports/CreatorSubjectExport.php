<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrSubject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CreatorSubjectExport extends BookExport
{
    private string $creatorId;
    private string $subjectTitle;
    private int $startYear;
    private int $endYear;

    public function __construct($creatorId, $subjectTitle, $startYear, $endYear)
    {
        $this->creatorId = $creatorId;
        $this->subjectTitle = $subjectTitle;
        $startYear != 0 ? $this->startYear = $startYear : $this->startYear = 1340;
        $endYear != 0 ? $this->endYear = $endYear : $this->endYear = getYearNow();
    }

    public function getBooksQuery()
    {
        $matchConditions = [
            ['partners.xcreator_id' => $this->creatorId] // Mandatory creator ID filter
        ];
        if ($this->subjectTitle) {
            $matchConditions [] = [
                'subjects.xsubject_name' => ['$regex' => $this->subjectTitle, '$options' => 'i']
            ];
        }
        $matchConditions[] = ['xpublishdate_shamsi' => ['$gte' => $this->startYear]];

        $matchConditions[] = ['xpublishdate_shamsi' => ['$lte' => $this->endYear]];


        $pipeline = [
            ['$match' => ['$and' => $matchConditions]],
        ];

        return BookIrBook2::raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });
    }
}
