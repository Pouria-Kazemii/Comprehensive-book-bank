<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakeCreatorsNameCleanInBookIrBooksCollectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $book;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($creators)
    {
        $this->book = $creators;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $partners = [];
        foreach ($this->book->partners as $creator){
            $creatorName = trim($creator['xcreatorname']);
            $partners[] = [
                'xcreator_id' => $creator['xcreator_id'],
                'xcreatorname' => $creatorName,
                'xwhite' => $creator['xwhite'],
                'xblack' => $creator['xblack'],
                'xrule' => $creator['xrule']
            ];
        }

        $this->book->update([
            'partners' => $partners
        ]);
    }
}
