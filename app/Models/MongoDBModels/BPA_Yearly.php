<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class BPA_Yearly extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'cached_book_price_average';

    protected $primaryKey = '_id';

    protected $fillable =[
        'year',
        'average',
    ];
}
