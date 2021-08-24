<?php

namespace App\Models\Union;

use Jenssegers\Mongodb\Eloquent\Model;
use App\Models\Union\UPerson;
use App\Models\Union\ULibrary;
use App\Models\Union\UTag;

class UBook extends Model
{
    protected $collection = 'UBook';
    use SoftDeletes;
    protected $dates = ['crawled_at','deleted_at'];
    protected $connection = 'mongodb';
    protected $primaryKey = 'id';
    protected $fillable = ['title', 'ISBN', 'ISBN10', 'source', 'subject', 'publisher', 'barcode', 'language', 'translate', 'classification', 'prints', 'publication_place', 'description', 'library_info', 'image', 'editor'];


    public function persons()
    {
        return $this->belongsToMany(UPerson::class);
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
