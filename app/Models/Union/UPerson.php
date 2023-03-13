<?php

namespace App\Models\Union;

use Jenssegers\Mongodb\Eloquent\Model;
use App\Models\Union\UBook;

class UPerson extends Model
{
    protected $collection = 'UPerson';
    protected $connection = 'mongodb';
    protected $fillable = ['name', 'first_name', 'last_name', 'role', 'ucode'];

    public function books()
    {
        return $this->belongsToMany(UBook::class);
    }
}

