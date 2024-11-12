<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;

class BookIrBook2 extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'bookir_books';

    protected $primaryKey = '_id';

    protected $fillable = [
        'xsqlid' ,
        'xsiteid',
        'xpageurl',
        'xpageurl2',
        'xname',
        'xdoctype',
        'xpagecount',
        'xprintnumber',
        'xcirculation',
        'xcovernumber',
        'xcovercount',
        'xapearance',
        'xisbn',
        'xisbn2',
        'xisbn3',
        'xpublishdate',
        'xpublishdate_shamsi',
        'xcoverprice',
        'xminprice',
        'xcongresscode',
        'xdiocode',
        'diocode_subject',
        'xlang',
        'xpublishplace',
        'xdescription',
        'xweight',
        'ximgeurl',
        'xpdfurl',
        'xregdata',
        'xwhite',
        'xblack',
        'xoldparent',
        'is_translate',
        'xparent',
        'xmongo_parent',
        'xrequestmerge',
        'xrequest_manage_parent',
        'xreg_userid',
        'xtotal_price',
        'xtotal_page',
        'xaudience',
        'partners' ,
        'publisher',
        'subjects' ,
        'languages' ,
        'age_group' ,
        'xformat' ,
        'xcover'
    ];


    public $timestamps = false ;
}
