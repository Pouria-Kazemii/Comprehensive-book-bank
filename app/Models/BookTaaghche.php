<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookTaaghche extends Model
{
    protected $fillable = ['recordNumber','parentIdI','title','nasher','saleNashr', 'tedadSafe', 'shabak','translate','content','images','price','lang', 'fileSize', 'partnerArray','tags','book_master_id','commentsCount','ghatechap','jeld','authorsname','authorbio','authorimg','rating','commentcrawl','check_status','has_permit'];
    protected $table = 'BookTaaghche';
    protected $primaryKey = 'id';

    // public function authors() {
    //     return $this->belongsToMany(Author::class);
    // }
}

