<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaaghcheComment extends Model
{
    protected $connection = 'mysql';
    protected $table = 'taaghche_comments';
    protected $fillable = [
        'name',
        'comment',
        'rate',
        'date',
        'taaghche_book_id'
    ];
    use HasFactory;
}
