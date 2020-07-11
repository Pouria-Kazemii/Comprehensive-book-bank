<?php

namespace App\Models;

use App\Models\Library\Library;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['all','recordNumber','Creator', 'MahalNashr', 'Title', 'mozoe', 'Yaddasht', 'TedadSafhe', 'saleNashr', 'EjazeReserv', 'EjazeAmanat', 'shabak'];

    // public function setLstYaddashtsAttribute($value)
    // {
    //     $this->attributes['lstYaddashts'] = json_encode($value);
    // }
    // public function setLstMozoeEsmAttribute($value)
    // {
    //     $this->attributes['lstMozoeEsm'] = json_encode($value);
    // }
    public function setAllAttribute($value)
    {
        $this->attributes['all'] = json_encode($value);
    }
    public function libraries()
    {
        return $this->belongsToMany(Library::class);
    }
    
}
