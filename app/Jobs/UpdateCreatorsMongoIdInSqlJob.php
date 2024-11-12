<?php

namespace App\Jobs;

use App\Models\BookirPartner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateCreatorsMongoIdInSqlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $creator ;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($creators)
    {
        $this->creator = $creators;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        BookirPartner::find($this->creator->xsqlid)
            ->update([
                'mongo_id' => $this->creator->_id
            ]);
    }
}
