<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiBookBiPublisher extends Model
{
    protected $fillable = ['xid', 'bi_book_xid', 'bi_publisher_xid'];
    protected $table = 'bi_book_bi_publisher';
    public $timestamps = false;
}
