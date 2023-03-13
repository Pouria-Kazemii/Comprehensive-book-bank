<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblBookMasterPerson extends Model
{
    protected $fillable = ['book_master_id', 'person_id', 'role'];
    protected $table = 'tbl_book_master_person';
    public $timestamps = false;

    public function tblBookMaster()
    {
        return $this->belongsToMany(TblBookMaster::class);
    }

    public function tblPerson()
    {
        return $this->belongsToMany(TblPerson::class);
    }
}
