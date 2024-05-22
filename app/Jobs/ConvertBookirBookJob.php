<?php

namespace App\Jobs;

use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConvertBookirBookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $bookir_book;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($bookir_book)
    {
        $this->bookir_book = $bookir_book;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $partners = convertCreators($this->bookir_book->xid);
        $publishers = convertPublishers($this->bookir_book->xid);
        $subjects = convertSubjects($this->bookir_book->xid);
        $languages = convertLanguages($this->bookir_book->xlang);
        $ageGroup = convertAgeGroup($this->bookir_book->xid);


        $mongoData = [
            'xsqlid' => $this->bookir_book->xid,
            'xsiteid' => $this->bookir_book->xsiteid,
            'xpageurl' => $this->bookir_book->xpageurl,
            'xpageurl2' => $this->bookir_book->xpageurl2,
            'xname' => $this->bookir_book->xname,
            'xdoctype' => $this->bookir_book->xdoctype,
            'xpagecount' => $this->bookir_book->xpagecount,
            'xformat' => $this->bookir_book->xformat,
            'xcover' => $this->bookir_book->xcover,
            'xprintnumber' => $this->bookir_book->xprintnumber,
            'xcirculation' => $this->bookir_book->xcirculation,
            'xcovernumber' => $this->bookir_book->xcovernumber,
            'xcovercount' => $this->bookir_book->xcovercount,
            'xapearance' => $this->bookir_book->xapearance,
            'xisbn' => $this->bookir_book->xisbn,
            'xisbn2' => $this->bookir_book->xisbn2,
            'xisbn3' => $this->bookir_book->xisbn3,
            'xpublishdate' => $this->bookir_book->xpublishdate,
            'xpublishdate_shamsi' => convertToSolarHijriYear($this->bookir_book->xpublishdate),
            'xcoverprice' => $this->bookir_book->xcoverprice,
            'xminprice' => $this->bookir_book->xminprice,
            'xcongresscode' => $this->bookir_book->xcongresscode,
            'xdiocode' => $this->bookir_book->xdiocode,
            'diocode_subject' => null,
            'xpublishplace' => $this->bookir_book->xpublishplace,
            'xdescription' => $this->bookir_book->xdescription,
            'xweight' => $this->bookir_book->xweight,
            'ximgeurl' => $this->bookir_book->ximgeurl,
            'xpdfurl' => $this->bookir_book->xpdfurl,
            'xregdata' => $this->bookir_book->xregdata,
            'xwhite' => $this->bookir_book->xwhite,
            'xblack' => $this->bookir_book->xblack,
            'xoldparent' => $this->bookir_book->xoldparent,
            'is_translate' => $this->bookir_book->is_translate,
            'xparent' => $this->bookir_book->xparent,
            'xrequestmerge' => $this->bookir_book->xrequestmerge,
            'xrequest_manage_parent' => $this->bookir_book->xrequest_manage_parent,
            'xreg_userid' => $this->bookir_book->xreg_userid,
            'xtotal_price' => $this->bookir_book->xcirculation * $this->bookir_book->xcoverprice,
            'xtotal_page' => $this->bookir_book->xpagecount * $this->bookir_book->xcirculation,
            'xaudience' => null,
            'partners' => $partners,
            'publisher' => $publishers,
            'subjects' => $subjects,
            'languages' => $languages,
            'age_group' => $ageGroup

        ];
        BookIrBook2::create($mongoData);
    }

}
