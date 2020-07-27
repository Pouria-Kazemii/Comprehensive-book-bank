<?php

namespace App\Models\Library;

use App\Models\Book;
use App\Models\Location\City;
use App\Models\Location\State;
use Illuminate\Database\Eloquent\Model;

class Library extends Model
{
    protected $fillable = ['all','stateCode','libraryCode','libraryName','townshipCode','partCode','cityCode','villageCode','address','phone','libTypeCode','postCode'];
    
    public function city()

    {
        return $this->belongsTo(City::class,'townshipCode', 'townshipCode');
    }

    public function state()

    {
        return $this->belongsTo(State::class,'stateCode', 'stateCode');
    }
    public function books()
    {
        return $this->belongsToMany(Book::class);
    }
    public function setAllAttribute($value)
    {
        $this->attributes['all'] = json_encode($value);
    }

}
