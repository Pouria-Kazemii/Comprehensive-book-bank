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
        $authArray = array();
        if(strpos($authorStr, "؛")){
            $authArray = explode("؛", $authorStr);

            $traslatorArray = array();

            foreach($authArray as $auth){
                if(strpos($auth, "T")){
                    $traslatorArray = explode("T", $authorStr);
                }
            }
            array_push($authArray, $traslatorArray);

            foreach($authArray as &$auth){
                if(strpos($auth, "،")){
                    $authNames = explode("،" , $auth);
                    $auth = $authNames[1]." ".$authNames[0];
                }

                $auth = preg_replace('/[0-9]+/', ' ', $auth);
                $auth = str_replace("به اهتمام", ' ', $auth);
                $auth = str_replace("ترجمه", ' ', $auth);
                $auth = str_replace("تالیف", ' ', $auth);
                $auth = str_replace("نگارش", ' ', $auth);
                $auth = str_replace("مترجم", ' ', $auth);

                $auth = trim($auth);
            }
        }


        return array_unique($authArray);
    }
}
