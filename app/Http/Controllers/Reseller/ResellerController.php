<?php

namespace App\Http\Controllers\Reseller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ResellerController extends Controller
{
    //分销系统
    public function reseller(Request $request)
    {
        $user_id=$request->input('user_id');
//        $ip = $_SERVER['SERVER_ADDR'];
//        $key = 'openid'.$ip;
//        $openid =  Redis::get($key);
        $openid='o9VUc5AOsdEdOBeUAw4TdYg-F-dM';
        $data=DB::table('mt_user')
            ->where(['openid'=>$openid])
            ->update(['p_id'=>1]);
        if($data){
            $data = [
                'code'=>0,
                'msg'=>'恭喜您，成为分销商'
            ];
            $response = [
                'data'=>$data
            ];
            return (json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $data = [
                'code'=>1,
                'msg'=>'请重新点击成为分销商'
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

}
