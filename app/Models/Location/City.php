<?php

namespace App\Models\Location;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['townshipCode','townshipName','stateCode'];
}
