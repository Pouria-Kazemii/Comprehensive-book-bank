<?php

namespace App\Models\Bookir;

use Illuminate\Database\Eloquent\Model;

class BiPartner extends Model
{
    protected $table = 'bookir_partner';
    protected $primaryKey = 'xid';
    public $timestamps = false;
    protected $fillable = ['xcreatorname','xketabir_id'];

    

}
