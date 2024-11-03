<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;

class BookDossier extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'book_dossier';

    protected $primaryKey = '_id';

    protected $fillable = [
        'xmain_name',
        'xis_translate' ,
        'xnames',
        'xmain_creator',
        'xisbns',
        'xdescriptions',
    ];

    public $timestamps = false ;
}
