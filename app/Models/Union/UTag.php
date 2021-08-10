<?php

namespace App\Models\Union;

use Jenssegers\Mongodb\Eloquent\Model;
use App\Models\Union\UBook;

class UTag extends Model
{
    protected $collection = 'UTag';
    protected $connection = 'mongodb';
    protected $fillable = ['title'];

    public function tags()
    {
        return $this->belongsToMany(UBook::class);
    }
}
