<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class BookIrDaily extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'bookir_daily';

    protected $primaryKey = '_id';

    protected $fillable = [
        'day' ,
        'month' ,
        'year' ,
        'count',
        'date'
    ];
}
