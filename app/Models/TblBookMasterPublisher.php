<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblBookMasterPublisher extends Model
{
    protected $fillable = ['book_master_id', 'publisher_id'];
    protected $table = 'tbl_book_master_publisher';
    public $timestamps = false;

    public function tblBookMaster()
    {
        return $this->belongsToMany(TblBookMaster::class);
    }

    public function tblPublisher()
    {
        return $this->belongsToMany(TblPublisher::class);
    }
}
