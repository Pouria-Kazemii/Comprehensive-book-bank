<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConvertCreatorsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $partners;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($partners)
    {
        $this->partners = $partners;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        for($i=0 ; $i < count($this->partners); $i++) {
            $mongoData = [] ;
                $mongoData = [
                "xsqlid" => $this->partners[$i]->xid,
                'xcreatorname' => $this->partners[$i]->xcreatorname,
                'ximageurl' => null,
                'xwhite' => $this->partners[$i]->xwhite,
                'xblack' => $this->partners[$i]->xblack
            ];
            BookIrCreator::create($mongoData);
        }
    }
}
