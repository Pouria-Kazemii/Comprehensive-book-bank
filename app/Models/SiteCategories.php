<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteCategories extends Model
{
    protected $fillable = ['id', 'domain', 'cat_link', 'cat_name'];
    protected $table = 'site_categories';
    protected $primaryKey = 'id';
    public $timestamps = true;
}
