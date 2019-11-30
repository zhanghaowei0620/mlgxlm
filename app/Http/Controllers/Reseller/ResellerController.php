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

        $shop_resellerInfo = DB::table('re_goods')
            ->join('mt_shop','re_goods.shop_id','=','mt_shop.shop_id')
            ->where('mt_shop.shop_reseller',1)
            ->get(['mt_shop.shop_id','mt_shop.shop_name','re_goods.re_goods_id','re_goods.re_goods_name','re_goods.re_goods_price','re_goods.re_goods_picture','re_goods.re_goods_volume'])->toArray();
//        var_dump($shop_resellerInfo);exit;
        $data = [
            'code'=>0,
            'shop_resellerInfo'=>$shop_resellerInfo,
            'msg'=>'数据请求成功'
        ];
        $response = [
            'data' => $data
        ];
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    //




}
