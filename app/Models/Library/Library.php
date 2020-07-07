<?php

namespace App\Models\Library;

use Illuminate\Database\Eloquent\Model;

class Library extends Model
{
    protected $fillable = ['stateCode','libraryCode','libraryName','townshipCode','partCode','cityCode','villageCode','address','phone','libTypeCode','postCode'];
}
