<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

class BookirBook extends Model
{
    protected $fillable = ['xid', 'xdocid', 'xsiteid', 'xpageurl', 'xname', 'xdoctype', 'xpagecount', 'xformat', 'xcover', 'xprintnumber', 'xcirculation', 'xcovernumber', 'xcovercount', 'xapearance', 'xisbn', 'xisbn2', 'xpublishdate', 'xcoverprice', 'xminprice', 'xcongresscode', 'xdiocode', 'xlang', 'xpublishplace', 'xdescription', 'xweight', 'ximgeurl', 'xpdfurl', 'xregdate', 'xissubject', 'xiscreator', 'xispublisher', 'xislibrary', 'xistag', 'xisseller', 'xname2', 'xisname', 'xisdoc', 'xisdoc2', 'xiswater', 'xwhite', 'xblack', 'xparent'];
    protected $table = 'bookir_book';
    public $timestamps = false;

    static public function getShamsiYear($date)
    {
        $date = CalendarUtils::strftime('Y', strtotime($date));

        return $date;
    }
}
