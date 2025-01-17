<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConvertIranketabPartnerIntoBookIrCreatorCollectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $partner;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($partner)
    {
        $this->partner = $partner;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        BookIrCreator::where('xsqlid',$this->partner->partner_master_id)
        ->update([
            'iranketabinfo' => [
                'enName' => $this->partner->partnerEnName,
                'partnerDesc' => $this->partner->partnerDesc,
                'image' => $this->partner->partnerImage
            ]
        ]);
    }
}
