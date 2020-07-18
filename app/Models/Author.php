<?php

namespace App\Models;

use App\Models\Library\Library;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable = ['f_name','l_name','d_name', 'country'];

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
        $encharsepArray = array();
        $traslatorArray = array();
        $encharsepArray2 = array();
        $traslatorArray2 = array();

        if(strpos($authorStr, "؛") || strpos($authorStr, ";") || strpos($authorStr, "T")){
            $authArray = explode("؛", $authorStr);
            $encharsepArray = explode(";", $authorStr);
            $traslatorArray = explode("T", $authorStr);

            foreach($authArray as &$auth){
                if(strpos($auth, ";")){
                    $encharsepArray2 = explode(";", $auth);
                    unset($auth);
                }
            }
            foreach($authArray as &$auth){
                if(strpos($auth, "T")){
                    $traslatorArray2 = explode("T", $auth);
                    unset($auth);
                }
            }
            $authArray = array_merge($authArray, $traslatorArray, $encharsepArray, $traslatorArray2, $encharsepArray2);

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
}
