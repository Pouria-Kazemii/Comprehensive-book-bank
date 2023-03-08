<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookirRules extends Model
{
    protected $fillable = ['xid', 'xrole', 'xregdate', 'xisauthors'];
    protected $table = 'bookir_rules';
    protected $primaryKey = 'xid';
    public $timestamps = false;
}
