<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookIrBook2;
use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MongoDB\BSON\ObjectId;

class AddXroleArrayToBookIrCreatorsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $creator;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($creator)
    {
        $this->creator = $creator;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $result = [];
        $pipeline = [
            ['$unwind' => '$partners'],
            ['$match' => ['partners.xcreator_id' => $this->creator->_id]],
            ['$group' => [
                '_id' => '$partners.xcreator_id',
                'roles' => ['$addToSet' => '$partners.xrule']
            ]]
        ];

        $rules = BookIrBook2::raw(function ($collection)  use($pipeline){
            return $collection->aggregate($pipeline);
        });

        foreach ($rules[0]->roles as $rule) {
            $result [] = trim($rule);
        }

        $result = array_values(array_unique($result));

        BookIrCreator::find(new ObjectId($this->creator->_id))->update([
            'xrules' => $result
        ]);
    }
}
