<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;

class ParsiToEnglishFormat extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'formats';

    protected $primaryKey = '_id';

    protected $fillable = [
        'en_title',
        'fa_title'
    ];
}
