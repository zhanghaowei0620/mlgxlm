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
    public function shop_goods(Request $request){
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
            ->select(['mt_goods.goods_name','mt_goods.goods_id','mt_goods.market_price','mt_goods.picture','mt_goods.goods_gd_num'])
            ->paginate(4);
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
            ->get(['mt_coupon.coupon_id','mt_coupon.coupon_id','mt_goods.goods_id','mt_shop.shop_id','mt_coupon.coupon_draw','mt_shop.shop_name','mt_coupon.discount','mt_shop.shop_id','mt_coupon.coupon_price','mt_coupon.coupon_redouction','mt_coupon.create_time','mt_coupon.expiration','mt_goods.picture','mt_coupon.coupon_type','mt_goods.goods_name','mt_coupon.discount','mt_goods.picture']);
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
    //点击领取优惠卷
    public function couponadd(Request $request)
    {
        $coupon_id=$request->input('coupon_id');
        $data=DB::table('mt_coupon')
            ->where(['coupon_id'=>$coupon_id])
            ->first();
//        var_dump($data);die;
        $info=DB::table('mt_coupon')
            ->where(['coupon_id'=>$coupon_id])
            ->update(['coupon_draw'=>3]);
//        var_dump($info);die;
        if($info){
            $data = [
                'code'=>0,
                'msg'=>'领取优惠卷成功，快去使用吧!'
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data = [
                'code'=>1,
                'msg'=>'您没有领取上优惠卷'
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
            ->get(['shop_name','admin_tel','shop_address_detail','goods_name','goods_effect','goods_duration','goods_process','goods_overdue_time','shop_bus','goods_appointment','goods_use_rule','shop_img','shop_logo','shop_star']);
//        var_dump($shopsetInfo);die;
//        $aaa=DB::table('mt_goods')
//            ->where(['shop_id'=>$data1->shop_id])
//            ->get(['promotion_prople']);
//        var_dump($aaa);die;
        $goods_list=DB::table('mt_goods')
            ->where(['mt_shop.shop_id'=>$data1->shop_id])
            ->join('mt_shop','mt_shop.shop_id','=','mt_goods.goods_id')
            ->limit(4)
            ->get();
        $seller = DB::table('mt_goods')
            ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
            ->join('mt_user','mt_goods.uid','=','mt_user.uid')
            ->where(['mt_shop.shop_id'=>$data1->shop_id])
//            ->limit(2)
            ->get(['goods_id','goods_name','picture','promotion_price','prople','promotion_prople','wx_name','wx_headimg'])->toArray();
//        var_dump($seller);die;
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
            $ip = $_SERVER['SERVER_ADDR'];
            $key = 'openid'.$ip;
            $openid = Redis::get($key);
//            $openid="o9VUc5AOsdEdOBeUAw4TdYg-F-dM";
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
                'seller'=>$seller,
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
        $ip = $_SERVER['SERVER_ADDR'];
        $key = 'openid'.$ip;
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
                $update = [
                    'buy_num'=>$goods_cart[0]->buy_num+$buy_num
                ];
                $update_buynum = DB::table('mt_cart')->where('goods_id',$goods_id)->update($update);
//                var_dump($update_buynum);exit;
                if($update_buynum==true){
                    $aa=[
                        'code'=>'0',
                        'msg'=>'加入购物车成功111'
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
        $ip = $_SERVER['SERVER_ADDR'];
        $key = 'openid'.$ip;
        $openid = Redis::get($key);
//        $openid="o9VUc5AOsdEdOBeUAw4TdYg-F-dM";
        $where = [
            'mt_cart.openid'=>$openid,
//            'collection_cart'=>0
        ];
//        $info=DB::table('')
//        var_dump($where);die;
        $cartInfo = DB::table('mt_cart')
            ->where($where)
            ->get()->toArray();
//        var_dump($cartInfo);exit;
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

    //购物车删除
    public function cart_delete(Request $request){
        $ip = $_SERVER['SERVER_ADDR'];
        $key = 'openid'.$ip;
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
        $ip = $_SERVER['SERVER_ADDR'];
        $key = 'openid'.$ip;
        $openid = Redis::get($key);
//        $openid="o9VUc5AOsdEdOBeUAw4TdYg-F-dM";
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
                //var_dump($goodsInfo);exit;
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
                    'uid'=>$uid
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
        $ip = $_SERVER['SERVER_ADDR'];
        $key = 'openid'.$ip;
        $openid = Redis::get($key);
        if($openid){
            $where = [
                'openid'=>$openid,
//                'collection'=>1
            ];
            $cartInfo = DB::table('mt_collection_goods')->where($where)->get()->toArray();
            //var_dump($cartInfo);exit;
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
        $ip = $_SERVER['SERVER_ADDR'];
        $key = 'openid'.$ip;
        $openid = Redis::get($key);
        if($openid){
            $user_info = DB::table('mt_user')->where('openid',$openid)->first();
            $uid = $user_info->uid;
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
                    'uid'=>$uid
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
        $ip = $_SERVER['SERVER_ADDR'];
        $key = 'openid'.$ip;
        $openid = Redis::get($key);
        if($openid){
            $user_info = DB::table('mt_user')->where('openid',$openid)->first();
            $uid = $user_info->uid;
            $where = [
                'mt_shop_collection.uid'=>$uid,
            ];
            $collectionInfo = DB::table('mt_shop_collection')
                ->join('mt_shop','mt_shop_collection.shop_id','=','mt_shop.shop_id')
                ->where($where)
                ->get()->toArray();
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
//        $lat1 = '112.558505';
//        $lng1 = '37.818498';
        $lat1 = $request->input('lat');//经度
        $lng1 = $request->input('lng');//纬度

        $shopInfo =  DB::select("SELECT s.shop_id,shop_name,shop_address_provice,shop_address_city,shop_address_area,shop_score,goods_id,goods_name,price,market_price,introduction,picture,promotion_price,prople,shop_label,shop_status, 6378.138*2*ASIN(SQRT(POW(SIN(($lat1*PI()/180-lat*PI()/180)/2),2)+COS($lat1*PI()/180)*COS(lat*PI()/180)*POW(SIN(($lng1*PI()/180-lng*PI()/180)/2),2))) AS juli  FROM mt_shop s inner join mt_goods g on s.shop_id = g.shop_id  where shop_status = 1 group by juli order by juli");
        //var_dump($shopInfo);exit;
        $data = [
            'code'=>0,
            'shopInfo'=>$shopInfo
        ];
        $response = [
            'data'=>$data,
        ];
        return json_encode($response,JSON_UNESCAPED_UNICODE);

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
