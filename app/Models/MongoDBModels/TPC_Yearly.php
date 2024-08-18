<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class TPC_Yearly extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'cached_top_price_creators';

    protected $primaryKey = '_id';

    protected $fillable = [
        'year' ,
        'creators',
    ];
}
