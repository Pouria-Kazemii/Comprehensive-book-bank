<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookKetabrah extends Model
{
    protected $fillable = ['recordNumber','parentId','title','nasher','nasherSouti','saleNashr', 'tedadSafe', 'shabak','translate','desc','images','price','lang', 'partnerArray','tags','cat','format','title2','book_master_id','check_status','has_permit'];
    protected $table = 'bookKetabrah';
    protected $primaryKey = 'id';

    // public function authors() {
    //     return $this->belongsToMany(Author::class);
    // }
}

