<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorBookgisoom extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'author_id', 'book_gisoom_id'];
    protected $table = 'author_book_gisoom';
    public $timestamps = true;
}