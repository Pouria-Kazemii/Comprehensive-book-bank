<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebSiteBookLinksDefects extends Model
{
    protected $fillable = ['id', 'siteName', 'recordNumber','book_links', 'bookId','bugId','crawlerInfo','crawlerStatus','crawlerTime','result','excelId'];
    protected $table = 'tbl_website_book_links_defects';
    protected $primaryKey = 'id';
    public $timestamps = true;
}
