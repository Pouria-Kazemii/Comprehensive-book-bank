<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookDigi extends Model
{
    protected $fillable = ['title','nasher', 'recordNumber','tedadSafe', 'cat', 'count', 'ghateChap', 'noechap', 'shabak','desc','images','price',  'rate', 'partnerArray','jeld','features', 'saveBook', 'sellers', 'noekaghaz', 'vazn'];
    protected $table = 'bookDigi';

    public function authors() {
        return $this->belongsToMany(Author::class);
    }
}
