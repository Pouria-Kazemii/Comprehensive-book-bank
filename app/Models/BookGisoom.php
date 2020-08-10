<?php

namespace App\Models;

use App\Models\Library\Library;
use Illuminate\Database\Eloquent\Model;

class BookGisoom extends Model
{
    protected $fillable = ['title','nasher','lang','editor','radeD','saleNashr','nobatChap','tiraj', 'tedadSafe', 'ghateChap', 'shabak10', 'shabak13','recordNumber','tarjome','desc','image','price','catText'];
    protected $table = 'bookgisoom';

    public function authors() {
        return $this->belongsToMany(Author::class);
    }
}
