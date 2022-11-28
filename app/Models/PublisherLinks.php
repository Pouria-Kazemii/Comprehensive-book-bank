<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublisherLinks extends Model
{
    use HasFactory;
    protected $table = 'publisherlinks';
    protected $fillable = ['idd', 'pub_url', 'total_page', 'crawl_page','pub_name','pub_phone','regtime','xcheck_status'];
    protected $primaryKey = 'idd';
    public $timestamps = false;
}
