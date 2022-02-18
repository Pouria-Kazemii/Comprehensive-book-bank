<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblBookMasterCategory extends Model
{
    protected $fillable = ['book_master_id', 'category_id'];
    protected $table = 'tbl_book_master_category';
    public $timestamps = false;

    public function tblBookMaster()
    {
        return $this->belongsToMany(TblBookMaster::class);
    }

    public function tblCategory()
    {
        return $this->belongsToMany(TblCategory::class);
    }
}
