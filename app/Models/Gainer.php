<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gainer extends Model
{
    protected $table = 'gainer';
    protected $fillable = ['name','ip','token','access_path', 'block'];

}
