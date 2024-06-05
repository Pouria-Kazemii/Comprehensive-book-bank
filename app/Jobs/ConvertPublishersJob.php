<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookIrPublisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConvertPublishersJob implements ShouldQueue
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
        $mongoData = [
            'xsqlid' => $this->publisher->xid,
            'xtabletype' => $this->publisher->xtabletype,
            'xsiteid' => $this->publisher->xsiteid,
            'xparentid' => $this->publisher->xparentid,
            'xpageurl' => $this->publisher->xpageurl,
            'xpageurl2' => $this->publisher->xpageurl2,
            'xpublishername' => $this->publisher->xpublishername,
            "xmanager" => $this->publisher->xmanager,
            'xactivity' => $this->publisher->xactivity,
            'xplace' => $this->publisher->xplace,
            'xaddress' => $this->publisher->xaddress,
            'xpobox' => $this->publisher->xpobox,
            'xzipcode' => $this->publisher->xzipcode,
            'xphone' => $this->publisher->xphone,
            'xcellphone' => $this->publisher->xcellphone,
            'xfax' => $this->publisher->xfax,
            'xlastupdate' => $this->publisher->xlastupdate,
            'xtype' => $this->publisher->xtype,
            'xpermitno' => $this->publisher->xpermitno,
            'xemail' => $this->publisher->xemail,
            'xsite' => $this->publisher->xsite,
            'xisbnid' => $this->publisher->xisbnid,
            'xfoundingdate' => $this->publisher->xfoundingdate,
            'xispos' => $this->publisher->xispos,
            'ximageurl' => $this->publisher->ximageurl,
            'xregdate' => $this->publisher->xregdate,
            'xpublishername2' => $this->publisher->xpublishername2,
            'xiswiki' => $this->publisher->xiswiki,
            'xismajma' => $this->publisher->xismajma,
            'xisname' => $this->publisher->xisname,
            'xsave' => $this->publisher->xsave,
            'xwhite' => $this->publisher->xwhite,
            'xblack' => $this->publisher->xblack,
            'check_publisher' => $this->publisher->check_publisher
        ];

        $mongoPublisher = BookIrPublisher::create($mongoData);

        $this->publisher->update(['mongo_id' => $mongoPublisher->_id]);
    }
}
