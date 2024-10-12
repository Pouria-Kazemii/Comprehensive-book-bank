<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use MongoDB\Model\BSONDocument;

abstract class BookExport implements FromCollection , WithHeadings , WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    abstract protected function getBooksQuery();

    public function collection()
    {
        $data = [];

        $books = $this->getBooksQuery();
        foreach ($books as $book) {
            $data[] = [
                "isbn" => $book->xisbn,
                "name" => $book->xname,
                "price" => priceFormat($book->xcoverprice),
                "pageCount" => $book->xpagecount,
                "format" => $book->xformat,
                "circulation" => priceFormat($book->xcirculation),
                "printNumber" => $book->xprintnumber,
                "year" => $book->xpublishdate_shamsi,
                "language" => (array)$book->languages,
                'publisher' => $book->publisher != ([] or null) ? $book->publisher[0]['xpublishername'] : '',
                "creators" => (array)$book->partners,
                'subjects' => (array)$book->subjects,

            ];
        }
        $processedData = array_map(function ($item) {
            if (isset($item['language']) && (is_array($item['language']) || $item['language'] instanceof  \MongoDB\Model\BSONArray)) {
                $languages = [];
                foreach ($item['language'] as $language) {
                    if (isset($language['name'])) {
                        $languages[] = $language['name'];
                    }
                }
                $item['language'] = implode(', ', $languages);
            }

            if (isset($item['subjects']) && (is_array($item['subjects']) || $item['subjects'] instanceof  \MongoDB\Model\BSONArray)) {
                $subjects = [];
                foreach ($item['subjects'] as $subject) {
                    if (isset($subject['xsubject_name'])) {
                        $subjects[] = $subject['xsubject_name'];
                    }
                }
                $item['subjects'] = implode(', ', $subjects);
            }

            if (isset($item['creators']) && (is_array($item['creators']) || $item['creators'] instanceof  \MongoDB\Model\BSONArray)) {
                $partners = [];
                foreach ($item['creators'] as $partner) {
                    if (isset($partner['xcreatorname'])) {
                        $partners[] = $partner['xcreatorname'];
                    }
                }
                $item['creators'] = implode(', ', $partners);
            }
            return $item;
        }, $data);
        return collect($processedData);
    }

    public function headings(): array
    {
        return [
            'شابک',
            'عنوان',
            'قیمت(به ریال)',
            'صفحات',
            'قطع',
            'تیراژ',
            'نوبت چاپ',
            'سال و ماه نشر',
            'زبان',
            'ناشر',
            'پدیدآورندگان',
            'موضوعات'
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
