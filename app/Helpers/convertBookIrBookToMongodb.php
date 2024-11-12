<?php

use App\Models\AgeGroup;
use App\Models\BiBookBiPublisher;
use App\Models\BiBookBiSubject;
use App\Models\BookirPartnerrule;
use App\Models\BookirRules;
use App\Models\BookirSubject;
use App\Models\MongoDBModels\BookIrCreator;
use App\Models\MongoDBModels\BookIrPublisher;
use App\Models\MongoDBModels\CheckDailyConvert;
use GuzzleHttp\Client;

if (!function_exists('convertCreators')) {
    function convertCreators($xbookid)
    {
        $output = [];
        $processedCreatorById = [];
        $processedCreatorByName = [];
        $books = BookirPartnerrule::where('xbookid', $xbookid)->get();
        foreach ($books as $book) {
            if (!in_array($book->xcreatorid, $processedCreatorById)) {
                $creator = BookIrCreator::where('xsqlid', '=', $book->xcreatorid)->get();

                if (count($creator) > 0 and !in_array($creator[0]->xcreatorname , $processedCreatorByName)) {
                    $output [] = [
                        'xcreator_id' => $creator[0]->_id,
                        'xcreatorname' => trim($creator[0]->xcreatorname),
                        'xwhite' => $creator[0]->xwhite,
                        'xblack' => $creator[0]->xblack,
                        'xrule' => BookirRules::where('xid', '=', $book->xroleid)->pluck('xrole')[0]
                    ];
                    $processedCreatorById [] = $book->xcreatorid;
                    $processedCreatorByName [] = $creator[0]->xcreatorname;
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
if (!function_exists('logCommandResult')){
    function logCommandResult($commandName, $success)
    {
        CheckDailyConvert::create([
            'command' => $commandName,
            'status' => $success ? 'success' : 'failure',
            'executed_at' => now(),
        ]);
    }
}

if (!function_exists('takeBookParagraph')){
    function takeBookParagraph($format , $totalPages)
    {
        $paragraph = 0;
        switch ($format){
            case ('وزیری' or 'رقعی' or 'بیاضی' or 'رقعی پالتویی' or 'جعبه ای');
                $paragraph += round($totalPages / 16000 , 1);
                break;
            case ('جیبی' or 'ربعی' or 'جیبی پالتویی');
                $paragraph += round($totalPages / 32000 , 1);
                break;
            case ('پالتویی');
                $paragraph += round($totalPages / 24000 , 1);
                break;
            case ('خشتی');
                $paragraph += round($totalPages / 12000 , 1);
                break;
            case ('رحلی' or 'رحلی کوچک' or 'بغلی');
                $paragraph += round($totalPages / 8000 , 1);
                break;
            case ('جانمازی' or '1/2 جیبی');
                $paragraph += round($totalPages / 64000 , 1);
                break;
            case ('سلطانی');
                $paragraph += round($totalPages / 4000 , 1);
                break;
            case ('آلبومی');
                $paragraph += round($totalPages / 6000 ,1);
                break;
            case ('1/4 جیبی');
                $paragraph += round($totalPages / 128000 , 1);
                break;
        }
        return $paragraph;
    }

    function getAuthorBooks($author) {
        $client = new Client([
            'verify' => 'C:\PHP.7.4.3\ssl\cacert.pem'
        ]);

        // Google Books API URL with your API key
        $url = 'https://www.googleapis.com/books/v1/volumes';

        // Your Google API Key
        $apiKey = env('GOOGLE_BOOKS_API_KEY');

        // Send a GET request to the API
        $response = $client->get($url, [
            'query' => [
                'q' => 'intitle:' . $author,  // Search for books by author
                'key' => $apiKey,
                'langRestrict' => 'fa',        // Restrict to Persian books (optional)
            ]
        ]);

        // Parse the JSON response
        $data = json_decode($response->getBody(), true);

        // Return the list of books from the API response
        return $data['items'] ?? [];
    }
}
