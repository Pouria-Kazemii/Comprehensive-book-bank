<?php

namespace App\Models\Bookir;

use Illuminate\Database\Eloquent\Model;

class BiRule extends Model
{
    protected $table = 'bookir_rules';
    protected $primaryKey = 'xid';
    public $timestamps = false;
    protected $fillable = ['xrole'];

    

}
