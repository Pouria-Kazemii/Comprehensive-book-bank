<?php

namespace App\Models\MongoDBModels;


use Jenssegers\Mongodb\Eloquent\Model;

class BookIrCreator extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'bookir_creators';

    protected $primaryKey = '_id';

    protected $fillable = [
        'xsqlid',
        'xcreatorname' ,
        'ximageurl' ,
        'xwhite' ,
        'xblack',
        'iranketabinfo',
        'xrules'
    ];

    public $timestamps = false ;
}
