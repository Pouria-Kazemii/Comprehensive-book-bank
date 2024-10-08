<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrSubject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CreatorSubjectExport implements FromCollection ,WithHeadings
{
    private string $creatorId;
    private string $subjectTitle;
    private int $startYear;
    private int $endYear;

    public function __construct($creatorId,$subjectTitle,$startYear,$endYear)
    {
        $this->creatorId = $creatorId;
        $this->subjectTitle = $subjectTitle;
        $startYear != 0 ? $this->startYear = $startYear : $this->startYear = 1340;
        $endYear != 0?$this->endYear = $endYear : $this->endYear = getYearNow();
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
            $matchConditions [] = [
                'subjects.xsubject_name' => ['$regex' => $this->subjectTitle, '$options' => 'i']
            ];
        }
        $matchConditions[] = ['xpublishdate_shamsi' => ['$gte' => $this->startYear]];

        $matchConditions[] = ['xpublishdate_shamsi' => ['$lte' => $this->endYear]];


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
