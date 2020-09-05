<?php

namespace App\Models\United;

use Jenssegers\Mongodb\Eloquent\Model;
use App\Models\United\UAuthor;
use App\Models\United\ULibrary;
use App\Models\United\UTag;

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
