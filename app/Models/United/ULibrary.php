<?php

namespace App\Models\United;

use Jenssegers\Mongodb\Eloquent\Model;

class ULibrary extends Model
{
    protected $collection = 'ULibrary';
    protected $connection = 'mongodb';
    protected $fillable = ['title', 'code','phone', 'postalcode', 'type', 'address', 'state', 'substate', 'city', 'village' ]
}
