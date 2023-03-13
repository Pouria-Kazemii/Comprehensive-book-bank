<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookirSubject extends Model
{
    protected $fillable = ['xid', 'xparentid', 'xsubject', 'xregdate', 'xhaschild', 'xsubjectname2', 'xisname'];
    protected $table = 'bookir_subject';
    public $timestamps = false;
}
