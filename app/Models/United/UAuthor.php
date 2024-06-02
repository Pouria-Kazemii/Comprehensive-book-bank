<?php

namespace App\Models\United;

use Jenssegers\Mongodb\Eloquent\Model;

class UAuthor extends Model
{
    protected $collection = 'UAuthor';

    public function books()
    {
        return $this->embedsMany(UBook::class);
    }
}

