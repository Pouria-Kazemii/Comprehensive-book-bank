<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class TempBookDossier extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'book_dossier_temp';

    protected $primaryKey = '_id';

    protected  $fillable = [

    ];
}
