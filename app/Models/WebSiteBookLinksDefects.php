<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebSiteBookLinksDefects extends Model
{
    protected $fillable = ['id', 'siteName', 'recordNumber','book_links', 'bookId','bugId','old_check_status','old_has_permit','old_unallowed','crawlerInfo','crawlerStatus','crawlerTime','new_check_status','new_has_permit','new_unallowed','result','excelId'];
    protected $table = 'tbl_website_book_links_defects';
    protected $primaryKey = 'id';
    public $timestamps = true;
}
