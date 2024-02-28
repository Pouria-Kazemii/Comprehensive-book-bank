<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookShahreKetabOnline extends Model
{
    protected $fillable = ['recordNumber','parentId','title','nasher','nobatChap','saleNashr','tedadSafe','shabak','translate','desc','images','price','lang','jeld', 'ghateChap','length','height','width','vazn','partnerArray','cats','tags','subject','book_master_id','check_status','has_permit','mongo_id'];
    protected $table = 'tbl_book_shahre_ketab_online';
    protected $primaryKey = 'id';

    // public function authors() {
    //     return $this->belongsToMany(Author::class);
    // }
}



