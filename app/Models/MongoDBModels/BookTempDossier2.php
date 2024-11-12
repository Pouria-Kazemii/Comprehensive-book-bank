<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;


class BookTempDossier2 extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'book_dossier_temp_2';
    protected $primaryKey  = '_id';
    protected $fillable =[
        'dossier_temp_one_id',
        'dossier_temp_one_id_original',
        'creator',
        'book_names',
        'book_names_original',
        'book_counts'
    ];
}
