<?php

namespace App\Models\United;


use Illuminate\Database\Eloquent\Model;

class UTag extends Model
{
    protected $collection = 'UTag';
    public function tags()
    {
        return $this->belongsToMany(UBook::class);
    }
}
