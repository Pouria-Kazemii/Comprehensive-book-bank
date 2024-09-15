<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class NewBookPublishDate extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'new_books_publish_date';

    protected $fillable = [
        'years',
        'creators',
        'publishers',
        'checked',
        'created_at'
    ];

    public $timestamps = false;
}
