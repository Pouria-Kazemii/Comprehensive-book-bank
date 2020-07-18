<?php

namespace App\Models;

use App\Models\Library\Library;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Author extends Model
{
    protected $fillable = ['f_name','l_name','d_name', 'country'];

    static protected $specialChars = array("؛", ",", "T", ";", "-", "،");
    static protected $ignorWords = array("به اهتمام", "ترجمه", "تالیف", "نگارش", "مترجم");

    public function setAllAttribute($value)
    {
        $this->attributes['all'] = json_encode($value);
    }
    public function books()
    {
        return $this->belongsToMany(BOOK::class);
    }
    static public function authorSeprator($authorStr){

        // initial filter
        $authorStr = preg_replace('/[0-9]+/', ' ', $authorStr);
        foreach(self::$ignorWords as $ignor){
            $authorStr = str_replace($ignor, ' ', $authorStr);
        }

        $authArray = array();
        $authArray = Author::specialCharFilterArray($authorStr, $authArray);
        $authArray = Author::specialCharCleanerArray($authArray);

        return array_unique($authArray);
    }
    static public function specialCharFilterArray($Str, $existArray){

            $tempArray = array();
            $temp2Array = array();
            foreach(self::$specialChars  as $key => $char){
                if(strpos($Str, $char)){
                    $tempArray = explode($char, $Str);
                    foreach($tempArray as $temp){
                       $temp2Array =  Author::specialCharFilterArray($temp, $tempArray);
                       $existArray = array_merge($existArray, $temp2Array);
                    }
                }
            }
            return array_merge($existArray,$tempArray);
    }
    static public function specialCharCleanerArray($dirtyArray){
        foreach($dirtyArray  as  $key=> &$dirty){

            if(strpos($dirty, "،")){
                $authNames = explode("،" , $dirty);
                $dirty = $authNames[1]." ".$authNames[0];
            }

            $dirty = trim($dirty);
            foreach(self::$specialChars  as  $char){
                if(strpos($dirty, $char)){
                    unset($dirtyArray[$key]);
                    break;
                }
            }

        }
        return $dirtyArray;
    }
}
