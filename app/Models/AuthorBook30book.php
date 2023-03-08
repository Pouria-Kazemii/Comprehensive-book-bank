<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorBook30book extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'author_id', 'book30book_id'];
    protected $table = 'author_book30book';
    public $timestamps = true;
}
