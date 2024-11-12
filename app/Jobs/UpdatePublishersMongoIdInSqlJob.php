<?php

namespace App\Jobs;

use App\Models\BookirPublisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdatePublishersMongoIdInSqlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $publisher;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        BookirPublisher::find($this->publisher->xsqlid)
            ->update([
                'mongo_id' => $this->publisher->_id
            ]);
    }
}
