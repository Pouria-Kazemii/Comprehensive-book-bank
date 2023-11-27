<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookBarkhatBook extends Model
{
    protected $fillable = ['recordNumber','parentId','title','nasher','saleNashr','tedadSafe','weight','nobatChap','shabak','translate','desc','subTopic','images','price','jeld','ghateChap','partnerArray','cats','tags','book_master_id','check_status','has_permit',];
    protected $table = 'tbl_book_barkhatbook';
    protected $primaryKey = 'id';

    // public function authors() {
    //     return $this->belongsToMany(Author::class);
    // }


    
}



