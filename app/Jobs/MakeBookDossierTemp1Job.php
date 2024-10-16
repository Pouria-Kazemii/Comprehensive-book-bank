<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookTempDossier1;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakeBookDossierTemp1Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $books;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($books)
    {
        $this->books = $books;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->books as $book) {
            $dossierBooks = BookIrBook2::raw(function ($collection) use ($book) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'xisbn3' => $book->xisbn3,
                            'xmongo_parent' => ['$exists' => false]
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => '$xisbn3',
                            'book_names' => [
                                '$push' => '$xname'
                            ],
                            'book_ids' => [
                                '$push' => '$_id'
                            ]
                        ]
                    ]
                ]);
            });
            if (count($dossierBooks) != 0) {
                $dossier = BookTempDossier1::create([
                    'isbn' => $dossierBooks[0]->_id,
                    'book_ids' => $dossierBooks[0]->book_ids,
                    'book_names' => $dossierBooks[0]->book_names
                ]);

                foreach ($dossierBooks[0]->book_ids as $dossierBook) {
                    BookIrBook2::where('_id', $dossierBook)->update([
                        'xmongo_parent' => $dossier->_id
                    ]);
                }
            }
        }
    }
}
