<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MajmaApiPublisher extends Model
{
    use HasFactory;
    protected $table = 'majma_api_publishers';
    protected $fillable = ['xpublisher_id', 'xstatus'];
    protected $primaryKey = 'xid';
    public $timestamps = true;
}
