<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgeGroup extends Model
{
    use HasFactory;
    protected $table = 'age_group';
    protected $fillable = ['xbook_id', 'xa','xb','xg','xd','xh','xstatus'];
    protected $primaryKey = 'xid';
    public $timestamps = true;
}
