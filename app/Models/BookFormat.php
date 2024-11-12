<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookFormat extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'name'];
    protected $table = 'book_formats';
    protected $primaryKey = 'id';
    public $timestamps = true;
}
