<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrSubject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PublisherSubjectExport implements FromCollection , WithHeadings
{
    private $publisherId;
    private $subjectTitle;
    private $starYear;
    private $endYear;

    public function __construct($publisherId,$subjectTitle,$starYear,$endYear)
    {
        $this->publisherId = $publisherId;
        $this->subjectTitle = $subjectTitle;
        $this->starYear = $starYear;
        $this->endYear = $endYear;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = [];
        $matchConditions = [
            ['publisher.xpublisher_id' => $this->publisherId]
        ];


        $subjects = BookIrSubject::raw(function ($collection)  {
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

        $matchConditions[] = ['xpublishdate_shamsi' => ['$gte' => (int)$this->starYear]];

        $matchConditions[] = ['xpublishdate_shamsi' => ['$lte' => (int)$this->endYear]];

        $pipeline = [
            ['$match' => ['$and' => $matchConditions]],
        ];

        $books = BookIrBook2::raw(function($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        foreach ($books as $book){
            $data [] = [
                'name' =>$book->xname,
                'subjects' =>$book->subjects,
                'xpublishdate_shamsi' => $book->xpublishdate_shamsi,
                'circulation' => priceFormat($book->xcirculation),
                'price' => priceFormat($book->xcoverprice)
            ];
        }
        $processedData = array_map(function ($item) {
            // Check if 'partners' exists and is an array
            if (isset($item['subjects']) && $item['subjects'] instanceof \MongoDB\Model\BSONArray) {
                $subjects = [];
                foreach ($item['subjects'] as $subject) {
                    if (isset($subject['xsubject_name'])) {
                        $subjects[] = $subject['xsubject_name']; // Collect all creator names
                    }
                }
                // Convert array of partner names into a comma-separated string
                $item['subjects'] = implode(', ', $subjects);
            }

            return $item;
        }, $data);

        return collect($processedData);

    }

    public function headings(): array
    {
        return [
            'عنوان',
            'موضوع',
            'سال نشر',
            'تیراژ',
            'مبلغ'
        ];
    }
}
