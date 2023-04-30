<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblCategory extends Model
{
    protected $fillable = ['title'];
    protected $table = 'tbl_category';

    public function tblBookMasterCategory()
    {
        return $this->belongsToMany(TblBookMasterCategory::class);
    }
}
