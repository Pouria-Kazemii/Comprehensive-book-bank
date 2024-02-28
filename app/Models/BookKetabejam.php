<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookKetabejam extends Model
{
    protected $fillable = ['pageUrl','parentId','title','nasher','saleNashr','tedadSafe','shabak','translate','desc','images','price','jeld', 'ghateChap','length','height','width','vazn','partnerArray','cats','tags','subject','book_master_id','check_status','has_permit','mongo_id'];
    protected $table = 'tbl_book_ketabejam';
    protected $primaryKey = 'id';

    // public function authors() {
    //     return $this->belongsToMany(Author::class);
    // }
}



