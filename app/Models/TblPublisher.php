<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblPublisher extends Model
{
    protected $fillable = ['title'];
    protected $table = 'tbl_publisher';

    public function tblBookMasterPublisher()
    {
        return $this->belongsToMany(TblBookMasterPublisher::class);
    }
}
