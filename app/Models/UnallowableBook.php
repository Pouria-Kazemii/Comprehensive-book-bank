<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnallowableBook extends Model
{
    protected $fillable = ['xtitle','xauthor','xpublish_date','xpublisher_name','xtranslator'];
    protected $table = 'unallowable_book';
    protected $primaryKey = 'xid';


}
