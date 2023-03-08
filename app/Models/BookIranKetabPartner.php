<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookIranKetabPartner extends Model
{
    use HasFactory;
    protected $fillable = ['id','partnerId','roleId','partnerEnName','partnerName','partnerDesc','partnerImage'];
    protected $table = 'bookiranketab_partner';
    protected $primaryKey = 'id';
}
