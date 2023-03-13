<?php

namespace App\Models\Bookir;

use Illuminate\Database\Eloquent\Model;

class BiSubject extends Model
{
    protected $table = 'bookir_subject';
    protected $primaryKey = 'xid';
    public $timestamps = false;
    protected $fillable = ['xsubject'];


}
