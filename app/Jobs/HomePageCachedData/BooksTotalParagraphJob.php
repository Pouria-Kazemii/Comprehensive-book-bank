<?php

namespace App\Jobs\HomePageCachedData;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BTB_Yearly;
use App\Models\MongoDBModels\ParsiToEnglishFormat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BooksTotalParagraphJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $year;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($year)
    {
        $this->year = $year;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $books = BookIrBook2::raw(function ($collection) {

            return $collection->aggregate([
                [
                    '$match' => [
                        'xtotal_page' => ['$ne' => 0],
                        'xformat' => ['$ne' => ''],
                        'xpublishdate_shamsi' => $this->year
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$xformat',
                        'total_pages' => ['$sum' => '$xtotal_page']
                    ]
                ]
            ]);
        });

        $total_paragraph = 0;

        foreach ($books as $book) {
            if (ParsiToEnglishFormat::where('fa_title', $book->_id)->exists()) {
                $column = ParsiToEnglishFormat::where('fa_title', $book->_id)->first()->en_title;
                $paragraph = takeBookParagraph($book['_id'], $book['total_pages']);
                $total_paragraph += $paragraph;
                BTB_Yearly::updateOrCreate(
                    ['year' => $this->year]
                    ,
                    [
                        $column => $paragraph,
                    ]
                );
            }
        }

        BTB_Yearly::updateOrCreate(
            ['year' => $this->year]
            ,
            ['paragraph' => $total_paragraph]
        );
    }
}
