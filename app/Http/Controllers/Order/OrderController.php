<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    //生成订单
    public function add_order(Request $request){
        $goods_id = $request->input('goods_id');    //商品id
        $total_price = $request->input('total_price');   //总价
//        $goods_id = [
//            '3',
//            '4'
//        ];
        //$total_price = "13131";
        $order_no = date("YmdHis",time()).rand(1000,9999);   //订单号
        $buy_num = $request->input('buy_num');    //购买数量
        $ip = $_SERVER['SERVER_ADDR'];
        $key = 'openid'.$ip;
        $openid = Redis::get($key);
        if($openid){
            $userInfo = DB::table('mt_user')->where('openid',$openid)->first();
            $wx_name = $userInfo->wx_name;
            $uid = $userInfo->uid;
            //var_dump($userInfo);

            $data_order = [
                'uid'=>$uid,
                'order_no'=>$order_no,
                'wx_name' =>$wx_name,
                'order_status'=>0,
                'total_price'=>$total_price,
                'create_time'=>time()
            ];
            $infodata =DB::table('mt_order')->insert($data_order);
            $dataData = DB::table('mt_order')->where('order_no',$order_no)->first();
            //var_dump($dataData);exit;
            $order_id = $dataData->order_id;
            session(['order_id'=>$order_id]);

            $cartUpdate=[
                'is_del'=>1,
                // 'buy_number'=>0,
                'update_time'=>time()
            ];
            $res = DB::table('mt_cart')->where('uid',$uid)->whereIn('goods_id',$goods_id)->update($cartUpdate);
            //添加订单详情表
            $num = DB::table('mt_goods')
                ->join('mt_cart','mt_goods.goods_id','=','mt_cart.goods_id')
                ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
                ->whereIn('mt_goods.goods_id',$goods_id)
                ->get();
            //var_dump($num);exit;
            foreach($num as $k=>$v){
                $info=[
                    'uid'=>$uid,
                    'order_id'=>$order_id,
                    'order_no'=>$order_no,
                    'goods_id'=>$v->goods_id,
                    'goods_name'=>$v->goods_name,
                    'price'=>$v->price,
                    'picture'=>$v->picture,
                    'buy_num'=>$v->buy_num,
                    'order_status'=>0,
                    'shop_id'=>$v->shop_id,
                    'shop_name'=>$v->shop_name,
                    'create_time'=>time()
                ];
                $datailData = DB::table('mt_order_detail')->insert($info);
            }

            $UpdateNum=[
                // 'is_del'=>2,
                'buy_num'=>0,
                'update_time'=>time()
            ];
            $res = DB::table('mt_cart')->where('uid',$uid)->whereIn('goods_id',$goods_id)->update($UpdateNum);
            if($res){
                $response = [
                    'error'=>'0',
                    'msg'=>'生成订单成功',
                    'order_no'=>$order_no
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $response = [
                    'error'=>'1',
                    'msg'=>'生成订单失败'
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }

        }else{
            $response = [
                'error'=>'1',
                'msg'=>'请先登录'
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }


    }

    //订单列表
    public function order_list(Request $request){
        $ip = $_SERVER['SERVER_ADDR'];
        $key = 'openid'.$ip;
        $openid = Redis::get($key);
        if($openid){
            $orderInfo = DB::table('mt_user')
                ->join('mt_order','mt_user.uid','=','mt_order.uid')
                ->where('mt_user.openid',$openid)
                ->get();
            $response = [
                'error'=>0,
                'data'=>$orderInfo
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $response = [
                'error'=>1,
                'msg'=>'请先去登陆'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //订单详情
    public function order_detail(Request $request){
        $order_id = $request->input('order_id');
        $order_id = 1;
        $order_detailInfo = DB::table('mt_order_detail')->where('order_id',$order_id)->get();
        //var_dump($order_detailInfo);exit;
        if($order_detailInfo){
            $response = [
                'error'=>0,
                'data'=>$order_detailInfo
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $response = [
                'error'=>1,
                'msg'=>'订单出现错误，请重新下单'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //删除订单
    public function delete_order(Request $request){
        $order_id = $request->input('order_id');
        $delete_order = DB::table('mt_order')->where('id',$order_id)->delete();
        //var_dump($delete_address);exit;
        if($delete_order == true){
            $response = [
                'error'=>'0',
                'msg'=>'删除成功'
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $response = [
                'error'=>'1',
                'msg'=>'修改失败'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //根据订单状态查找订单信息
    public function order_status_list(Request $request){
        $order_status_id = $request->input('order_status_id');
        //$order_status_id = 0;
        $ip = $_SERVER['SERVER_ADDR'];
        $key = 'openid'.$ip;
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
}
