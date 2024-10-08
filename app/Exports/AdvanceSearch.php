<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\BookIrPublisher;
use App\Models\MongoDBModels\BookIrSubject;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;

class AdvanceSearch implements FromCollection , WithHeadings
{
    public $request;
    public function __construct($request)
    {
        $this->request = $request;
    }


    public function advanceSearch()
    {
        $textIndex = false;
        $firstIndex = true;
        $where = [];
        $currentLogicalOperation = null; // Default logical operation

        foreach ($this->request->input('search') as $key => $condition) {
            $field = $condition['field'];
            $comparisonOperator = $condition['comparisonOperator'];
            $logicalOperation = strtolower($condition['logicalOperator']);
            $value = $condition['value'];

            if ($field == 'xpublishdate_shamsi' or $field == 'xprintnumber' or $field == 'xcirculation' or $field == 'xcoverprice'){
                $value = (int)$value;
            }
            // Prepare condition based on comparison operator and field
            switch ($comparisonOperator) {
                case 'like':
                    switch ($field) {
                        case 'xname':
                            if ($firstIndex) {
                                $conditionArray = ['$text' => ['$search' => $value]];
                                $textIndex = true;
                                $firstIndex = false;
                            } else {
                                $books = BookIrBook2::raw(function ($collection) use ($value) {
                                    return $collection->aggregate([
                                        ['$match' => ['$text' => ['$search' => $value]]],
                                        ['$project' => ['_id' => 1]]
                                    ]);
                                });
                                $bookIds = [];
                                if ($books != null) {
                                    foreach ($books as $book) {
                                        $bookIds[] = new ObjectId($book->_id);
                                    }
                                }
                                $conditionArray = ['_id' => ['$in' => $bookIds]];
                            }
                            break;
                        case 'partners.xcreatorname':
                            $creators = BookIrCreator::raw(function ($collection) use ($value) {
                                return $collection->aggregate([
                                    ['$match' => ['$text' => ['$search' => $value]]],
                                    ['$project' => ['_id' => 1, 'score' => ['$meta' => 'textScore']]],
                                    ['$sort' => ['score' => ['$meta' => 'textScore']]],
                                ]);
                            });
                            $creatorIds = [];
                            if ($creators != null) {
                                foreach ($creators as $creator) {
                                    $creatorIds[] = (string)$creator->_id;
                                }
                            }
                            $conditionArray = ['partners.xcreator_id' => ['$in' => $creatorIds]];
                            break;
                        case 'publisher.xpublishername':
                            $publishers = BookIrPublisher::raw(function ($collection) use ($value) {
                                return $collection->aggregate([
                                    ['$match' => ['$text' => ['$search' => $value]]],
                                    ['$project' => ['_id' => 1, 'score' => ['$meta' => 'textScore']]],
                                    ['$sort' => ['score' => ['$meta' => 'textScore']]],
                                ]);
                            });
                            $publisherIds = [];
                            if ($publishers != null) {
                                foreach ($publishers as $publisher) {
                                    $publisherIds[] = (string)$publisher->_id;
                                }
                            }
                            $conditionArray = ['publisher.xpublisher_id' => ['$in' => $publisherIds]];
                            break;
                        case 'subjects.xsubject_name';
                            $subjects = BookIrSubject::raw(function ($collection) use ($value) {
                                return $collection->aggregate([
                                    ['$match' => ['$text' => ['$search' => $value]]],
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
                            $conditionArray = ['subjects.xsubject_id' => ['$in' => $subjectIds]];
                            break;
                        case 'xdiocode':
                            $conditionArray = [$field => ['$regex' => new Regex($value, 'i')]];
                            break;
                        case 'isbn';
                            $conditionArray = ['$or' => [
                                ['xisbn' => ['$regex' => new Regex($value, 'i')]],
                                ['xisbn2' => ['$regex' => new Regex($value, 'i')]],
                                ['xisbn3' => ['$regex' => new Regex($value, 'i')]],
                            ]];
                            break;
                    }
                    break;
                case '=':
                    switch ($field) {
                        case('isbn');
                            $conditionArray = ['$or' => [
                                ['xisbn' => ['$eq' => $value]],
                                ['xisbn2' => ['$eq' => $value]],
                                ['xisbn3' => ['$eq' => $value]],
                            ]];
                            break;
                        case 'xname' or 'partners.xcreatorname' or 'publisher.xpublishername' or 'xdiocode' or 'subjects.xsubject_name';
                            $conditionArray = [$field => ['$eq' => $value]];
                            break;
                        default;
                            $conditionArray = [$field => ['$eq' => (int)$value]];
                            break;
                    }
                    break;
                case '>=';
                    switch ($field) {
                        case('xdiocode');
                            $conditionArray = [$field => ['$gt' => $value]];
                            break;
                        default;
                            $conditionArray = [$field => ['$gt' => (int)$value]];
                            break;
                    }
                    break;
                case '<=';
                    switch ($field) {
                        case('xdiocode');
                            $conditionArray = [$field => ['$lt' => $value]];
                            break;
                        default;
                            $conditionArray = [$field => ['$lt' => (int)$value]];
                            break;
                    }
                    break;
            }

            if ($key == 0  and $logicalOperation == ""){
                $where['$and'] [] =  $conditionArray;
            }

            if ($key == 0 and $logicalOperation != ''){
                $where['$'.$logicalOperation][] = $conditionArray;
                $currentLogicalOperation = $logicalOperation;
            }

            if ($currentLogicalOperation == 'and' and $key != 0) {
                $where['$and'][] = $conditionArray;
                $currentLogicalOperation = $logicalOperation;

            }

            if ($currentLogicalOperation == 'or' and $key != 0){
                $where['$or'][] = $conditionArray;
                $currentLogicalOperation = $logicalOperation;

            }
        }
        // Call the listsForAdvanceSearch method with the constructed $where clause
        return $this->collection($where , $textIndex);
    }

    /**
     * @return Collection
     */
    public function collection($where = [] , $textIndex = false)
    {
        $column = (isset($this->request["column"]) && preg_match('/\p{L}/u', $this->request["column"])) ? $this->request["column"] : "xpublishdate_shamsi";
        $sortDirection = (isset($this->$request["sortDirection"]) && $request['sortDirection'] == (1 or -1)) ? (int)$request["sortDirection"] : 1;
        $data = [];

        if ($textIndex) {
            $pipeline = [
                ['$match' => $where],
                ['$addFields' => ['score' => ['$meta' => 'textScore']]],
                ['$sort' => ['score' => ['$meta' => 'textScore']]],
            ];
        } else {
            $pipeline = [
                ['$match' => $where],
                ['$sort' => [$column => $sortDirection]],
            ];
        }

        $books = BookIrBook2::raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        // Get total count without fetching all records

        if ($books->isNotEmpty()) {
            foreach ($books as $book) {
                $data[] = [
                    "price" => priceFormat($book->xcoverprice),
                    "pageCount" => $book->xpagecount,
                    "format" => $book->xformat,
                    "circulation" => priceFormat($book->xcirculation),
                    "printNumber" => $book->xprintnumber,
                    "year" => $book->xpublishdate_shamsi,
                    "language" => $book->languages,
                    'publisher' => $book->publisher != [] ? $book->publisher[0]['xpublishername']:'',
                    'creators' => $book->partners,
                    "name" => $book->xname,
                    "isbn" => $book->xisbn,
                ];
            }
        }

        $processedData = array_map(function ($item) {
            // Check if 'language' exists and is a BSONArray
            if (isset($item['language']) && $item['language'] instanceof \MongoDB\Model\BSONArray) {
                // If there are multiple languages, concatenate their names
                $languages = [];
                foreach ($item['language'] as $language) {
                    if (isset($language['name'])) {
                        $languages[] = $language['name']; // Collect all language names
                    }
                }
                // Convert the array of language names into a comma-separated string
                $item['language'] = implode(', ', $languages);
            }

            if (isset($item['creators']) && is_array($item['creators'])) {
                $partners = [];
                foreach ($item['creators'] as $partner) {
                    if (isset($partner['xcreatorname'])) {
                        $partners[] = $partner['xcreatorname']; // Collect all creator names
                    }
                }
                // Convert array of partner names into a comma-separated string
                $item['creators'] = implode(', ', $partners);
            }
            // Handle other BSONArray objects similarly if necessary

            return $item;
        }, $data);

        return collect($processedData);
    }

    public function headings() : array
    {
        return [
          'مبلغ','صفحات','قطع','تیراژ','نوبت چاپ','سال و ماه نشر','زبان','ناشر','عنوان','شابک'
        ];
    }
}
