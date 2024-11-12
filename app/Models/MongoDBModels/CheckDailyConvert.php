<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class CheckDailyConvert extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'convert_log';

    protected $primaryKey = '_id';

    protected $fillable = [
        'command' ,
        'executed_at' ,
        'status'
    ];
}
