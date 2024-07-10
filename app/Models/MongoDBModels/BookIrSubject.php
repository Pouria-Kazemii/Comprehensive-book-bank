<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class BookIrSubject extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'bookir_subjects';

    protected $primaryKey = '_id';

    protected $fillable = [
        'xsubject_name',
        '_id',
    ];

    public $timestamps = false;
}
