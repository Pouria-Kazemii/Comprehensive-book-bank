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
        $books = BookIrBook2::all();

        $processedBooks = [];

        foreach ($books as $book1) {
            // Skip if book1 has already been processed
            if (in_array($book1->_id, $processedBooks)) {
                continue;
            }

            // Initialize an empty collection for BookDossier models
            $booksOfDossier = [];

            foreach ($books as $book2) {
                // Skip if book2 is the same as book1 or has already been processed
                if ($book1->_id == $book2->_id || in_array($book2->_id, $processedBooks)) {
                    continue;
                }

                // Check conditions
                if ($book1->xisbn3 == $book2->xisbn3 ||
                    $this->isParentChild($book1, $book2) ||
                    (count($this->isNameSimilar([$book1, $book2])) > 0 && $this->hasCommonCreator($book1, $book2))
                ) {
                    $booksOfDossier [] = $book2->toArray();

                    // Mark book2 as processed
                    $processedBooks[] = $book2->_id;
                }
            }

            // Add book1 to the collection
            $booksOfDossier [] = $book1->toArray();

            // Process the collection after the inner loop
            $firstPartOfArray = [
                'xmain_name' => $this->takeMainName($booksOfDossier),
                'xtotal_pages' => $this->takeTotalPages($booksOfDossier),
                'xtotal_prices' => $this->takeTotalPrices($booksOfDossier),
                'xwhite' => null,
                'xblack' => null,
                'xis_translate' => $booksOfDossier[0]['is_translate'],

            ];
            $mongoData = array_merge($firstPartOfArray, $this->takeOthersField($booksOfDossier));
            BookDossier::create($mongoData);

            // Mark book1 as processed
            $processedBooks[] = $book1->_id;
        }
    }

    private function takeOthersField($books)
    {
        $fields = [];
        foreach ($books as  $key => $book) {
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
    private function isParentChild($book1, $book2)
    {
        return $book1->xparent == $book2->_id || $book2->xparent == $book1->_id;
    }

    private function takeMainName($array)
    {
        $smallestXname = null;

        foreach ($array as $item) {
            if (isset($item['xname'])) {
                if ($smallestXname === null || strlen($item['xname']) < strlen($smallestXname)) {
                    $smallestXname = $item['xname'];
                }
            }
        }
        return $smallestXname;
    }
    private function isNameSimilar($array)
    {
        $words = [];
        foreach ($array as $key => $value) {
            $words [] = preg_split('/[\s\p{P}]+/u', mb_strtolower($value->xname, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY);
        }
        $commonValues = $words[0];
        for ($i = 1; $i < count($words); $i++) {
            $commonValues = array_intersect($commonValues, $words[$i]);
        }
        return$commonValues;
    }

    private function hasCommonCreator($book1 , $book2)
    {
        $book1Creators = $this->getCreators($book1->_id);
        $book2Creators = $this->getCreators($book2->_id);
        if ($book1Creators == $book2Creators and $book1Creators != null and $book2Creators != null){
            return 1;
        }else{
            return 0;
        }
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
}
