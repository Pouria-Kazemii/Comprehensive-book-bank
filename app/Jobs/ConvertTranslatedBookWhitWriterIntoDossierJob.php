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
use MongoDB\Client;

class ConvertTranslatedBookWhitWriterIntoDossierJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //take all translated books
        $translateBooksWithParent = BookIrBook2::where('is_translate',2)
            ->where('xparent' ,'!=' , 0)
            ->where('xparent' , '!=', 1)
            ->where('xparent' , '!=' , -1);

        $translateBooksWithParent->chunk(1000 , function ($books) use ($translateBooksWithParent){
            foreach($books as $book) {
                $requiredWriters = getCreators($book->_id);
                $requiredWriterCount = count($requiredWriters);
                $pipeline = [
                    ['$project' => makeProjectOfWriterPipeline()],
                    ['$match' => [
                        '$or' => [
                            ['xparent' => $book->_id],
                            ['_id' => $book->xparent],
                            [
                                '$and' => [
                                    // Match on the required number of writers
                                    ['writerCount' => $requiredWriterCount],
                                    // Ensure all required writers are present
                                    ['writers.xcreatorname' => [
                                        '$all' => $requiredWriters
                                    ]]
                                ]
                            ]
                        ]
                    ]]
                ];

                 $relatedBooks = $translateBooksWithParent->raw(function($collection) use ($pipeline) {
                    return $collection->aggregate($pipeline);
                 });

                dd($relatedBooks->toArray());
            }
        });


        //we are going to dossier translated books with equal writers in  3 part:
        //first part : where books have a parent or are parent
