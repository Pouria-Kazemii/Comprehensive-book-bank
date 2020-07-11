<?php

namespace App\Models\Location;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = ['all','stateCode','stateName'];
    public function setAllAttribute($value)
    {
        $this->attributes['all'] = json_encode($value);
    }

}
