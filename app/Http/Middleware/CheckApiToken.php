<?php

namespace App\Http\Middleware;

use App\Models\Gainer;
use Closure;

class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $gainerObj = Gainer::where('token',$request->input('token'))->first();
        if(is_object($gainerObj)){
            if($gainerObj->ip =="*" || $gainerObj->ip==$request->ip()){
                if($gainerObj->access_path ==""){
                    return $next($request);
                }else{
                    $access_path_array = unserialize($gainerObj->access_path);
                    if(in_array($request->path(), $access_path_array))return $next($request);
                }
            }
        }
        return response()->json(['error'=>'Unauthorised'], 401);

    }
}