//        $pipeline1 = [
//            '$project' => [
//            '_id' =>1 ,
//            'xpagecount' =>1 ,
//            'xformat' =>1 ,
//            'xcover' =>1 ,
//            'xprintnumber' =>1 ,
//            'xcirculation' =>1,
//            'xcovernumber' => 1,
//            'xcovercount' => 1 ,
//            'xisbn' => 1 ,
//            'xisbn2' => 1,
//            'xisbn3' => 1,
//            'xpublishdate_shamsi' =>1,
//            'xcoverprice' =>1 ,
//            'xdiocode' => 1,
//            'xpublishplace' =>1,
//            'xdescription' => 1 ,
//            'xweight' => 1 ,
//            'ximgeurl' => 1,
//            'xpdfurl' => 1,
//            'xtotal_price' =>1,
//            'xtotal_page' =>1,
//            'xis_translate' => 1,
//            'xname' => 1 ,
//            'partners' => 1,
//            'subjects' => 1 ,
//            'publisher' => 1,
//            'languages' => 1 ,
//            'age_group' => 1,
//            'writers' => [
//                '$filter' => [
//                    'input' => '$partners',
//                    'as' => 'partner',
//                    'cond' => [
//                        '$eq' => ['$$partner.xrule', 'نويسنده']
//                    ]
//                ]
//            ],
//            'writerCount' => [
//                '$size' => [
//                    '$filter' => [
//                        'input' => '$partners',
//                        'as' => 'partner',
//                        'cond' => [
//                            '$eq' => ['$$partner.xrule', 'نويسنده']
//                        ]
//                    ]
//                ]
//            ]
//        ],
//        ];
//        $books =BookIrBook2::raw(function($collection) use ($pipeline1) {
//            return $collection->aggregate($pipeline1);
//        });
//
//        $processedBooks = [];
//
//        foreach ($books as $book1) {
//
//            if (in_array($book1->_id, $processedBooks)) {
//                continue;
//            }
//            $booksOfDossier = [];
//            $requiredWriters = getCreators($book1->_id);
//            $requiredWriterCount = count($requiredWriters);
//
//
//            $pipeline = [
//
//                ['$match' => [
//                    'is_translate' => 2
//                ]],
//
//
//
//                ['$match' => [
//                    '$or' => [
//                        ['xisbn3' => $book1->xisbn3],
//                        ['xparent' => $book1->_id],
//                        ['_id' => $book1->xparent],
//                        [
//                            '$and' => [
//                                // Match on the required number of writers
//                                ['writerCount' => $requiredWriterCount],
//                                // Ensure all required writers are present
//                                ['writers.xcreatorname' => [
//                                    '$all' => $requiredWriters
//                                ]]
//                            ]
//                        ]
//                    ]
//                ]]
//            ];
//            $relatedBooks = BookIrBook2::raw(function($collection) use ($pipeline) {
//                return $collection->aggregate($pipeline);
//            });
//
//            $mainId = takeMain($relatedBooks)['_id'];
//
//            if (count($relatedBooks) == 1) {
//                $booksOfDossier[] = $book1;
//            }elseif($book1->_id == $relatedBooks[$mainId]->_id and count($relatedBooks) > 1){
//                $booksOfDossier [] = $book1;
//
//                $mainName = takeMain($relatedBooks)['name'];
//                $mainWords = takeWords($mainName);
//                $mainSubjects = takeSubjects($relatedBooks[$mainId]);
//
//                foreach ($relatedBooks as $relatedBook) {
//                    if ($book1->_id == $relatedBook->_id or in_array($relatedBook->_id, $processedBooks)) {
//                        continue;
//                    }
//
//                    $relatedBookWords = takeWords($relatedBook->xname);
//                    $relatedBookSubjects = takeSubjects($relatedBook);
//
//                    if (count(array_intersect($mainWords, $relatedBookWords)) >= intval(count($mainWords) /2) and
//                        count(array_intersect($mainSubjects, $relatedBookSubjects)) >= 2 ) {
//                        $booksOfDossier [] = $relatedBook;
//                        $processedBooks [] = $relatedBook->_id;
//                    }
//                }
//            } else{
//                $mainName = takeMain($relatedBooks)['name'];
//                $mainWords = takeWords($mainName);
//                $mainSubjects = takeSubjects($relatedBooks[$mainId]);
//
//                $book1Words = takeWords($book1->xname);
//                $book1Subjects = takeSubjects($book1);
//
//                if (count(array_intersect($mainWords, $book1Words)) >= intval(count($mainWords) / 2) and
//                    count(array_intersect($mainSubjects, $book1Subjects)) >= 2) {
//                    $booksOfDossier [] = $book1;
//
//                    $booksOfDossier [] = $relatedBooks[$mainId];
//                    $processedBooks [] = $relatedBooks[$mainId]->_id;
//
//                    foreach ($relatedBooks as $relatedBook) {
//                        if ($book1->_id == $relatedBook->_id or $relatedBooks[$mainId]->_id == $relatedBook->_id or in_array($relatedBook->_id, $processedBooks)) {
//                            continue;
//                        }
//                        $relatedBookWords = takeWords($relatedBook->xname);
//                        $relatedBookSubjects = takeSubjects($relatedBook);
//                        if (count(array_intersect($mainWords, $relatedBookWords)) >= intval(count($mainWords) / 2) and
//                            count(array_intersect($mainSubjects, $relatedBookSubjects)) >= 2) {
//                            $booksOfDossier [] = $relatedBook;
//                            $processedBooks [] = $relatedBook->_id;
//                        }
//                    }
//                }else{
//                    $booksOfDossier [] = $book1;
//                }
//            }
//
//            // Process the collection after the inner loop
//            $firstPartOfArray = [
//                'xmain_name' => takeMain($booksOfDossier)['name'],
//                'xtotal_pages' => takeTotalPages($booksOfDossier),
//                'xtotal_prices' => takeTotalPrices($booksOfDossier),
//                'xwhite' => null,
//                'xblack' => null,
//                'xis_translate' => $booksOfDossier[0]['is_translate'],
//            ];
//
//            $mongoData = array_merge($firstPartOfArray, takeOthersField($booksOfDossier));
//
//            BookDossier::create($mongoData);
//
//            $processedBooks[] = $book1->_id;
//        }
    }
}
