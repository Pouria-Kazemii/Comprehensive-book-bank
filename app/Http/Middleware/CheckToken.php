<?php

namespace App\Http\Middleware;
namespace App\Models\Gainer;

use Closure;

class CheckToken
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
        echo "----".$request->ip()." + ".$request->input('token')." + ".$request->path();
        $gainer = Gainer::where('token',$request->input('token'))->first();
        if(is_object($gainer)){
            if($gainer->ip =="*" || $gainer->ip==$request->ip()){
                if($gainer->access_path ==""){
                    return $next($request);
                }else{
                    $access_path_array = unserialize($gainer->access_path);
                    if(in_array($request->path(), $access_path_array))return $next($request);
                }
            }
        }
        return response()->json(['error'=>'Unauthorised'], 401);

    }
}
