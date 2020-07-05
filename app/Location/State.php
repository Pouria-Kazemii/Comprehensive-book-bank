<?php

namespace App\Location;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = ['stateCode','stateName'];
}
