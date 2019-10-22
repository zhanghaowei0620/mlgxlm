<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{
    //获取accessToken
    public function accessToken()
    {
        $access = Cache('access');
        if (empty($access)) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('WX_APP_ID')."&secret=".env('WX_KEY')."";
            $info = file_get_contents($url);
            $arrInfo = json_decode($info, true);
            $key = "access";
            $access = $arrInfo['access_token'];
            $time = $arrInfo['expires_in'];

            cache([$key => $access], $time);
        }

        return $access;
    }

    //登录
    public function weChat(Request $request){
        $code = $request->input('code');
        $wx_name = $request->input('wx_name');
        $wx_headimg = $request->input('wx_headimg');
//        $arr['openid'] = "adadad31313";
        //$code = 'dada4d6a54d6a';
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".env('WX_APP_ID')."&secret=".env('WX_KEY')."&js_code=$code&grant_type=authorization_code";
        $info = file_get_contents($url);
        $arr = json_decode($info,true);

        $insertInfo = [
            'wx_name'=>$wx_name,
            'wx_headimg'=>$wx_headimg,
            'openid'=>$arr['openid'],
            'session_key'=>$arr['session_key'],
            'wx_unionid'=>$arr['unionid'],
            'wx_login_time'=>time()
        ];
        //var_dump($arr);exit;
        $insertUserInfo = DB::table('mt_user')->insertGetId($insertInfo);
        if($insertUserInfo){
            $data = [
                'openid'=>arr['openid'],
                'session_key'=>['session_key']
            ];
            if($arr['openid'] && $arr['session_key']){
                $key = "openid";
                Redis::set($key,$arr['openid']);
//                $openid = Redis::get($key);
//                var_dump($openid);exit;
                $response = [
                    'error'=>'0',
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $response = [
                    'error'=>'1',
                    'msg'=>'无效的code'
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response = [
                'error'=>'2',
                'msg'=>'微信授权失败，请检查你的网络'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }
}
