<?php

namespace App\Models\United;


use Jenssegers\Mongodb\Eloquent\Model;

class UBook extends Model
{
    protected $collection = 'UBook';
    protected $dates = ['crawled_at'];

    public function authors()
    {
        return $this->embedsMany(UAuthor::class);
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
