<?php

namespace App\Models\United;

use Illuminate\Database\Eloquent\Model;
use App\Models\United\UBook;

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
