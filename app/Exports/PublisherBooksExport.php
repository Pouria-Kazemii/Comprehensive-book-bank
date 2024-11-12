<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;

class PublisherBooksExport extends BookExport
{
    private string $publisherId;
    public function __construct($publisherId)
    {
        $this->publisherId = $publisherId;
    }

    public function getBooksQuery()
    {
        return BookIrBook2::where('publisher.xpublisher_id' , $this->publisherId)->get();
    }
}
