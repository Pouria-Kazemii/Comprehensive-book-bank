<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PublisherWithYearExport implements FromCollection , WithHeadings
{
    private $publisherId;
    private $startYear;
    private $endYear;

    public function __construct($publisherId,$startYear,$endYear)
    {
        $this->publisherId = $publisherId;
        $this->startYear = (int)$startYear;
        $this->endYear = (int)$endYear;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = [];
        $books = BookIrBook2::where('publisher.xpublisher_id', $this->publisherId)
            ->where('xpublishdate_shamsi', '>=', $this->startYear)
            ->where('xpublishdate_shamsi', '<=', $this->endYear)
            ->get();

        foreach ($books as $book) {
            // Handling translation types
            if ($book->is_translate == 1) {
                $translate = "تالیف";
            } elseif ($book->is_translate == 2) {
                $translate = "ترجمه";
            } else {
                $translate = "نامشخص";
            }

            // Add book data to array
            $data[] = [
                "price" => priceFormat($book->xcoverprice),
                "format" => $book->xformat,
                "circulation" => priceFormat($book->xcirculation),
                "is_translate" => $translate,
                'creators' => $book->partners,  // Partners will be processed later
                "name" => $book->xname,
            ];
        }

        $processedData = array_map(function ($item) {
            // Check if 'partners' exists and is an array
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

            return $item;
        }, $data);

        return collect($processedData);

    }

    public function headings(): array
    {
        return [
            'قیمت','قطع','تیراژ','تالیف/ترجمه','پدیدآورندگان','عنوان'
        ];
    }
}
