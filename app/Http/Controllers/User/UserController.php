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
        $userinfo = $request->input('userinfo');
        //var_dump(json_decode($userinfo));exit;
        $userinfo = json_decode($userinfo);
        $wx_name = $userinfo->userInfo->nickName;
        $wx_headimg = $userinfo->userInfo->avatarUrl;

        //$code = 'dada4d6a54d6a';
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".env('WX_APP_ID')."&secret=".env('WX_KEY')."&js_code=$code&grant_type=authorization_code";
        $info = file_get_contents($url);
        $arr = json_decode($info,true);
        //var_dump($arr['unionid']);exit;
        $mt_userInfo = DB::table('mt_user')->where('openid',$arr['openid'])->get();
        if($mt_userInfo){
            $update = [
                'wx_login_time'=>time()
            ];
            $updateInfo = DB::table('mt_user')->where('openid',$arr['openid'])->update($update);
            if($updateInfo){
                $data = [
                    'openid'=>$arr['openid'],
                    'session_key'=>$arr['session_key']
                ];
                if($arr['openid'] && $arr['session_key']){
                    $key = "openid";
                    Redis::set($key,$arr['openid']);
//                $openid = Redis::get($key);
//                var_dump($openid);exit;
                    $response = [
                        'code'=>'0',
                        'msg'=>'登录成功',
                        'data'=>$data
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    $response = [
                        'code'=>'2',
                        'msg'=>'微信授权失败，请检查你的网络'
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }else{
                $response = [
                    'code'=>'1',
                    'msg'=>'登陆失败'
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $insertInfo = [
                'wx_name'=>$wx_name,
                'wx_headimg'=>$wx_headimg,
                'openid'=>$arr['openid'],
                'session_key'=>$arr['session_key'],
                //'wx_unionid'=>$arr['unionid'],
                'wx_login_time'=>time()
            ];
            //var_dump($arr);exit;
            $insertUserInfo = DB::table('mt_user')->insertGetId($insertInfo);
            if($insertUserInfo){
                $data = [
                    'openid'=>$arr['openid'],
                    'session_key'=>$arr['session_key']
                ];
                if($arr['openid'] && $arr['session_key']){
                    $key = "openid";
                    Redis::set($key,$arr['openid']);
//                $openid = Redis::get($key);
//                var_dump($openid);exit;
                    $response = [
                        'code'=>'0',
                        'msg'=>'登录成功',
                        'data'=>$data
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    $response = [
                        'code'=>'1',
                        'msg'=>'无效的code'
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }else{
                $response = [
                    'code'=>'2',
                    'msg'=>'微信授权失败，请检查你的网络'
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }


    }

    //用户地址添加
    public function user_Address(Request $request){
        $address = $request->input('address');
        $address_detail = $request->input('address_detail');
        $tel = $request->input('tel');
        $postal = $request->input('postal');
        $is_default = $request->input('is_default');

        $openid = Redis::get('openid');
        $userInfo = DB::table('mt_user')->where('openid',$openid)->first();
        if($userInfo){
            $uid = $userInfo->uid;
            //var_dump($uid);
            $data = [
                'uid'=>$uid,
                'address'=>$address,
                'address_detail'=>$address_detail,
                'tel'=>$tel,
                'postal'=>$postal,
                'is_default'=>$is_default
            ];
            $add_address = DB::table('mt_address')->insertGetId($data);
            if($address_detail){
                $data = [
                    'code'=>0,
                    'msg'=>'地址添加成功'
                ];
                $response = [
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $data = [
                    'code'=>1,
                    'msg'=>'地址添加失败'
                ];
                $response = [
                    'data'=>$data
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }

    }

    //用户地址列表
    public function user_Address_list(Request $request){
        $openid = Redis::get('openid');
        $user_addressInfo = DB::table('mt_user')
            ->join('mt_address','mt_user.uid','=','mt_address.uid')
            ->where('mt_user.openid',$openid)
            ->get()->toArray();
        if($user_addressInfo){
            $data = [
                'code'=>0,
                'user_addressInfo'=>$user_addressInfo
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data = [
                'code'=>1,
                'msg'=>'暂未添加收货地址'
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }

    //修改地址信息
    public function update_address(Request $request){
        $id = $request->input('address_id');
        //$id = 1;
        $address = $request->input('address');
        $address_detail = $request->input('address_detail');
        $tel = $request->input('tel');
        $postal = $request->input('postal');
        $is_default = $request->input('is_default');
        //$is_default = '1';
        if($is_default == 2){
            $update = [
                'address'=>$address,
                'address_detail'=>$address_detail,
                'tel'=>$tel,
                'postal'=>$postal,
                'is_default'=>$is_default
            ];
            $update_address = DB::table('mt_address')->where('id',$id)->update($update);
            //var_dump($update_address);exit;
            if($update_address==true){
                $data = [
                    'code'=>0,
                    'msg'=>'修改成功'
                ];
                $response = [
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $data = [
                    'code'=>1,
                    'msg'=>'修改失败'
                ];
                $response = [
                    'data'=>$data
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $update = [
                'is_default'=>2
            ];
            $update_address_default = DB::table('mt_address')->update($update);
            //print_r($update_address_default);exit;
            if($update_address_default == true){
                //echo 1111;exit;
                $update = [
                    'address'=>$address,
                    'address_detail'=>$address_detail,
                    'tel'=>$tel,
                    'postal'=>$postal,
                    'is_default'=>$is_default
                ];
                $update_address = DB::table('mt_address')->where('id',$id)->update($update);
                //var_dump($update_address);exit;
                if($update_address == true){
                    $data = [
                        'code'=>0,
                        'msg'=>'修改成功'
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    $data = [
                        'code'=>1,
                        'msg'=>'修改失败,请重试'
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }else{
                $data = [
                    'code'=>1,
                    'msg'=>'修改失败'
                ];
                $response = [
                    'data'=>$data
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }

    }

    //删除地址信息
    public function delete_address(Request $request){
        $address_id = $request->input('id');
        //$address_id = 3;
        $delete_address = DB::table('mt_address')->where('id',$address_id)->delete();
        //var_dump($delete_address);exit;
        if($delete_address == true){
            $data = [
                'code'=>0,
                'msg'=>'删除成功'
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data = [
                'code'=>1,
                'msg'=>'修改失败'
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //用户中心
    public function  user_center(Request $request){
        $openid = Redis::get('openid');
        if($openid){
            $userInfo = DB::table('mt_user')
                ->where('mt_user.openid',$openid)->get();
            //var_dump($userInfo);
            $coupon_num = DB::table('mt_user')
                ->join('mt_coupon','mt_user.uid','=','mt_coupon.uid')
                ->where('mt_user.openid',$openid)->get()->count();
            //var_dump($coupon_num);exit;
            if($userInfo){
                if($coupon_num){
                    $data = [
                        'userInfo'=>$userInfo,
                        'coupon_num'=>$coupon_num,
                        'code'=>0
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    $data = [
                        'code'=>3,
                        'msg'=>'暂无优惠券,快去领取吧'
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }else{
                $data = [
                    'code'=>1,
                    'msg'=>'信息出错'
                ];
                $response = [
                    'data'=>$data
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $data = [
                'code'=>2,
                'msg'=>'请先登录'
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }

    //用户名修改
    public function user_update(Request $request){
        $uid = $request->input('uid');
        $wx_name = $request->input('u_name');
        $openid = Redis::get('openid');
        if($openid){
            $update = [
                'wx_name'=>$wx_name
            ];
            $update_userInfo = DB::table('mt_user')->update($update);
            if($update_userInfo==true){
                $data = [
                    'code'=>0,
                    'msg'=>'修改成功'
                ];
                $response = [
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $data = [
                    'code'=>1,
                    'msg'=>'系统出现问题,修改失败 请重试'
                ];
                $response = [
                    'data'=>$data
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $data = [
                'code'=>2,
                'msg'=>请先登录
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //我的足迹
    public function user_history(Request $request){
        $openid = Redis::get('openid');
        //var_dump($openid);exit;
        if($openid){
            $userInfo = DB::table('mt_user')->where('openid',$openid)->first();
            //var_dump($userInfo);exit;
            $uid = $userInfo->uid;
            $historyInfo = DB::table('mt_history')
                ->join('mt_goods','mt_history.goods_id','=','mt_goods.goods_id')
                ->where('uid',$uid)->get();
            //var_dump($historyInfo);exit;
            if($historyInfo){
                $data = [
                    'code'=>0,
                    'historyInfo'=>$historyInfo
                ];
                $response = [
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $data = [
                    'code'=>1,
                    'msg'=>你暂时未浏览过任何商品
                ];
                $response = [
                    'data'=>$data
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $data = [
                'code'=>2,
                'msg'=>请先登录
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }

    //点击获取主营项目
    public function shop_type(Request $request){
        $shop_type = DB::table('mt_type')->where(['p_id'=>0])->get()->toArray();
        //var_dump($shop_type);exit;
        $data = [
            'code'=>0,
            'shop_type'=>$shop_type
        ];
        $response = [
            'data'=>$data
        ];
        return json_encode($response,JSON_UNESCAPED_UNICODE);
    }

    //商家入驻
    public function shop_settled(Request $request){
        //var_dump(time()+86400);exit;
        $openid = Redis::get('openid');
//        var_dump($openid);exit;
        if($openid){
            $userInfo = DB::table('mt_user')->where('openid',$openid)->first();
            //var_dump($userInfo);exit;
            $uid = $userInfo->uid;
            $shop_name = $request->input('shop_name');
            $shop_type = $request->input('shop_type');
            $shop_desc = $request->input('shop_desc');
            $contacts = $request->input('shop_contacts');
            $shop_phone = $request->input('shop_phone');
            $shop_area = $request->input('shop_area');
            $shop_area = explode(',',$shop_area);
            $shop_provice = $shop_area[0];
            $shop_city = $shop_area[1];
            $shop_area1 = $shop_area[2];
            $shop_address_detail = $request->input('shop_address_detail');
            $shop_add_time = time();

            $data = [
                'shop_name'=>$shop_name,
                'shop_desc'=>$shop_desc,
                't_id'=>$shop_type,
                'shop_phone'=>$shop_phone,
                'shop_address_provice'=>$shop_provice,
                'shop_address_city'=>$shop_city,
                'shop_address_area'=>$shop_area1,
                'shop_address_detail'=>$shop_address_detail,
                'shop_add_time'=>$shop_add_time,
                'shop_contacts'=>$contacts,
                'shop_status'=>2,
                'uid'=>$uid
            ];
            $where = [
                'shop_name'=>$shop_name,
                'shop_contacts'=>$contacts,
                'shop_phone'=>$shop_phone,
                'uid'=>$uid
            ];
            $shopInfo = DB::table('mt_shop')->where($where)->get()->toArray();
//            var_dump($shopInfo);exit;
            if($shopInfo){
                $data = [
                    'code'=>'1',
                    'msg'=>'您已在本平台申请过商铺'
                ];
                $response = [
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $settled = DB::table('mt_shop')->insertGetId($data);
                if($settled==true){
                    $data = [
                        'code'=>'0',
                        'msg'=>'申请成功，请耐心等待审核'
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }
            }
        }else{
            $data = [
                'code'=>'2',
                'msg'=>'请先登录'
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }

    //个人中心优惠券
    public function user_coupon(Request $request){
        echo date('Y-m-d H:i:s',time());exit;
        var_dump(time());exit;
        //$is_use=1;
        $openid = Redis::get('openid');
        if($openid){
            $userInfo = DB::table('mt_user')->where('openid',$openid)->first();
            //var_dump($userInfo);exit;
            $uid = $userInfo->uid;

            $get = [
                'mt_coupon.coupon_id',
                'mt_coupon.shop_id',
                'mt_coupon.goods_id',
                'mt_coupon.coupon_price',
                'mt_coupon.coupon_redouction',
                'mt_coupon.is_use',
                'mt_coupon.create_time',
                'mt_coupon.expiration',
                'mt_goods.goods_name',
                'mt_shop.shop_name'
            ];
            $where = [
                'mt_coupon.uid'=>$uid,
                'is_use'=>0
            ];
            $coupon = DB::table('mt_coupon')
                ->join('mt_goods','mt_coupon.goods_id','=','mt_goods.goods_id')
                ->join('mt_shop','mt_coupon.shop_id','=','mt_shop.shop_id')
                ->where($where)
                ->get($get)->toArray();
            $where = [
                'mt_coupon.uid'=>$uid,
                'is_use'=>1
            ];
            $coupon1 = DB::table('mt_coupon')
                ->join('mt_goods','mt_coupon.goods_id','=','mt_goods.goods_id')
                ->join('mt_shop','mt_coupon.shop_id','=','mt_shop.shop_id')
                ->where($where)
                ->get($get)->toArray();

            $where = [
                'mt_coupon.uid'=>$uid,
                'is_use'=>2
            ];
            $coupon2 = DB::table('mt_coupon')
                ->join('mt_goods','mt_coupon.goods_id','=','mt_goods.goods_id')
                ->join('mt_shop','mt_coupon.shop_id','=','mt_shop.shop_id')
                ->where($where)
                ->get($get)->toArray();
            $data = [
                'code'=>0,
                'coupon'=>$coupon,
                'coupon1'=>$coupon1,
                'coupon2'=>$coupon2
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);


        }else{
            $data = [
                'code'=>2,
                'msg'=>'请先登录'
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

}