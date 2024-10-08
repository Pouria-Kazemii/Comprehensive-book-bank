<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PublisherWithYearExport extends BookExport
{
    private string $publisherId;
    private int $startYear;
    private int $endYear;

    public function __construct($publisherId, $startYear, $endYear)
    {
        $this->publisherId = $publisherId;
        $startYear != 0 ? $this->startYear = $startYear : $this->startYear = 1340;
        $endYear != 0 ? $this->endYear = $endYear : $this->endYear = getYearNow();
    }


    public function getBooksQuery()
    {
         return BookIrBook2::where('publisher.xpublisher_id', $this->publisherId)
            ->where('xpublishdate_shamsi', '>=', $this->startYear)
            ->where('xpublishdate_shamsi', '<=', $this->endYear)
            ->get();
    }
}
