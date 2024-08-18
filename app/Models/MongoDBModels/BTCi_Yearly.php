<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class BTCi_Yearly extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'cached_book_total_circulation';

    protected $primaryKey = '_id';

    protected $fillable = [
        'year',
        'circulation'
    ];
}
