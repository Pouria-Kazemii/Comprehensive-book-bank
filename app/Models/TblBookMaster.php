<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblBookMaster extends Model
{
    protected $fillable = ['record_number', 'shabak', 'title', 'title_en', 'publisher', 'author', 'translator', 'language', 'category', 'weight', 'book_cover_type', 'paper_type', 'type_printing', 'editor', 'first_year_publication', 'last_year_publication', 'count_pages', 'book_size', 'print_period_count', 'print_count', 'print_location', 'translation', 'desc', 'image', 'price', 'dio_code'];
    protected $table = 'tbl_book_master';

    public function tblBookMasterCategory()
    {
        return $this->belongsToMany(TblBookMasterCategory::class);
    }

    public function tblBookMasterPerson()
    {
        return $this->belongsToMany(TblBookMasterPerson::class);
    }

    public function tblBookMasterPublisher()
    {
        return $this->belongsToMany(TblBookMasterPublisher::class);
    }
}
