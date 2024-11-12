<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookirPartner extends Model
{
    protected $fillable = ['xid','mongo_id', 'xcreatorname', 'xiswiki', 'xname2', 'xisname', 'xregdate', 'xwhite', 'xblack', 'xketabir_id','xstatus'];
    protected $table = 'bookir_partner';
    protected $primaryKey = 'xid';

    public $timestamps = false;

    public function books()
    {
        return $this->belongsToMany(BookirBook::class,'bookir_partnerrule','xcreatorid','xbookid');
    }
}
