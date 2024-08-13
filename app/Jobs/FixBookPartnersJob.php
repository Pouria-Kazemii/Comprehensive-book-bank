<?php

namespace App\Jobs;

use App\Models\BookirPartner;
use App\Models\BookirPartnerrule;
use App\Models\BookirRules;
use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FixBookPartnersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $book;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($book)
    {
        $this->book = $book;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $partners = BookirPartnerrule::where('xbookid', $this->book->xsqlid)->get();
        if (count($partners) == 0) {
            echo 'no sql partner found';
        } else {
            $data = [];
            foreach ($partners as $partner) {
                $partnerSqlInfo = BookirPartner::where('xid', $partner->xcreatorid)->first();
                if ($partnerSqlInfo == null) {
                    echo "partner : id = $partner->xcreatorid not exists in sql";
                    continue;
                }
                $partnerMongoInfo = BookIrCreator::where('xsqlid', $partner->xcreatorid)->first();
                $role = BookirRules::where('xid', $partner->xroleid)->first()->xrole;
                if ($partnerMongoInfo == null) {
                    $createdPartner = BookIrCreator::create([
                        'xsqlid' => $partner->xcreator_id,
                        'xcreatorname' => $partnerSqlInfo->xcreatorname,
                        'ximageurl' => null,
                        'xwhite' => 0,
                        'xblack' => 0,
                    ]);
                    $data [] = [
                        'xcreator_id' => $createdPartner->_id,
                        'xcreatorname' => trim($createdPartner->xcreatorname),
                        'xwithe' => 0,
                        'xblack' => 0,
                        'xrule' => $role
                    ];
                } else {
                    if(!in_array($role,$partnerMongoInfo->xrules)){
                        $newXrules = $partnerMongoInfo->xrules;
                        $newXrules [] = $role;
                        $partnerMongoInfo->update([
                            'xrules' => $newXrules
                        ]);
                    };
                    $data [] = [
                        'xcreator_id' => $partnerMongoInfo->_id,
                        'xcreatorname' => $partnerMongoInfo->xcreatorname,
                        'xwithe' => 0,
                        'xblack' => 0,
                        'xrule' => $role
                    ];
                }
            }
            $this->book->update([
                'partners' => $data
            ]);
            echo "partner of book created successfully";
        }
    }
}
