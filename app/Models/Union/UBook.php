<?php

namespace App\Models\Union;

use Jenssegers\Mongodb\Eloquent\Model;
use App\Models\Union\UAuthor;
use App\Models\Union\ULibrary;
use App\Models\Union\UTag;

class UBook extends Model
{
    protected $collection = 'UBook';
    protected $dates = ['crawled_at'];
    protected $connection = 'mongodb';
    protected $fillable = ['title', 'ISBN', 'ISBN10', 'source', 'subject', 'publisher', 'barcode', 'language', 'translate', 'classification', 'prints', 'publication_place', 'description', 'library_info', 'image', 'editor'];

    public function authors()
    {
        return $this->belongsToMany(UAuthor::class);
    }

    public function libraries()
    {
        return $this->belongsToMany(ULibrary::class);
    }

    public function tags()
    {
        return $this->belongsToMany(UTag::class);
    }
}
