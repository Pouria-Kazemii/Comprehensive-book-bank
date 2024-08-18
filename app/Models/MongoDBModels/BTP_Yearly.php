<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class BTP_Yearly extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'cached_book_total_price';

    protected $primaryKey = '_id';

    protected $fillable = [
        'year' ,
        'price'
    ];
}
