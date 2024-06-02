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
        $mongoData = [
            "xsqlid" => $this->partners->xid,
            'xcreatorname' => $this->partners->xcreatorname,
            'ximageurl' => null,
            'xwhite' => $this->partners->xwhite,
            'xblack' => $this->partners->xblack
            ];
            BookIrCreator::create($mongoData);
    }
}
