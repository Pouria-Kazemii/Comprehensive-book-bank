<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

class BookirBook extends Model
{
    protected $fillable = ['xid', 'xdocid', 'xsiteid', 'xpageurl', 'xpageurl2', 'xname', 'xdoctype', 'xpagecount', 'xformat', 'xcover', 'xprintnumber', 'xcirculation', 'xcovernumber', 'xcovercount', 'xapearance', 'xisbn', 'xisbn2','xisbn3', 'xpublishdate', 'xcoverprice', 'xminprice', 'xcongresscode', 'xdiocode', 'xlang', 'xpublishplace', 'xdescription', 'xweight', 'ximgeurl', 'xpdfurl', 'xregdate', 'xissubject', 'xiscreator', 'xispublisher', 'xislibrary', 'xistag', 'xisseller', 'xname2', 'xisname', 'xisdoc', 'xisdoc2', 'xiswater', 'xwhite', 'xblack', 'xparent','xreg_userid'];
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
        if ($flagEnd == 1)
            $date = (new Jalalian($year, 1, 1, 0, 0, 0))->toCarbon()->year . "-12-30";
        else
            $date = (new Jalalian($year, 12, 29, 0, 0, 0))->toCarbon()->year . "-01-01";

        return $date;
    }

    static public function convertMiladi2Shamsi($date)
    {
        return CalendarUtils::strftime('Y-m-d', strtotime($date));
    }
    static public function convertMiladi2Shamsi_with_slash($date)
    {
        return CalendarUtils::strftime('Y/m/d', strtotime($date));
    }
    public function children()
    {
        return $this->hasMany(self::class, 'xparent');
    }

    public function parents()
    {
        return $this->belongsTo(self::class, 'xid');
    }

    public static function toGregorian($date,$input_delimiter,$output_delimiter)
    {
        $jdate_arr = explode($input_delimiter,$date);
        $gdate = \Morilog\Jalali\CalendarUtils::toGregorian($jdate_arr['0'], $jdate_arr['1'], $jdate_arr['2']);
        return $gdate['0'].$output_delimiter.$gdate['1'].$output_delimiter.$gdate['2'];
    }

    public function publishers(){
        return $this->belongsToMany(BookirPublisher::class,'bi_book_bi_publisher','bi_book_xid','bi_publisher_xid');
    }

    // public function creators(){
    //     return $this->belongsToMany(BookirPartner::class,'bookir_partnerrule','xbookid','xcreatorid');
    // }
    public function subjects(){
        return $this->belongsToMany(BookirSubject::class,'bi_book_bi_subject','bi_book_xid','bi_subject_xid');
    }
    public function partnersRoles(){
        return $this->belongsToMany(bookirpartner::class,'bookir_partnerrule','xbookid','xcreatorid')->withPivot('xbookid', 'xcreatorid','xroleid');
    }



}
