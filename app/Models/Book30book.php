<?php

namespace App\Models;

use App\Models\Library\Library;
use Illuminate\Database\Eloquent\Model;

class Book30book extends Model
{
    protected $fillable = ['title','nasher','lang','cats','saleNashr','nobatChap', 'recordNumber','tedadSafe', 'ghateChap', 'jeld', 'shabak', 'vazn','catPath','tarjome','desc','image','price', 'saveBook','mongo_id'];
    protected $table = 'book30book';

    public function authors() {
        return $this->belongsToMany(Author::class);
    }
}
