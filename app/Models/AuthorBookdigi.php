<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorBookdigi extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'author_id', 'book_digi_id'];
    protected $table = 'author_book_digi';
    public $timestamps = true;
}
