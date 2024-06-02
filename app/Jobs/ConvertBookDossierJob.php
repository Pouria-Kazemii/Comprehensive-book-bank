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

class ConvertBookDossierJob implements ShouldQueue
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
        $pipeline1 = [
            ['$match' => [
                'is_translate' => 1
            ]],
        ];
        $books =BookIrBook2::raw(function($collection) use ($pipeline1) {
            return $collection->aggregate($pipeline1);
        });

        $processedBooks = [];

        foreach ($books as $book1) {
            // Skip if book1 has already been processed
            if (in_array($book1->_id, $processedBooks)) {
                continue;
            }
            $booksOfDossier = [];
            $requiredWriters = $this->getCreators($book1->_id);
            $requiredWriterCount = count($requiredWriters);


            $pipeline = [

                ['$match' => [
                    'is_translate' => 1
                ]],

                ['$project' => [
                    '_id' =>1 ,
                    'xpagecount' =>1 ,
                    'xformat' =>1 ,
                    'xcover' =>1 ,
                    'xprintnumber' =>1 ,
                    'xcirculation' =>1,
                    'xcovernumber' => 1,
                    'xcovercount' => 1 ,
                    'xisbn' => 1 ,
                    'xisbn2' => 1,
                    'xisbn3' => 1,
                    'xpublishdate_shamsi' =>1,
                    'xcoverprice' =>1 ,
                    'xdiocode' => 1,
                    'xpublishplace' =>1,
                    'xdescription' => 1 ,
                    'xweight' => 1 ,
                    'ximgeurl' => 1,
                    'xpdfurl' => 1,
                    'xtotal_price' =>1,
                    'xtotal_page' =>1,
                    'xis_translate' => 1,
                    'xname' => 1 ,
                    'partners' => 1,
                    'subjects' => 1 ,
                    'publisher' => 1,
                    'languages' => 1 ,
                    'age_group' => 1,
                    'writers' => [
                        '$filter' => [
                            'input' => '$partners',
                            'as' => 'partner',
                            'cond' => [
                                '$eq' => ['$$partner.xrule', 'نويسنده']
                            ]
                        ]
                    ],
                    'writerCount' => [
                        '$size' => [
                            '$filter' => [
                                'input' => '$partners',
                                'as' => 'partner',
                                'cond' => [
                                    '$eq' => ['$$partner.xrule', 'نويسنده']
                                ]
                            ]
                        ]
                    ]
                ]],

                // Final match stage with the $or conditions
                ['$match' => [
                    '$or' => [
                        ['xisbn3' => $book1->xisbn3],
                        ['xparent' => $book1->_id],
                        ['_id' => $book1->xparent],
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
            $relatedBooks = BookIrBook2::raw(function($collection) use ($pipeline) {
                return $collection->aggregate($pipeline);
            });
            $mainId = $this->takeMain($relatedBooks)['_id'];
            //TODO question if there isn't eny writer . instead have poet
            if (count($relatedBooks) == 1) {
                $booksOfDossier[] = $book1;
            }elseif($book1->_id == $relatedBooks[$mainId]->_id and count($relatedBooks) > 1){
                $booksOfDossier [] = $book1;

                $mainName = $this->takeMain($relatedBooks)['name'];
                $mainWords = $this->takeWords($mainName);
                $mainSubjects = $this->takeSubjects($relatedBooks[$mainId]);

                foreach ($relatedBooks as $relatedBook) {
                    if ($book1->_id == $relatedBook->_id or in_array($relatedBook->_id, $processedBooks)) {
                        continue;
                    }

                    $relatedBookWords = $this->takeWords($relatedBook->xname);
                    $relatedBookSubjects = $this->takeSubjects($relatedBook);

                    if (count(array_intersect($mainWords, $relatedBookWords)) >= intval(count($mainWords) / 2.5) and
                        count(array_intersect($mainSubjects, $relatedBookSubjects)) >= 2) {
                        $booksOfDossier [] = $relatedBook;
                        $processedBooks [] = $relatedBook->_id;
                    }
                }
            } else{
                $mainName = $this->takeMain($relatedBooks)['name'];
                $mainWords = $this->takeWords($mainName);
                $mainSubjects = $this->takeSubjects($relatedBooks[$mainId]);

                $book1Words = $this->takeWords($book1->xname);
                $book1Subjects = $this->takeSubjects($book1);

                if (count(array_intersect($mainWords, $book1Words)) >= intval(count($mainWords) / 2.5) and
                    count(array_intersect($mainSubjects, $book1Subjects)) >= 2) {
                    $booksOfDossier [] = $book1;

                    $booksOfDossier [] = $relatedBooks[$mainId];
                    $processedBooks [] = $relatedBooks[$mainId]->_id;

                    foreach ($relatedBooks as $relatedBook) {
                        if ($book1->_id == $relatedBook->_id or $relatedBooks[$mainId]->_id == $relatedBook->_id or in_array($relatedBook->_id, $processedBooks)) {
                            continue;
                        }
                        $relatedBookWords = $this->takeWords($relatedBook->xname);
                        $relatedBookSubjects = $this->takeSubjects($relatedBook);
                        if (count(array_intersect($mainWords, $relatedBookWords)) >= intval(count($mainWords) / 2.5) and
                            count(array_intersect($mainSubjects, $relatedBookSubjects)) >= 2) {
                            $booksOfDossier [] = $relatedBook;
                            $processedBooks [] = $relatedBook->_id;
                        }
                    }
                }else{
                    $booksOfDossier [] = $book1;
                }
            }

            // Process the collection after the inner loop
            $firstPartOfArray = [
                'xmain_name' => $this->takeMain($booksOfDossier)['name'],
                'xtotal_pages' => $this->takeTotalPages($booksOfDossier),
                'xtotal_prices' => $this->takeTotalPrices($booksOfDossier),
                'xwhite' => null,
                'xblack' => null,
                'xis_translate' => $booksOfDossier[0]['is_translate'],
            ];

            $mongoData = array_merge($firstPartOfArray, $this->takeOthersField($booksOfDossier));

            // Create BookDossier document
            BookDossier::create($mongoData);

            // Mark book1 as processed
            $processedBooks[] = $book1->_id;
        }
    }


    private function takeOthersField($books)
    {

        $fields = [];
        foreach ($books as  $key => $book) {
            $fields['xbooks_id'] [] = $book['_id'];
            $fields['xnames'][] = $book['xname'];
            $fields['xpage_counts'][] = $book['xpagecount'];
            $fields['xformats'][] = $book['xformat'];
            $fields['xcovers'][] = $book['xcover'];
            $fields['xprint_numbers'][] = $book['xprintnumber'];
            $fields['xcirculations'][] = $book['xcirculation'];
            $fields['xisbns'][] = $book['xisbn'];
            $fields['xisbns2'][] = $book['xisbn2'];
            $fields['xisbns3'][] = $book['xisbn3'];
            $fields['xpublishdates_shamsi'][] = $book['xpublishdate_shamsi'];
            $fields['cover_prices'][] = $book['xcoverprice'];
            $fields['xdiocodes'][] = $book['xdiocode'];
            $fields['xpublish_places'] [] = $book['xpublishplace'];
            $fields['xdescriptions'] [] = $book['xdescription'];
            $fields['xweights'] [] = $book['xweight'];
            $fields['ximageurls'] [] = $book['ximgeurl'];
            $fields['xpdfurls'] [] = $book['xpdfurl'];
            foreach ($book['languages'] as $language) {
                $fields['xlanguages'] [] = ['xlanguage' => $language['name']];
            }
            foreach ($book['partners'] as $partner) {
                $fields['xpartners'] [] = $partner;
            }
            foreach ($book['publisher'] as $publisher) {
                $fields['xpublishers'] [] = $publisher;
            }
            foreach ($book['subjects'] as $subject) {
                $fields['xsubjects'][] = $subject;
            }
            foreach ($book['age_group'] as $ageGroup) {
                $fields['xage_groups'][] = $ageGroup;
            }
            if ($key == count($books) - 1) {
                $fields['xbooks_id'] = array_unique($fields['xbooks_id']);
                $fields['xnames'] = array_unique($fields['xnames']);
                $fields['xpage_counts'] = array_unique($fields['xpage_counts']);
                $fields['xformats'] = array_unique($fields['xformats']);
                $fields['xcovers'] = array_unique($fields['xcovers']);
                $fields['xprint_numbers'] = array_unique($fields['xprint_numbers']);
                $fields['xcirculations'] = array_unique($fields['xcirculations']);
                $fields['xisbns'] = array_unique($fields['xisbns']);
                $fields['xisbns2'] = array_unique($fields['xisbns2']);
                $fields['xisbns3'] = array_unique($fields['xisbns3']);
                $fields['xpublishdates_shamsi'] = array_unique($fields['xpublishdates_shamsi']);
                $fields['cover_prices'] = array_unique($fields['cover_prices']);
                $fields['xdiocodes'] = array_unique($fields['xdiocodes']);
                $fields['xpublish_places'] = array_unique($fields['xpublish_places']);
                $fields['xdescriptions'] = array_unique($fields['xdescriptions']);
                $fields['xweights'] = array_unique($fields['xweights']);
                $fields['ximageurls'] = array_unique($fields['ximageurls']);
                $fields['xpdfurls'] = array_unique($fields['xpdfurls']);

                $fields['xlanguages'] = $this->uniqueValuesWithKeys($fields['xlanguages']);
                $fields['xpublishers'] = $this->uniqueValuesWithKeys($fields['xpublishers']);
                $fields['xsubjects'] = $this->uniqueValuesWithKeys($fields['xsubjects']);
                $fields['xpartners'] = $this->uniqueValuesWithKeys($fields['xpartners']);
                $fields['xage_groups'] = $this->uniqueValuesWithKeys($fields['xage_groups']);

            }
        }
        return $fields;
    }
    function uniqueValuesWithKeys($array) {
        $uniqueValues = [];
        return array_reduce($array, function($result, $item) use (&$uniqueValues) {
            $value = serialize($item);
            if (!in_array($value, $uniqueValues)) {
                $uniqueValues[] = $value;
                $result[] = $item;
            }
            return $result;
        }, []);
    }
    private function takeTotalPrices($array)
    {
        $totalPrices = 0 ;
        foreach ($array as $key =>$value){
            $totalPrices += $value['xtotal_price'];
        }
        return$totalPrices;
    }
    private function takeTotalPages($array)
    {
        $totalPages = 0 ;
        foreach ($array as $key =>$value){
            $totalPages += $value['xtotal_page'];
        }
        return$totalPages;
    }


    private function takeMain($array)
    {
        $smallestXname = null;
        $id = null;

        foreach ($array as $item) {
            if (isset($item['xname'])) {
                if ($smallestXname === null || strlen($item['xname']) < strlen($smallestXname)) {
                    $smallestXname = $item['xname'];
                }
            }
        }

        foreach ($array as $key =>$item){
            if ($item['xname'] == $smallestXname){
                $id = $key;
            }
        }
        return ['name' =>$smallestXname , '_id' =>$id];
    }


    private function getCreators($id)
    {
        $id = new ObjectId($id);

        $writers = BookIrBook2::raw(function ($collection) use ($id) {
            return $collection->aggregate([
                ['$match' => ['_id' => $id]],
                ['$unwind' => '$partners'],
                ['$match' => ['partners.xrule' => 'نويسنده']],
                ['$group' => ['_id' => '$partners.xcreatorname']],
                ['$project' => ['_id' => 0, 'writer' => '$_id']]
            ]);
        });

        $writerNames = [];

        foreach ($writers as $key=>$writer) {
            $writerNames[] = $writer->writer;
        }
        return$writerNames;
    }
    private function takeWords($name)
    {
        return preg_split('/[\s\p{P}]+/u', mb_strtolower($name, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY);
    }

    private function takeSubjects($book)
    {
        $subjects = [];
        foreach ($book->subjects as $subject){
            $subjects [] = $subject['xsubject_name'];
        }
        return $subjects;
    }

}
