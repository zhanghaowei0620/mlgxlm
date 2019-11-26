<?php

namespace App\Http\Controllers\Reseller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ResellerController extends Controller
{
    //成为分销商
    public function reseller(Request $request)
    {
        $shop_random_str=$request->input('shop_random_str');
//        $ip = $_SERVER['SERVER_ADDR'];
//        $key = 'openid'.$ip;
//        $openid =  Redis::get($key);
//        $openid='o9VUc5AOsdEdOBeUAw4TdYg-F-dM';
        $data=DB::table('mt_user')
            ->where(['shop_random_str'=>$shop_random_str])
            ->get();
        if($data){
            $data = [
                'code'=>0,
                'msg'=>'您的请求已提交,请耐心等待审核'
            ];
            $response = [
                'data'=>$data
            ];
            return (json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $data = [
                'code'=>1,
                'msg'=>'操作有误,请重新提交'
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //分销商品列表
    public function reselleradd(Request $request)
    {
        $shop_id=$request->input('shop_id');
        $data1=DB::table('re_goods')
            ->where(['shop_id'=>$shop_id])
            ->get();
        $data2=DB::table('re_goods')
            ->where(['shop_id'=>$shop_id])
            ->orderBy(['re_goods_volume'],'desc')
            ->get();
        if($data){
            $data = [
                'code'=>0,
                'data1'=>$data1,
                'data2'=>$data2,
                'msg'=>'展示成功'
            ];
            $response = [
                'data'=>$data
            ];
            return (json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $data = [
                'code'=>1,
                'msg'=>'展示失败'
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //分销商品详情
    public function  resellergoods(Request $request)
    {
        $re_goods_id=$request->input('re_goods_id');
        $data1=DB::table('re_goods')
            ->join('mt_shop','mt_shop.shop_id','=','re_goods.shop_id')
            ->where(['re_goods_id'=>$re_goods_id])
            ->first(['mt_shop.shop_name','mt_shop.shop_credit','mt_shop.shop_logo','re_goods_name','re_goods_price','re_goods_planting_picture','re_goods_volume','re_goods_stock','re_production_time','re_expiration_time','re_goods_introduction','re_goods_picture_detail']);
        if($data1){
            $data = [
                'code'=>0,
                'data1'=>$data1,
                'msg'=>'展示成功'
            ];
            $response = [
                'data'=>$data
            ];
            return (json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $data = [
                'code'=>1,
                'msg'=>'展示失败'
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //


}
