<?php

namespace App\Models;

use App\Models\Library\Library;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Author extends Model
{
    protected $fillable = ['f_name','l_name','d_name', 'country'];
    protected $specialChars = array("؛", ",", "T", ";", "-", "،");

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
        $authorStr = str_replace("به اهتمام", ' ', $authorStr);
        $authorStr = str_replace("ترجمه", ' ', $authorStr);
        $authorStr = str_replace("تالیف", ' ', $authorStr);
        $authorStr = str_replace("نگارش", ' ', $authorStr);
        $authorStr = str_replace("مترجم", ' ', $authorStr);

        $authArray = array();
        return Author::specialCharFilterArray($authorStr, $authArray);


        $encharsepArray = array();
        $envcharsepArray = array();
        $traslatorArray = array();
        $encharsepArray2 = array();
        $envcharsepArray2 = array();
        $traslatorArray2 = array();

        if(strpos($authorStr, "؛") || strpos($authorStr, ";") || strpos($authorStr, "T") || strpos($authorStr, ",")){
            $authArray = explode("؛", $authorStr);
            $encharsepArray = explode(";", $authorStr);
            $envcharsepArray = explode(",", $authorStr);
            $traslatorArray = explode("T", $authorStr);


            foreach($authArray as $authKey =>&$auth){
                if(strpos($auth, ";")){
                    $encharsepArray2 = explode(";", $auth);
                    unset($authArray[$authKey]);
                }
                if(strpos($auth, "T")){
                    $traslatorArray2 = explode("T", $auth);
                    unset($authArray[$authKey]);
                }
                if(strpos($auth, ",")){
                    $envcharsepArray2 = explode(",", $auth);
                    unset($authArray[$authKey]);
                }
                $auth.="-A-";
            }

            foreach($traslatorArray as $authKey =>&$auth){
                if(strpos($auth, ";")){
                    unset($traslatorArray[$authKey]);
                }
                if(strpos($auth, "T")){
                    unset($traslatorArray[$authKey]);
                }
                if(strpos($auth, ",")){
                    unset($traslatorArray[$authKey]);
                }
                $auth.="-B-";
            }
            foreach($encharsepArray as $authKey =>&$auth){
                if(strpos($auth, ";")){
                    unset($encharsepArray[$authKey]);
                }
                if(strpos($auth, "T")){
                    unset($encharsepArray[$authKey]);
                }
                if(strpos($auth, ",")){
                    unset($encharsepArray[$authKey]);
                }
                $auth.="-C-";
            }
            foreach($envcharsepArray as $authKey =>&$auth){
                if(strpos($auth, ";")){
                    unset($envcharsepArray[$authKey]);
                }
                if(strpos($auth, "T")){
                    unset($envcharsepArray[$authKey]);
                }
                if(strpos($auth, ",")){
                    unset($envcharsepArray[$authKey]);
                }
                $auth.="-D-";
            }

            foreach($traslatorArray2 as $authKey =>&$auth){
                if(strpos($auth, ";")){
                    unset($traslatorArray2[$authKey]);
                }
                if(strpos($auth, "T")){
                    unset($traslatorArray2[$authKey]);
                }
                if(strpos($auth, ",")){
                    unset($traslatorArray2[$authKey]);
                }
                $auth.="-E-";
            }
            foreach($encharsepArray2 as $authKey =>&$auth){
                if(strpos($auth, ";")){
                    unset($encharsepArray2[$authKey]);
                }
                if(strpos($auth, "T")){
                    unset($encharsepArray2[$authKey]);
                }
                if(strpos($auth, ",")){
                    unset($encharsepArray2[$authKey]);
                }
                $auth.="-F-";
            }
            foreach($envcharsepArray2 as $authKey =>&$auth){
                if(strpos($auth, ";")){
                    unset($envcharsepArray2[$authKey]);
                }
                if(strpos($auth, "T")){
                    unset($envcharsepArray2[$authKey]);
                }
                if(strpos($auth, ",")){
                    unset($envcharsepArray2[$authKey]);
                }
            }

            $authArray = array_merge($authArray, $traslatorArray, $encharsepArray, $traslatorArray2, $encharsepArray2, $envcharsepArray2);

            foreach($authArray as &$auth){
                if(strpos($auth, "،")){
                    $authNames = explode("،" , $auth);
                    $auth = $authNames[1]." ".$authNames[0];
                }

                $auth = trim($auth);
            }
        }


        return array_unique($authArray);
    }
    static public function specialCharFilterArray($Str, $existArray){


            $tempArray = array();
            $temp2Array = array();
            $ntFound = 0 ;
            foreach($this->specialChars as $key => $char){
                if(strpos($Str, $char)){
                    $tempArray = explode($char, $Str);
                    foreach($tempArray as $temp){
                        Log::info('specialCharFilterArray : '.$temp."===".$char);
                       $temp2Array =  Author::specialCharFilterArray($temp, $tempArray);
                       $existArray = array_merge($existArray, $temp2Array);
                    }

                }else{
                    $ntFound ++;
                }
            }
            //if($ntFound == count($specialChars)) $tempArray[]=$Str . "--PLUS STR--";
            return array_merge($existArray,$tempArray);
    }
}
