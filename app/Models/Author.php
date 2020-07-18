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
        $authArray = explode("؛", $authorStr);
        foreach($authArray as &$auth){
            if(strpos($auth, "،")){
                $authNames = explode("،" , $auth);
                $auth = $authNames[1]." ".$authNames[0];
            }
        }
        return array_unique($authArray);
    }
}
