<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConvertRepeatedCreatorsJob implements ShouldQueue
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
        for ($i = 0; $i < count($this->partners); $i++) {
            $mongoData = [];
            if ($this->partners[$i]->xid < $this->partners[$i + 1]->xid) {
                $mongoData = [
                    "xsqlid" => $this->partners[$i]->xid,
                    'xcreatorname' => $this->partners[$i]->xcreatorname,
                    'ximageurl' => null,
                    'xwhite' => $this->partners[$i]->xwhite,
                    'xblack' => $this->partners[$i]->xblack
                ];
                $i++;
            } else {
                $mongoData = [
                    "xsqlid" => $this->partners[$i + 1]->xid,
                    'xcreatorname' => $this->partners[$i + 1]->xcreatorname,
                    'ximageurl' => null,
                    'xwhite' => $this->partners[$i + 1]->xwhite,
                    'xblack' => $this->partners[$i + 1]->xblack
                ];
                $i++;
            }
            BookIrCreator::create($mongoData);
        }
    }
}
