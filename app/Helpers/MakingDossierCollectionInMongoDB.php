<?php

use App\Models\MongoDBModels\BookIrBook2;
use MongoDB\BSON\ObjectId;

if (!function_exists('takeOthersField')) {
    function takeOthersField($books )
    {

        $fields = [];
        foreach ($books as $key => $book) {
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

                $fields['xlanguages'] = uniqueValuesWithKeys($fields['xlanguages']);
                $fields['xpublishers'] = uniqueValuesWithKeys($fields['xpublishers']);
                $fields['xsubjects'] = uniqueValuesWithKeys($fields['xsubjects']);
                $fields['xpartners'] = uniqueValuesWithKeys($fields['xpartners']);
                $fields['xage_groups'] = uniqueValuesWithKeys($fields['xage_groups']);

            }
        }
        return $fields;
    }
}

if (!function_exists('uniqueValuesWithKeys')) {
    function uniqueValuesWithKeys($array)
    {
        $uniqueValues = [];
        return array_reduce($array, function ($result, $item) use (&$uniqueValues) {
            $value = serialize($item);
            if (!in_array($value, $uniqueValues)) {
                $uniqueValues[] = $value;
                $result[] = $item;
            }
            return $result;
        }, []);
    }
}

if (!function_exists('takeTotalPrices')) {
    function takeTotalPrices($array)
    {
        $totalPrices = 0;
        foreach ($array as $key => $value) {
            $totalPrices += $value['xtotal_price'];
        }
        return $totalPrices;
    }
}

if (!function_exists('takeTotalPages')) {
    function takeTotalPages($array)
    {
        $totalPages = 0;
        foreach ($array as $key => $value) {
            $totalPages += $value['xtotal_page'];
        }
        return $totalPages;
    }
}

if (!function_exists('takeMain')) {
    function takeMain($array)
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

        foreach ($array as $key => $item) {
            if ($item['xname'] == $smallestXname) {
                $id = $key;
            }
        }
        return ['name' => $smallestXname, '_id' => $id];
    }
}

if (!function_exists('getCreators')) {
    function getCreators($id)
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

        foreach ($writers as $key => $writer) {
            $writerNames[] = $writer->writer;
        }
        return $writerNames;
    }
}

if (!function_exists('getPoets')) {
    function getPoets($id)
    {
        $id = new ObjectId($id);

        $writers = BookIrBook2::raw(function ($collection) use ($id) {
            return $collection->aggregate([
                ['$match' => ['_id' => $id]],
                ['$unwind' => '$partners'],
                ['$match' => ['partners.xrule' => 'شاعر']],
                ['$group' => ['_id' => '$partners.xcreatorname']],
                ['$project' => ['_id' => 0, 'poet' => '$_id']]
            ]);
        });

        $poetNames = [];

        foreach ($writers as $key => $writer) {
            $poetNames[] = $writer->writer;
        }
        return $poetNames;
    }
}

if (!function_exists('takeWords')) {
    function takeWords($name)
    {
        return preg_split('/[\s\p{P}]+/u', mb_strtolower($name, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY);
    }
}

if (!function_exists('takeSubjects')) {
     function takeSubjects($book)
    {
        $subjects = [];
        foreach ($book->subjects as $subject){
            $subjects [] = $subject['xsubject_name'];
        }
        return $subjects;
    }
}

if (!function_exists('makeProjectOfWriterPipeline')){
    function makeProjectOfWriterPipeline()
    {
        return [
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
        ];
    }
}

