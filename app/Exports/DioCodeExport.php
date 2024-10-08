<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DioCodeExport extends BookExport
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
        $startYear != 0 ? $this->startYear = $startYear : $this->startYear = 1340;
        $endYear != 0 ? $this->endYear = $endYear : $this->endYear = getYearNow();
    }

    public function getBooksQuery()
    {
        return BookIrBook2::raw(function ($collection) {
            $isTranslateValue = 0;

            if ($this->authorship == 1) {
                $isTranslateValue = 1;
            }

            if ($this->translate == 1) {
                $isTranslateValue = 2;
            }

            if ($isTranslateValue != 0) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'xdiocode' => $this->diocode,
                            'xpublishdate_shamsi' => [
                                '$gte' => $this->startYear,
                                '$lte' => $this->endYear,
                            ],
                            'is_translate' => $isTranslateValue
                        ]
                    ]
                ]);
            } else {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'xdiocode' => $this->diocode,
                            'xpublishdate_shamsi' => [
                                '$gte' => $this->startYear,
                                '$lte' => $this->endYear,
                            ],
                        ]
                    ]
                ]);
            }
        });
    }
}
