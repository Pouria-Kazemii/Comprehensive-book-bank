<?php

namespace App\Models\United;

use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Relations\EmbedsMany;
use App\Models\United\UBook;

class UAuthor extends Model
{
    protected $collection = 'UAuthor';
    protected $connection = 'mongodb';
    protected $fillable = ['name', 'first_name','last_name'];

    public function books()
    {
        return $this->embedsMany(UBook::class);
    }
}

