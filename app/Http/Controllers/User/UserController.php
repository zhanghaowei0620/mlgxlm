<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        //$code = $request->input('code');
        $code = 'dada4d6a54d6a';
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".env('WX_APP_ID')."&secret=".env('WX_KEY')."&js_code=$code&grant_type=authorization_code";
        $info = file_get_contents($url);
        $arr = json_decode($info,true);
        var_dump($arr);exit;
        $data = [
            'openid'=>arr['openid'],
            'session_key'=>['session_key']
        ];
        if($arr['openid'] && $arr['session_key']){
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

    }
}
