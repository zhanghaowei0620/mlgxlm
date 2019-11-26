<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Storage;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

class Admin_loginController extends Controller
{
     /*
     * 后台登录接口
     */
    public function user(Request $request)
    {
        $admin_user=$request->input('admin_user');
        $admin_pwd=$request->input('admin_pwd');
        $where=[
          'admin_user'=>$admin_user
        ];
        $data=DB::table('admin_user')->where($where)->first();
        if($data){
            if(password_verify($admin_pwd,$data->admin_pwd)){
                if($data->admin_judge == 2){
                    $judge=[
                        'admin_judge'=>$data->admin_judge,
                        'shop_id'=>$data->shop_id
                    ];
                    $token = sha1(Str::random(10).md5(time()));
                    $ip = $_SERVER['SERVER_ADDR'];
                    $key = 'H:userlogin_id'.$ip;
                    Redis::set($key,$token);
                    $response=[
                        'code'=>0,
                        'data'=>$judge,
                        'msg'=>'登录成功'
                    ];
                    return (json_encode($response,JSON_UNESCAPED_UNICODE));
                }else{
                    $token = sha1(Str::random(10).md5(time()));
                    $ip = $_SERVER['SERVER_ADDR'];
                    $key = 'H:userlogin_id'.$ip;
                    Redis::set($key,$token);
                    $judge=[
                        'admin_judge'=>$data->admin_judge,
                    ];
                    $response=[
                        'code'=>0,
                        'data'=>$judge,
                        'msg'=>'登录成功'
                    ];
                    return (json_encode($response,JSON_UNESCAPED_UNICODE));
                }

            }else{
                        $response=[
                        'code'=>1,
                        'msg'=>'账号或密码错误'
                    ];
                    return (json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>2,
                'msg'=>'没有此账号'
            ];
            return (json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }
    /*
     * 用户列表
     */
    public function userlist(Request $request)
    {
        $data=DB::table('admin_user')
            ->select(['admin_id','admin_judge','admin_user','shop_status','admin_names','admin_tel','admin_consumption','admin_user_money','admin_user_integral'])      //shop_status 2启用  1拉黑
            ->paginate(7);
//        var_dump($data);exit;
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'您的数据类表请求成功',
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $response=[
                'code'=>1,
                'msg'=>'您的数据类表请求失败',
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //用户管理列表
    public function admin_list(Request $request)
    {
        $data=DB::table('admin_user')
            ->join('mt_shop','admin_user.shop_id','=','mt_shop.shop_id')
            ->select(['admin_user.admin_id','admin_user.admin_judge','admin_user.admin_user','admin_user.shop_status','mt_shop.shop_name','admin_user.shop_id'])      //shop_status 2启用  1拉黑
            ->paginate(7);
//        var_dump($data);exit;
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'您的数据类表请求成功',
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $response=[
                'code'=>1,
                'msg'=>'您的数据类表请求失败',
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //用户拉黑
    public function admin_black(Request $request){
         $admin_id = $request->input('admin_id');
         $shop_status = $request->input('shop_status');
         $shop_id = $request->input('shop_id');
         $update = [
             'shop_status'=>$shop_status
         ];
         $updateInfo = DB::table('admin_user')->where('admin_id',$admin_id)->update($update);
         if($updateInfo > 0){
             DB::table('mt_shop')->where('shop_id',$shop_id)->update($update);
             $response=[
                 'code'=>0,
                 'msg'=>'请求成功'
             ];
             return json_encode($response,JSON_UNESCAPED_UNICODE);
         }else{
             $response=[
                 'code'=>1,
                 'msg'=>'请求失败,请重试'
             ];
             die(json_encode($response,JSON_UNESCAPED_UNICODE));
         }
    }

    //用户删除
    public function admin_delete(Request $request){
        $admin_id = $request->input('admin_id');
        $shop_id = $request->input('shop_id');
        $deleteInfo = DB::table('admin_user')->where('admin_id',$admin_id)->delete();
        //var_dump($deleteInfo);exit;
        if($deleteInfo == 1){
            DB::table('mt_shop')->where('shop_id',$shop_id)->delete();
            DB::table('mt_goods')->where('shop_id',$shop_id)->delete();
            $response=[
                'code'=>0,
                'msg'=>'删除成功'
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $response=[
                'code'=>1,
                'msg'=>'请求失败,请重试'
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }

    //修改密码-验证上一个密码
    public function admin_pwdUpdate(Request $request){
        $admin_id = $request->input('admin_id');   //用户id
        $last_admin_pwd = $request->input('last_admin_pwd');   //上一次使用的密码
        $update_admin_pwd = $request->input('update_admin_pwd');    //要修改的密码
        $update_admin_pwd = password_hash($update_admin_pwd, PASSWORD_BCRYPT);
        $adminInfo = DB::table('admin_user')->where('admin_id',$admin_id)->first();
        if(password_verify($last_admin_pwd,$adminInfo->admin_pwd)){
            $u_admin_pwd = DB::table('admin_user')->where('admin_id',$admin_id)->update(['admin_pwd'=>$update_admin_pwd]);
            if($u_admin_pwd>=0){
                $ip = $_SERVER['SERVER_ADDR'];
                $key = 'H:userlogin_id'.$ip;
                redis::del($key);
                $response=[
                    'code'=>0,
                    'msg'=>'修改成功,请重新登录'
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
        }else{
            $response=[
                'code'=>1,
                'msg'=>'上个密码出现错误,请重试'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    /*
     * 用户分享币修改
     */
    public function money(Request $request)
    {
        $admin_id=$request->input('admin_id');
        $admin_user_money=$request->input('admin_user_money');
        $money=[
            'admin_user_money'=>$admin_user_money
        ];
        $data=DB::table('admin_user')->where('admin_id',$admin_id)->update($money);
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'您的分享币修改成功',
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'您的分享币修改失败',
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    /*
     * 用户积分修改
     */
    public function integral(Request $request)
    {
        $admin_id=$request->input('admin_id');
        $admin_user_integral=$request->input('admin_user_integral');
        $integral=[
          'admin_user_integral'=>$admin_user_integral
        ];
        $data=DB::table('admin_user')->where('admin_id',$admin_id)->update($integral);
                if($data){
                    $response=[
                        'code'=>0,
                        'data'=>$data,
                        'msg'=>'您的积分修改成功',
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }else{
                    $response=[
                        'code'=>1,
                        'msg'=>'您的积分修改失败',
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
    }

    /*
     * 用户搜索
     */
    public function search(Request $request)
    {
        $admin_names=$request->input('admin_names');
        $admin_tel=$request->input('admin_tel');
        if($admin_tel){
            $data=DB::table('admin_user')
                ->where('admin_tel' , '=' , "$admin_tel")
                ->get(['admin_id','admin_names','admin_tel','admin_consumption','admin_user_integral','admin_user_money'])
                ->toArray();
            if($admin_tel){
                $response=[
                    'code'=>0,
                    'data'=>$data,
                    'msg'=>'用户查询成功',
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'用户查询失败',
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }
        if($admin_names){
            $data=DB::table('admin_user')
                ->where('admin_names' , '=' , "$admin_names")
                ->get(['admin_id','admin_names','admin_tel','admin_consumption','admin_user_integral','admin_user_money'])
                ->toArray();
            if($admin_names){
                $response=[
                    'code'=>0,
                    'data'=>$data,
                    'msg'=>'用户查询成功',
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'用户查询失败',
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }
    }
    /*
     * 用户总人数
     */
    public function userman(Request $request)
    {
        $shop_status=[
            'shop_status'=>2
        ];
        $data=DB::table('admin_user')->where($shop_status)->count();
//        var_dump($data);die;
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'',
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }
    /*
     * 总分享币
     */
    public function usermoney(Request $request)
    {
        $shop_status=[
            'shop_status'=>2
        ];
        $data=DB::table('admin_user')->where($shop_status)->sum('admin_user_money');
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'成功',
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'失败'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    /*
     * 总积分
     */
    public function userintegral(Request $request)
    {
        $shop_status=[
            'shop_status'=>2
        ];
        $data=DB::table('admin_user')->where($shop_status)->sum('admin_user_integral');
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'',
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    /*
    * 普通用户移除
    */
    public function userdelete(Request $request)
    {
        $admin_id=$request->input('admin_id');
        $where=[
            'admin_id'=>$admin_id
        ];
        $data=DB::table('admin_user')->where($where)->delete();
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'此用户已被移除'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'用户移除失败'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    /*
     * 普通用户拉黑
     */
    public function userblack(Request $request)
    {
        $admin_id=$request->input('admin_id');
        $where=[
            'admin_id'=>$admin_id
        ];
        $data=DB::table('admin_user')->where($where)->delete();
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'此用户已被拉黑'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'用户拉黑失败'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }


    /*
     * 商家信息展示
     */
    public function business(Request $request)
    {
        $data1=[
          'shop_status'=>2
        ];
        $data=DB::table('mt_shop')
            ->where($data1)
            ->select(['shop_id','shop_name','shop_phone','shop_account','shop_cash','shop_address_detail','shop_sales','shop_contacts','shop_address_provice','shop_address_city','shop_address_area'])
            ->paginate(7);
//        var_dump($data);die;
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'您的数据类表请求成功',
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'您的数据类表请求失败',
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    /*
     * 商家总人数
     */
    public function businessman(Request $request)
    {
        $shop_status=[
            'shop_status'=>0
        ];
        $data=DB::table('mt_shop')->where($shop_status)->count();
//        var_dump($data);die;
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'成功',
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'失败'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    /*
     * 商家总营业额
     */
    public function businessmoney(Request $request)
    {
        $shop_status=[
            'shop_status'=>0
        ];
        $data=DB::table('mt_shop')->where($shop_status)->sum('shop_sales');
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'成功',
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'失败'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    /*
     * 商家总余额
     */
    public function businessall(Request $request)
    {
        $shop_status=[
          'shop_status'=>0
        ];
        $data=DB::table('mt_shop')->where($shop_status)->sum('shop_account');
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'成功',
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'失败'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    /*
     * 商家用户移除
     */
    public function businessdelete(Request $request)
    {
        $shop_id=$request->input('shop_id');
        $where=[
          'shop_id'=>$shop_id
        ];
        $data=DB::table('mt_shop')->where($where)->delete();
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'此用户已被移除'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'用户移除失败'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    /*
    * 将商家移入拉黑类表
    */
    public function businessblack(Request $request)
    {
        $shop_id=$request->input('shop_id');
        $where=[
            'shop_id'=>$shop_id
        ];
//        var_dump($where);die;
        $data1=[
            'shop_status'=>1
        ];
        $data=DB::table('mt_shop')->where($where)->update($data1);
//        var_dump($data);die;
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'此商家已被拉黑'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'此商家没有被拉黑'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }

    /*
     * 商家拉黑列表
     */
    public function businessblacktype(Request $request)
    {
        $data2=[
            'shop_status'=>1
        ];
        $data=DB::table('mt_shop')->where($data2)->get(['shop_id','shop_phone','shop_account','shop_sales','shop_name','shop_contacts']);
        $datas=[
            'data'=>$data,
        ];
        if($data){
            $response=[
                'code'=>0,
                'data'=>$datas,
                'msg'=>'拉黑类表展示成功'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'拉黑类表展示失败'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }


    /*
     * 商家拉黑表单移除
     */

    public function remove(Request $request){
        $shop_id=$request->input('shop_id');
        $shop_status=$request->input('shop_status');
        $where=[
            'shop_id'=>$shop_id
        ];
        $data1=[
            'shop_status'=>2
        ];
        $data=DB::table('mt_shop')->where($where)->update($data1);
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'已从黑表单移除'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'没有从黑表单移除'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    /*
     * 商家搜索
     */
    public function serachbusiness(Request $request)
    {
        $shop_contacts=$request->input('shop_contacts');
        $shop_phone=$request->input('shop_phone');
        if($shop_contacts){
            $data=DB::table('mt_shop')
                ->where('shop_contacts' , '=' , "$shop_contacts")
                ->get(['shop_name','shop_phone','shop_account','shop_cash','shop_address_detail','shop_sales','shop_contacts','shop_address_provice','shop_address_city','shop_address_area'])
                ->toArray();
            if($shop_contacts){
                $response=[
                    'code'=>0,
                    'data'=>$data,
                    'msg'=>'商家查询成功',
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'商家查询失败',
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }
        if($shop_phone){
            $data=DB::table('mt_shop')
                ->where('shop_phone' , '=' , "$shop_phone")
                ->get(['shop_name','shop_phone','shop_account','shop_cash','shop_address_detail','shop_sales','shop_contacts','shop_address_provice','shop_address_city','shop_address_area'])
                ->toArray();
            if($shop_phone){
                $response=[
                    'code'=>0,
                    'data'=>$data,
                    'msg'=>'商家查询成功',
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'商家查询失败',
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
             }
            }
        }


    /*
* 商家入驻信息提交
*/
    public function  businesssettled(Request $request)
    {
        $shop_status=[
            'shop_status'=>0
        ];
        $data=DB::table('mt_type')
            ->where($shop_status)
            ->join('mt_shop','mt_shop.t_id','=','mt_type.t_id')
            ->select(['mt_shop.shop_id','mt_shop.shop_name','mt_shop.shop_phone','mt_shop.shop_address_provice','mt_shop.shop_address_city','mt_shop.shop_address_area','mt_shop.shop_address_detail','shop_contacts','mt_type.t_name'])
            ->paginate(4);
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'商家待审核列表展示成功'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'error'=>1,
                'msg'=>'商家待审核列表展示失败'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }



    /*
     * 商家申请入驻
     */
    public function settled(Request $request)
    {
        $data=DB::table('mt_type')
            ->join('mt_shop','mt_shop.t_id','=','mt_type.t_id')
            ->select(['mt_shop.shop_id','mt_shop.shop_name','mt_shop.shop_phone','mt_shop.shop_address_provice','mt_shop.shop_address_city','mt_shop.shop_address_area','mt_shop.shop_address_detail','mt_shop.lat','mt_shop.lng','mt_shop.shop_contacts','mt_shop.shop_certificate','mt_type.t_name'])
            ->paginate(7);
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'商家入驻信息展示'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'error'=>1,
                'msg'=>'商家入驻信息展示失败'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //获取access_Token
    public function admin_accessToken(){
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

    public function curl_post($url='',$postdata='',$options=array()){
        $ch=curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        if(!empty($options)){
         curl_setopt_array($ch, $options);
        }
        $data=curl_exec($ch);
        curl_close($ch);
        return $data;
     }

    public function data_uri($contents, $mime)
    {
        $base64   = base64_encode($contents);
        return ('data:' . $mime . ';base64,' . $base64);
    }

    /*
     * 商家申请入驻审核
     */
    public function examine(Request $request)
    {
        $accessToken = $this->admin_accessToken();
//        var_dump($accessToken);exit;
        $shop_id=$request->input('shop_id');
        $scene = mt_rand(1111,9999) . Str::random(6);
        //var_dump($scene);exit;
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=$accessToken";
        $postdata = [
            "page" => "/pages/index/index",
            "scene" => $scene,
        ];
        $res = $this->curl_post($url,json_encode($postdata),$options=array());
        $img = './images/'.time().'.jpg';
        //var_dump($img);exit;
        $r = file_put_contents($img,$res);
        //var_dump($r);exit;
        $where=[
          'shop_id'=>$shop_id
        ];
        $data1=[
            'shop_status'=>2,
            'shop_rand'=>$img,
            'shop_random_str'=>$scene
        ];
        $data=DB::table('mt_shop')->where($where)->update($data1);
//        var_dump($data);die;
        if($data){
            $shopUserInfo = DB::table('admin_user')->where('shop_id',$shop_id)->get()->toArray();
            if(!$shopUserInfo){
//                echo "<img src='".$img."'>";
                $shopPhone = DB::table('mt_shop')->where('shop_id',$shop_id)->first('shop_phone');
//                var_dump($shopPhone);exit;
                $shop_phone = $shopPhone->shop_phone;
                $insert = [
                    'admin_user'=>$shop_phone,
                    'admin_pwd'=>password_hash($shop_phone,PASSWORD_DEFAULT),
                    'admin_tel'=>$shop_phone,
                    'admin_judge'=>2,
                    'shop_id'=>$shop_id,
                ];
                DB::table('admin_user')->insertGetId($insert);
            }
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'审核通过'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'error'=>1,
                'msg'=>'审核失败'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }


    /*
     * 后台图片上传
     */
    public function advertis_img(Request $request){
        $rotation_net_id=$request->input('rotation_net_id');
        $rotation_id=$request->input('rotation_id');
        $imgarr=$_FILES;
        $imgurl=$imgarr['file']['tmp_name'];
        $imgname=$imgarr['file']['name'];
        $time=time();
        $rand=rand(1000,9999)+$time;
        $address="/imgadvertis/$rand$imgname";
//        var_dump($address);die;
        $add=[
          'rotation_img'=>$address,
        ];
//        var_dump($add);die;
        $data=DB::table('mt_recommend')->insert($add);
//        var_dump($datea);die;
        if (!is_dir(public_path() . '/imgadvertis')) mkdir(public_path() . '/imgadvertis', 0777, true);
        $uploaded = move_uploaded_file($imgurl, public_path() . $address);

        //var_dump($uploaded);exit;
        if ($uploaded == true) {
            return json_encode([
                'code'=>1,
                'data'=>$data,
                'msg'=>'图片上传成功'
            ]);
        } else {
            return json_encode([
                'code'=>2,
                'data'=>$address,
                'msg'=>'图片上传失败'
            ]);
        }
    }
    /*
     * 图片修改
     */
    public function imguptate(Request $request)
    {
        $rotation_id=$request->input('rotation_id');
        $imgarr=$_FILES;
        $imgurl=$imgarr['file']['tmp_name'];
        $imgname=$imgarr['file']['name'];
        $time=time();
        $rand=rand(1000,9999)+$time;
        $address="/imgadvertis/$rand$imgname";
//        var_dump($address);die;
        $add=[
            'rotation_img'=>$address,
        ];
//        var_dump($add);die;
        $arr=DB::table('mt_recommend')
            ->where('rotation_id',$rotation_id)
            ->update(['rotation_img'=>$address]);
//        var_dump($datea);die;
        $data=[
            'rotation_img'=>$address,
            'rotation_id'=>$rotation_id,
        ];
        //此处地址根据项目而定，唯一注意的就是图片命名，这里难得去获取后缀，随便写了个png
//        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
//        $website = $http_type . $_SERVER['HTTP_HOST'];
        if (!is_dir(public_path() . '/imgadvertis')) mkdir(public_path() . '/imgadvertis', 0777, true);
        $uploaded = move_uploaded_file($imgurl, public_path() . $address);

        //var_dump($uploaded);exit;
        if ($uploaded == true) {
            return json_encode([
                'code'=>1,
                'data'=>$data,
                'msg'=>'图片修改成功'
            ],);
        } else {
            return json_encode([
                'code'=>2,
                'data'=>$arr,
                'msg'=>'图片修改失败'
            ]);
        }
    }

    /*
     * 首页轮播图
     */
    public function recommend(Request $request)
    {
//        $rotation_img=$request->input('rotation_img');
        $data1=[
            'shop_status'=>0
        ];
        $rotation_net_id=$request->input('rotation_net_id');
        $rotation_rou=$request->input('rotation_rou');
        $data=DB::table('mt_recommend')
            ->select(['rotation_id','rotation_img','rotation_net_id','rotation_rou'])
            ->paginate(4);
        $data1=[
          'data'=>$data
        ];
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'成功'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'error'=>1,
                'msg'=>'失败'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    /*
     * 图片路径和id修改
     */
    public function rotationupdate(Request $request)
    {

        $rotation_id=$request->input('rotation_id');
        $rotation_rou=$request->input('rotation_rou');
        $rotation_net_id=$request->input('rotation_net_id');
        $integral=[
            'rotation_rou'=>$rotation_rou,
            'rotation_net_id'=>$rotation_net_id,
        ];
//        var_dump($integral);die;
        $data=DB::table('mt_recommend')->where('rotation_id',$rotation_id)->update($integral);
//        var_dump($data);die;
        if($data ==true){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'修改成功',
            ];
            return (json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'修改失败',
            ];
            return (json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    /*
    * 和轮播图的推荐位6条
    */
    public function recommendrou(Request $request)
    {
        $data1=[
            'shop_status'=>0
        ];
        $data=DB::table('mt_goods')
            ->where($data1)
            ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
            ->select(['goods_name','shop_name','mt_goods.price','mt_goods.picture','mt_goods.is_recommend','mt_goods.goods_id'])
            ->paginate(6);
        $data1=[
            'data'=>$data
        ];
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data1,
                'msg'=>'展示成功'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'error'=>1,
                'msg'=>'展示失败'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }


    /*
     * 6条推荐位的
     */
    public function modification(Request $request)
    {
        $shop_name=$request->input('shop_name');
        $shop_id = DB::table('mt_shop')->where('shop_name',$shop_name)->first('shop_id');
//        var_dump($shop_id);exit;
        $where=[
            'shop_id'=>$shop_id->shop_id
        ];
//        var_dump($shop_id);die;
        $dataa=DB::table('mt_goods')
            ->where($where)
            ->select(['goods_name','price','picture','goods_id'])
            ->paginate(6);
//        var_dump($dataa);die;
        if($shop_name){
//            var_dump($data);die;
            if($shop_name){
                $response=[
                    'code'=>0,
                    'data'=>$dataa,
                    'msg'=>'成功',
                ];
                return (json_encode($response,JSON_UNESCAPED_UNICODE));
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'失败',
                ];
                return (json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>2,
                'msg'=>'您查询的商家不存在',
            ];
            return (json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }

    /*
     * 修改推荐位商品
     */
    public function yesorno(Request $request)
    {
        $goods_id=$request->input('goods_id');
        $mt_goods_id=$request->input('mt_goods_id');
        $bb=[
            'is_recommend'=>0
        ];
        $arr=DB::table('mt_goods')
            ->where('goods_id',$mt_goods_id)
            ->update($bb);
//        var_dump($arr);die;
        $cc=[
           'is_recommend'=>1
        ];
        $data=DB::table('mt_goods')
            ->where('goods_id',$goods_id)
            ->update($cc);
//        var_dump($data);exit;
        if($data ==true){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'修改成功',
            ];
            return (json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'修改失败',
            ];
            return (json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    /*
     * 优惠卷展示列表
     */
    public function couponexhibition(Request $request)
    {
        $shop_id = $request->input('shop_id');
        $admin_judge = $request->input('admin_judge');
        if($admin_judge == 1){
            $data=DB::table('mt_coupon')
                ->join('mt_shop','mt_coupon.shop_id','=','mt_shop.shop_id')
                ->join('mt_goods','mt_coupon.goods_id','=','mt_goods.goods_id')
                ->select(['mt_goods.goods_name','mt_shop.shop_name','mt_coupon.coupon_id','mt_coupon.coupon_names','mt_coupon.coupon_num','mt_coupon.coupon_type','mt_coupon.coupon_create','mt_coupon.create_time','mt_coupon.coupon_redouction','mt_coupon.discount'])
                ->paginate(6);
//        var_dump($data);die;
            if($data){
                $response=[
                    'code'=>0,
                    'data'=>$data,
                    'msg'=>'展示成功',
                ];
                return (json_encode($response,JSON_UNESCAPED_UNICODE));
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'展示失败',
                ];
                return (json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }elseif($admin_judge == 2){
            $data=DB::table('mt_coupon')
                ->join('mt_shop','mt_coupon.shop_id','=','mt_shop.shop_id')
                ->join('mt_goods','mt_coupon.goods_id','=','mt_goods.goods_id')
                ->where('mt_coupon.shop_id',$shop_id)
                ->select(['mt_goods.goods_name','mt_shop.shop_name','mt_coupon.coupon_id','mt_coupon.coupon_names','mt_coupon.coupon_num','mt_coupon.coupon_type','mt_coupon.coupon_create','mt_coupon.create_time','mt_coupon.coupon_redouction','mt_coupon.discount'])
                ->paginate(6);
//        var_dump($data);die;
            if($data){
                $response=[
                    'code'=>0,
                    'data'=>$data,
                    'msg'=>'展示成功',
                ];
                return (json_encode($response,JSON_UNESCAPED_UNICODE));
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'展示失败',
                ];
                return (json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>2,
                'msg'=>'请先登录'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    /*
     * 优惠卷搜索
     */

    public function couponsearch(Request $request)
    {
        $goods_name=$request->input('goods_name');
        $shop_name=$request->input('shop_name');
        $shop_id = $request->input('shop_id');
        $admin_judge = $request->input('admin_judge');
        if($admin_judge == 1){
            if($goods_name){
                $data=DB::table('mt_coupon')
                    ->where('goods_name' , '=' , "$goods_name")
                    ->join('mt_shop','mt_coupon.shop_id','=','mt_shop.shop_id')
                    ->join('mt_goods','mt_coupon.goods_id','=','mt_goods.goods_id')
                    ->get(['mt_goods.goods_name','mt_shop.shop_name','mt_coupon.coupon_id','mt_coupon.coupon_names','mt_coupon.coupon_num','mt_coupon.coupon_type','mt_coupon.coupon_create','mt_coupon.create_time']);
//            var_dump($data);die;
                $data1=[
                    'data'=>$data
                ];
                if($goods_name){
                    $response=[
                        'code'=>0,
                        'data'=>$data1,
                        'msg'=>'查询成功',
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }else{
                    $response=[
                        'code'=>1,
                        'msg'=>'查询失败',
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }
            if($shop_name){
                $data=DB::table('mt_coupon')
                    ->where('shop_name' , '=' , "$shop_name")
                    ->join('mt_shop','mt_coupon.shop_id','=','mt_shop.shop_id')
                    ->join('mt_goods','mt_coupon.goods_id','=','mt_goods.goods_id')
                    ->get(['mt_goods.goods_name','mt_shop.shop_name','mt_coupon.coupon_id','mt_coupon.coupon_names','mt_coupon.coupon_num','mt_coupon.coupon_type','mt_coupon.coupon_create','mt_coupon.create_time']);
//            var_dump($data);die;
                $data1=[
                    'data'=>$data
                ];
                if($shop_name){
                    $response=[
                        'code'=>0,
                        'data'=>$data1,
                        'msg'=>'查询成功',
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }else{
                    $response=[
                        'code'=>1,
                        'msg'=>'查询失败',
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }
        }else{
            if($goods_name){
                $data=DB::table('mt_coupon')
                    ->where('mt_coupon.shop_id',$shop_id)
                    ->where('goods_name' , '=' , "$goods_name")
                    ->join('mt_shop','mt_coupon.shop_id','=','mt_shop.shop_id')
                    ->join('mt_goods','mt_coupon.goods_id','=','mt_goods.goods_id')
                    ->get(['mt_goods.goods_name','mt_shop.shop_name','mt_coupon.coupon_id','mt_coupon.coupon_names','mt_coupon.coupon_num','mt_coupon.coupon_type','mt_coupon.coupon_create','mt_coupon.create_time']);
//            var_dump($data);die;
                $data1=[
                    'data'=>$data
                ];
                if($goods_name){
                    $response=[
                        'code'=>0,
                        'data'=>$data1,
                        'msg'=>'查询成功',
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }else{
                    $response=[
                        'code'=>1,
                        'msg'=>'查询失败',
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }
            if($shop_name){
                $data=DB::table('mt_coupon')
                    ->where('mt_coupon.shop_id',$shop_id)
                    ->where('shop_name' , '=' , "$shop_name")
                    ->join('mt_shop','mt_coupon.shop_id','=','mt_shop.shop_id')
                    ->join('mt_goods','mt_coupon.goods_id','=','mt_goods.goods_id')
                    ->get(['mt_goods.goods_name','mt_shop.shop_name','mt_coupon.coupon_id','mt_coupon.coupon_names','mt_coupon.coupon_num','mt_coupon.coupon_type','mt_coupon.coupon_create','mt_coupon.create_time']);
//            var_dump($data);die;
                $data1=[
                    'data'=>$data
                ];
                if($shop_name){
                    $response=[
                        'code'=>0,
                        'data'=>$data1,
                        'msg'=>'查询成功',
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }else{
                    $response=[
                        'code'=>1,
                        'msg'=>'查询失败',
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }
        }


    }

    /*
     * 优惠卷添加时候的商品和id
     */

    public function coupon(Request $request)
    {

        $shop_id=$request->input('shop_id');
        $where=[
            'mt_shop.shop_id'=>$shop_id
        ];
        $data=DB::table('mt_shop')
            ->where($where)
            ->join('mt_goods','mt_goods.shop_id','=','mt_shop.shop_id')
            ->select(['goods_name','mt_goods.goods_id'])
            ->get();
        $data1=[
            'data'=>$data
        ];
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data1,
                'msg'=>'查询成功'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'查询失败'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    /*
     *优惠卷添加
     */
    public function couponinsert(Request $request)
    {
        $coupon_names=$request->input('coupon_names');
        $coupon_num=$request->input('coupon_num');
        $coupon_redouction=$request->input('coupon_redouction');
        $coupon_price=$request->input('coupon_price');
        $discount=$request->input('discount');
        $coupon_type=$request->input('coupon_type');
        $create_time=$request->input('create_time');
        $expiration=$request->input('expiration');
        $goods_id=$request->input('goods_id');
        $shop_id=$request->input('shop_id');
        $det=[
            'coupon_redouction'=>$coupon_redouction,
            'coupon_price'=>$coupon_price,
            'coupon_names'=>$coupon_names,
            'coupon_num'=>$coupon_num,
            'coupon_type'=>$coupon_type,
            'create_time'=>$create_time,
            'expiration'=>$expiration,
            'discount'=>$discount,
            'shop_id'=>$shop_id,
            'goods_id'=>$goods_id
        ];
        $where=[
            'goods_id'=>$goods_id
        ];
        $is_promotion = DB::table('mt_goods')->where($where)->first(['is_promotion']);     //是否开启拼团   0关闭  1开启
        $limited_buy = DB::table('mt_goods')->where($where)->first(['limited_buy']);    //是否开启抢购 1开启  0关闭
        if($is_promotion->is_promotion == 0 && $limited_buy->limited_buy == 0){
            $couponInfo = DB::table('mt_coupon')->where($where)->get()->toArray();
            if($couponInfo){
                $response=[
                    'code'=>2,
                    'msg'=>'该商品已存在一张优惠券'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }else{
                $data=DB::table('mt_coupon')->where($where)->insert($det);
                $aa=[
                    'data'=>$data
                ];
                if($data == true){
                    $updateGoodsInfo = DB::table('mt_goods')->where('goods_id',$goods_id)->update(['is_coupon'=>1,'promotion_type'=>2]);
//                    var_dump($updateGoodsInfo);exit;
                    if($updateGoodsInfo >=0){
                        $response=[
                            'code'=>0,
                            'data'=>$aa,
                            'msg'=>'优惠卷添加成功'
                        ];
                        return (json_encode($response, JSON_UNESCAPED_UNICODE));
                    }
                }else{
                    $response=[
                        'code'=>3,
                        'msg'=>'优惠卷添加失败'
                    ];
                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                }
            }
        }else{
            $response=[
                'code'=>1,
                'msg'=>'同一商品只能同时开启一种活动'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    /*
     * 优惠卷删除
     */
    public function coupontele(Request $request)
    {
        $coupon_id=$request->input('coupon_id');
        $where=[
            'coupon_id'=>$coupon_id
        ];
        $data=DB::table('mt_coupon')->where($where)->delete();
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'优惠卷删除成功'
            ];
                return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'优惠卷删除失败'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //店铺管理
    public function admin_shop(Request $request){
        $shop_id = $request->input('shop_id');
        $admin_judge = $request->input('admin_judge');
        if($admin_judge == 2){
            $shopInfo = DB::table('mt_shop')
                ->join('admin_user','admin_user.shop_id','=','mt_shop.shop_id')
                ->where('mt_shop.shop_id',$shop_id)
                ->first(['shop_name','shop_img','shop_project','shop_desc','shop_bus','shop_service','shop_address_provice','shop_address_city','shop_address_area','admin_tel','admin_id','shop_logo']);
            $response=[
                'code'=>0,
                'data'=>$shopInfo,
                'msg'=>'数据请求成功'
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }elseif($admin_judge == 1){
            $shopInfo = DB::table('mt_shop')
                ->join('admin_user','mt_shop.shop_id','=','admin_user.shop_id')
                ->where('mt_shop.shop_status',2)
                ->get(['shop_name','shop_img','shop_project','shop_desc','shop_bus','shop_service','shop_address_provice','shop_address_city','shop_address_area','shop_logo'])->toArray();
            $response=[
                'code'=>0,
                'data'=>$shopInfo,
                'msg'=>'数据请求成功'
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $response=[
                'code'=>2,
                'msg'=>'请先登录'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //店铺信息修改
    public function admin_shop_update(Request $request){
        $shop_id = $request->input('shop_id');
        $update = [
            'shop_img'=>$request->input('shop_img'),     //主图
            'shop_project'=>$request->input('shop_project'),    //项目
            'shop_desc'=>$request->input('shop_desc'),          //简介
            'shop_bus'=>$request->input('shop_bus'),            //营业时间
            'shop_service'=>$request->input('shop_service'),   //服务
            'shop_logo'=>$request->input('shop_logo')
        ];
        $updateShopInfo = DB::table('mt_shop')->where('shop_id',$shop_id)->update($update);
        if($updateShopInfo >0){
            $response=[
                'code'=>0,
                'msg'=>'修改成功'
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $response=[
                'code'=>1,
                'msg'=>'亲,您并未修改任何信息'
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }

    //忘记密码
    public function admin_forgetPwd(Request $request){

    }





}
