<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookK24 extends Model
{
    protected $fillable = ['title','nasher','lang','cats','saleNashr','nobatChap', 'recordNumber','tedadSafe', 'ghateChap', 'shabak', 'tarjome','desc','image','price', 'DioCode', 'printCount', 'printLocation', 'partnerArray', 'saveBook'];
    protected $table = 'bookK24';

    public function authors() {
        return $this->belongsToMany(Author::class);
    }
}
