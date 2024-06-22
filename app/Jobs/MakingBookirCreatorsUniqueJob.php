<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MongoDB\BSON\ObjectId;

class MakingBookirCreatorsUniqueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $docs;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($docs)
    {
        $this->docs = $docs;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->docs as $doc) {
            if (!BookIrBook2::where('partners.xcreator_id', (string)$doc)->exists()) {
                    BookIrCreator::where('_id' ,$doc)->delete();
            }
        }
    }
}
