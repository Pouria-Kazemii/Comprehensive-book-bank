<?php

namespace App\Models\Location;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['all','townshipCode','townshipName','stateCode'];
    public function setAllAttribute($value)
    {
        $this->attributes['all'] = json_encode($value);
    }
}
