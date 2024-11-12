<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MongoDB\BSON\ObjectId;

class AddNewXruleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $partners;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($book)
    {
        $this->partners = $book->partners;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        foreach ($this->partners as $partner) {
            $newPartner = BookIrCreator::where('_id', new ObjectId($partner['xcreator_id']))->first();


            $roles = $newPartner->xrules;
            if ($roles == null or count($roles) == 0) {
                $roles[] = $partner['xrule'];
            } else {
                foreach ($roles as $role) {
                    if ($role != $partner['xrule']) {
                        $roles [] = $partner['xrule'];
                    }
                }
            }
            $roles = array_values(array_unique($roles));

            $newPartner->update([
                'xrules' => $roles
            ]);
        }
    }
}
