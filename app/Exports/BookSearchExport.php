<?php

namespace App\Exports;

use App\Models\MongoDBModels\BookIrBook2;

class BookSearchExport extends BookExport

{
    private string $isbn;
    private string $textSearch;

    public function __construct($textSearch , $isbn)
    {
        $this->textSearch = $textSearch;
        $this->isbn = $isbn;
    }
    public function getBooksQuery ()
    {
        $pipeline = [];
        $matchConditions = [];

        if ($this->textSearch != '0') {
            $matchConditions['$text'] = ['$search' => $this->textSearch];
        }

        if ($this->isbn != '0') {
            $isbn = trim($this->isbn, '"');
            $matchConditions['$or'] = [
                ['xisbn2' => ['$regex' => '^' . preg_quote($isbn, '/')]],
                ['xisbn3' => ['$regex' => '^' . preg_quote($isbn, '/')]],
                ['xisbn' => ['$regex' => '^' . preg_quote($isbn, '/')]],
            ];
        }

        if (!empty($matchConditions)) {
            $pipeline[] = ['$match' => $matchConditions];
        }
        if (!empty($searchText)) {
            $pipeline[] = ['$addFields' => ['score' => ['$meta' => 'textScore']]];
            $pipeline[] = ['$sort' => ['score' => ['$meta' => 'textScore']]];
        }

        return BookIrBook2::raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });
    }
}
