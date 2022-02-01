<?php

namespace App\Models\Bookir;

use Illuminate\Database\Eloquent\Model;

class BiBook extends Model
{
    protected $table = 'bookir_book';
    protected $primaryKey = 'xid';
    public $timestamps = false;
    protected $fillable = ['xname','xpagecount','xformat','xcover', 'xprintnumber', 'xcirculation', 'xcovernumber', 'xisbn2', 'xpublishdate', 'xcoverprice', 'xdiocode', 'xlang', 'xpublishplace', 'xdescription', 'xweight', 'ximageurl'];

    public function partners() {
        return $this->belongsToMany(BiPartnerRules::class, 'xid', 'xbookid');
    }

    public function publisher() {
        return $this->hasOne(BiPublisher::class);
    }

    public function subjects() {
        return $this->belongsToMany(BiSubject::class);
    }

}
