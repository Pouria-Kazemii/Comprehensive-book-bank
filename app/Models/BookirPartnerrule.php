<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookirPartnerrule extends Model
{
    protected $fillable = ['xid', 'xbookid', 'xcreatorid', 'xroleid'];
    protected $table = 'bookir_partnerrule';
    public $timestamps = false;
}
