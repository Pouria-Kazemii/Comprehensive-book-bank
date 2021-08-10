<?php

namespace App\Models\Union;

use Jenssegers\Mongodb\Eloquent\Model;
use App\Models\Union\UBook;

class UAuthor extends Model
{
    protected $collection = 'UAuthor';
    protected $connection = 'mongodb';
    protected $fillable = ['name', 'first_name','last_name'];

    public function books()
    {
        return $this->belongsToMany(UBook::class);
    }
}

