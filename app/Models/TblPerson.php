<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblPerson extends Model
{
    protected $fillable = ['name'];
    protected $table = 'tbl_person';

    public function tblBookMasterPerson()
    {
        return $this->belongsToMany(TblBookMasterPerson::class);
    }
}
