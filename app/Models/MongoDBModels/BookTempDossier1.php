<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class BookTempDossier1 extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'book_dossier_temp_1';
    protected $primaryKey  = '_id';
    protected $fillable = [
        'book_ids',
        'creator',
        'book_names',
        'isbn',
        'other_isbns'
    ];


}
