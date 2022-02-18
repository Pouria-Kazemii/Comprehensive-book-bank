<?php

namespace App\Models\Bookir;

use Illuminate\Database\Eloquent\Model;

class BiPartnerRules extends Model
{
    protected $table = 'bookir_partnerrule';
    protected $primaryKey = 'xid';
    public $timestamps = false;
    protected $fillable = [];

    public function book()
    {
        return $this->hasOne(BiBook::class, 'xbookid', 'xid');
    }

    public function rule()
    {
        return $this->hasOne(BiRule::class, 'xroleid', 'xid');
    }

    public function partner()
    {
        return $this->hasOne(BiPartner::class, 'xcreatorid', 'xid');
    }
    

}
