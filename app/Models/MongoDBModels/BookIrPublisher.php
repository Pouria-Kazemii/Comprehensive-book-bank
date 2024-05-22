<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;

class BookIrPublisher extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'bookir_publishers';

    protected $primaryKey = '_id';

    protected $fillable = [
        'xsqlid' ,
        'xtabletype',
        'xsiteid' ,
        'xparentid' ,
        'xpageurl' ,
        'xpageurl2' ,
        'xpublishername' ,
        'xmanager' ,
        'xactivity',
        'xplace' ,
        'xaddress' ,
        'xpobox' ,
        'xzipcode' ,
        'xphone' ,
        'xcellphone' ,
        'xfax' ,
        'xlastupdate' ,
        'xtype' ,
        'xpermitno' ,
        'xemail' ,
        'xsite' ,
        'xisbnid' ,
        'xfoundingdate' ,
        'xispos' ,
        'ximageurl' ,
        'xregdate' ,
        'xpublishername2' ,
        'xiswiki' ,
        'xismajma' ,
        'xisname' ,
        'xsave' ,
        'xwhite' ,
        'xblack' ,
        'check_publisher'

    ];

    public $timestamps = false ;
}
