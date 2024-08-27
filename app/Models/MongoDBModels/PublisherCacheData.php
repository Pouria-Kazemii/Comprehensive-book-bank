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
        'first_cover_total_circulation',
        'total_price',
        'first_cover_total_price',
        'average',
        'first_cover_average',
        'total_pages',
        'first_cover_total_pages',
        'count',
        'first_cover_count'
    ];
}
