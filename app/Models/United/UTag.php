<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Models\United\UBook;

class UTag extends Model
{
    protected $collection = 'UTag';
    public function tags()
    {
        return $this->belongsToMany(UBook::class);
    }
}
