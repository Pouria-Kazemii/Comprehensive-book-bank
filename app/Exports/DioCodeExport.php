<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DioCodeExport implements FromCollection , WithHeadings
{
    private $diocode;
    private $startYear;
    private $endYear;
    private $translate;
    private $authorship;

    public function __construct($diocode, $startYear, $endYear, $translate, $authorship)
    {
        $this->authorship = (int)$authorship;
        $this->translate = (int)$translate;
        $this->diocode = $diocode;
        $this->startYear = (int)$startYear;
        $this->endYear = (int)$endYear;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $books = BookIrBook2::raw(function ($collection) {
            $isTranslateValue = 1; // Default value or whatever makes sense

            // Adjust the value of 'is_translate' based on the conditions
            if ($this->authorship == 1) {
                $isTranslateValue = 1;
            } elseif ($this->translate == 1) {
                $isTranslateValue = 2;
            }

            return $collection->aggregate([
                [
                    '$match' => [
                        'xdiocode' => (string)$this->diocode,
                        'xpublishdate_shamsi' => [
                            '$gte' => $this->startYear,
                            '$lte' => $this->endYear,
                        ],
                        'is_translate' => $isTranslateValue
                    ]
                ]
            ]);
        });
//        $books = BookIrBook2::where('xdiocode', (string)$this->diocode)->where('xpublishdate_shamsi', '<=', $this->endYear)->where('xpublishdate_shamsi', '>=', $this->startYear);
//
//         if ((int)$this->authorship == 1) {
//            $books->where('is_translate', 1);
//        } else if ((int)$this->translate == 1) {
//            $books->where('is_translate', 2);
//        }
//        $books->get();
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
            if (isset($item['language'])) {
                // If there are multiple languages, concatenate their names
                $languages = [];
                foreach ($item['language'] as $language) {
                    if (isset($language['name'])) {
                        $languages[] = $language['name']; // Collect all language names
                    }
                }
                $item['language'] = implode(', ', $languages);
            }


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
}
