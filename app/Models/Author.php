<?php

namespace App\Models;

use App\Models\Library\Library;
use Illuminate\Database\Eloquent\Model;


class Author extends Model
{
    protected $table = 'author';
    protected $fillable = ['f_name','l_name','d_name', 'country'];

    static protected $specialChars = array("T", ";", "-");
    static protected $ignorWords = array("به اهتمام", "ترجمه", "تاًلیف" , "نوشته " ,"تالیف", "نگارش", "مترجم");

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
        $authorStr = faCharToEN($authorStr);
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
                if(mb_strpos($Str, $char) !== false){
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
        $oldDirty = "";
        $dirtyArray = array_unique($dirtyArray);
        foreach($dirtyArray  as  $key=> &$dirty){

            // handle family, name string
            if(mb_strpos($dirty, ",")!== false){
                $authNames = explode("," , $dirty);
                $dirty = $authNames[1]." ".$authNames[0];
            }


            // Clean From ignor Char
            $dirty = trim($dirty);
            if($dirty != ""){
                foreach(self::$specialChars  as  $char){
                    if(mb_strpos($dirty, $char) !== false){
                        unset($dirtyArray[$key]);
                        $dirty = "";
                    }
                }
            }else{
                unset($dirtyArray[$key]);
                $dirty = "";
            }

            // handle family name == name family string
            if($oldDirty != "" && $dirty!=""){
                $spaceArrayDirty = explode(" " , $dirty);
                $spaceArrayOldDirty = explode(" " , $oldDirty);
                if(count($spaceArrayDirty) == count($spaceArrayOldDirty)){
                    $dumplicatCounter = 0 ;
                    foreach($spaceArrayDirty as $spacePart){
                        if(in_array($spacePart, $spaceArrayOldDirty))$dumplicatCounter++;
                    }
                    if($dumplicatCounter == count($spaceArrayDirty)){
                        unset($dirtyArray[$key]);
                        $dirty ="";
                    }
                }
            }else{
                $oldDirty = $dirty;
            }
        }
        return $dirtyArray;
    }
}
