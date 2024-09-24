<?php

namespace App\Models\MongoDBModels;

use Jenssegers\Mongodb\Eloquent\Model;

class BookDioCachedData extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'cached_books_dio';

    protected $primaryKey = '_id';

    protected $fillable =[
        'dio_subject_title',
        'dio_subject_id',
        'year',
        'total_circulation',
        'total_price',
        'average',
        'total_pages',
        'count',
        'paragraph',
        'first_cover_total_circulation',
        'first_cover_total_price',
        'first_cover_average',
        'first_cover_total_pages',
        'first_cover_count',
        'first_cover_paragraph'
    ];
}
