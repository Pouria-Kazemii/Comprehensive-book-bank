<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookirPublisher extends Model
{
    protected $fillable = ['xid', 'xtabletype', 'xsiteid', 'xparentid', 'xpageurl', 'xpageurl2','xpublishername', 'xmanager', 'xactivity', 'xplace', 'xaddress', 'xpobox', 'xzipcode', 'xphone', 'xcellphone', 'xfax', 'xlastupdate', 'xtype', 'xpermitno', 'xemail', 'xsite', 'xisbnid', 'xfoundingdate', 'xispos', 'ximageurl', 'xregdate', 'xpublishername2', 'xiswiki', 'xismajma', 'xisname', 'xsave', 'xwhite', 'xblack'];
    protected $table = 'bookir_publisher';
    protected $primaryKey = 'xid';

    public $timestamps = false;

    public function books()
    {
        return $this->belongsToMany(BookirBook::class,'bi_book_bi_publisher','bi_publisher_xid','bi_book_xid');
    }
    
}
