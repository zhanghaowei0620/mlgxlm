<?php

namespace App\Http\Controllers\Goods;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use App\Models\ShopModel;

class GoodsController extends Controller
{
    //根据导航栏父级分类获取子级分类及店铺信息
    public function father_type_shop(Request $request){
        $f_type_id = $request->input('f_type_id');
        $s_type_id = $request->input('s_type_id');
        $g_s_type_id = $request->input('g_s_type_id');
//        $g_s_type_id = 8;
        if($f_type_id){
            $type = DB::table('mt_type')->where('t_id',$f_type_id)->first();        //获取分类数据
            //var_dump($type);exit;
            $p_id = $type->p_id;
            if($p_id==0){    //判断分类是否为最大级
                $t_id = $type->t_id;
//                var_dump($t_id);exit;
                $s_type = DB::table('mt_type')->where('p_id',$t_id)->get()->toArray();    //获取父级分类下所有的子级分类
                //var_dump($s_type);exit;
                $is_hot = DB::table('mt_type')->where('is_hot',1)->get();    //热门项目
//                var_dump($is_hot);exit;
                $recommend_picture = DB::table('mt_goods')->where('is_recommend',1)->limit('4')->get(['goods_id','picture']);    //推荐位图片
                if($s_type_id == NULL){
                    if($g_s_type_id == NULL){
                        $jingxuan = DB::table('mt_goods')      //精选商品
                            ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
                            ->get(['mt_shop.shop_id','shop_name','shop_address_provice','shop_address_city','shop_address_area','shop_score','shop_desc','shop_label','shop_logo','goods_id','goods_name','price','picture']);
                        //var_dump($jingxuan);exit;
                    }else{
                        $where = [
                            'mt_goods.t_id'=>$g_s_type_id,
                        ];
                        $jingxuan = DB::table('mt_goods')      //精选商品
                            ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
                            ->where($where)->get(['mt_shop.shop_id','shop_name','shop_address_provice','shop_address_city','shop_address_area','shop_score','shop_desc','shop_label','shop_logo','goods_id','goods_name','price','picture']);
//                        var_dump($jingxuan);exit;
                    }
                }else{
                    if($g_s_type_id == NULL){
                        $where = [
                            'mt_goods.t_id'=>$s_type_id,
                        ];
                        $jingxuan = DB::table('mt_goods')      //精选商品
                        ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
                            ->where($where)->get(['mt_shop.shop_id','shop_name','shop_address_provice','shop_address_city','shop_address_area','shop_score','shop_desc','shop_label','shop_logo','goods_id','goods_name','price','picture']);
                    }else{
                        $where = [
                            'mt_goods.t_id'=>$g_s_type_id,
                        ];
                        $jingxuan = DB::table('mt_goods')      //精选商品
                        ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
                            ->where($where)->get(['mt_shop.shop_id','shop_name','shop_address_provice','shop_address_city','shop_address_area','shop_score','shop_desc','shop_label','shop_logo','goods_id','goods_name','price','picture']);
                    }
                }
                $data = [
                    'code'             =>       '0',
                    's_type'            =>      $s_type,
                    'hot'               =>      $is_hot,
                    'recommend_picture' =>      $recommend_picture,
                    'select'            =>      $jingxuan
                ];
                $response = [
                   'data'=>$data
                ];
                //var_dump($response);exit;
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $data = DB::table('mt_goods')->where('t_id',$f_type_id)->get(['goods_id','goods_name'])->toArray();    //二级分类页面 导航栏
//                var_dump($data);
                $goods_id = $request->input('goods_id');
                $goods_id = 5;
                if($goods_id == NULL){
                    $recommend_picture = DB::table('mt_goods')->where('t_id',$f_type_id)->limit('4')->get(['goods_id','picture']);    //商品轮播图
//                    var_dump($recommend_picture);exit;
                    $beauty = DB::table('mt_shop')          //美容院
                        ->join('mt_goods','mt_shop.shop_id','=','mt_goods.shop_id')
                        ->where('mt_goods.t_id',$f_type_id)->get(['goods_id','goods_name','goods_gd_num','mt_shop.shop_name','mt_shop.shop_id','mt_shop.shop_address_provice','mt_shop.shop_address_city','mt_shop.shop_address_area','mt_shop.shop_score','shop_label']);
                    //var_dump($beauty);exit;
                    $caseInfo = DB::table('mt_case')      //案例
                        ->join('mt_goods','mt_case.goods_id','=','mt_goods.goods_id')
                        ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
                        ->where('mt_goods.t_id',$f_type_id)->get(['case_id','case_front','case_after','case_trouble','goods_name','shop_name']);
                    //var_dump($caseInfo);exit;
                    $data = [
                        'code' => '0',
                        'type' => $data,
                        'recommend_picture' => $recommend_picture,
                        'beauty' => $beauty,
                        'caseInfo' => $caseInfo
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    //var_dump($response);exit;
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    $recommend_picture = DB::table('mt_goods')->where('goods_id',$goods_id)->first(['goods_id','picture']);    //商品轮播图
//                    var_dump($recommend_picture);exit;
                    $beauty = DB::table('mt_shop')          //美容院
                    ->join('mt_goods','mt_shop.shop_id','=','mt_goods.shop_id')
                        ->where('mt_goods.goods_id',$goods_id)->get(['goods_id','goods_name','goods_gd_num','mt_shop.shop_name','mt_shop.shop_id','mt_shop.shop_address_provice','mt_shop.shop_address_city','mt_shop.shop_address_area','mt_shop.shop_score','shop_label']);
                    //var_dump($beauty);exit;
                    $caseInfo = DB::table('mt_case')      //案例
                    ->join('mt_goods','mt_case.goods_id','=','mt_goods.goods_id')
                        ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
                        ->where('mt_goods.goods_id',$goods_id)->get(['case_id','case_front','case_after','case_trouble','goods_name','shop_name']);
//                    var_dump($caseInfo);exit;
                    $data = [
                        'code' => '0',
                        'type' => $data,
                        'recommend_picture' => $recommend_picture,
                        'beauty' => $beauty,
                        'caseInfo' => $caseInfo
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    //var_dump($response);exit;
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }
            }
        }else{
            $data = [
                'code'=>'1',
                'msg'=>'接口出现错误,正在维护中'
            ];
            $response = [
                'data'=>$data
            ];
            //var_dump($response);exit;
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //根据导航栏子级分类获取店铺 根据热门项目分类id获取店铺
//    public function type_shop(Request $request){
//        $type_id = $request->input('type_id');
//        //$page_num = $request->input('page_num');  //当前展示页数
//        $type_id = 7;
//        if($type_id){
//            $shop_type = DB::table('mt_shop')->where('t_id',$type_id)->paginate(7);
//            //var_dump($shop_type);exit;
//            $response = [
//                'error'=>'0',
//                'shop_goodsInfo'=>$shop_type
//            ];
//            return json_encode($response,JSON_UNESCAPED_UNICODE);
//        }else{
//            $response = [
//                'error'=>'1',
//                'msg'=>'暂未开通该类型店铺'
//            ];
//            die(json_encode($response,JSON_UNESCAPED_UNICODE));
//        }
//    }

    //点击店铺获取店铺详情信息及店铺下所有的商品
    public function shop_goods(Request $request)
    {
        $shop_id = $request->input('shop_id');
//        $shop_id = 2;
//        $shop_goodsInfo = DB::table('mt_goods')
//            ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
//            ->where('mt_goods.shop_id',$shop_id)->paginate(7);
        $shopInfo=DB ::table('mt_shop')
            ->where(['shop_id'=>$shop_id])
            ->join('mt_type','mt_type.t_id','=','mt_shop.t_id')
            ->first(['shop_id','shop_name','shop_phone','shop_desc','shop_address_detail','shop_score','shop_img','mt_type.t_name','shop_logo','shop_bus','shop_service']);
//        var_dump($shopInfo);die;
        $shop_coupon=DB::table('mt_coupon')
            ->where(['shop_id'=>$shop_id])
            ->limit(2)
            ->get(['coupon_redouction','discount','coupon_id','coupon_type','discount','coupon_redouction','coupon_price','coupon_num']);
//                var_dump($shop_coupon);die;
        $goods_shop=DB::table('mt_goods')
            ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
            ->where(['mt_shop.shop_id'=>$shop_id])
            ->select(['mt_goods.goods_name','mt_goods.goods_id','mt_goods.market_price','mt_goods.picture','mt_goods.goods_gd_num','shop_name','mt_shop.shop_address_provice','mt_shop.shop_address_city','mt_shop.shop_address_area'])
            ->paginate(4);
//        var_dump($goods_shop);die;
        $goods_list=DB::table('mt_goods')
            ->where(['mt_shop.shop_id'=>$shop_id])
            ->join('mt_shop','mt_shop.shop_id','=','mt_goods.goods_id')
            ->limit(4)
            ->get();
//       var_dump($caseInfo);die;
        if($shopInfo){
            $data=[
//                'shop_goodsInfo'=>$shop_goodsInfo,
                'shopInfo'=>$shopInfo,
                'shop_coupon'=>$shop_coupon,
                'goods_shop'=>$goods_shop,
                'goods_list'=>$goods_list,
                'code'=>'0'
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data1=[
                'code'=>'1',
                'msg'=>'该店铺下暂未任何商品'
            ];
            $response = [
                'data'=>$data1
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }
    //案列
    public function caselist(Request $request)
    {
        $shop_id=$request->input('shop_id');
        $caseInfo = DB::table('mt_case')      //案例
            ->join('mt_goods','mt_case.goods_id','=','mt_goods.goods_id')
            ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
            ->where(['mt_case.shop_id'=>$shop_id])
            ->get(['case_id','case_front','case_after','case_trouble','goods_name','shop_name']);
//        var_dump($caseInfo);die;
        if($caseInfo){
            $data=[
                'code'=>0,
                'caseInfo'=>$caseInfo,
                'msg'=>'案例展示成功'
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data1=[
                'code'=>'1',
                'msg'=>'案例展示失败'
            ];
            $response = [
                'data'=>$data1
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }
    //优惠卷列表
    public function couponlist(Request $request)
    {
        $shop_id=$request ->input('shop_id');
        $couponInfo = DB::table('mt_goods')
            ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
            ->join('mt_coupon','mt_coupon.goods_id','=','mt_goods.goods_id')
            ->where(['mt_shop.shop_id'=>$shop_id])
            ->get(['mt_coupon.coupon_id','mt_coupon.coupon_id','mt_goods.goods_id','mt_shop.shop_id','mt_shop.shop_name','mt_coupon.discount','mt_shop.shop_id','mt_coupon.coupon_price','mt_coupon.coupon_redouction','mt_coupon.create_time','mt_coupon.expiration','mt_goods.picture','mt_coupon.coupon_type','mt_goods.goods_name','mt_coupon.discount','mt_goods.picture']);
//        var_dump($couponInfo);
        if($couponInfo){
            $data = [
                'code'=>0,
                'couponInfo'=>$couponInfo
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data = [
                'code'=>1,
                'msg'=>'暂时没有商品优惠券'
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //点击商品获取商品详情+店铺详情信息
    public function goodsinfo(Request $request){
        $goods_id = $request->input('goods_id');
//        var_dump($goods_id);die;
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
//        $openid="o9VUc5MWyq5GgW3kF_90NnrQkBH8";
        $infono=DB::table('mt_user')->where(['openid'=>$openid])->first();
        $uid=$infono->uid;
//        var_dump($uid);die;
        $data1=DB::table('mt_goods')
            ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
//            ->join('mt_coupon','mt_coupon.goods_id','=','mt_goods.goods_id')
            ->where(['mt_goods.goods_id'=>$goods_id])
            ->first();
//        var_dump($data1);exit;
        $shopsetInfo=DB::table('mt_shop')
            ->join('admin_user','mt_shop.shop_id','=','admin_user.shop_id')
            ->join('mt_goods','mt_goods.shop_id','=','mt_shop.shop_id')
            ->where(['mt_goods.goods_id'=>$goods_id])
            ->get(['shop_name','admin_tel','shop_address_detail','goods_name','goods_effect','goods_duration','goods_process','goods_overdue_time','shop_bus','goods_appointment','goods_use_rule','shop_img','shop_logo','shop_star','mt_goods.prople']);
        $coupon_lists=DB::table('mt_goods')->where(['goods_id'=>$goods_id,])->first(['coupon_type','coupon_redouction','coupon_price','is_member_discount']);
//        var_dump($coupon_lists);die;
        $goods_list=DB::table('mt_goods')
            ->where(['mt_shop.shop_id'=>$data1->shop_id])
            ->join('mt_shop','mt_shop.shop_id','=','mt_goods.goods_id')
            ->limit(4)
            ->get();
        $assesslist=DB::table('mt_assess')
            ->join('mt_user','mt_assess.uid','=','mt_user.uid')
            ->where(['goods_id'=>$goods_id])
            ->limit(2)
            ->get(['assess_text','mt_user.wx_name','mt_user.wx_headimg']);
//        var_dump($assesslist);die;
        $reconmend_shop = DB::table('mt_goods')
            ->where(['mt_shop.shop_id'=>$data1->shop_id,'p_id'=>2])
            ->join('mt_type','mt_goods.t_id','=','mt_type.t_id')
            ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
            ->limit(4)->get(['t_name','goods_name','picture','goods_gd_num','price','shop_address_provice','shop_address_city','shop_address_area','goods_id','shop_name','limited_price','promotion_price','promotion_type']);
//        var_dump($reconmend_shop);exit;
        if($data1==NULL){
            $response = [
                'code'=>'1',
                'msg'=>'商品不存在'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $openid1 = $request->input('openid');
            $key = $openid1;
            $openid = Redis::get($key);
//            $openid="o9VUc5MWyq5GgW3kF_90NnrQkBH8";
//            var_dump($openid);exit;
            $userInfo = DB::table('mt_user')->where('openid',$openid)->first();
//            var_dump($userInfo);exit;
            $uid = $userInfo->uid;
            $where = [
                'uid'=>$uid,
                'goods_id'=>$goods_id
            ];
            $historyInfo = DB::table('mt_history')->where($where)->get()->toArray();
            //var_dump($historyInfo);exit;
            if($historyInfo){
                $update = [
                    'create_time'=>time()
                ];
                $updateInfo = DB::table('mt_history')->update($update);
            }else{
                $data = [
                    'uid'=>$uid,
                    'goods_id'=>$goods_id,
                    'create_time'=>time()
                ];
                DB::table('mt_history')->insertGetId($data);
            }
            $data2=[
                'code'=>'0',
                'goodsInfo'=>$data1,
                'shop_set'=>$shopsetInfo,
                'goods_list'=>$goods_list,
                'coupon_lists'=>$coupon_lists,
                'assesslist'=>$assesslist,
                'recommend_shop'=>$reconmend_shop
            ];
            $response = [
                'data'=>$data2
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }

    }

    //点击加入购物车
    public function add_cart(Request $request){
        $goods_id = $request->input('goods_id');
//        $ip = $_SERVER['SERVER_ADDR'];
//        $key = 'openid'.$ip;
//        $openid = Redis::get($key);
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
//        $openid="o9VUc5AOsdEdOBeUAw4TdYg-F-dM";
        if($openid){
            $buy_num = $request->input('buy_num');
            $user_info = DB::table('mt_user')->where('openid',$openid)->first();
//            var_dump($user_info);die;
            $uid = $user_info->uid;
//            $buy_num = 1;
//            $goods_id = 7;
            $where = [
                'goods_id'=>$goods_id,
                'collection_cart'=>0
            ];
            $goods_cart = DB::table('mt_cart')->where($where)->get()->toArray();
//            var_dump($goods_cart);exit;
            if($goods_cart){
//                $update = [
//                    'buy_num'=>$goods_cart[0]->buy_num+$buy_num
//                ];
//                $update_buynum = DB::table('mt_cart')->where('goods_id',$goods_id)->update($update);
                $cart_is=DB::table('mt_cart')->where(['goods_id'=>$goods_id])->get();
//                var_dump($cart_is);exit;
                if($cart_is==true){
                    $aa=[
                        'code'=>'0',
                        'msg'=>'您的购物车已有此商品'
                    ];
                    $response = [
                        'data'=>$aa
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                } else{
                    $data1=[
                        'code'=>'1',
                        'msg'=>'加入购物车失败'
                    ];
                    $response = [
                        'data'=>$data1
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }else{
                $goodsInfo = DB::table('mt_shop')
                    ->join('mt_goods','mt_shop.shop_id','=','mt_shop.shop_id')
                    ->where('mt_goods.goods_id',$goods_id)
                    ->first();
//                var_dump($goodsInfo);exit;
                $data = [
                    'goods_id'=>$goodsInfo->goods_id,
                    'shop_id'=>$goodsInfo->shop_id,
                    'openid'=>$openid,
                    'shop_name'=>$goodsInfo->shop_name,
                    'goods_name'=>$goodsInfo->goods_name,
                    'price'=>$goodsInfo->price,
                    'buy_num'=>$buy_num,
                    'create_time'=>time(),
                    'collection_cart'=>0,
                    'uid'=>$uid
                ];
//                var_dump($data);exit;
                $add_cart = DB::table('mt_cart')->insertGetId($data);
                if($add_cart){
                    $aa=[
                        'code'=>'0',
                        'msg'=>'加入购物车成功'
                    ];
                    $response = [
                        'data'=>$aa
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    $data1=[
                        'code'=>'1',
                        'msg'=>'加入购物车失败'
                    ];
                    $response = [
                        'data'=>$data1
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }
        }else{
            $response = [
                'code'=>'2',
                'msg'=>'请先登录'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }

    //获取购物车列表
    public function cartList(Request $request){
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
        $where = [
            'mt_cart.openid'=>$openid,
//            'collection_cart'=>0
        ];
        $cartInfo = DB::table('mt_cart')
            ->where($where)
            ->get()->toArray();

        if($cartInfo){
            $data=[
                'code'=>'0',
                'cartInfo'=>$cartInfo
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $datainfo=[

            ];
            $data=[
                'code'=>0,
                'msg'=>'购物车暂无数据，快去添加商品吧',
                'cartInfo'=>$datainfo
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //使用分享币点击购买服务
    public function moneybuy(Request $request)
    {
        $openid1 = $request->input('openid');
        $pt_id=$request->input('pt_id');
        $key = $openid1;
        $openid = Redis::get($key);
//        $method_type=$request->input('method_type'); //1为普通购买，2.拼团购买，3.优惠券购买，4限时抢购买
//        $openid='o9VUc5KN78P_jViUQnGjica4GIQs';
        $order_id=$request->input('order_id');
        $price=$request->input('price');
        $data=DB::table('mt_user')
//            ->join('mt_order_detail','mt_user.uid','=','mt_order_detail.uid')
            ->where(['openid'=>$openid])
            ->first();
        $uid=$data->uid;
        $money=$data->money-$price;
        $infos=DB::table('mt_order')
            ->join('mt_order_detail','mt_order_detail.order_id','=','mt_order.order_id')
            ->where(['mt_order_detail.order_id'=>$order_id])->first();
//        var_dump($infos);die;
        $order_info_add=DB::table('mt_order')
            ->where(['order_id'=>$order_id])->first();
//        var_dump($order_info_add);die;
        if($infos->order_status!=0){
            $data=[
                'code'=>0,
                'msg'=>'此订单已被支付，请勿重新支付'
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
        $inerdb=DB::table('mt_order')->where(['order_id'=>$order_id])->first();
        $a=$inerdb->order_method;  //0为普通购买，1.拼团购买，2.优惠券购买，3限时抢购买
//        var_dump($a);die;
        if($a ==0){
//            echo 1111;die;
            $mt_order_detail_add=DB::table('mt_order_detail')->where(['order_id'=>$order_id])->first();
            $infoadd=DB::table('mt_order')
                ->join('mt_order_detail','mt_order_detail.order_id','=','mt_order.order_id')
                ->where(['mt_order.uid'=>$uid,'goods_id'=>$mt_order_detail_add->goods_id])->first();
            $money=$data->money-$infoadd->total_price;
            $updates_info=[
                'money'=>$money,
            ];
            $update_order=DB::table('mt_user')
                ->where(['mt_user.uid'=>$uid])
                ->update($updates_info);
            $infosaa=[
                'order_status'=>1,
                'order_pay'=>0
            ];
            $inerttofo=DB::table('mt_order')->where(['uid'=>$uid,'order_id'=>$order_id])->update($infosaa);
            $update_orderss=DB::table('mt_order_detail')->where(['uid'=>$uid,'order_id'=>$order_id])->update(['order_status'=>1]);
            if($update_order){
                $data=[
                    'code'=>0,
                    'msg'=>'您已普通支付成功'
                ];
                $response=[
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $data=[
                    'code'=>1,
                    'msg'=>'支付失败,请重试'
                ];
                $response=[
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
        }else if($a == 1){
//            $mt_order_detail_add=DB::table('mt_order_detail')->where(['uid'=>$uid,'order_id'=>$order_id])->first();
//            $goods_price=DB::table('mt_goods')->where(['goods_id'=>$mt_order_detail_add->goods_id])->first();
//                $moey_infos=$data->money - $goods_price->promotion_price;
//                $money_upup=[
//                  'money'=> $moey_infos
//                ];
//                $updateinfos=DB::table('mt_user')->where(['uid'=>$uid])->update($money_upup);
                //添加拼团团队信息
//                if($infos->has_pt_id==0){
//                    $data_order = [
//                        'goods_id'=> $infos->goods_id,
//                        'shop_id'=> $infos->shop_id,
//                        'uid'=>$data->uid,
//                        'pt_team'=>$data->uid,
//                        'pt_order_id' =>$order_id,
//                        'pt_start_time'=>time(),
//                        'pt_state'=>0,
//                        'pt_sum'=>1,
//                    ];
//                    $infodata =DB::table('mt_pt_list')->insert($data_order);
//                }else{
//                    $data2=DB::table("mt_pt_list")->where("pt_id",$infos->has_pt_id)->first();
//                    if($data2){
//                        $data_order = [
//                            'goods_id'=> $infos->goods_id,
//                            'shop_id'=>  $infos->shop_id,
//                            'pt_team'=>$data2->pt_team.','.$data->uid,
//                            'pt_order_id' =>$data2->pt_order_id.','.$order_id,
//                            'pt_start_time'=>time(),
//                            'pt_state'=>0,
//                            'pt_sum'=>$data2->pt_sum+1,
//                        ];
//                        $infodata = DB::table('mt_pt_list')->where('pt_id',$infos->has_pt_id)->update($data_order);
//                        //判断该订单是否已经完成
//                        $sss = DB::table('mt_pt_list')
//                            ->join('mt_goods','mt_pt_list.goods_id','=','mt_goods.goods_id')
//                            ->where('mt_pt_list.pt_id',$infos->has_pt_id)
//                            ->first(['mt_pt_list.pt_sum','mt_goods.promotion_prople']);
//                        if($sss->pt_sum == $sss->promotion_prople){
//                            $data_up = [
//                                'pt_state'=> 1,
//                            ];
//                            $res = DB::table('mt_pt_list')->where('pt_id',$pt_id)->update($data_up);
//                        }
//                    }else{
//                        $data=[
//                            'code'=>'0',
//                            'msg'=>'该团队不存在',
//                            'order_id'=>$order_id,
//                        ];
//                        $response = [
//                            'data'=>$data
//                        ];
//                        return json_encode($response,JSON_UNESCAPED_UNICODE);
//                    }
//
//                }
//
//                $ress=DB::table('mt_goods')->where("goods_id",$infos->goods_id)->first();
//                $ress_inser=[
//                    'pt_num_all'=> $ress->pt_num_all+1,
//                ];
//                $ressad=DB::table('mt_goods')->where("goods_id",$infos->goods_id)->update($ress_inser);
//                $data=[
//                    'code'=>0,
//                    'msg'=>'支付成功,并完成拼团'
//                ];
//                $response = [
//                    'data'=>$data
//                ];
//                return json_encode($response,JSON_UNESCAPED_UNICODE);

            //伪拼团
            $mt_order_detail_add1=DB::table('mt_order_detail')->where(['order_id'=>$order_id])->first();
//            var_dump($mt_order_detail_add1);die;
            if($mt_order_detail_add1){
                $mt_goods_detail=DB::table('mt_goods')->where(['goods_id'=>$mt_order_detail_add1->goods_id])->first();
//                var_dump($mt_goods_detail);die;
                $money_add_op=$data->money - $mt_goods_detail->promotion_price;
                $updas_add=[
                    'money'=>$money_add_op
                ];
                $user_money_add=DB::table('mt_user')->where(['uid'=>$uid])->update($updas_add);
                $updateinfo1=[
                    'pay_price'=>$mt_goods_detail->promotion_price,
                    'order_status'=>1
                ];
                $sqlupdate1=DB::table('mt_order')->where(['order_id'=>$order_id])->update($updateinfo1);
                $update_info1=[
                    'order_status'=>1,
                    'price'=>$mt_goods_detail->promotion_price
                ];
                $detail_order1=DB::table('mt_order_detail')->where(['order_id'=>$order_id])->update($update_info1);
                if($user_money_add){
                    $data=[
                        'code'=>0,
                        'msg'=>'拼团价支付成功'
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    $data=[
                        'code'=>1,
                        'msg'=>'拼团价支付失败'
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }
            }else{
                $data=[
                    'code'=>1,
                    'msg'=>'您的支付出现问题,请重新尝试生成订单并支付'
                ];
                $response = [
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
        }else if ($a == 2){
            $coupon_lists=DB::table('mt_coupon')->where(['uid'=>$uid])->first();
            $aaa=DB::table('mt_order_detail')->where(['order_id'=>$order_id])->get(['goods_id']);
//            var_dump($aaa);die;
            $mt_order_detail_add=DB::table('mt_order_detail')->where(['uid'=>$uid])->first();
            $goods_price=DB::table('mt_goods')->where(['goods_id'=>$mt_order_detail_add->goods_id])->first();
            if($coupon_lists->coupon_type ==0){                 //coupon_type判断0为满减   1 为折扣
                if($coupon_lists->coupon_redouction  >  $goods_price->price){
                    $money_lists=$mt_order_detail_add->price - $coupon_lists->coupon_price;
                    $moenfo=$data->money - $money_lists;
//                    var_dump($moenfo);die;
                    $user_money_add=[
                        'money'=>$moenfo
                    ];
                    $user_money=DB::table('mt_user')->where(['uid'=>$uid])->update($user_money_add);
                    $updateinfo=[
                      'pay_price'=>$money_lists,
                        'order_status'=>1
                    ];
                    $sqlupdate=DB::table('mt_order')->where(['order_id'=>$order_id])->update($updateinfo);
                    $update_info=[
                      'order_status'=>1,
                      'price'=>$money_lists
                    ];
                    $detail_order=DB::table('mt_order_detail')->where(['order_id'=>$order_id])->update($update_info);
                    if($detail_order){
                        $data=[
                            'code'=>0,
                            'msg'=>'优惠卷支付成功'
                        ];
                        $response = [
                            'data'=>$data
                        ];
                        return json_encode($response,JSON_UNESCAPED_UNICODE);
                    }else{
                        $data=[
                            'code'=>1,
                            'msg'=>'优惠支付失败'
                        ];
                        $response = [
                            'data'=>$data
                        ];
                        return json_encode($response,JSON_UNESCAPED_UNICODE);
                    }
                }
            }else if ($coupon_lists->coupon_type == 1){
                $address=$goods_price->price*($coupon_lists->discount/10);
                $address1=[
                        'pay_price'=> $address,
                        'order_status'=>1
                    ];
                $moenfo=$mt_order_detail_add->price - $address;
                $user_money_add=[
                    'money'=>$moenfo
                ];
                $user_money=DB::table('mt_user')->where(['uid'=>$uid])->update($user_money_add);
                $sqlupdate1=DB::table('mt_order')->where(['uid'=>$uid])->update($updateinfo);
                $update_info1=[
                    'order_status'=>1,
                    'price'=>$address
                ];
                $detail_order1=DB::table('mt_order_detail')->where(['uid'=>$uid])->update($update_info);
                if($detail_order1){
                    $data=[
                        'code'=>0,
                        'msg'=>'优惠卷支付成功'
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    $data=[
                        'code'=>1,
                        'msg'=>'优惠支付失败'
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }
            }



        }else if ($a == 3){

        }else if ($a == 4){

        }





    }

    //购物车删除
    public function cart_delete(Request $request){
//        $ip = $_SERVER['SERVER_ADDR'];
//        $key = 'openid'.$ip;
//        $openid = Redis::get($key);
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
        $cart_id=$request->input('cart_id');
        $where=[
            'cart_id'=>$cart_id,
            'openid'=>$openid
        ];
        $data=DB::table('mt_cart')->where($where)->delete();
        if($data){
            $data1=[
              'data'=>$data
            ];
            $response=[
                'code'=>0,
                'data'=>$data1,
                'msg'=>'删除成功'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $data1=[
                'code'=>1,
                'msg'=>'删除失败'
            ];
            $response=[
                'data'=>$data1
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }
    //点击加入收藏-商品
    public function  add_collection(Request $request){
        $goods_id = $request->input('goods_id');
//        $ip = $_SERVER['SERVER_ADDR'];
//        $key = 'openid'.$ip;
//        $openid = Redis::get($key);
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
//        $openid="o9VUc5MWyq5GgW3kF_90NnrQkBH8";
        if($openid){
            $user_info = DB::table('mt_user')->where('openid',$openid)->first();
            $uid = $user_info->uid;
            //        $buy_num = 1;
            $where = [
                'goods_id'=>$goods_id,
//                'collection'=>1
            'collection_info'=>1
            ];
            $goods_cart = DB::table('mt_collection_goods')->where($where)->get()->toArray();
            //var_dump($goods_cart);exit;
            if($goods_cart){
                $response = [
                    'code'=>'0',
                    'msg'=>'该商品已在您的收藏夹中'
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $goodsInfo = DB::table('mt_shop')
                    ->join('mt_goods','mt_shop.shop_id','=','mt_shop.shop_id')
                    ->where('mt_goods.goods_id',$goods_id)
                    ->first();
//                var_dump($goodsInfo);exit;
                $data = [
                    'goods_id'=>$goodsInfo->goods_id,
                    'shop_id'=>$goodsInfo->shop_id,
                    'openid'=>$openid,
                    'shop_name'=>$goodsInfo->shop_name,
                    'goods_name'=>$goodsInfo->goods_name,
                    'price'=>$goodsInfo->price,
                    'create_time'=>time(),
//                    'collection'=>1,
                    'collection_info'=>1,
                    'uid'=>$uid,
                    'openid'=>$openid
                ];
//                var_dump($data);exit;
                $add_cart = DB::table('mt_collection_goods')->insertGetId($data);
//                var_dump($add_cart);die;
                if($add_cart){
                    $datainfo=DB::table('mt_cart')
                        ->where(['goods_id'=>$goods_id])
                        ->first();
                    if($datainfo){
                        $infos=DB::table('mt_cart')
                            ->where(['goods_id'=>$goods_id])
                            ->update(['collection_cart'=>1]);
                    }
                    $data1=[
                        'code'=>'0',
                        'msg'=>'加入收藏成功'
                    ];
                    $response = [
                        'data'=>$data1
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    $data1=[
                        'code'=>'1',
                        'msg'=>'加入收藏失败'
                    ];
                    $response = [
                        'data'=>$data1
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }
        }else{
            $info=[
                'code'=>'2',
                'msg'=>'请先去登录'
            ];
            $response = [
                'data'=>$info
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }

    //收藏列表-商品
    public function collection_list(Request $request){
//        $ip = $_SERVER['SERVER_ADDR'];
//        $key = 'openid'.$ip;
//        $openid = Redis::get($key);
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
//        $openid='o9VUc5MWyq5GgW3kF_90NnrQkBH8';
        if($openid){
            $where = [
                'openid'=>$openid,
//                'collection'=>1
            ];
            $cartInfo = DB::table('mt_collection_goods')
                ->join('mt_shop','mt_shop.shop_id','=','mt_collection_goods.shop_id')
                ->where($where)->select()->paginate(10);
//            var_dump($cartInfo);exit;
            if($cartInfo){
                $data=[
                    'code'=>'0',
                    'cartInfo'=>$cartInfo
                ];
                $response = [
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $data1=[
                    'code'=>'1',
                    'msg'=>'收藏夹暂无数据，快去添加商品吧'
                ];
                $response = [
                    'data'=>$data1
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $data1=[
                'code'=>'2',
                'msg'=>'请先登录'
            ];
            $response = [
                'data'=>$data1
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //收藏商品删除（取消收藏）
    public function collectiondele(Request $request)
    {
        $goods_id=$request->input('goods_id');
//        $cart_id=$request->input('cart_id');
        $aa=[
          'goods_id'=>$goods_id,
//            'collection'=>1
        ];
//        var_dump($aa);die;

        $data=DB::table('mt_collection_goods')
            ->where($aa)
            ->delete();
//        var_dump($data);die;
        if($data){
            $data1=[
              'code'=>0,
              'msg'=>'删除成功'
            ];
            $response=[
                'data'=>$data1
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data1=[
                'code'=>'1',
                'msg'=>'此件商品没有被收藏，无法删除'
            ];
            $response = [
                'data'=>$data1
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }
    //查询店铺是否收藏
    public function collectionaddd(Request $request)
    {
        $shop_id = $request->input('shop_id');
        $where=[
          'shop_id'=>$shop_id,
        ];
        $data=DB::table('mt_shop_collection')
            ->where($where)
            ->first();
        if($data){
            $data1=[
              'code'=>0,
              'msg'=>'店铺已收藏'
            ];
            $response=[
                'data'=>$data1
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data1=[
                'code'=>0,
                'msg'=>'此店铺没有被收藏，快去收藏吧'
            ];
            $response = [
                'data'=>$data1
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //查询商品是否收藏
    public function collectionshop(Request $request)
    {
        $goods_id = $request->input('goods_id');
        $where=[
            'goods_id'=>$goods_id,
            'collection_info'=>1
        ];
        $data=DB::table('mt_collection_goods')
            ->where($where)
            ->first();
        if($data){
            $data1=[
                'code'=>0,
                'msg'=>'商品已收藏'
            ];
            $response=[
                'data'=>$data1
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data1=[
                'code'=>0,
                'msg'=>'此商品没有被收藏，快去收藏吧'
            ];
            $response = [
                'data'=>$data1
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }
    //店铺收藏
    public function shop_collection(Request $request){
        $shop_id = $request->input('shop_id');
//        $ip = $_SERVER['SERVER_ADDR'];
//        $key = 'openid'.$ip;
//        $openid = Redis::get($key);
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
//        $openid='o9VUc5MWyq5GgW3kF_90NnrQkBH8';
        if($openid){
            $user_info = DB::table('mt_user')->where('openid',$openid)->first();
            $uid = $user_info->uid;
            $shop_infos=DB ::table('mt_shop')->where(['shop_id'=>$shop_id])->first();
            $t_id=$shop_infos->t_id;
            //$shop_id = 1;
            $where = [
                'shop_id'=>$shop_id,
                'uid'=>$uid
            ];
            $shop_collection = DB::table('mt_shop_collection')->where($where)->get()->toArray();
            //var_dump($shop_collection);exit;
            if($shop_collection){
                $response = [
                    'code'=>'0',
                    'msg'=>'该店铺已在您的收藏夹中'
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $data = [
                    'shop_id'=>$shop_id,
                    'uid'=>$uid,
                    't_id'=>$t_id
                ];
                //var_dump($data);exit;
                $add_shop_collection = DB::table('mt_shop_collection')->insertGetId($data);
                if($add_shop_collection){
                    $data1=[
                        'code'=>'0',
                        'msg'=>'店铺收藏成功'
                    ];
                    $response = [
                        'data'=>$data1
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    $data1=[
                        'code'=>'1',
                        'msg'=>'店铺收藏失败'
                    ];
                    $response = [
                        'data'=>$data1
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }
        }else{
            $response = [
                'code'=>'2',
                'msg'=>'请先去登录'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //店铺收藏列表
    public function shop_collection_list(Request $request){
//        $ip = $_SERVER['SERVER_ADDR'];
//        $key = 'openid'.$ip;
//        $openid = Redis::get($key);
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
//        $openid='o9VUc5MWyq5GgW3kF_90NnrQkBH8';
        if($openid){
            $user_info = DB::table('mt_user')->where('openid',$openid)->first();
            $uid = $user_info->uid;
            $where = [
                'mt_shop_collection.uid'=>$uid,
            ];
            $collectionInfo = DB::table('mt_shop_collection')
                ->join('mt_shop','mt_shop_collection.shop_id','=','mt_shop.shop_id')
                ->join('mt_type','mt_shop_collection.t_id','=','mt_type.t_id')
                ->where($where)
                ->select()->paginate(10);
            //var_dump($collectionInfo);exit;
            if($collectionInfo){
                $data1=[
                    'code'=>'0',
                    'cartInfo'=>$collectionInfo
                ];
                $response = [
                    'data'=>$data1
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $data1=[
                    'code'=>'1',
                    'msg'=>'收藏夹暂无数据，快去收藏店铺吧'
                ];
                $response = [
                    'data'=>$data1
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $data1=[
                'code'=>'2',
                'msg'=>'请先登录'
            ];
            $response = [
                'data'=>$data1
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }
    //店铺收藏删除
    public function shop_collection_dele(Request $request)
    {
        $shop_id=$request->input('shop_id');
//        $cart_id=$request->input('cart_id');
        $aa=[
            'shop_id'=>$shop_id,
        ];
//        var_dump($aa);die;
        $data=DB::table('mt_shop_collection')
            ->where($aa)
            ->delete();
//        var_dump($data);die;
        if($data){
            $data1=[
                'code'=>0,
                'msg'=>'删除成功'
            ];
            $response=[
                'data'=>$data1
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data1=[
                'code'=>'1',
                'msg'=>'此件店铺没有被收藏，无法删除'
            ];
            $response = [
                'data'=>$data1
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //导航栏附近店铺-全部店铺
    public function whole_shop(Request $request){
        $shopInfo = DB::table('mt_shop')->paginate(7);
        //var_dump($shopInfo);
        if($shopInfo){
            $data1=[
                'code'=>'0',
                'data'=>$shopInfo
            ];
            $response = [
                'data'=>$data1
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data1=[
                'code'=>'1',
                'msg'=>'暂无店铺'
            ];
            $response = [
                'data'=>$data1
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }

    }

    //附近店铺-附近店铺
    public function nearby_shop(Request $request){
//        $lng1 = '112.606565';
//        $lat1= '37.69946';
        $lat1 = $request->input('lat');//纬度
        $lng1 = $request->input('lng');//经度
        $limited_type = $request->input('limited_type');// 1为附近店铺 2为销量最高
//        $aa=DB::table('mt_goods')
//            ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
//            ->first(['goods_id','mt_goods.shop_id','goods_gd_num']);
//        $qqq=DB::table('mt_goods')
//            ->where(['shop_id'=>$aa->shop_id])
//            ->get(['goods_gd_num']);
//        $sss=count($qqq);
//        var_dump($qqq);die;
        if($limited_type == 1){     //附近
            $page1=$request->input('page');
            $page_num=$request->input('page_num');
            $page=($page1-1)*10;
//            var_dump($page);die;
//            var_dump($page2);die;
//            $shopInfo =  DB::select("SELECT s.shop_id,shop_name,shop_address_provice,shop_address_city,shop_address_area,shop_score,goods_id,goods_name,price,market_price,introduction,picture,promotion_price,prople,shop_label,shop_status, 6378.138*2*ASIN(SQRT(POW(SIN(($lat1*PI()/180-lat*PI()/180)/2),2)+COS($lat1*PI()/180)*COS(lat*PI()/180)*POW(SIN(($lng1*PI()/180-lng*PI()/180)/2),2))) AS juli  FROM mt_shop s inner join mt_goods g on s.shop_id = g.shop_id  where shop_status = 2 group by juli order by juli");
//            $shopInfo =  DB::select("SELECT s.shop_id,shop_name,shop_address_provice,shop_address_city,shop_address_area,shop_score,goods_id,goods_name,price,market_price,introduction,shop_img,promotion_price,prople,shop_label,shop_status,t.t_name,t.p_id, 6378.138*2*ASIN(SQRT(POW(SIN(($lat1*PI()/180-lat*PI()/180)/2),2)+COS($lat1*PI()/180)*COS(lat*PI()/180)*POW(SIN(($lng1*PI()/180-lng*PI()/180)/2),2))) AS juli  FROM mt_shop s inner join mt_goods g on s.shop_id = g.shop_id  inner join mt_type t on t.t_id = s.t_id where s.shop_status = 2  group by juli order by juli limit $page,$page2");
            $shopInfo =  DB::select("SELECT *, 6378.138*2*ASIN(SQRT(POW(SIN(($lat1*PI()/180-lat*PI()/180)/2),2)+COS($lat1*PI()/180)*COS(lat*PI()/180)*POW(SIN(($lng1*PI()/180-lng*PI()/180)/2),2))) AS juli  from mt_shop s inner join mt_type t on s.t_id = t.t_id where s.shop_status = 2  order by juli limit $page,$page_num");
//            var_dump($shopInfo);exit;
            $data=[
              'code'=>0,
              'shopInfo'=>$shopInfo
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else if($limited_type ==2){ //销量
            $page1=$request->input('page');
            $page_num=$request->input('page_num');
            $page=($page1-1)*10;
            $shopInfo =  DB::select("SELECT *, 6378.138*2*ASIN(SQRT(POW(SIN(($lat1*PI()/180-lat*PI()/180)/2),2)+COS($lat1*PI()/180)*COS(lat*PI()/180)*POW(SIN(($lng1*PI()/180-lng*PI()/180)/2),2))) AS juli  from mt_shop s inner join mt_type t on s.t_id = t.t_id where s.shop_status = 2 order by s.shop_volume  desc limit $page,$page_num");
//            $shopInfo =  DB::select("SELECT s.shop_id,shop_name,shop_volume,shop_address_provice,shop_address_city,shop_address_area,shop_score,goods_id,goods_name,price,market_price,introduction,shop_img,promotion_price,prople,shop_label,shop_status,t.t_name,t.p_id, 6378.138*2*ASIN(SQRT(POW(SIN(($lat1*PI()/180-lat*PI()/180)/2),2)+COS($lat1*PI()/180)*COS(lat*PI()/180)*POW(SIN(($lng1*PI()/180-lng*PI()/180)/2),2))) AS juli  FROM mt_shop s inner join mt_goods g on s.shop_id = g.shop_id  inner join mt_type t on t.t_id = s.t_id where shop_status = 2 group by juli order by juli limit $page,10");
//            var_dump($shopInfo);exit;
            $data=[
                'code'=>0,
                'shopInfo'=>$shopInfo
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
        //var_dump($shopInfo);exit;

//        $shopInfo = DB::table('mt_shop')
//            ->join('mt_goods','mt_shop.shop_id','=','mt_goods.shop_id')
//            ->orderBy('mt_shop.shop_id')
//            ->limit(6)
//            ->where('mt_goods.is_recommend',1)
//            ->get(['mt_shop.shop_id','shop_name','shop_address_provice','shop_address_city','shop_address_area','shop_score','shop_desc','shop_label','shop_logo','goods_id','goods_name','price','picture','latitude_longitude'])->toArray();
    }




    //距离算法
    public function getDistance($lat1, $lng1, $lat2, $lng2){
        $earthRadius = 6367000; //approximate radius of earth in meters
        $lat1 = ($lat1 * pi() ) / 180;
        $lng1 = ($lng1 * pi() ) / 180;
        $lat2 = ($lat2 * pi() ) / 180;
        $lng2 = ($lng2 * pi() ) / 180;
        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return round($calculatedDistance);
    }

    //预约
    public function subscribe(Request $request)
    {
        $goods_id=$request->input('goods_id');
        $subscribe_time=$request->input('subscribe_time');
        $subscrib_sum=$request->input('subscrib_sum');
        $subscribe_tel=$request->input('subscribe_tel');
        $subscribe_text=$request->input('subscribe_text');
        $data=DB::table('mt_goods')
            ->where(['goods_id'=>$goods_id])
            ->first(['goods_name','market_price']);
//        var_dump($data);die;
        $subscribeAdd=$data->market_price;
        $aa=$subscribeAdd * $subscrib_sum;
//        var_dump($aa);die;
        $subscribeInfo=$data->goods_name;
        $info1=[
            'subscribe_time'=>$subscribe_time,
            'subscrib_sum'=>$subscrib_sum,
            'subscribe_tel'=>$subscribe_tel,
            'subscribe_text'=>$subscribe_text,
            'subscribe_money'=>$aa
        ];
        $info=DB::table('mt_subscribe')
            ->insert($info1);
        $datainfo=[
          'data'=>$info
        ];
        if($info == true){
            $response = [
                'code'=>0,
                'msg'=>"预约成功",
                'data'=>$datainfo,
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $response = [
                'code'=>1,
                'msg'=>"预约失败",
            ];
            die (json_encode($response, JSON_UNESCAPED_UNICODE));
        }


    }

//    //置换商城
//    public function displace(Request $request)
//    {
//        $openid = Redis::set('openid','o9VUc5HEPNrYq5d5iQFygPVbX7EM');
////        $openid = Redis::get('openid');
//        $data=DB::table('mt_displace')
////            ->where('openid',$openid)
//            ->join('mt_shop','mt_shop.shop_id','=','mt_displace.shop_id')
//            ->join('mt_goods','mt_goods.goods_id','=','mt_displace.goods_id')
//            ->join('mt_address','mt_address.id','=','mt_displace.id')
//            ->select(['mt_shop.shop_name','mt_goods.goods_name','mt_goods.stock','mt_goods.market_price','mt_address.address_provice','mt_address.address_city','mt_address.address_area','mt_address.address_detail','mt_displace.displace_time'])
//            ->paginate(4);
//        var_dump($data);die;
//    }











}
