<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class TPP_Yearly extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'cached_top_price_publishers';

    protected $primaryKey = '_id';

    protected $fillable = [
        'year' ,
        'publishers',
    ];
}
