<?php

namespace App\Models\Bookir;

use Illuminate\Database\Eloquent\Model;

class BiPublisher extends Model
{
    protected $table = 'bookir_publisher';
    protected $primaryKey = 'xid';
    public $timestamps = false;
    protected $fillable = ['xpublishername','xmanager','xactivity','xplace', 'xaddress', 'xzipcode', 'xpobox', 'xphone', 'xcellphone', 'xfax', 'xtype', 'xpermitno', 'xemail', 'xsite', 'xisbnid', 'xfoundingdate'];

    

}
