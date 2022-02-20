<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

class BookirBook extends Model
{
    protected $fillable = ['xid', 'xdocid', 'xsiteid', 'xpageurl', 'xname', 'xdoctype', 'xpagecount', 'xformat', 'xcover', 'xprintnumber', 'xcirculation', 'xcovernumber', 'xcovercount', 'xapearance', 'xisbn', 'xisbn2', 'xpublishdate', 'xcoverprice', 'xminprice', 'xcongresscode', 'xdiocode', 'xlang', 'xpublishplace', 'xdescription', 'xweight', 'ximgeurl', 'xpdfurl', 'xregdate', 'xissubject', 'xiscreator', 'xispublisher', 'xislibrary', 'xistag', 'xisseller', 'xname2', 'xisname', 'xisdoc', 'xisdoc2', 'xiswater', 'xwhite', 'xblack', 'xparent'];
    protected $table = 'bookir_book';
    protected $primaryKey = 'xid';
    public $timestamps = false;

    static public function getShamsiYear($date)
    {
        $date = CalendarUtils::strftime('Y', strtotime($date));

        return $date;
    }

    static public function getShamsiYearMonth($date)
    {
        $date = CalendarUtils::strftime('Y-m', strtotime($date));

        return $date;
    }

    static public function generateMiladiDate($year, $flagEnd = false)
    {
        // 1 = end year --- 0 = start year
        if($flagEnd == 1)
            $date = (new Jalalian($year, 1, 1, 0, 0, 0))->toCarbon()->year."-12-30";
        else
            $date = (new Jalalian($year, 12, 29, 0, 0, 0))->toCarbon()->year."-01-01";

        return $date;
    }

    static public function convertMiladi2Shamsi($date)
    {
        return CalendarUtils::strftime('Y-m-d', strtotime($date));
    }
}
