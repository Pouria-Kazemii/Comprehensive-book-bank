<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookTempDossier1;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateBookDossierTemp1Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private BookTempDossier1 $doc;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($doc)
    {
        $this->doc = $doc;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $newBooks = BookTempDossier1::raw(function ($collection){
            return $collection->aggregate([
                [

                ]
            ]);
        });
    }
}
