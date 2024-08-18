<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class BTC_Yearly extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'cached_book_total_count';

    protected $primaryKey = '_id';

    protected $fillable = [
        'year' ,
        'count'
    ];
}
