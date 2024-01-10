<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MajmaApiBook extends Model
{
    use HasFactory;
    protected $table = 'majma_api_books';
    protected $fillable = ['xbook_id', 'xstatus','xfunction_caller'];
    protected $primaryKey = 'xid';
    public $timestamps = true;
}
