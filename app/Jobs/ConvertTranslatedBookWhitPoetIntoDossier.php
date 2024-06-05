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
use MongoDB\Client;


class ConvertTranslatedBookWhitPoetIntoDossier implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $searchTerm = 'فن ترجمه کاربردی: عربی به فارسی و فارسی به عربی شامل واژه های فارسی عربی شده، اتعابیر الاصطلاحیه، فرهنگ مختصر عربی به عربی همراه با جمله، فرهنگ مختصر واژه ها و ...'; // The Persian text you are searching for

        $results = BookIrBook2::raw(function ($collection) use ($searchTerm) {
            return $collection->find(
                [
                    '$text' => ['$search' => $searchTerm],
                    'xparent' => ['$nin' => [1]],
                ],
                [
                    'projection' => ['score' => ['$meta' => 'textScore']],
                    'sort' => ['score' => ['$meta' => 'textScore']]
                ]
            );
        })->all();
        $filteredResults = [];
        $searchTermLength = mb_strlen($searchTerm);

        foreach ($results as $document) {
            $levenshteinDistance = levenshtein($searchTerm, $document['xname']);
            $similarity = (1 - $levenshteinDistance / max($searchTermLength, mb_strlen($document['xname']))) * 100;

            if ($similarity >= 90) { // 90% similarity threshold
                $filteredResults[] = $document;
            }
        }

        dd($filteredResults);
    }
}

