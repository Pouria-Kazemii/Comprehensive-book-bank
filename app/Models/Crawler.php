<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crawler extends Model
{
    protected $table = 'crawler';
    protected $fillable = ['name','start','end','status'];
    static $crawlerSize = 1000;
}
