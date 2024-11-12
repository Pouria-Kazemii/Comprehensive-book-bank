<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class TCC_Yearly extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'cached_top_circulation_creators';

    protected $primaryKey = '_id';

    protected $fillable = [
        'year' ,
        'creators',
    ];
}
