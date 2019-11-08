<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;

class UserController extends Controller
{
    //获取accessToken
    public function accessToken()
    {
        $access = Cache('access');
        if (empty($access)) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . env('WX_APP_ID') . "&secret=" . env('WX_KEY') . "";
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
    public function weChat(Request $request)
    {
        $code = $request->input('code');
        $userinfo = $request->input('userinfo');
        //var_dump(json_decode($userinfo));exit;
        $userinfo = json_decode($userinfo);
        $wx_name = $userinfo->userInfo->nickName;
        $wx_headimg = $userinfo->userInfo->avatarUrl;

        //$code = 'dada4d6a54d6a';
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . env('WX_APP_ID') . "&secret=" . env('WX_KEY') . "&js_code=$code&grant_type=authorization_code";
        $info = file_get_contents($url);
        $arr = json_decode($info, true);
        //var_dump($arr['unionid']);exit;
        $mt_userInfo = DB::table('mt_user')->where('openid', $arr['openid'])->get()->toArray();
        if ($mt_userInfo) {
            $update = [
                'wx_login_time' => time()
            ];
            $updateInfo = DB::table('mt_user')->where('openid', $arr['openid'])->update($update);
            if ($updateInfo) {
                $data = [
                    'openid' => $arr['openid'],
                    'session_key' => $arr['session_key']
                ];
                if ($arr['openid'] && $arr['session_key']) {
                    $key = "openid";
                    Redis::set($key, $arr['openid']);
//                $openid = Redis::get($key);
//                var_dump($openid);exit;
                    $response = [
                        'code' => '0',
                        'msg' => '登录成功',
                        'data' => $data
                    ];
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                } else {
                    $response = [
                        'code' => '2',
                        'msg' => '微信授权失败，请检查你的网络'
                    ];
                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                }
            } else {
                $response = [
                    'code' => '1',
                    'msg' => '登陆失败'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        } else {
            $insertInfo = [
                'wx_name' => $wx_name,
                'wx_headimg' => $wx_headimg,
                'openid' => $arr['openid'],
                'session_key' => $arr['session_key'],
                //'wx_unionid'=>$arr['unionid'],
                'wx_login_time' => time()
            ];
            //var_dump($arr);exit;
            $insertUserInfo = DB::table('mt_user')->insertGetId($insertInfo);
            if ($insertUserInfo) {
                $data = [
                    'openid' => $arr['openid'],
                    'session_key' => $arr['session_key']
                ];
                if ($arr['openid'] && $arr['session_key']) {
                    $key = "openid";
                    Redis::set($key, $arr['openid']);
//                $openid = Redis::get($key);
//                var_dump($openid);exit;
                    $response = [
                        'code' => '0',
                        'msg' => '登录成功',
                        'data' => $data
                    ];
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                } else {
                    $response = [
                        'code' => '1',
                        'msg' => '无效的code'
                    ];
                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                }
            } else {
                $response = [
                    'code' => '2',
                    'msg' => '微信授权失败，请检查你的网络'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }


    }

    //用户地址添加
    public function user_Address(Request $request)
    {
        $address_area = $request->input('address_area');
        // $address_area = '山西省,太原市,小店区';
        $address_area = explode(',', $address_area);
        //var_dump($address_area);die;
        $address_provice = $address_area[0];
        // var_dump($address_provice);die;
        $address_city = $address_area[1];
        $address_area1 = $address_area[2];
        $address_detail = $request->input('address_detail');
        $tel = $request->input('tel');
        $postal = $request->input('postal');
        $is_default = $request->input('is_default');

        // $openid = Redis::set('openid','o9VUc5HEPNrYq5d5iQFygPVbX7EM');
        $openid = Redis::get('openid');
        $userInfo = DB::table('mt_user')->where('openid', $openid)->first();
        //var_dump($userInfo);die;
        if ($userInfo) {
            $uid = $userInfo->uid;
            //var_dump($uid);
            $data = [
                'uid' => $uid,
                'address_provice' => $address_provice,
                'address_city' => $address_city,
                'address_area' => $address_area1,
                'address_detail' => $address_detail,
                'tel' => $tel,
//                'postal' => $postal,
                'is_default' => $is_default
            ];
            $add_address = DB::table('mt_address')->insertGetId($data);
            if ($add_address) {
                $data = [
                    'code' => 0,
                    'msg' => '地址添加成功'
                ];
                $response = [
                    'data' => $data
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $data = [
                    'code' => 1,
                    'msg' => '地址添加失败'
                ];
                $response = [
                    'data' => $data
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }

    }

    //用户地址列表
    public function user_Address_list(Request $request)
    {
        // $openid = Redis::set('openid','o9VUc5HEPNrYq5d5iQFygPVbX7EM');
        $openid = Redis::get('openid');
        $user_addressInfo = DB::table('mt_user')
            ->join('mt_address', 'mt_user.uid', '=', 'mt_address.uid')
            ->where('mt_user.openid', $openid)
            ->get()->toArray();
        var_dump($user_addressInfo);die;
        if ($user_addressInfo) {
            $data = [
                'code' => 0,
                'user_addressInfo' => $user_addressInfo
            ];
            $response = [
                'data' => $data
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } else {
            $data = [
                'code' => 1,
                'msg' => '暂未添加收货地址'
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    //修改地址信息
    public function update_address(Request $request)
    {
        $id = $request->input('address_id');
        //$id = 1;
        $address_area = $request->input('address_area');
        $address_area = explode(',', $address_area);
        $address_provice = $address_area[0];
        $address_city = $address_area[1];
        $address_area1 = $address_area[2];
        $address_detail = $request->input('address_detail');
        $tel = $request->input('tel');
//        $postal = $request->input('postal');
        $is_default = $request->input('is_default');
        //$is_default = '1';
        if ($is_default == 2) {
            $update = [
                'address_provice' => $address_provice,
                'address_city' => $address_city,
                'address_area' => $address_area1,
                'address_detail' => $address_detail,
                'tel' => $tel,
//                'postal' => $postal,
                'is_default' => $is_default
            ];
            $update_address = DB::table('mt_address')->where('id', $id)->update($update);
            //var_dump($update_address);exit;
            if ($update_address == true) {
                $data = [
                    'code' => 0,
                    'msg' => '修改成功'
                ];
                $response = [
                    'data' => $data
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $data = [
                    'code' => 1,
                    'msg' => '修改失败'
                ];
                $response = [
                    'data' => $data
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        } else {
            $update = [
                'is_default' => 2
            ];
            $update_address_default = DB::table('mt_address')->update($update);
            //print_r($update_address_default);exit;
            if ($update_address_default == true) {
                //echo 1111;exit;
                $update = [
                    'address_provice' => $address_provice,
                    'address_city' => $address_city,
                    'address_area' => $address_area1,
                    'address_detail' => $address_detail,
                    'tel' => $tel,
                    'postal' => $postal,
                    'is_default' => $is_default
                ];
                $update_address = DB::table('mt_address')->where('id', $id)->update($update);
                //var_dump($update_address);exit;
                if ($update_address == true) {
                    $data = [
                        'code' => 0,
                        'msg' => '修改成功'
                    ];
                    $response = [
                        'data' => $data
                    ];
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                } else {
                    $data = [
                        'code' => 1,
                        'msg' => '修改失败,请重试'
                    ];
                    $response = [
                        'data' => $data
                    ];
                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                }
            } else {
                $data = [
                    'code' => 1,
                    'msg' => '修改失败'
                ];
                $response = [
                    'data' => $data
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }

    }

    //删除地址信息
    public function delete_address(Request $request)
    {
        $address_id = $request->input('id');
        //$address_id = 3;
        $delete_address = DB::table('mt_address')->where('id', $address_id)->delete();
        //var_dump($delete_address);exit;
        if ($delete_address == true) {
            $data = [
                'code' => 0,
                'msg' => '删除成功'
            ];
            $response = [
                'data' => $data
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } else {
            $data = [
                'code' => 1,
                'msg' => '修改失败'
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //用户中心
    public function user_center(Request $request)
    {
        $openid = Redis::get('openid');
        if ($openid) {
            $userInfo = DB::table('mt_user')
                ->where('mt_user.openid', $openid)->get()->toArray();
            //var_dump($userInfo);
            if($userInfo) {
                $coupon_num = DB::table('mt_user')
                    ->join('mt_coupon', 'mt_user.uid', '=', 'mt_coupon.uid')
                    ->where('mt_user.openid', $openid)->get()->count();
                //var_dump($coupon_num);exit;
                if ($coupon_num) {
                    $data = [
                        'userInfo' => $userInfo,
                        'coupon_num' => $coupon_num,
                        'code' => 0
                    ];
                    $response = [
                        'data' => $data
                    ];
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            } else {
                $data = [
                    'code' => 1,
                    'msg' => '信息出错'
                ];
                $response = [
                    'data' => $data
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        } else {
            $data = [
                'code' => 2,
                'msg' => '请先登录'
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    //用户名修改
    public function user_update(Request $request)
    {
        $uid = $request->input('uid');
        $wx_name = $request->input('u_name');
        $openid = Redis::get('openid');
        if ($openid) {
            $update = [
                'wx_name' => $wx_name
            ];
            $update_userInfo = DB::table('mt_user')->update($update);
            if ($update_userInfo == true) {
                $data = [
                    'code' => 0,
                    'msg' => '修改成功'
                ];
                $response = [
                    'data' => $data
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $data = [
                    'code' => 1,
                    'msg' => '系统出现问题,修改失败 请重试'
                ];
                $response = [
                    'data' => $data
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        } else {
            $data = [
                'code' => 2,
                'msg' => 请先登录
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //我的足迹
    public function user_history(Request $request)
    {
        $openid = Redis::get('openid');
        //var_dump($openid);exit;
        if ($openid) {
            $userInfo = DB::table('mt_user')->where('openid', $openid)->first();
            //var_dump($userInfo);exit;
            $uid = $userInfo->uid;
            $historyInfo = DB::table('mt_history')
                ->join('mt_goods', 'mt_history.goods_id', '=', 'mt_goods.goods_id')
                ->where('uid', $uid)->get();
            //var_dump($historyInfo);exit;
            if ($historyInfo) {
                $data = [
                    'code' => 0,
                    'historyInfo' => $historyInfo
                ];
                $response = [
                    'data' => $data
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $data = [
                    'code' => 1,
                    'msg' => 你暂时未浏览过任何商品
                ];
                $response = [
                    'data' => $data
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        } else {
            $data = [
                'code' => 2,
                'msg' => 请先登录
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    //主营项目
    public function shop_type(){
        $info = DB::table('mt_type')->get();
        $result = $this->list_level($info,$pid=0,$level=0);
        //var_dump($result);
        $data = [
            'code' => 0,
            'shop_type' => $result
        ];
        $response = [
            'data' => $data
        ];
        return json_encode($response, JSON_UNESCAPED_UNICODE);

    }
    public function list_level($data,$pid,$level){

        static $array = array();

        foreach ($data as $k => $v) {

            if($pid == $v->p_id){

                $v->level = $level;

                $array[] = $v;

                self::list_level($data,$v->t_id,$level+1);
            }
        }
        return $array;
    }


    //商家入驻
    public function shop_settled(Request $request)
    {
        //var_dump(time()+86400);exit;
        $openid = Redis::get('openid');
//        var_dump($openid);exit;
        if ($openid) {
            $userInfo = DB::table('mt_user')->where('openid', $openid)->first();
            //var_dump($userInfo);exit;
            $uid = $userInfo->uid;
            $shop_name = $request->input('shop_name');
            $shop_type = $request->input('shop_type');
            $shop_desc = $request->input('shop_desc');
            $contacts = $request->input('shop_contacts');
            $shop_phone = $request->input('shop_phone');
            $shop_area = $request->input('shop_area');
            $shop_area = explode(',', $shop_area);
            $shop_provice = $shop_area[0];
            $shop_city = $shop_area[1];
            $shop_area1 = $shop_area[2];
            $shop_address_detail = $request->input('shop_address_detail');
            $shop_add_time = time();

            $data = [
                'shop_name' => $shop_name,
                'shop_desc' => $shop_desc,
                't_id' => $shop_type,
                'shop_phone' => $shop_phone,
                'shop_address_provice' => $shop_provice,
                'shop_address_city' => $shop_city,
                'shop_address_area' => $shop_area1,
                'shop_address_detail' => $shop_address_detail,
                'shop_add_time' => $shop_add_time,
                'shop_contacts' => $contacts,
                'shop_status' => 2,
                'uid' => $uid
            ];
            $where = [
                'shop_name' => $shop_name,
                'shop_contacts' => $contacts,
                'shop_phone' => $shop_phone,
                'uid' => $uid
            ];
            $shopInfo = DB::table('mt_shop')->where($where)->get()->toArray();
//            var_dump($shopInfo);exit;
            if ($shopInfo) {
                $data = [
                    'code' => '1',
                    'msg' => '您已在本平台申请过商铺'
                ];
                $response = [
                    'data' => $data
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $settled = DB::table('mt_shop')->insertGetId($data);
                if ($settled == true) {
                    $data = [
                        'code' => '0',
                        'msg' => '申请成功，请耐心等待审核'
                    ];
                    $response = [
                        'data' => $data
                    ];
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            }
        } else {
            $data = [
                'code' => '2',
                'msg' => '请先登录'
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    //个人中心优惠券
    public function user_coupon(Request $request)
    {
        $openid = Redis::get('openid');
        if ($openid) {
            $userInfo = DB::table('mt_user')->where('openid', $openid)->first();
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
                'mt_coupon.uid' => $uid,
                'is_use' => 0
            ];
            $coupon = DB::table('mt_coupon')
                ->join('mt_goods', 'mt_coupon.goods_id', '=', 'mt_goods.goods_id')
                ->join('mt_shop', 'mt_coupon.shop_id', '=', 'mt_shop.shop_id')
                ->where($where)
                ->get($get)->toArray();
            $where = [
                'mt_coupon.uid' => $uid,
                'is_use' => 1
            ];
            $coupon1 = DB::table('mt_coupon')
                ->join('mt_goods', 'mt_coupon.goods_id', '=', 'mt_goods.goods_id')
                ->join('mt_shop', 'mt_coupon.shop_id', '=', 'mt_shop.shop_id')
                ->where($where)
                ->get($get)->toArray();

            $where = [
                'mt_coupon.uid' => $uid,
                'is_use' => 2
            ];
            $coupon2 = DB::table('mt_coupon')
                ->join('mt_goods', 'mt_coupon.goods_id', '=', 'mt_goods.goods_id')
                ->join('mt_shop', 'mt_coupon.shop_id', '=', 'mt_shop.shop_id')
                ->where($where)
                ->get($get)->toArray();
            $data = [
                'code' => 0,
                'coupon' => $coupon,
                'coupon1' => $coupon1,
                'coupon2' => $coupon2
            ];
            $response = [
                'data' => $data
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);


        } else {
            $data = [
                'code' => 2,
                'msg' => '请先登录'
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //根据图片获取银行卡
    public function bankcard(Request $request){
        $token = $this->accessToken();
        $destination = './images/';
        $file = $_FILES['file']; // 获取上传的图片
        $filename = $file['name'];
        $filesize = $file['size'];
        $filetype = $file['type'];
        $test   = move_uploaded_file($file['tmp_name'], $destination . iconv("UTF-8", "gb2312", $filename));
        if($test == true){
            $img_url = 'http://mt.mlgxlm.com/images/'.$filename;
            $url = "https://api.weixin.qq.com/cv/ocr/bankcard?type=MODE&img_url=$img_url&access_token=$token";
            $objurl = new Client();
            $response = $objurl->request('POST',$url);
            $res_str = $response->getBody();
            //var_dump($res_str);
            return $res_str;
        }
    }

    //添加到银行卡包
    public function add_bankcard(Request $request){
        $bankcard_name = $request->input('bankcard_name');
        $bankcard_num = $request->input('bankcard_num');
        $bankcard_type = $request->input('bankcard_type');
        $bank = $request->input('bank');
        $openid = Redis::get('openid');
        if($openid){
            $userInfo = DB::table('mt_user')->where('openid', $openid)->first();
            //var_dump($userInfo);exit;
            $uid = $userInfo->uid;
            $insert = [
                'bankcard_name'=>$bankcard_name,
                'bankcard_num'=>$bankcard_num,
                'bankcard_type'=>$bankcard_type,
                'bank'=>$bank,
                'uid'=>$uid
            ];
            $where = [
                'bankcard_num' => $bankcard_num
            ];
            $bankInfo = DB::table('mt_bankcard')->where($where)->get();
            if($bankInfo){
                $data = [
                    'code' => '1',
                    'msg' => '此卡已存在你的卡包中'
                ];
                $response = [
                    'data' => $data
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }else{
                $bankInsert = DB::table('mt_bankcard')->insertGetId($insert);
                if($bankInsert == true){
                    $data = [
                        'code' => '0',
                        'msg' => '添加成功'
                    ];
                    $response = [
                        'data' => $data
                    ];
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }else{
                    $data = [
                        'code' => '3',
                        'msg' => '添加失败'
                    ];
                    $response = [
                        'data' => $data
                    ];
                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                }
            }

        }else{
            $data = [
                'code' => 2,
                'msg' => '请先登录'
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }



    }

    //签到
    public function user_sign(Request $request){
        $timestr = time();
        $now_day = date('w',$timestr);
        //获取一周的第一天，注意第一天应该是星期一
        $sunday_str = $timestr;
        $sunday = date('Y-m-d', $sunday_str);
        //获取一周的最后一天，注意最后一天是星期六
        $strday_str = $timestr + (7-$now_day)*60*60*24;
        $strday = date('Y-m-d', $strday_str);
//        echo "星期一： $sunday\n";echo "</br>";
//        echo "星期天： $strday\n";echo "</br>";
        //die;

        $weekarray=["星期日","星期一","星期二","星期三","星期四","星期五","星期六"];
         //var_dump($weekarray[date("w",strtotime("2019-11-4"))]);
        //echo $weekarray[date("w",time())];echo "</br>";
        if($weekarray[date("w",time())] == '星期六' || $weekarray[date("w",time())] == '星期天'){
            $integral = 2;
        }else{
            $integral = 1;
        }
        $openid = Redis::get('openid');
        //var_dump($openid);
        if($openid){
            $userInfo = DB::table('mt_user')->where('openid', $openid)->first();
            //var_dump($userInfo);exit;
            $uid = $userInfo->uid;
            $user_signInfo = DB::table('mt_user_sign')->where('uid',$uid)->first();
            //var_dump($user_signInfo);exit;
            if($user_signInfo == NULL){
                $insert = [
                    'uid'=>$uid,
                    'first_sign_time'=>time(),
                    'sign_time'=>time(),
                    'integral'=>$integral,
                    'sign_num'=>$user_signInfo->sign_num+1
                ];
                $sign = DB::table('mt_user_sign')->insertGetId($insert);
                if($sign == true){
                    $data = [
                        'code'=>0,
                        'msg'=>'签到成功'
                    ];
                    $response = [
                        'data' => $data
                    ];
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }else{
                    $data = [
                        'code'=>0,
                        'msg'=>'签到成功'
                    ];
                    $response = [
                        'data' => $data
                    ];
                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                }
            }else{
                //php获取今日开始时间戳和结束时间戳
                $today_start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                $today_end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
                //php获取昨日起始时间戳和结束时间戳
                $yesterday_start = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
                $yesterday_end = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;

                $id_select = Db::table('mt_user_sign')
                    ->where('uid', '=', $uid)
                    ->where('sign_time', '>=', $yesterday_start)
                    ->where('sign_time', '<=', $yesterday_end)
                    ->first();//判断昨天是否已签到过
                if($id_select == NULL){
                    $issign = Db::table('mt_user_sign')
                        ->where('uid', '=', $uid)
                        ->where('sign_time', '>=', $today_start)
                        ->where('sign_time', '<=', $today_end)
                        ->first();
                    if($issign != NULL){
                        $data = [
                            'code'=>1,
                            'msg'=>'你今天已经签到过了'
                        ];
                        $response = [
                            'data' => $data
                        ];
                        die(json_encode($response, JSON_UNESCAPED_UNICODE));
                    }else{
                        $update = [
                            'first_sign_time'=>time(),
                            'sign_time'=>time(),
                            'integral'=>$issign->integral+1,
                            'sign_num'=>1
                        ];
                        $updateInfo = DB::table('mt_user_sign')->where('uid',$uid)->update($update);
                        if($updateInfo==true){
                            $data = [
                                'code'=>0,
                                'msg'=>'签到成功'
                            ];
                            $response = [
                                'data' => $data
                            ];
                            return json_encode($response, JSON_UNESCAPED_UNICODE);
                        }else{
                            $data = [
                                'code'=>2,
                                'msg'=>'签到失败，请重试1'
                            ];
                            $response = [
                                'data' => $data
                            ];
                            die(json_encode($response, JSON_UNESCAPED_UNICODE));
                        }
                    }
                }else{
                    $issign = Db::table('mt_user_sign')
                        ->where('uid', '=', $uid)
                        ->where('sign_time', '>=', $today_start)
                        ->where('sign_time', '<=', $today_end)
                        ->first();
                    if($issign != NULL){
                        $data = [
                            'code'=>1,
                            'msg'=>'你今天已经签到过了'
                        ];
                        $response = [
                            'data' => $data
                        ];
                        die(json_encode($response, JSON_UNESCAPED_UNICODE));
                    }else{
                        $update = [
                            'sign_time'=>time(),
                            'integral'=>$issign->integral+1,
                            'sign_num'=>$issign->sign_num+1
                        ];
                        $updateInfo = DB::table('mt_user_sign')->where('uid',$uid)->update($update);
                        if($updateInfo==true){
                            $data = [
                                'code'=>0,
                                'msg'=>'签到成功'
                            ];
                            $response = [
                                'data' => $data
                            ];
                            return json_encode($response, JSON_UNESCAPED_UNICODE);
                        }else{
                            $data = [
                                'code'=>2,
                                'msg'=>'签到失败，请重试2'
                            ];
                            $response = [
                                'data' => $data
                            ];
                            die(json_encode($response, JSON_UNESCAPED_UNICODE));
                        }
                    }
                }
            }
        }else{
            $data = [
                'code'=>2,
                'msg'=>'请先登录'
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }
    
}