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
    {//TODO:START
        dd($this->docs->count());
        $relatedCreators = [];
        $nonRelatedCreators = [];
        foreach ($this->docs as $key => $doc) {
            $exist = BookIrBook2::where('partners.xcreator_id', (string)$doc)->exists();
            $repeatedCreators [] = [(string)$doc => $exist];

//            if (!$isReferenced) {
////                BookIrCreator::where('_id', new ObjectId((string)$doc))->delete();
//            }
//        }
//        if (count($repeatedCreators) == 2){
//            if (){
//
//            }
//        }
//        foreach ($repeatedCreators as $reference){
//
//        }
            dd($allReferences);
        }
    }
}
