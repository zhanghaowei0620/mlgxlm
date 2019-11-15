<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class CheckLogin
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
        $keylist = "H:user_login";
        $token = Redis::get($keylist);
        //var_dump($token);exit;
        if(empty($token)){
            $response = [
                'error'=>1,
                'msg'=>'亲,请先登录'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
        //var_dump();die;
        return $next($request);
    }
}
