<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;

class DioSubject extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'dio_subjects';

    protected $primaryKey = '_id';

    protected $fillable = [
        'id_by_law',
        'title',
        'dio_type',
        'parent_id',
        'has_child',
        'level',
        'range',
        'except'
    ];
}
