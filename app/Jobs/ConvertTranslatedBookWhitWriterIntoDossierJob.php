<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookDossier;
use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MongoDB\BSON\ObjectId;
use function PHPUnit\Framework\isEmpty;

class ConvertTranslatedBookWhitWriterIntoDossierJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private  $books ;
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
            $creators = [];
            if ($book->partners != null) {
                foreach ($book->partners as $partner) {
                    if (in_array($partner['xrule'], ['نويسنده', 'نویسنده', 'شاعر'])) {
                        $creators[] = [
                            'xcreator_id' => $partner['xcreator_id'],
                            'xcreatorname' => $partner['xcreatorname'],
                            'xrule' => $partner['xrule'],
                        ];
                        break;
                    }
                }
            }
            if (!empty($creators)) {
                $dossierBooks = BookIrBook2::raw(function ($collection) use ($book, $creators) {
                    return $collection->aggregate([
                        [
                            '$match' => [
                                'xmongo_parent' => ['$exists' => false],
                                'diocode_subject' => $book->diocode_subject,
                                '$text' => ['$search' => $book->xname],
                                'partners' => [
                                    '$elemMatch' => [
                                        'xcreator_id' => $creators[0]['xcreator_id'],
                                        'xcreatorname' => $creators[0]['xcreatorname'],
                                        'xrule' => $creators[0]['xrule']
                                    ]
                                ]
                            ]
                        ],
                        [
                            '$addFields' => [
                                'stringLength' => ['$strLenCP' => '$xname']
                            ]
                        ],
                        [
                            '$sort' => ['stringLength' => 1]
                        ],
                    ]);
                });

                if (!count($dossierBooks) == 0) {
                    $dossier = BookDossier::create();
                    $bookIds = [];
                    $bookNames = [];

                    foreach ($dossierBooks as $dossierBook) {
                        $dossierBook->update([
                            'xmongo_parent' => $dossier->_id
                        ]);
                        $bookIds [] = $dossierBook->_id;
                        $bookNames[] = $dossierBook->xname;
                    }
                    $dossier->update([
                        'xmain_name' => $dossierBooks->first()->xname,
                        'xnames' => $bookNames,
                        'xbooks_id' => $bookIds
                    ]);
                }
            }
        }
    }
}
