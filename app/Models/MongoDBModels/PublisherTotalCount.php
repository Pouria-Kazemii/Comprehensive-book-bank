<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class PublisherTotalCount extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'cached_publishers_total_count';

    protected $primaryKey = '_id';

    protected $fillable = [
        'publisher_id' ,
        'count',
    ];
}
