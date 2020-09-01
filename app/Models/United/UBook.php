<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use App\Models\United\UAuthor;
use App\Models\United\ULibrary;
use App\Models\United\UTag;

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
