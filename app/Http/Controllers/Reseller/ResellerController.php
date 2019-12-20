<?php

namespace App\Http\Controllers\Reseller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Flex\Express\ExpressBird;

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
            $userInfo = DB::table('mt_user')->where('openid',$openid)->first(['mt_reseller','uid']);
            $mt_reseller = $userInfo->mt_reseller;

            $shop_resellerInfo = DB::table('mt_shop')
                ->join('re_goods','re_goods.shop_id','=','mt_shop.shop_id')
                ->join('mt_user','mt_shop.uid','=','mt_user.uid')
                ->where('mt_shop.shop_reseller',1)
                ->get(['mt_shop.shop_id','mt_shop.shop_name','mt_shop.shop_img','re_goods.re_goods_id','re_goods.re_goods_name','re_goods.re_goods_price','re_goods.re_goods_picture','re_goods.re_goods_volume','mt_user.shop_random_str'])->toArray();

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
            ->where('re_goods.re_goods_id',$re_goods_id)->first(['re_goods_name','re_goods_price','re_goods_stock','re_goods_picture','re_goods_introduction','is_distribution','re_goods_volume','re_goods_planting_picture','re_goods_picture_detail','re_production_time','re_expiration_time','mt_shop.shop_id','shop_name','shop_score','shop_address_provice','shop_address_city','shop_address_area','shop_img','shop_phone','shop_star']);
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

    //生成订单
    public function index_reseller_orderAdd(Request $request){
        $openid1 = $request->input('openid');
        $address_id = $request->input('address_id');
        $buy_num = $request->input('buy_num');
        $key = $openid1;
        $openid = Redis::get($key);
        if($openid){
            $userInfo = DB::table('mt_user')->where('openid',$openid)->first();
            $uid = $userInfo->uid;
            $re_goods_id = $request->input('re_goods_id');
            $reGoodsInfo = DB::table('re_goods')->where('re_goods_id',$re_goods_id)->first();
//        var_dump($reGoodsInfo);
            $re_goods_name = $reGoodsInfo->re_goods_name;
            $re_goods_price = $reGoodsInfo->re_goods_price;
            $re_goods_picture = $reGoodsInfo->re_goods_picture;
            $shop_id = $reGoodsInfo->shop_id;
            $shopInfo = DB::table('mt_shop')->where('shop_id',$shop_id)->first(['shop_name']);
            $shop_name = $shopInfo->shop_name;
            $order_no = date("YmdHis", time()) . rand(1000, 9999);   //订单号
            $insert = [
                'uid'=>$uid,
                're_order_no'=>$order_no,
                're_goods_name'=>$re_goods_name,
                're_goods_id'=>$re_goods_id,
                're_goods_price'=>$re_goods_price,
                're_goods_picture'=>$re_goods_picture,
                'buy_num'=>$buy_num,
                'shop_id'=>$shop_id,
                'shop_name'=>$shop_name,
                'create_time'=>time(),
                'address_id'=>$address_id
            ];

            $re_orderInsert = DB::table('re_order')->insert($insert);
            if($re_orderInsert){
                $re_orderInfo = DB::table('re_order')->where('re_order_no',$order_no)->first(['re_order_id']);
                $data = [
                    'code'=>0,
                    'data'=>$re_orderInfo,
                    'msg'=>'订单生成成功'
                ];
                $response = [
                    'data' => $data
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $data = [
                    'code'=>1,
                    'msg'=>'订单生成失败'
                ];
                $response = [
                    'data' => $data
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
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

    //分校订单支付-选择支付方式
    public function index_reseller_Choose_payment(Request $request){
        $re_order_id = $request->input('re_order_id');
        $reGoodsInfo = DB::table('re_order')
            ->join('mt_shop','re_order.shop_id','=','mt_shop.shop_id')
            ->join('mt_address','re_order.address_id','=','mt_address.id')
            ->join('re_goods','re_order.re_goods_id','=','re_goods.re_goods_id')
            ->where('re_order_id',$re_order_id)
            ->first(['re_order_id','re_order_no','re_order.re_goods_name','re_order.re_goods_id','pay_price','re_order.re_goods_price','re_order.re_goods_picture','re_goods_introduction','buy_num','mt_shop.shop_name','mt_shop.shop_id','shop_logo','pay_time','sign_time','consign_time','re_order.update_time','re_order.create_time','finish_time','order_status','logistics_no','shipping_type','mt_address.id','address_provice','address_city','address_area','address_detail','tel','is_default','name','pay_type']);
        if($reGoodsInfo){
            $data = [
                'code'=>0,
                'data'=>$reGoodsInfo,
                'msg'=>'数据请求成功'
            ];
            $response = [
                'data' => $data
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $data = [
                'code'=>1,
                'msg'=>'系统出现错误,请检查订单是否真实存在'
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    //分校订单支付-分享币支付
    public function index_reseller_Topay(Request $request){
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
        if($openid){
            $userInfo = DB::table('mt_user')->where('openid',$openid)->first(['uid','money','mt_reseller','p_id','a_id']);
            $uid = $userInfo->uid;   //当前支付的用户的id
            $re_order_id = $request->input('re_order_id');
            $reGoodsInfo = DB::table('re_order')->where('re_order_id',$re_order_id)->first(['re_goods_price','shop_id','buy_num','order_status']);
            if($reGoodsInfo->order_status == 0){
                $shopInfo = DB::table('mt_shop')->where('shop_id',$reGoodsInfo->shop_id)->first(['uid','up_rebate','indirect_up_rebate']);
                $shopInfo1 = DB::table('mt_user')->where('uid',$shopInfo->uid)->first();
                $total_num = $reGoodsInfo->re_goods_price*$reGoodsInfo->buy_num;
                if($userInfo->money >= $total_num){
                    $update = [
                        'money'=>$userInfo->money - $total_num
                    ];
                    $updateUserInfo = DB::table('mt_user')->where('uid',$uid)->update($update);
                    if($updateUserInfo > 0){
                        if($userInfo->mt_reseller == 1){   //判断用户是否为分销员
                            $u_shopInfo = DB::table('mt_shop')->where('uid',$userInfo->a_id)->first(['shop_id']);
                            if($reGoodsInfo->shop_id == $u_shopInfo->shop_id){    //判断用户购买的商品是否为自己上级分销商的商品   如果不是 给直接上级分钱
                                $p_userInfo = DB::table('mt_user')->where('uid',$userInfo->p_id)->first();
                                if($p_userInfo->uid != $p_userInfo->a_id){   //判断用户直接上级 是否为分销商  如果不是 给间接上级分钱
                                    $a_userInfo = DB::table('mt_user')->where('uid',$p_userInfo->p_id)->first();
                                    if($a_userInfo->uid != $a_userInfo->a_id){   //判断用户间接上级是否为分销商
                                        $re_orderInfoUpdate = DB::table('re_order')->where('re_order_id',$re_order_id)->update(['order_status'=>1,'pay_type'=>0,'pay_price'=>$total_num,'pay_time'=>time()]);
                                        $p_userInfoUpdate = DB::table('mt_user')->where('uid',$p_userInfo->uid)->update(['no_reflected'=>$p_userInfo->no_reflected + $total_num*$shopInfo->up_rebate/100]);
                                        $a_userInfoUpdate = DB::table('mt_user')->where('uid',$a_userInfo->uid)->update(['no_reflected'=>$a_userInfo->no_reflected + $total_num*$shopInfo->indirect_up_rebate/100]);
                                        $shopUserInfoUpdate = DB::table('mt_user')->where('uid',$shopInfo->uid)->update(['no_reflected'=>$shopInfo1->no_reflected + $total_num*(100 - $shopInfo->up_rebate - $shopInfo->indirect_up_rebate)/100]);
                                        if($re_orderInfoUpdate>0 && $p_userInfoUpdate>0 && $a_userInfoUpdate>0 && $shopUserInfoUpdate>0){
                                            $data = [
                                                'code'=>0,
                                                'msg'=>'支付成功'
                                            ];
                                            $response = [
                                                'data' => $data
                                            ];
                                            return json_encode($response, JSON_UNESCAPED_UNICODE);
                                        }else{
                                            $data = [
                                                'code'=>3,
                                                'msg'=>'系统出现错误,分账失败,请重试'
                                            ];
                                            $response = [
                                                'data' => $data
                                            ];
                                            die(json_encode($response, JSON_UNESCAPED_UNICODE));
                                        }
                                    }else{
                                        $re_orderInfoUpdate = DB::table('re_order')->where('re_order_id',$re_order_id)->update(['order_status'=>1,'pay_type'=>0,'pay_price'=>$total_num,'pay_time'=>time()]);
                                        $p_userInfoUpdate = DB::table('mt_user')->where('uid',$p_userInfo->uid)->update(['no_reflected'=>$p_userInfo->no_reflected + $total_num*$shopInfo->up_rebate/100]);
                                        $shopUserInfoUpdate = DB::table('mt_user')->where('uid',$shopInfo->uid)->update(['no_reflected'=>$shopInfo1->no_reflected + $total_num*(100 - $shopInfo->up_rebate)/100]);
                                        if($re_orderInfoUpdate>0 && $p_userInfoUpdate>0 && $shopUserInfoUpdate>0){
                                            $data = [
                                                'code'=>0,
                                                'msg'=>'支付成功'
                                            ];
                                            $response = [
                                                'data' => $data
                                            ];
                                            return json_encode($response, JSON_UNESCAPED_UNICODE);
                                        }else{
                                            $data = [
                                                'code'=>4,
                                                'msg'=>'系统出现错误,分账失败,请重试'
                                            ];
                                            $response = [
                                                'data' => $data
                                            ];
                                            die(json_encode($response, JSON_UNESCAPED_UNICODE));
                                        }
                                    }
                                }else{
                                    $re_orderInfoUpdate = DB::table('re_order')->where('re_order_id',$re_order_id)->update(['order_status'=>1,'pay_type'=>0,'pay_price'=>$total_num,'pay_time'=>time()]);
                                    $shopUserInfoUpdate = DB::table('mt_user')->where('uid',$shopInfo->uid)->update(['no_reflected'=>$shopInfo1->no_reflected + $total_num]);
                                    if($re_orderInfoUpdate>0 && $shopUserInfoUpdate>0){
                                        $data = [
                                            'code'=>0,
                                            'msg'=>'支付成功'
                                        ];
                                        $response = [
                                            'data' => $data
                                        ];
                                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                                    }else{
                                        $data = [
                                            'code'=>5,
                                            'msg'=>'系统出现错误,修改订单信息失败,请重试'
                                        ];
                                        $response = [
                                            'data' => $data
                                        ];
                                        die(json_encode($response, JSON_UNESCAPED_UNICODE));
                                    }
                                }
                            }else{
                                $re_orderInfoUpdate = DB::table('re_order')->where('re_order_id',$re_order_id)->update(['order_status'=>1,'pay_type'=>0,'pay_price'=>$total_num,'pay_time'=>time()]);
                                $shopUserInfoUpdate = DB::table('mt_user')->where('uid',$shopInfo->uid)->update(['no_reflected'=>$shopInfo1->no_reflected + $total_num]);
                                if($re_orderInfoUpdate>0 && $shopUserInfoUpdate>0){
                                    $data = [
                                        'code'=>0,
                                        'msg'=>'支付成功'
                                    ];
                                    $response = [
                                        'data' => $data
                                    ];
                                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                                }else{
                                    $data = [
                                        'code'=>6,
                                        'msg'=>'系统出现错误,修改订单信息失败,请重试'
                                    ];
                                    $response = [
                                        'data' => $data
                                    ];
                                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                                }
                            }
                        }else{
                            $re_orderInfoUpdate = DB::table('re_order')->where('re_order_id',$re_order_id)->update(['order_status'=>1,'pay_type'=>0,'pay_price'=>$total_num,'pay_time'=>time()]);
                            $shopUserInfoUpdate = DB::table('mt_user')->where('uid',$shopInfo->uid)->update(['no_reflected'=>$shopInfo1->no_reflected + $total_num]);
                            if($re_orderInfoUpdate>0 && $shopUserInfoUpdate>0){
                                $data = [
                                    'code'=>0,
                                    'msg'=>'支付成功'
                                ];
                                $response = [
                                    'data' => $data
                                ];
                                return json_encode($response, JSON_UNESCAPED_UNICODE);
                            }else{
                                $data = [
                                    'code'=>6,
                                    'msg'=>'系统出现错误,修改订单信息失败,请重试'
                                ];
                                $response = [
                                    'data' => $data
                                ];
                                die(json_encode($response, JSON_UNESCAPED_UNICODE));
                            }
                        }

                    }else{
                        $data = [
                            'code'=>1,
                            'msg'=>'系统出现错误,请重试'
                        ];
                        $response = [
                            'data' => $data
                        ];
                        die(json_encode($response, JSON_UNESCAPED_UNICODE));
                    }
                }
            }else{
                $data = [
                    'code'=>7,
                    'msg'=>'订单已支付,请勿重复支付'
                ];
                $response = [
                    'data' => $data
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
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

    //订单列表
    public function index_reseller_orderList(Request $request){
        $openid1 = $request->input('openid');
        $order_status = $request->input('order_status');
        $key = $openid1;
        $openid = Redis::get($key);
        if($openid){
            if($order_status == 99){
                $re_orderInfo = DB::table('re_order')
                    ->join('mt_user','re_order.uid','=','mt_user.uid')
                    ->where('mt_user.openid',$openid)
                    ->where('order_status','!=',4)
                    ->orderBy('create_time','desc')
                    ->get()->toArray();
                $data = [
                    'code'=>0,
                    'data'=>$re_orderInfo,
                    'msg'=>'数据请求成功'
                ];
                $response = [
                    'data' => $data
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $re_orderInfo = DB::table('re_order')
                    ->join('mt_user','re_order.uid','=','mt_user.uid')
                    ->where('mt_user.openid',$openid)
                    ->where('order_status',$order_status)->orderBy('create_time','desc')->get()->toArray();
                $data = [
                    'code'=>0,
                    'data'=>$re_orderInfo,
                    'msg'=>'数据请求成功'
                ];
                $response = [
                    'data' => $data
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        }else{
            $data = [
                'code'=>1,
                'msg'=>'请先登录'
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //获取物流信息
    public function reseller_order_information(Request $request){
        $openid1 = $request->input('openid');
        $re_order_id = $request->input('re_order_id');
        $key = $openid1;
        $openid = Redis::get($key);
        if($openid){
            $reOrderInfo = DB::table('re_order')
                ->join('mt_logistics','re_order.shipping_type','=','mt_logistics.log_id')
                ->where('re_order_id',$re_order_id)->first();
            $express = new ExpressBird('1609892','d383f272-38fa-4d61-9260-fc6369fa61cb');
//            $tracking_code = "YT4282310249330";
//            $shipping_code = "YTO";
//            $order_code = "";
            $tracking_code = $reOrderInfo->logistics_no;
            $shipping_code = $reOrderInfo->log_code;
            $order_code = $reOrderInfo->re_order_no;
            $info = $express->track($tracking_code, $shipping_code,$order_code); //快递单号 物流公司编号 订单编号(选填)
            $info = json_decode($info);
            $info = json_encode($info,JSON_UNESCAPED_UNICODE);
            $data = [
                'code'=>0,
                'data'=>$info,
                'reOrderInfo'=>$reOrderInfo,
                'msg'=>'数据请求成功'
            ];
            $response = [
                'data' => $data
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $data = [
                'code'=>1,
                'msg'=>'请先登录'
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    //确认收货
    public function reseller_order_Confirm_receipt(Request $request){
        $openid1 = $request->input('openid');
        $re_order_id = $request->input('re_order_id');
        $key = $openid1;
        $openid = Redis::get($key);
        if($openid){
            $userInfo = DB::table('mt_user')->where('openid',$openid)->first();
            $reOrderInfo = DB::table('re_order')->where('re_order_id',$re_order_id)->first();
            $shopInfo = DB::table('mt_shop')->where('shop_id',$reOrderInfo->shop_id)->first();
            $shopUserInfo = DB::table('mt_user')->where('uid',$shopInfo->uid)->first();
            if($userInfo->mt_reseller == 1){
                if($reOrderInfo->order_status !=0){
                    $u_shopInfo = DB::table('mt_shop')->where('uid',$userInfo->a_id)->first(['shop_id']);
                    if($reOrderInfo->shop_id == $u_shopInfo->shop_id){
                        $p_userInfo = DB::table('mt_user')->where('uid',$userInfo->p_id)->first();
                        if($p_userInfo->uid != $p_userInfo->a_id){
                            $a_userInfo = DB::table('mt_user')->where('uid',$p_userInfo->p_id)->first();
                            if($a_userInfo->uid != $a_userInfo->a_id){
                                $re_orderInfoUpdate = DB::table('re_order')->where('re_order_id',$re_order_id)->update(['order_status'=>3,'sign_time' => time()]);
                                $p_userInfoUpdate = DB::table('mt_user')->where('uid',$p_userInfo->uid)->update(['no_reflected'=>$p_userInfo->no_reflected - $reOrderInfo->pay_price*$shopInfo->up_rebate/100,'money'=>$p_userInfo->money + $reOrderInfo->pay_price*$shopInfo->up_rebate/100]);
                                $a_userInfoUpdate = DB::table('mt_user')->where('uid',$a_userInfo->uid)->update(['no_reflected'=>$a_userInfo->no_reflected - $reOrderInfo->pay_price*$shopInfo->indirect_up_rebate/100,'money'=>$a_userInfo->money + $reOrderInfo->pay_price*$shopInfo->indirect_up_rebate/100]);
                                $shopUserInfoUpdate = DB::table('mt_user')->where('uid',$shopInfo->uid)->update(['no_reflected'=>$shopUserInfo->no_reflected - $reOrderInfo->pay_price*(100 - $shopInfo->up_rebate - $shopInfo->indirect_up_rebate)/100,'money'=>$shopUserInfo->money + $reOrderInfo->pay_price*(100 - $shopInfo->up_rebate - $shopInfo->indirect_up_rebate)/100]);
                                if($re_orderInfoUpdate>0 && $p_userInfoUpdate>0 && $a_userInfoUpdate>0 && $shopUserInfoUpdate>0){
                                    $data = [
                                        'code'=>0,
                                        'msg'=>'确认收货成功'
                                    ];
                                    $response = [
                                        'data' => $data
                                    ];
                                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                                }else{
                                    $data = [
                                        'code'=>5,
                                        'msg'=>'系统出现错误,确认收货失败,请重试'
                                    ];
                                    $response = [
                                        'data' => $data
                                    ];
                                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                                }
                            }else{
                                $re_orderInfoUpdate = DB::table('re_order')->where('re_order_id',$re_order_id)->update(['order_status'=>3,'sign_time' => time()]);
                                $p_userInfoUpdate = DB::table('mt_user')->where('uid',$p_userInfo->uid)->update(['no_reflected'=>$p_userInfo->no_reflected - $reOrderInfo->pay_price*$shopInfo->up_rebate/100]);
                                $shopUserInfoUpdate = DB::table('mt_user')->where('uid',$shopInfo->uid)->update(['no_reflected'=>$shopUserInfo->no_reflected - $reOrderInfo->pay_price*(100 - $shopInfo->up_rebate)/100]);
                                if($re_orderInfoUpdate>0 && $p_userInfoUpdate>0 && $shopUserInfoUpdate>0){
                                    $data = [
                                        'code'=>0,
                                        'msg'=>'确认收货成功'
                                    ];
                                    $response = [
                                        'data' => $data
                                    ];
                                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                                }else{
                                    $data = [
                                        'code'=>6,
                                        'msg'=>'系统出现错误,确认收货失败,请重试'
                                    ];
                                    $response = [
                                        'data' => $data
                                    ];
                                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                                }
                            }
                        }else{
                            $re_orderInfoUpdate = DB::table('re_order')->where('re_order_id',$re_order_id)->update(['order_status'=>3,'sign_time' => time()]);
                            $shopUserInfoUpdate = DB::table('mt_user')->where('uid',$shopInfo->uid)->update(['no_reflected'=>$shopUserInfo->no_reflected - $reOrderInfo->pay_price,'money'=>$shopUserInfo->money + $reOrderInfo->pay_price]);
                            if($re_orderInfoUpdate>0 && $shopUserInfoUpdate>0){
                                $data = [
                                    'code'=>0,
                                    'msg'=>'确认收货成功'
                                ];
                                $response = [
                                    'data' => $data
                                ];
                                return json_encode($response, JSON_UNESCAPED_UNICODE);
                            }else{
                                $data = [
                                    'code'=>7,
                                    'msg'=>'系统出现错误,确认收货失败,请重试'
                                ];
                                $response = [
                                    'data' => $data
                                ];
                                die(json_encode($response, JSON_UNESCAPED_UNICODE));
                            }
                        }
                    }else{
                        $re_orderInfoUpdate = DB::table('re_order')->where('re_order_id',$re_order_id)->update(['order_status'=>3,'sign_time' => time()]);
                        $shopUserInfoUpdate = DB::table('mt_user')->where('uid',$shopInfo->uid)->update(['no_reflected'=>$shopUserInfo->no_reflected - $reOrderInfo->pay_price,'money'=>$shopUserInfo->money + $reOrderInfo->pay_price]);
                        if($re_orderInfoUpdate>0 && $shopUserInfoUpdate>0){
                            $data = [
                                'code'=>0,
                                'msg'=>'确认收货成功'
                            ];
                            $response = [
                                'data' => $data
                            ];
                            return json_encode($response, JSON_UNESCAPED_UNICODE);
                        }else{
                            $data = [
                                'code'=>8,
                                'msg'=>'系统出现错误,确认收货失败,请重试'
                            ];
                            $response = [
                                'data' => $data
                            ];
                            die(json_encode($response, JSON_UNESCAPED_UNICODE));
                        }
                    }
                }else{
                    $data = [
                        'code'=>2,
                        'msg'=>'此订单未支付'
                    ];
                    $response = [
                        'data' => $data
                    ];
                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                }
            }else{
                if($reOrderInfo->order_status !=0){
                    $update = [
                        'no_reflected' => $shopUserInfo->no_reflected - $reOrderInfo->pay_price,
                        'money' => $shopUserInfo->money + $reOrderInfo->pay_price,
                    ];
                    $re_orderInfoUpdate = DB::table('re_order')->where('re_order_id',$re_order_id)->update(['order_status'=>3,'sign_time' => time()]);
                    $updateuserInfo = DB::table('mt_user')->where('uid',$shopInfo->uid)->update($update);
                    if($updateuserInfo > 0 && $re_orderInfoUpdate > 0){
                        $data = [
                            'code'=>0,
                            'msg'=>'确认收货成功'
                        ];
                        $response = [
                            'data' => $data
                        ];
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }else{
                        $data = [
                            'code'=>1,
                            'msg'=>'系统出现问题,请重试'
                        ];
                        $response = [
                            'data' => $data
                        ];
                        die(json_encode($response, JSON_UNESCAPED_UNICODE));
                    }
                }else{
                    $data = [
                        'code'=>2,
                        'msg'=>'此订单未支付'
                    ];
                    $response = [
                        'data' => $data
                    ];
                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                }
            }
        }else{
            $data = [
                'code'=>3,
                'msg'=>'请先登录'
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    //定时任务--定时获取物流实时信息
    public function crontab_information(){
        $reOrderInfo = DB::table('re_order')
            ->join('mt_logistics','re_order.shipping_type','=','mt_logistics.log_id')
            ->where('order_status',2)->get()->toArray();
        $data = [];
        foreach ($reOrderInfo as $k=>$v){
            $express = new ExpressBird('1609892','d383f272-38fa-4d61-9260-fc6369fa61cb');
//            $tracking_code = "YT4282310249330";
//            $shipping_code = "YTO";
//            $order_code = "";
            $tracking_code = $v->logistics_no;
            $shipping_code = $v->log_code;
            $order_code = $v->re_order_no;
            $info = $express->track($tracking_code, $shipping_code,$order_code); //快递单号 物流公司编号 订单编号(选填)
            $info = json_decode($info);
//            $info = json_encode($info,JSON_UNESCAPED_UNICODE);
            array_push($data,$info);
        }
        foreach ($data as $val) {
            if($val->State == 3){
                $last_names = array_column($val->Traces,'AcceptTime');
                array_multisort($last_names,SORT_DESC,$val->Traces);
                if(strtotime($val->Traces[0]->AcceptTime)+86400*7 < time()){
                    DB::table('re_order')->where('logistics_no',$val->LogisticCode)->update(['order_status'=>3]);
                }else if(strtotime($val->Traces[0]->AcceptTime)+86400*7 > time()){
                    DB::table('re_order')->where('logistics_no',$val->LogisticCode)->update(['order_status'=>3]);
                }
            }
        }
    }

    //删除订单
    public function reseller_order_delete(Request $request){
        $re_order_id = $request->input('re_order_id');
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
        if($openid){
            $orderInfo = DB::table('re_order')->where('re_order_id',$re_order_id)->first(['order_status']);
            if($orderInfo->order_status == 0 || $orderInfo->order_status == 5){
                $orderDelete = DB::table('re_order')->where(['re_order_id'=>$re_order_id])->delete();
                if($orderDelete){
                    $data = [
                        'code'=>0,
                        'msg'=>'订单删除成功'
                    ];
                    $response = [
                        'data' => $data
                    ];
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }else{
                    $data = [
                        'code'=>1,
                        'msg'=>'订单删除失败'
                    ];
                    $response = [
                        'data' => $data
                    ];
                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                }
            }else{
                $data = [
                    'code'=>2,
                    'msg'=>'只有未付款或者已退款的订单可删除'
                ];
                $response = [
                    'data' => $data
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }
    }

    //我的团队
    public function my_team(Request $request){
        $openid = $request->input('openid');
        $userInfo = DB::table('mt_user')->where('openid',$openid)->first();
        $p_id = $userInfo->p_id;
        $uid = $userInfo->uid;
        $a_id = $userInfo->a_id;

        $son = DB::table('mt_user')->where('p_id',$uid)->get()->toArray();    //子类
        $uInfo = DB::table('mt_user')->where('uid',$uid)->first();
        $parent = DB::table('mt_user')->where('uid',$uInfo->p_id)->get()->toArray();   //父类
        $total_num = DB::table('mt_user')->where('a_id',$a_id)->count();   //总人数
        $start_time=strtotime(date("Y-m-d",time()));    //求今天开始时间
        $tomorrow = $start_time+86400;    //明日开始时间
        $today_new_num = DB::table('mt_user')->where('reseller_time','>',$start_time)->where('reseller_time','<',$tomorrow)->where('p_id',$uid)->count();
        $data = [
            'code'=>0,
            'son'=>$son,
            'parent'=>$parent,
            'uInfo'=>$uInfo,
            'total_num'=>$total_num,
            'today_new_num'=>$today_new_num,
            'msg'=>'数据请求成功'
        ];
        $response = [
            'data' => $data
        ];
        return json_encode($response, JSON_UNESCAPED_UNICODE);


    }

    //获取access_Token
    public function admin_accessToken2(){
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

    //添加分销员
    public function my_team_Add(Request $request){
        $accessToken = $this->admin_accessToken2();
        $openid = $request->input('openid');
        $invite_code = $request->input('invite_code');
        $shop_resellerInfo = DB::table('mt_user')->where('openid',$openid)->first(['p_id','a_id','shop_rand','shop_random_str']);
        $p_id = $shop_resellerInfo->p_id;
        $a_id = $shop_resellerInfo->a_id;
        $shop_rand = $shop_resellerInfo->shop_rand;
        $shop_random_str = $shop_resellerInfo->shop_random_str;
        if($p_id == NULL && $a_id == NULL && $shop_rand == NULL && $shop_random_str == NULL){
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

            $userInfo = DB::table('mt_user')->where('shop_random_str',$invite_code)->first(['uid','a_id']);
            $uid = $userInfo->uid;
            $a_id = $userInfo->a_id;

            $update = [
                'p_id'=>$uid,
                'a_id'=>$a_id,
                'mt_reseller'=>1,
                'shop_rand'=>$img,
                'shop_random_str'=>$scene
            ];
            $updateUserInfo = DB::table('mt_user')->where('openid',$openid)->update($update);
            if($updateUserInfo >=0){
                $data = [
                    'code'=>0,
                    'msg'=>'申请成功'
                ];
                $response = [
                    'data' => $data
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $data = [
                    'code'=>2,
                    'msg'=>'暂无资格成为分销员'
                ];
                $response = [
                    'data' => $data
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }else{
            $data = [
                'code'=>1,
                'msg'=>'系统出现错误。请重试'
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    //个人中心-分销中心
    public function user_reseller_List(Request $request){
        $openid = $request->input('openid');
        $userInfo = DB::table('mt_user')->where('openid',$openid)->first(['wx_headimg','wx_name','uid','shop_random_str','withdrawals_money','withdrawable_money','not_acquired_money']);

        $data = [
            'code'=>0,
            'userInfo'=>$userInfo,
            'msg'=>'数据请求成功'
        ];
        $response = [
            'data' => $data
        ];
        return json_encode($response, JSON_UNESCAPED_UNICODE);

    }

    //分销排行
    public function reseller_my_Profit(Request $request){
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
        $userInfo = DB::table('mt_user')->where('openid',$openid)->first(['uid']);
        $uid = $userInfo->uid;
        $my_Profit = DB::table('mt_user')->where('p_id',$uid)->orderBy('my_p_profit','desc')->get(['uid','wx_name','my_p_profit'])->toArray();
        $data = [
            'code'=>0,
            'my_Profit'=>$my_Profit,
            'msg'=>'数据请求成功'
        ];
        $response = [
            'data' => $data
        ];
        return json_encode($response, JSON_UNESCAPED_UNICODE);

    }

    //用户默认收货地址
    public function reseller_user_address(Request $request){
        $openid = $request->input('openid');
        $userInfo = DB::table('mt_user')->where('openid',$openid)->first(['uid']);
        $uid = $userInfo->uid;
        $user_addressInfo = DB::table('mt_address')->where(['uid'=>$uid,'is_default'=>1])->first();
        $data = [
            'code'=>0,
            'user_addressInfo'=>$user_addressInfo,
            'msg'=>'数据请求成功'
        ];
        $response = [
            'data' => $data
        ];
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    //用户所有收货地址
    public function reseller_user_AddressDetail(Request $request){
        $openid1 = $request->input('openid');
        $address_id = $request->input('address_id');
        $key = $openid1;
        $openid = Redis::get($key);
        if($openid){
            $user_addressInfo = DB::table('mt_address')->where(['id'=>$address_id])->first();
            $data = [
                'code'=>0,
                'user_addressInfo'=>$user_addressInfo,
                'msg'=>'数据请求成功'
            ];
            $response = [
                'data' => $data
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $data = [
                'code'=>1,
                'msg'=>'请先登录'
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }










}
