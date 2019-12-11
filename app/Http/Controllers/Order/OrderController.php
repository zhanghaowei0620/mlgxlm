<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // 普通订单  拼团订单  优惠卷订单   限时抢订单  分销订单
    public function order_insert(Request $request)
    {
        $method_type = $request->input('method_type');  //接收 普通订单为1  拼团订单为2  优惠卷订单为3   限时抢订单为4
        $goods_id = $request->input('goods_id');  //商品id
        $goods_id = explode(',',$goods_id);
//        var_dump($goods_id);die;
        $order_address = $request->input('order_address');   //分销商品的收货地址
        $re_goods_id = $request->input('re_goods_id');  //分销商品id
        $is_cart = $request->input('is_cart');  //0为否 1为是
        $buy_num = $request->input('buy_num'); //数量
        $total_price = $request->input('total_price');   //总价
        $good_cate = $request->input('good_cate'); //0是服务1是商品
        $coupon_type = $request->input('coupon_type');  //根据前端传回来的是0还是1，0为优惠卷   1为折扣
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
        $order_no = date("YmdHis", time()) . rand(1000, 9999);   //订单号
//        $openid='o9VUc5AOsdEdOBeUAw4TdYg-F-dM';
        $userInfo = DB::table('mt_user')->where('openid', $openid)->first();
//            var_dump($userInfo);die;
        $wx_name = $userInfo->wx_name;
//        var_dump($wx_name);die;
        $uid = $userInfo->uid;
        if ($openid) {
            if ($method_type == 1) {
                $data_order = [
                    'uid'=>$uid,
                    'order_no'=>$order_no,
                    'wx_name' =>$wx_name,
                    'order_status'=>0,
                    'total_price'=>$total_price,
                    'create_time'=>time()
                ];
//            var_dump($data_order);die;
                $infodata =DB::table('mt_order')->insert($data_order);
                $dataData = DB::table('mt_order')->where('order_no',$order_no)->first();
                $order_id = $dataData->order_id;
                session(['order_id'=>$order_id]);

                $cartUpdate=[
                    'buy_num'=>0,
                    'update_time'=>time()
                ];
//            var_dump($cartUpdate);die;
                $res = DB::table('mt_cart')->where('uid',$uid)->where('goods_id',$goods_id)->update($cartUpdate);
//            var_dump($res);exit;
                //添加订单详情表
                if($is_cart == 1){

                    foreach($goods_id as &$value){
//                        var_dump($value);
                         $num = DB::table('mt_goods')
                            ->join('mt_cart','mt_goods.goods_id','=','mt_cart.goods_id')
                            ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
                            ->where('mt_cart.goods_id',$value)
                            ->first();
//                            var_dump($num);
                            $info=[
                                'uid'=>$uid,
                                'order_id'=>$order_id,
                                'order_no'=>$order_no,
                                'goods_id'=>$num->goods_id,
                                'goods_name'=>$num->goods_name,
                                'price'=>$num->price,
                                'picture'=>$num->picture,
                                'buy_num'=>$num->buy_num,
                                'order_status'=>0,
                                'shop_id'=>$num->shop_id,
                                'shop_name'=>$num->shop_name,
                                'create_time'=>time()
                            ];
                            $datailData = DB::table('mt_order_detail')->insert($info);
                            $aa=[
                                'goods_id'=>$num->goods_id,
                                'openid'=>$openid
                            ];
                            $datailData123456 = DB::table('mt_cart')->where($aa)->delete();
                        }
                }else{
                    $num = DB::table('mt_goods')
//                    ->join('mt_cart','mt_goods.goods_id','=','mt_cart.goods_id')
                        ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
                        ->where('mt_goods.goods_id',$goods_id)
                        ->get();
//                            var_dump($num);exit;
                    foreach($num as $k=>$v){
                        $info=[
                            'uid'=>$uid,
                            'order_id'=>$order_id,
                            'order_no'=>$order_no,
                            'goods_id'=>$v->goods_id,
                            'goods_name'=>$v->goods_name,
                            'price'=>$v->price,
                            'picture'=>$v->picture,
                            'buy_num'=>$buy_num,
                            'order_status'=>0,
                            'shop_id'=>$v->shop_id,
                            'shop_name'=>$v->shop_name,
                            'create_time'=>time()
                        ];
                        $datailData = DB::table('mt_order_detail')->insert($info);
                    }
                }
//                die;
                $UpdateNum=[
                    // 'is_del'=>2,
                    'buy_num'=>0,
                    'update_time'=>time()
                ];
                $res = DB::table('mt_cart')->where('uid',$uid)->where('goods_id',['goods_id'=>$goods_id])->update($UpdateNum);
//            var_dump($res);die;
                if($res>=0){
                    $data=[
                        'code'=>'0',
                        'msg'=>'生成订单成功',
                        'order_id'=>$order_id,
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }
            } else if ($method_type == 2) {
                    $data_order = [
                        'uid' => $uid,
                        'order_no' => $order_no,
                        'wx_name' => $wx_name,
                        'order_status' => 0,
                        'order_method' => 1,
                        'total_price' => $total_price,
                        'create_time' => time(),
                        'good_cate' => $good_cate,
                    ];
                    $infodata = DB::table('mt_order')->insert($data_order);

                    $dainfo = DB::table('mt_order')
                        ->where(['order_no' => $order_no])
                        ->first(['order_id']);
                $dataData = DB::table('mt_order')->where('order_no', $order_no)->first();
//                    var_dump($dataData);die;
                $order_id = $dataData->order_id;
                    $num = DB::table('mt_goods')
                        ->join('mt_shop', 'mt_goods.shop_id', '=', 'mt_shop.shop_id')
                        ->where('mt_goods.goods_id', $goods_id)
                        ->get();
                    foreach ($num as $k => $v) {
                        $info = [
                            'uid' => $uid,
                            'order_id' => $order_id,
                            'order_no' => $order_no,
                            'goods_id' => $v->goods_id,
                            'goods_name' => $v->goods_name,
                            'price' => $v->price,
                            'picture' => $v->picture,
                            'buy_num' => 1,
                            'order_status' => 0,
                            'shop_id' => $v->shop_id,
                            'shop_name' => $v->shop_name,
                            'create_time' => time(),
                        ];
                        $datailData = DB::table('mt_order_detail')->insert($info);
//                            var_dump($datailData);die;
                    }
                    if ($infodata) {
                        $data = [
                            'code' => 0,
                            'msg' => '拼团成功',
                            'order_id' => $order_id
                        ];
                        $response = [
                            'data' => $data
                        ];
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    } else {
                        $data = [
                            'code' => 1,
                            'msg' => '拼团失败',
                        ];
                        $response = [
                            'data' => $data
                        ];
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }
            }else if($method_type == 3){            //$coupon_type：0,满减   1，折扣
                $coupon_add=DB::table('mt_coupon')->where(['uid'=>$uid,'goods_id'=>$goods_id])->first();
//            var_dump($coupon_add);die;
                if($coupon_type == 0){
                    if($total_price >= $coupon_add->coupon_redouction){
                        $data_order = [
                            'uid'=>$uid,
                            'order_no'=>$order_no,
                            'wx_name' =>$wx_name,
                            'order_status'=>0,
                            'order_method'=>2,
                            'total_price'=>$total_price,
                            'create_time'=>time(),
                            'good_cate'=>$good_cate,
                        ];
                        $infodata =DB::table('mt_order')->insert($data_order);
                        $coupon_add=DB::table('mt_coupon')->where(['uid'=>$uid,'goods_id'=>$goods_id])->update(['is_use'=>1]);
//                    var_dump($infodata);die;
                        $dainfo=DB::table('mt_order')
                            ->where(['order_no'=>$order_no])
                            ->first(['order_id']);
                        $dataData = DB::table('mt_order')->where('order_no',$order_no)->first();
                        $order_id = $dataData->order_id;
                        $num = DB::table('mt_goods')
                            ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
                            ->where('mt_goods.goods_id',$goods_id)
                            ->get();
//            var_dump($num);die;
                        foreach($num as $k=>$v){
                            $info=[
                                'uid'=>$uid,
                                'order_id'=>$order_id,
                                'order_no'=>$order_no,
                                'goods_id'=>$v->goods_id,
                                'goods_name'=>$v->goods_name,
                                'price'=>$v->price,
                                'picture'=>$v->picture,
                                'buy_num'=>1,
                                'order_status'=>0,
                                'shop_id'=>$v->shop_id,
                                'shop_name'=>$v->shop_name,
                                'create_time'=>time(),
                            ];
                            $datailData = DB::table('mt_order_detail')->insert($info);
                        }
                        if($infodata){
                            $data=[
                                'code'=>0,
                                'msg'=>'成功',
                                'order_id'=>$order_id
                            ];
                            $response = [
                                'data'=>$data
                            ];
                            return json_encode($response,JSON_UNESCAPED_UNICODE);
                        }else{
                            $data=[
                                'code'=>1,
                                'msg'=>'失败',
                            ];
                            $response = [
                                'data'=>$data
                            ];
                            return json_encode($response,JSON_UNESCAPED_UNICODE);
                        }
                    }else{
                        $data=[
                            'code'=>1,
                            'msg'=>'您没有达到优惠标准',
                        ];
                        $response = [
                            'data'=>$data
                        ];
                        return json_encode($response,JSON_UNESCAPED_UNICODE);
                    }
                }else{
                    $data_order = [
                        'uid'=>$uid,
                        'order_no'=>$order_no,
                        'wx_name' =>$wx_name,
                        'order_status'=>0,
                        'is_use'=>1,
                        'order_method'=>2,
                        'total_price'=>$total_price,
                        'create_time'=>time(),
                        'good_cate'=>$good_cate,
                    ];
//                var_dump($data_order);die;
                    $infodata =DB::table('mt_order')->insert($data_order);
                    $coupon_add=DB::table('mt_coupon')->where(['uid'=>$uid,'goods_id'=>$goods_id])->update(['is_use'=>1]);
                    $dainfo=DB::table('mt_order')
                        ->where(['order_no'=>$order_no])
                        ->first(['order_id']);
                    $dataData = DB::table('mt_order')->where('order_no',$order_no)->first();
                    $order_id = $dataData->order_id;
                    $num = DB::table('mt_goods')
                        ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
                        ->where('mt_goods.goods_id',$goods_id)
                        ->get();
                    foreach($num as $k=>$v){
                        $info=[
                            'uid'=>$uid,
                            'order_id'=>$order_id,
                            'order_no'=>$order_no,
                            'goods_id'=>$v->goods_id,
                            'goods_name'=>$v->goods_name,
                            'price'=>$v->price,
                            'picture'=>$v->picture,
                            'buy_num'=>1,
                            'order_status'=>0,
                            'shop_id'=>$v->shop_id,
                            'shop_name'=>$v->shop_name,
                            'create_time'=>time(),
                        ];
                        $datailData = DB::table('mt_order_detail')->insert($info);
                    }
                    $dainfo=DB::table('mt_order')
                        ->where(['order_no'=>$order_no])
                        ->first();
                    if($dainfo){
                        $data=[
                            'code'=>0,
                            'msg'=>'成功',
                            'order_id'=>$order_id
                        ];
                        $response = [
                            'data'=>$data
                        ];
                        return json_encode($response,JSON_UNESCAPED_UNICODE);
                    }else{
                        $data=[
                            'code'=>1,
                            'msg'=>'失败',
                        ];
                        $response = [
                            'data'=>$data
                        ];
                        return json_encode($response,JSON_UNESCAPED_UNICODE);
                    }
                }
            }else if($method_type == 4){    //限时抢

                $limited_add=DB::table('mt_goods')->where(['goods_id'=>$goods_id,'limited_buy'=>1])->first(['limited_start_time','limited_stop_time','shop_id','limited_price']);
//            var_dump($limited_add);die;
                $aa=time();
                if($aa >$limited_add->limited_start_time){
                    $infos=[
                        'uid'=>$uid,
                        'order_no'=>$order_no,
                        'wx_name' =>$wx_name,
                        'order_status'=>0,
                        'order_method'=>3,
                        'total_price'=>$total_price,
                        'good_cate'=>$good_cate,
                        'create_time'=>time(),
                    ];
                    $insertinto=DB::table('mt_order')->insert($infos);
                    $dainfo=DB::table('mt_order')
                        ->where(['order_no'=>$order_no])
                        ->first(['order_id']);
                    $dataData = DB::table('mt_order')->where('order_no',$order_no)->first();
                    $order_id = $dataData->order_id;

                    $num = DB::table('mt_goods')
                        ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
                        ->where('mt_goods.goods_id',$goods_id)
                        ->get();
//            var_dump($num);die;
                    foreach($num as $k=>$v){
                        $info=[
                            'uid'=>$uid,
                            'order_id'=>$order_id,
                            'order_no'=>$order_no,
                            'goods_id'=>$v->goods_id,
                            'goods_name'=>$v->goods_name,
                            'price'=>$v->price,
                            'picture'=>$v->picture,
                            'buy_num'=>1,
                            'order_status'=>0,
                            'shop_id'=>$v->shop_id,
                            'shop_name'=>$v->shop_name,
                            'create_time'=>time(),
                        ];
                        $datailData = DB::table('mt_order_detail')->insert($info);
                    }



                    $dainfo=DB::table('mt_order')
                        ->where(['order_no'=>$order_no])
                        ->first();
                    if($dainfo){
                        $data=[
                            'code'=>0,
                            'msg'=>'成功',
                            'order_id'=>$order_id
                        ];
                        $response = [
                            'data'=>$data
                        ];
                        return json_encode($response,JSON_UNESCAPED_UNICODE);
                    }else{
                        $data=[
                            'code'=>1,
                            'msg'=>'失败',
                        ];
                        $response = [
                            'data'=>$data
                        ];
                        return json_encode($response,JSON_UNESCAPED_UNICODE);
                    }
                }else{
                    $data=[
                        'code'=>1,
                        'msg'=>'此商品没有开启限时抢,请仔细查看'
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }
            }else if ($method_type == 5){
                $data_order = [
                    'uid'=>$uid,
                    'order_no'=>$order_no,
                    'wx_name' =>$wx_name,
                    'order_status'=>0,
                    'total_price'=>$total_price,
                    'create_time'=>time(),
                ];
//            var_dump($data_order);die;
                $infodata =DB::table('mt_order')->insert($data_order);
                $dataData = DB::table('mt_order')->where('order_no',$order_no)->first();
//            var_dump($dataData);exit;
                $order_id = $dataData->order_id;
                session(['order_id'=>$order_id]);

                $cartUpdate=[
                    'buy_num'=>$buy_num,
                    'update_time'=>time(),
                    'order_address'=>$order_address
                ];
//            var_dump($cartUpdate);die;
                $res = DB::table('mt_cart')->where('uid',$uid)->where('goods_id',$goods_id)->update($cartUpdate);
//            var_dump($res);exit;
                //添加订单详情表
                    $num = DB::table('re_goods')
                        ->where('re_goods_id',$re_goods_id)
                        ->get();
//                            var_dump($num);exit;
                    foreach($num as $k=>$v){
                        $info=[
                            'uid'=>$uid,
                            'order_id'=>$order_id,
                            'order_no'=>$order_no,
                            'goods_id'=>$v->re_goods_id,
                            'goods_name'=>$v->re_goods_name,
                            'price'=>$v->re_goods_price,
                            'buy_num'=>$v->buy_num,
                            'order_status'=>0,
                            'shop_id'=>$v->shop_id,
//                            'shop_name'=>$v->shop_name,
                            'create_time'=>time()
                        ];
                        $datailData = DB::table('mt_order_detail')->insert($info);

                    }
                if($res>=0){
                    $data=[
                        'code'=>'0',
                        'msg'=>'生成分销订单成功',
                        'order_id'=>$order_id,
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }



            }
        }else{
            $response = [
                'error'=>'1',
                'msg'=>'请先登录'
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    //查询当前商品的所有优惠卷
    public function coupon_list_all(Request $request)
    {
        $goods_id=$request->input('goods_id');
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
//        $openid='o9VUc5MWyq5GgW3kF_90NnrQkBH8';
        $data1=DB::table('mt_user')->where(['openid'=>$openid])->first();
        $uid=$data1->uid;
        $data=DB::table('mt_coupon')->where(['uid'=>$uid,'goods_id'=>$goods_id])->get();
        if($data){
            $data=[
              'code'=>0,
              'data'=>$data,
              'msg'=>'OK'
            ];
            $response=[
              'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data=[
                'code'=>1,
                'msg'=>'NO'
            ];
            $response=[
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    //拼团列表
    public function pt_add_list(Request $request)
    {
        $goods_id=$request->input('goods_id');
        $data=DB::table('mt_pt_list')
            ->join('mt_user','mt_user.uid','=','mt_pt_list.uid')
            ->join('mt_goods','mt_goods.goods_id','=','mt_pt_list.goods_id')
            ->where(['pt_state'=>0],['mt_pt_list.goods_id'=>$goods_id])
            ->get(['mt_pt_list.pt_id','mt_pt_list.uid','mt_user.wx_name','mt_user.wx_headimg','mt_pt_list.pt_team','mt_pt_list.pt_sum','mt_goods.promotion_prople']);
//        var_dump($data);die;
        if($data){
            $data=[
                'code'=>0,
                'msg'=>'展示成功',
                'data'=>$data
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data=[
                'code'=>1,
                'msg'=>'请重新尝试展示列表'
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    //用户下所有的订单
    public function open_order_list(Request $request)
    {
        $order_status=$request->input('order_status'); // 订单状态 0->未支付，1->已付款 待发货，3->确认收货,4->已完成,5->已关闭
        $openid1 = $request->input('openid');
        $good_cate=$request->input('good_cate');
        $key = $openid1;
        $openid = Redis::get($key);
//        $openid='o9VUc5AOsdEdOBeUAw4TdYg-F-dM';
        $orderInfo = DB::table('mt_user')
//            ->join('mt_order','mt_user.uid','=','mt_order.uid')
            ->where('openid',$openid)
            ->first();
//        $datainfo=DB::table('mt_refund')->where([''])
        if($order_status == 99){
            $data=DB::table('mt_order_detail')
                ->join('mt_order','mt_order.order_id','=','mt_order_detail.order_id')
                ->join('mt_shop','mt_shop.shop_id','=','mt_order_detail.shop_id')
//                    ->join('mt_refund','mt_refund.id','=','mt_order_detail.id')
                ->where(['mt_order_detail.uid'=>$orderInfo->uid,'good_cate'=>$good_cate])
                ->select()->paginate(10);
        }else{
            $data=DB::table('mt_order_detail')
                ->join('mt_order','mt_order.order_id','=','mt_order_detail.order_id')
                ->join('mt_shop','mt_shop.shop_id','=','mt_order_detail.shop_id')
//                ->join('mt_refund','mt_refund.id','=','mt_order_detail.id')
                ->where(['mt_order_detail.uid'=>$orderInfo->uid,'good_cate'=>$good_cate,'mt_order.order_status'=>$order_status])
                ->select()->paginate(10);
        }
        if($data){
            $data=[
                'code'=>0,
                'msg'=>'展示成功',
                'data'=>$data
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data=[
                'code'=>0,
                'msg'=>'展示失败'
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }

    }
    //退款的
    public function re_refund_add(Request $request){
        $id=$request->input('id');
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
//        $openid='o9VUc5AOsdEdOBeUAw4TdYg-F-dM';
        if($openid){
            $data1=DB::table('mt_refund')->where(['id'=>$id])->first();
            if($data1){
                $data=[
                    'code'=>0,
                    'msg'=>'OK',
                    'data'=>$data1
                ];
                $response = [
                    'data'=>$data
                ];
                return (json_encode($response,JSON_UNESCAPED_UNICODE));
            }else{
                $data=[
                    'code'=>1,
                    'msg'=>'NO'
                ];
                $response = [
                    'data'=>$data
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $data=[
                'code'=>1,
                'msg'=>'请先去登陆'
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }



    //订单列表
    public function order_list(Request $request){
//        $ip = $_SERVER['SERVER_ADDR'];
//        $key = 'openid'.$ip;
//        $openid = Redis::get($key);
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
//        $openid='o9VUc5AOsdEdOBeUAw4TdYg-F-dM';
        if($openid){
            $orderInfo = DB::table('mt_user')
                ->join('mt_order','mt_user.uid','=','mt_order.uid')
                ->where('mt_user.openid',$openid)
                ->get();
            $data=[
                'code'=>0,
                'data'=>$orderInfo
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data=[
                'code'=>1,
                'msg'=>'请先去登陆'
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //订单详情
    public function order_detail(Request $request){
        $order_id = $request->input('order_id');
//        $order_id = 1;
        $order_detailInfo = DB::table('mt_order')
            ->join('mt_order_detail','mt_order.order_id','=','mt_order_detail.order_id')
            ->where('mt_order_detail.order_id',$order_id)->get();
        //var_dump($order_detailInfo);exit;
        if($order_detailInfo){
            $data=[
                'code'=>0,
                'data'=>$order_detailInfo
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data=[
                'code'=>1,
                'msg'=>'订单出现错误，请重新下单'
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }


    //根据订单状态查找订单信息
    public function order_status_list(Request $request){
        $order_status_id = $request->input('order_status_id');
        //$order_status_id = 0;
//        $ip = $_SERVER['SERVER_ADDR'];
//        $key = 'openid'.$ip;
//        $openid = Redis::get($key);
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
        $where = [
            'mt_user.openid'=>$openid,
            'mt_order.order_status'=>$order_status_id
        ];
        if($openid){
            $orderInfo = DB::table('mt_order')
                ->join('mt_user','mt_order.uid','=','mt_user.uid')
                ->join('mt_order_detail','mt_order.order_id','=','mt_order_detail.order_id')
                ->where($where)
                ->get();
            //var_dump($orderInfo);exit;
            if($orderInfo){
                $response = [
                    'error'=>0,
                    'data'=>$orderInfo
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $response = [
                    'error'=>2,
                    'msg'=>'暂无订单，快去下单吧'
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
        }else{
            $response = [
                'error'=>1,
                'msg'=>'请先去登陆'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }
    //订单删除
    public function update_static_del(Request $request){
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
        $id=$request->input("id");
//        $openid='o9VUc5AOsdEdOBeUAw4TdYg-F-dM';
        if($openid){
            $user=DB::table("mt_user")->where("openid",$openid)->first();
            $uid=$user->uid;
            $info=[
                'id'=>$id,
                'uid'=>$uid
            ];
            $data1 = DB::table('mt_order_detail')->where($info)->first();
//            var_dump($data1);die;
            if($data1){
                $datainfo=DB::table('mt_order_detail')->where(['order_id'=>$data1->order_id])->get();
//                var_dump (count($datainfo));die;
                if( (count($datainfo)) >1){
//
                    $data2=DB::table('mt_order_detail')->where(['id'=>$id])->delete();
                    if($data2){
                        $data=[
                            'code'=>0,
                            'msg'=>'订单删除成功'
                        ];
                        $response = [
                            'data'=>$data
                        ];
                        return (json_encode($response,JSON_UNESCAPED_UNICODE));
                    }else{
                        $data=[
                            'code'=>1,
                            'msg'=>'订单删除失败'
                        ];
                        $response = [
                            'data'=>$data
                        ];
                        return (json_encode($response,JSON_UNESCAPED_UNICODE));
                    }

                }else{
                    $data2=DB::table('mt_order_detail')->where(['id'=>$id])->delete();
                    $data3=DB::table('mt_order')->where(['order_id'=>$data1->order_id,'uid'=>$uid])->delete();
                    if($data2 && $data3){
                        $data=[
                            'code'=>0,
                            'msg'=>'订单删除成功'
                        ];
                        $response = [
                            'data'=>$data
                        ];
                        return (json_encode($response,JSON_UNESCAPED_UNICODE));
                    }else{
                        $data=[
                            'code'=>1,
                            'msg'=>'订单删除失败'
                        ];
                        $response = [
                            'data'=>$data
                        ];
                        return (json_encode($response,JSON_UNESCAPED_UNICODE));
                    }
                }
            }
        }else{
            $response = [
                'code'=>1,
                'msg'=>'请先去登陆'
            ];
            return (json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }
    //修改用户订单状态              订单状态 0->未支付，1->已付款，3->确认收货,4->已完成,5->已关闭
    public function up_status_add(Request $request)
    {
        $order_id=$request->input('order_id');
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
//        $openid='o9VUc5AOsdEdOBeUAw4TdYg-F-dM';
        $datainfo=DB::table('mt_user')->where(['openid'=>$openid])->first();
        $uid=$datainfo->uid;
        if($openid){
//            $data_infos=DB::table('mt_order')->where(['uid'=>$uid,'order_id'=>$order_id,'order_status'=>1])->first();
            $up_status=DB::table('mt_order')->where(['uid'=>$uid,'order_id'=>$order_id,'order_status'=>1])->update(['order_status'=>3]);
            $up_detail=DB::table('mt_order_detail')->where(['uid'=>$uid,'order_id'=>$order_id,'order_status'=>1])->update(['order_status'=>3]);
            if($up_status && $up_detail){
                $data=[
                    'code'=>0,
                    'msg'=>'确认收货成功'
                ];
                $response = [
                    'data'=>$data
                ];
                return (json_encode($response,JSON_UNESCAPED_UNICODE));
            }else{
                $data=[
                    'code'=>1,
                    'msg'=>'确认收货失败,请重试'
                ];
                $response = [
                    'data'=>$data
                ];
                return (json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response = [
                'code'=>1,
                'msg'=>'请先去登陆'
            ];
            return (json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //退款申请
    public function refund_add(Request $request)
    {
        $id=$request->input('id');
        $refund_text_id=$request->input('refund_text_id');
        $refund_msg=$request->input('refund_msg');
        $openid1 = $request->input('openid');
        $key = $openid1;
        $openid = Redis::get($key);
//        $openid='o9VUc5AOsdEdOBeUAw4TdYg-F-dM';
        $dainfo_add=DB::table('mt_order_detail')->where(['id'=>$id])->first();
        $datainfo=DB::table('mt_user')->where(['openid'=>$openid])->first();
        $uid=$datainfo->uid;
        if($openid){
            $inser_refund=[
                'status_refund'=>1,
                'goods_id'=>$dainfo_add->goods_id,
                'id'=>$id,
                'refund_text_id'=>$refund_text_id,
                'refund_msg'=>$refund_msg
            ];
            $inser_add=DB::table('mt_refund')->insert($inser_refund);
            if($inser_add){
                $data=[
                    'code'=>0,
                    'msg'=>'审核已提交,请耐心等待审核',
                    'data'=>$dainfo_add->pay_price
                ];
                $response = [
                    'data'=>$data
                ];
                return (json_encode($response,JSON_UNESCAPED_UNICODE));
            }else{
                $data=[
                    'code'=>1,
                    'msg'=>'提交失败,请重新尝试退款'
                ];
                $response = [
                    'data'=>$data
                ];
                return (json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response = [
                'code'=>1,
                'msg'=>'请先去登陆'
            ];
            return (json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }



}
