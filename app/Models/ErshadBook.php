<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErshadBook extends Model
{
    use HasFactory;
    protected $table = 'ershad_book';
    protected $fillable = ['xid', 'xtitle_fa', 'xtitle_en', 'xtype', 'xrade', 'xpublisher_name', 'xlang', 'xisbn', 'xpage_number', 'xmoalefin', 'xmotarjemin', 'xdesc', 'xformat', 'xgerdavarande', 'xpadidavarande', 'created_at', 'updated_at' ];
    protected $primaryKey = 'xid';

}
