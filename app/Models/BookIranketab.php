<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookIranketab extends Model
{
    protected $fillable = ['parentId','title','enTitle','subTitle','refCode','nasher','traslate','tags','saleNashr','nobatChap', 'recordNumber','tedadSafe', 'ghateChap', 'shabak','desc','images','price',  'rate', 'partnerArray','jeld','features', 'partsText', 'notes', 'prizes', 'saveBook','recheck_info'];
    protected $table = 'bookIranketab';
    protected $primaryKey = 'id';

    public function authors() {
        return $this->belongsToMany(Author::class);
    }

    public function parent()
    {
    return $this->belongsTo(BookIranketab::class, 'parentId');
    }

    public function children()
    {
        return $this->hasMany(BookIranketab::class, 'parentId');
    }

}
