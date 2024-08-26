<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;

class PublisherCacheData extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'cached_publishers';

    protected $primaryKey = '_id';

    protected $fillable = [
        'publisher_id' ,
        'year',
        'total_circulation',
        'total_price',
        'average',
        'total_pages',
        'count',
    ];
}
