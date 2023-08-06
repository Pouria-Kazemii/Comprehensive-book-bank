<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookFidibo extends Model
{
    protected $fillable = ['recordNumber','parentId','title','nasher','saleNashr', 'tedadSafe', 'shabak','translate','desc','images','price','lang', 'fileSize', 'partnerArray','tags'];
    protected $table = 'bookFidibo1';
    protected $primaryKey = 'id';

    // public function authors() {
    //     return $this->belongsToMany(Author::class);
    // }
}

