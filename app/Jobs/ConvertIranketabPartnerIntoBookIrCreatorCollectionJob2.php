<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookIrCreator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConvertIranketabPartnerIntoBookIrCreatorCollectionJob2 implements ShouldQueue
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
        $searchName = $this->partner->partnerName;
        $creators = BookIrCreator::where('xcreatorname', 'LIKE', "%$searchName%")
            ->where('iranketabinfo' , 'exists' , false)
            ->get();
        if (count($creators) > 0) {
            foreach ($creators as $creator) {
                $creator->update([
                    'iranketabinfo' => [
                        'enName' => $this->partner->partnerEnName,
                        'partnerDesc' => $this->partner->partnerDesc,
                        'image' => $this->partner->partnerImage
                    ]
                ]);
            }
        }
    }
}
