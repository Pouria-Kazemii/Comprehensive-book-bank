<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiBookBiSubject extends Model
{
    protected $fillable = ['xid', 'bi_book_xid', 'bi_subject_xid', 'xorder'];
    protected $table = 'bi_book_bi_subject';
    public $timestamps = false;
}
