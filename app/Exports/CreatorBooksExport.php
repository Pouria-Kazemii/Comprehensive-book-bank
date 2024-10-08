<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CreatorBooksExport implements FromCollection , WithHeadings
{
    private string $creator;

    public function __construct($creator)
    {
        $this->creator = $creator;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = [];
        $books = BookIrBook2::raw( function ($collection) {
            return $collection->aggregate([
                [
                    '$unwind' => '$partners'
                ],
                [
                    '$match' => [
                        'partners.xcreator_id'=> $this->creator
                    ]
                ]
            ]);
        });
        foreach ($books as $book) {
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

        $processedData = array_map(function ($item) {
            // Check if 'language' exists and is a BSONArray
            if (isset($item['language'])) {
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
     public  function headings(): array
     {
         return [
             'مبلغ', 'صفحات', 'قطع', 'تیراژ', 'نوبت چاپ', 'سال و ماه نشر', 'زبان', 'ناشر', 'عنوان', 'شابک'
         ];
     }
}
