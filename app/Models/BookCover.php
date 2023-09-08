<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookCover extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'name'];
    protected $table = 'book_covers';
    protected $primaryKey = 'id';
    public $timestamps = true;



}
