<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookirPartner extends Model
{
    protected $fillable = ['xid', 'xcreatorname', 'xiswiki', 'xname2', 'xisname', 'xregdate', 'xwhite', 'xblack', 'xketabir_id'];
    protected $table = 'bookir_partner';
    public $timestamps = false;
}
