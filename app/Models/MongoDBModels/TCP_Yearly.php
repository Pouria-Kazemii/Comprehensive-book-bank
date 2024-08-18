<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class TCP_Yearly extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'cached_top_circulation_publishers';

    protected $primaryKey = '_id';

    protected $fillable = [
        'year' ,
        'publishers',
    ];
}
