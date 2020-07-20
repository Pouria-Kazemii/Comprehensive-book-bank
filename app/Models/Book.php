<?php

namespace App\Models;

use App\Models\Library\Library;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['all','recordNumber','Creator', 'MahalNashr', 'Title', 'mozoe', 'Yaddasht', 'TedadSafhe', 'saleNashr', 'EjazeReserv', 'EjazeAmanat', 'shabak'];
    static protected $shabakSeparator = array(",", " ");

    public function authors() {
        return $this->belongsToMany(Author::class);
    }
    public function setAllAttribute($value)
    {
        $this->attributes['all'] = json_encode($value);
    }
    public function libraries()
    {
        return $this->belongsToMany(Library::class);
    }

    static public function getLastBookRecordNumber()
    {
        $lastBook = Book::orderBy('id', 'desc')->first();
        return (is_null($lastBook))?0:$lastBook->recordNumber;
    }
    static public function getShabakArray($shabakStr){
        $shabakStr = faCharToEN($shabakStr);
        $shabakArray=array();
        $shabakStr = trim(str_replace("-", '', $shabakStr));
        foreach (self::$shabakSeparator as $sep){
            if(mb_strpos($shabakStr, $sep) !== false){
                $shabakArray = explode($sep,$shabakStr);
                //$shabakArray = array_filter($shabakArray, function($var) {$var=(int)(trim(str_replace(" ", '', $var)));return $var;});
                foreach($shabakArray as &$shabak){
                    $shabak = (int)(trim(str_replace(" ", '', $shabak)));
                }
                break;
            }
        }
        if(count($shabakArray)==0)$shabakArray[]=(int)($shabakStr);
        return array_unique($shabakArray);
    }

}
