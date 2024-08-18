<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class BTPa_Yearly extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'cached_book_total_pages';

    protected $primaryKey = '_id';

    protected $fillable = [
        'year' ,
        'total_pages'
    ];
}
