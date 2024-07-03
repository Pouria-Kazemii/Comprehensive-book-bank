<?php

namespace App\Jobs;

use App\Models\BookirPartnerrule;
use App\Models\BookirRules;
use App\Models\BookirSubject;
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
        $xrules = [];
        if (!$this->creator->offsetExists('xrules')) {
            $roleIds = BookirPartnerrule::where('xcreatorid' , $this->creator->xsqlid)
                ->select('xroleid')
                ->distinct('xroleid')
                ->get()
                ->toArray();

            $roles = BookirRules::whereIn('xid',$roleIds)->select('xrole')->get();

            $results = array_values($roles->toArray());

            foreach ($results as $result){
                foreach ($result as $rule){
                    $xrules[] = $rule;
                }
            }

            BookIrCreator::find(new ObjectId($this->creator->_id))->update([
                'xrules' => $xrules
            ]);
        }
    }
}
