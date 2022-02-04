<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookirPublisher extends Model
{
    protected $fillable = ['xid', 'xtabletype', 'xsiteid', 'xparentid', 'xpageurl', 'xpublishername', 'xmanager', 'xactivity', 'xplace', 'xaddress', 'xpobox', 'xzipcode', 'xphone', 'xcellphone', 'xfax', 'xlastupdate', 'xtype', 'xpermitno', 'xemail', 'xsite', 'xisbnid', 'xfoundingdate', 'xispos', 'ximageurl', 'xregdate', 'xpublishername2', 'xiswiki', 'xismajma', 'xisname', 'xsave', 'xwhite', 'xblack'];
    protected $table = 'bookir_publisher';
    public $timestamps = false;
}
