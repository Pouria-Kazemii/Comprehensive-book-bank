<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookDigiRelated extends Model
{
    protected $fillable = ['book_id'];
    protected $table = 'bookDigiRelated';

    public function book() {
        return $this->hasOne(BookDigi::class, 'id', 'book_id');
    }
}
