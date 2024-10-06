<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BookSearchExport implements FromCollection , WithHeadings , WithEvents
{
    public $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = [];
        $isbn = (isset($this->request["isbn"]) and !empty($this->request["isbn"])) ? str_replace("-", "", $this->request["isbn"]) : "";
        $searchText = (isset($this->request["searchText"]) && !empty($this->request["searchText"])) ? $this->request["searchText"] : "";
        $column = (isset($this->request["column"]) && preg_match('/\p{L}/u', $this->request["column"])) ? $this->request["column"] : "xpublishdate_shamsi";
        $sortDirection = (isset($this->request["sortDirection"]) && $this->request['sortDirection'] == (1 or -1)) ? (int)$this->request["sortDirection"] : 1;

        $pipeline = [];

        // Match conditions based on search criteria
        $matchConditions = [];

        if (!empty($searchText)) {
            $matchConditions['$text'] = ['$search' => $searchText];
        }

        if ($isbn != "") {
            $isbn = trim($this->request['isbn'], '"');
            $matchConditions['$or'] = [
                ['xisbn2' => ['$regex' => '^' . preg_quote($isbn, '/')]],
                ['xisbn3' => ['$regex' => '^' . preg_quote($isbn, '/')]],
                ['xisbn' => ['$regex' => '^' . preg_quote($isbn, '/')]],
            ];
        }

        if (!empty($matchConditions)) {
            $pipeline[] = ['$match' => $matchConditions];
        }
        // Add $addFields and $sort stages for text score and sorting by score
        if (!empty($searchText)) {
            $pipeline[] = ['$addFields' => ['score' => ['$meta' => 'textScore']]];
            $pipeline[] = ['$sort' => ['score' => ['$meta' => 'textScore']]];
        } else {
            $pipeline[] = ['$sort' => [$column => $sortDirection]];
        }

        // Execute the aggregation pipeline
        $books = BookIrBook2::raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        // Process aggregated results
        $books = iterator_to_array($books);
        if (!empty($books)) {
            foreach ($books as $book) {
                $dossier_id = ($book->xparent == -1 || $book->xparent == 0) ? $book->_id : $book->xparent;
                $data[] = [
                    "price" => priceFormat($book->xcoverprice),
                    "pageCount" => $book->xpagecount,
                    "format" => $book->xformat,
                    "circulation" => priceFormat($book->xcirculation),
                    "printNumber" => $book->xprintnumber,
                    "year" => $book->xpublishdate_shamsi,
                    "language" => $book->languages,
                    'publisher' => $book->publisher[0]['xpublishername'],
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

            // Handle other BSONArray objects similarly if necessary

            return $item;
        }, $data);

        return collect($processedData);
    }

    public function headings(): array
    {
        return [
            'مبلغ', 'صفحات', 'قطع', 'تیراژ', 'نوبت چاپ', 'سال و ماه نشر', 'زبان', 'ناشر', 'عنوان', 'شابک'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Set column auto-width for each column
                foreach (range('A', 'O') as $columnID) { // Adjust the range according to your headings
                    $event->sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }

}
