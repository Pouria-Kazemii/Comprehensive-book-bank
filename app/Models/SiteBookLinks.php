<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteBookLinks extends Model
{
    protected $fillable = ['id', 'domain', 'book_links', 'status','check_repeat'];
    protected $table = 'site_book_links';
    protected $primaryKey = 'id';
    public $timestamps = true;
}
