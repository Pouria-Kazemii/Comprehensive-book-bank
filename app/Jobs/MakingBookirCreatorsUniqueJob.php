<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakingBookirCreatorsUniqueJob implements ShouldQueue
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
        // Step 1: Identify Duplicate Documents
        $duplicates = BookIrCreator::raw(function($collection) {
            return $collection->aggregate([
                ['$group' => ['_id' => '$xcreatorname', 'count' => ['$sum' => 1], 'docs' => ['$push' => '$_id']]],
                ['$match' => ['count' => ['$gt' => 1]]],
            ]);
        });

        $duplicateIds = [];
        foreach ($duplicates as $duplicate) {
            $duplicateIds = array_merge($duplicateIds, $duplicate->docs);
        }

        // Step 2: Check References in Another Collection
        $toDelete = [];
        foreach ($duplicateIds as $id) {
            $isReferenced = BookIrBook2::where('psrtners.xcreator_id', $id)->exists();
            if (!$isReferenced) {
                $toDelete[] = $id;
            }
        }
        dd($toDelete[0]);
        // Step 3: Delete Non-Referenced Documents
        if (!empty($toDelete)) {
            BookIrCreator::whereIn('_id', $toDelete)->delete();
        }
    }
}
