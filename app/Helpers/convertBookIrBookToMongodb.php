<?php

use App\Models\AgeGroup;
use App\Models\BiBookBiPublisher;
use App\Models\BiBookBiSubject;
use App\Models\BookirPartnerrule;
use App\Models\BookirRules;
use App\Models\BookirSubject;
use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\BookIrPublisher;

if (!function_exists('convertCreators')) {
    function convertCreators($xbookid)
    {
        $output = [];
        $processedCreator = [];
        $books = BookirPartnerrule::where('xbookid', $xbookid)->get();
        foreach ($books as $book) {
            if (!in_array($book->xcreatorid, $processedCreator)) {
                $creator = BookIrCreator::where('xsqlid', '=', $book->xcreatorid)->get();
                if (count($creator) > 0) {
                    $output [] = [
                        'xcreator_id' => $creator[0]->_id,
                        'xcreatorname' => $creator[0]->xcreatorname,
                        'xwhite' => $creator[0]->xwhite,
                        'xblack' => $creator[0]->xblack,
                        'xrule' => BookirRules::where('xid', '=', $book->xroleid)->pluck('xrole')[0]
                    ];
                    $processedCreator [] = $book->xcreatorid;
                }
            }
        }
        return ($output);
    }
}

if (!function_exists('convertPublishers')) {
    function convertPublishers($xbookid)
    {

        $books = BiBookBiPublisher::where('bi_book_xid' , '=' , $xbookid)->get();
        $output = [];
        $processedPublishers = [];
        foreach ($books as $book) {
            if (!in_array($book->bi_publisher_xid, $processedPublishers)) {
                $publisher = BookIrPublisher::where('xsqlid', '=', $book->bi_publisher_xid)->get();
                if (count($publisher) > 0) {
                    $output [] = [
                        'xpublisher_id' => $publisher[0]->_id,
                        'xpublishername' => $publisher[0]->xpublishername,
                        'ximageurl' => $publisher[0]->ximageurl,
                        'xblack' => $publisher[0]->xblack,
                        'xwhite' => $publisher[0]->xwhite,
                        ];
                    $processedPublishers [] = $book->bi_publisher_xid;
                }
            }
        }
        return ($output);
    }
}

if (!function_exists('convertSubjects')) {
    function convertSubjects($xbookid)
    {
        $books = BiBookBiSubject::where('bi_book_xid' , '=' , $xbookid)->get();
        $output = [];
        $processedSubjects = [];

        foreach ($books as $book) {
            if (!in_array($book->bi_subject_xid, $processedSubjects)) {
                $subject = BookirSubject::where('xid', '=', $book->bi_subject_xid)->get();
                if (count($subject) > 0) {
                    $output [] = [
                        'xsubject_id' => $subject[0]->xid,
                        'xsubject_name' => $subject[0]->xsubject
                    ];
                    $processedSubjects [] = $book->bi_subject_xid;
                }
            }
        }
        return ($output);
    }
}

if (!function_exists('convertLanguages')) {
    function convertLanguages($xlang)
    {
        $languages = [$xlang];
        $output = [];
        $processedLanguages = [];

        $languages = explode('/', $languages[0]);

        foreach ($languages as $language) {
            if (!in_array($language , $processedLanguages)){
                $output [] = ['name' => $language];
                $processedLanguages [] = $language;
            }
        }

        return $output;
    }
}

if (!function_exists('convertAgeGroup')) {
    function convertAgeGroup($xbookid)
    {
        $ageGroups = AgeGroup::where('xbook_id' , '=' , $xbookid)->get();
        $output = [];
        $processedAgeGroups = [];

        foreach($ageGroups as $ageGroup){
            if (!in_array($ageGroup->xbook_id , $processedAgeGroups))
            $output [] = [
                'xa' => $ageGroup->xa,
                'xb' => $ageGroup->xb,
                'xg' => $ageGroup->xg,
                'xd' => $ageGroup->xd,
                'xh' => $ageGroup->xh,
            ];
            $processedAgeGroups [] = $ageGroup->xbook_id;
        }
        return $output ;
    }

}
