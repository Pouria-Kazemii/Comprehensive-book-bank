<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DioCodeExport implements FromCollection , WithHeadings
{
    private string $diocode;
    private int $startYear;
    private int $endYear;
    private int $translate;
    private int $authorship;

    public function __construct($diocode, $startYear, $endYear, $translate, $authorship)
    {
        $this->authorship = $authorship;
        $this->translate = $translate;
        $this->diocode = $diocode;
        $startYear != 0 ?$this->startYear = $startYear : $this->startYear = 1340;
        $endYear != 0 ?$this->endYear = $endYear : $this->endYear = getYearNow();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $books = BookIrBook2::raw(function ($collection) {

            $isTranslateValue = 0;
           if ($this->authorship == 1) {
               $isTranslateValue = 1;
           }

            if ($this->translate == 1) {
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
                        $isTranslateValue != 0 ?? 'is_translate' => $isTranslateValue
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
