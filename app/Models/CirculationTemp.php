<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CirculationTemp extends Model
{
    use HasFactory;
    protected $table = 'circulation_temp';
    protected $fillable = ['xid', 'xyear', 'xbook_id', 'xpublisher_id','xauthor_id','xbooks_count','xfirst_edition_books_count','xcirculations_count','xfirst_edition_circulations_count'];
    protected $primaryKey = 'xid';

    public function book()
    {
        return $this->belongsTo(BookirBook::class,'xbook_id','xid');
    }

    public function publisher(){
        return $this->belongsTo(BookirPublisher::class,'xpublisher_id','xid');
    }

    public function creator(){
        return $this->belongsTo(BookirPartner::class,'xid','xid');
    }


}
