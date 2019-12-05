<?php

namespace App\Http\Controllers\Reseller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ResellerController extends Controller
{
    //分销中心--申请
    public function index_reseller_Apply(Request $request){
        $is_distribution = $request->input('is_distribution');    //1为分销代理   2为分销商  3为异业联盟
        $shop_name = $request->input('shop_name');   //店铺名称
        $contacts = $request->input('contacts');   //联系人
        $contacts_tel = $request->input('contacts_tel');   //联系电话
        $shop_address = $request->input('shop_address');   //店铺地址
        $industry = $request->input('industry');   //所属行业
        $remarks = $request->input('remarks');     //备注
        if($is_distribution == 1){
            $insert = [
                'shop_name'=>$shop_name,
                'contacts'=>$contacts,
                'contacts_tel'=>$contacts_tel,
                'shop_address'=>$shop_address,
                'is_distribution'=>$is_distribution
            ];
        }elseif ($is_distribution == 2){
            $insert = [
                'shop_name'=>$shop_name,
                'contacts'=>$contacts,
                'contacts_tel'=>$contacts_tel,
                'shop_address'=>$shop_address,
                'is_distribution'=>$is_distribution
            ];
        }else{
            $insert = [
                'industry'=>$industry,
                'contacts'=>$contacts,
                'contacts_tel'=>$contacts_tel,
                'remarks'=>$remarks,
                'is_distribution'=>$is_distribution
            ];
        }
        $Insert = DB::table('mt_distribution')->insertGetId($insert);
        if($Insert){
            $data = [
                'code'=>0,
                'msg'=>'申请成功，请耐心等待'
            ];
            $response = [
                'data' => $data
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $data = [
                'code'=>1,
                'msg'=>'系统出现错误，请重试'
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //分销中心列表
    public function index_rellerList(Request $request){
        $openid = $request->input('openid');
        if($openid){
            $userInfo = DB::table('mt_user')->where('openid',$openid)->first(['mt_reseller']);
            $mt_reseller = $userInfo->mt_reseller;

            $shop_resellerInfo = DB::table('re_goods')
                ->join('mt_shop','re_goods.shop_id','=','mt_shop.shop_id')
                ->where('mt_shop.shop_reseller',1)
                ->get(['mt_shop.shop_id','mt_shop.shop_name','mt_shop.shop_img','re_goods.re_goods_id','re_goods.re_goods_name','re_goods.re_goods_price','re_goods.re_goods_picture','re_goods.re_goods_volume'])->toArray();
//        var_dump($shop_resellerInfo);exit;
            $data = [
                'code'=>0,
                'shop_resellerInfo'=>$shop_resellerInfo,
                'mt_reseller'=>$mt_reseller,
                'msg'=>'数据请求成功'
            ];
            $response = [
                'data' => $data
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $shop_resellerInfo = DB::table('re_goods')
                ->join('mt_shop','re_goods.shop_id','=','mt_shop.shop_id')
                ->where('mt_shop.shop_reseller',1)
                ->get(['mt_shop.shop_id','mt_shop.shop_name','mt_shop.shop_img','re_goods.re_goods_id','re_goods.re_goods_name','re_goods.re_goods_price','re_goods.re_goods_picture','re_goods.re_goods_volume'])->toArray();
//        var_dump($shop_resellerInfo);exit;
            $data = [
                'code'=>0,
                'shop_resellerInfo'=>$shop_resellerInfo,
                'mt_reseller'=>0,
                'msg'=>'数据请求成功'
            ];
            $response = [
                'data' => $data
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

    }

    // 分销商品列表
    public function index_reseller_goodsList(Request $request){
        $is_reseller = $request->input('is_reseller');    //1为全部商品   2为店铺销量  3为新品
        $shop_id = $request->input('shop_id');   //店铺id

        if($is_reseller == 1){
            $re_goodsInfo = DB::table('re_goods')->where('shop_id',$shop_id)->paginate(6);

            $data = [
                'code'=>0,
                're_goodsInfo'=>$re_goodsInfo,
                'msg'=>'数据请求成功'
            ];
            $response = [
                'data' => $data
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }elseif($is_reseller == 2){
//            echo time();exit;
            $re_goodsInfo = DB::table('re_goods')->where('shop_id',$shop_id)->orderBy('re_goods_volume','desc')->paginate(6);
//            var_dump($re_goodsInfo);exit;
            $data = [
                'code'=>0,
                're_goodsInfo'=>$re_goodsInfo,
                'msg'=>'数据请求成功'
            ];
            $response = [
                'data' => $data
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }elseif ($is_reseller == 3){
            $re_goodsInfo = DB::table('re_goods')->where('shop_id',$shop_id)->orderBy('create_time','desc')->paginate(6);
//            var_dump($re_goodsInfo);exit;
            $data = [
                'code'=>0,
                're_goodsInfo'=>$re_goodsInfo,
                'msg'=>'数据请求成功'
            ];
            $response = [
                'data' => $data
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

    }

    //分销商品详情
    public function index_reseller_goodsDetail(Request $request){
        $re_goods_id = $request->input('re_goods_id');
        $shop_id = $request->input('shop_id');
        //商品详情信息
        $re_goodsShopInfo = DB::table('re_goods')->join('mt_shop','re_goods.shop_id','=','mt_shop.shop_id')
            ->where('re_goods.re_goods_id',$re_goods_id)->first(['re_goods_name','re_goods_price','re_goods_stock','re_goods_picture','re_goods_introduction','is_distribution','re_goods_volume','re_goods_planting_picture','re_goods_picture_detail','re_production_time','re_expiration_time','mt_shop.shop_id','shop_name','shop_score','shop_address_provice','shop_address_city','shop_address_area','shop_img']);
//        var_dump($re_goodsShopInfo);exit;

        $re_evaluateInfo = DB::table('re_evaluate')
            ->join('mt_user','re_evaluate.uid','=','mt_user.uid')
            ->where('re_goods_id',$re_goods_id)->orderBy('create_time','desc')->limit(5)->get()->toArray();    //评价信息

        $re_evaluateInfo_count = DB::table('re_evaluate')->where('re_goods_id',$re_goods_id)->count();   //评论条数

        $re_goods_recommend = DB::table('re_goods')->where('shop_id',$shop_id)->get();   //店铺推荐
        $data = [
            'code'=>0,
            're_goodsShopInfo'=>$re_goodsShopInfo,
            're_evaluateInfo_count'=>$re_evaluateInfo_count,
            're_evaluateInfo'=>$re_evaluateInfo,
            're_goods_recommend'=>$re_goods_recommend,
            'msg'=>'数据请求成功'
        ];
        $response = [
            'data' => $data
        ];
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    //我的团队
    public function my_team(Request $request){
        $openid = $request->input('openid');
        $userInfo = DB::table('mt_user')->where('openid',$openid)->first();
        $p_id = $userInfo->p_id;
        $uid = $userInfo->uid;
        $a_id = $userInfo->a_id;
        if($p_id == 0){
            $son = DB::table('mt_user')->where('p_id',$uid)->get()->toArray();
            $total_num = DB::table('mt_user')->where('a_id',$a_id)->count();
            $data = [
                'code'=>0,
                'son'=>$son,
                'total_num'=>$total_num,
                'msg'=>'数据请求成功'
            ];
            $response = [
                'data' => $data
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $son = DB::table('mt_user')->where('p_id',$uid)->get()->toArray();    //子类
            $uInfo = DB::table('mt_user')->where('uid',$uid)->first();
            $parent = DB::table('mt_user')->where('uid',$uInfo->p_id)->get()->toArray();   //父类
            $total_num = DB::table('mt_user')->where('a_id',$a_id)->count();   //总人数
            $data = [
                'code'=>0,
                'son'=>$son,
                'parent'=>$parent,
                'total_num'=>$total_num,
                'msg'=>'数据请求成功'
            ];
            $response = [
                'data' => $data
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

    }

    //添加分销员
    public function my_team_Add(Request $request){
        $invite_code = $request->input('invite_code');
    }




}
