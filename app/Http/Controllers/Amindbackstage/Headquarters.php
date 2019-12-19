<?php

namespace App\Http\Controllers\Amindbackstage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Flex\Express\ExpressBird;

class Headquarters extends Controller
{
    /*
     * 商品展示
     */
    public function goodsList(Request $request)
    {
        $admin_judge = $request->input('admin_judge');
        $shop_id = $request->input('shop_id');
        if($admin_judge == 1){
            $data=DB::table('mt_goods')
                ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
                ->select(['goods_id','goods_name','market_price','price','picture','stock','promotion_type','goods_gd_num','mt_shop.shop_name','mt_goods.goods_effect','mt_goods.goods_duration','mt_goods.goods_process','mt_goods.goods_frequency','mt_goods.goods_overdue_time','mt_goods.goods_appointment'])
                ->paginate(6);
//        var_dump($data);exit;
            if($data){
                $response=[
                    'code'=>0,
                    'data'=>$data,
                    'msg'=>'商品展示成功'
                ];
                return (json_encode($response, JSON_UNESCAPED_UNICODE));
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'商品展示失败'
                ];
                return (json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }elseif($admin_judge == 2){
            $data=DB::table('mt_goods')
                ->where('shop_id',$shop_id)
                ->select(['goods_id','goods_name','price','picture','stock','goods_gd_num','goods_effect','goods_duration','goods_process','goods_frequency','goods_overdue_time','goods_appointment','is_promotion','limited_buy'])
                ->paginate(6);
//        var_dump($data);exit;
            if($data){
                $response=[
                    'code'=>0,
                    'data'=>$data,
                    'msg'=>'商品展示成功'
                ];
                return (json_encode($response, JSON_UNESCAPED_UNICODE));
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'商品展示失败'
                ];
                return (json_encode($response, JSON_UNESCAPED_UNICODE));
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
 * 上传文件
 */
    public function upload(Request $request)
    {
        $url_name = $request->input('url_name');
//        var_dump($url_name);exit;
        $destination = './images/';
        $file = $_FILES['file']; // 获取上传的图片
        $ext=$request->file('file')->getClientOriginalExtension();
        $filename=time().rand().".".$ext;
        $upload   = move_uploaded_file($file['tmp_name'], $destination . iconv("UTF-8", "gb2312", $filename));
//        $da = 111;

        $path = '/images/' . $filename;
        if($upload){
            $response = [
                'code' => 0,
                'path' => $path,
                'url_name' => $url_name,
                'msg' => '上传成功'
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

    }

    //分类
    public function goods_type(Request $request){
        $shop_id = $request->input('shop_id');
        $typeInfo = DB::select("select * from mt_type where p_id in(select t_id from mt_shop where shop_id = $shop_id)");
        if($typeInfo){
            $response = [
                'code'=>0,
                'data'=>$typeInfo,
                'msg'=>'成功'
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $response = [
                'code'=>1,
                'msg'=>'页面出错，请重试'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //商品添加
    public function goodsAdd(Request $request){
        $insert = [
            't_id'=>$request->input('t_id'),
            'goods_name'=>$request->input('goods_name'),
            'price'=>$request->input('price'),
            'goods_effect'=>$request->input('goods_effect'),
            'goods_duration'=>$request->input('goods_duration'),
            'goods_process'=>$request->input('goods_process'),
            'goods_frequency'=>$request->input('goods_frequency'),
            'goods_overdue_time'=> $request->input('goods_overdue_time'),
            'goods_appointment'=>$request->input('goods_appointment'),
            'goods_use_rule'=>$request->input('goods_use_rule'),
            'goods_planting_picture'=>$request->input('goods_planting_picture'),
            'picture'=>$request->input('picture'),
            'picture2'=>$request->input('picture2'),
            'picture3'=>$request->input('picture3'),
            'goods_picture_detail'=>$request->input('goods_picture_detail'),
            'shop_id'=>$request->input('shop_id')
        ];

        $goodsInsert = DB::table('mt_goods')->insertGetId($insert);
        if($goodsInsert){
            $response = [
                'code'=>0,
                'msg'=>'添加成功'
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $response = [
                'code'=>1,
                'msg'=>'添加失败,请重试'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    /*
     * 商品删除
     */
    public function goodsdelete(Request $request)
    {
        $goods_id=$request->input('goods_id');
        $where=[
            'goods_id'=>$goods_id
        ];
        $data=DB::table('mt_goods')->where($where)->delete();
        if($data){
            $response=[
                'code'=>0,
                'msg'=>'商品删除成功'
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $response=[
                'code'=>1,
                'msg'=>'商品删除失败'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //商品修改
    public function admin_goodsInfo(Request $request){
        $goods_id = $request->input('goods_id');
        $goodsInfo = DB::table('mt_goods')->where('goods_id',$goods_id)->get(['t_id','goods_name','price','goods_effect','goods_duration','goods_process','goods_frequency','goods_overdue_time','goods_appointment','goods_use_rule','goods_planting_picture','picture','picture2','picture3','goods_picture_detail'])->toArray();
//        var_dump($goodsInfo);exit;
        $response=[
            'code'=>0,
            'data'=>$goodsInfo,
            'msg'=>''
        ];
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
    public function admin_goodsUpdate(Request $request){
        $goods_id = $request->input('goods_id');
        $update = [
            't_id'=>$request->input('t_id'),
            'goods_name'=>$request->input('goods_name'),
            'price'=>$request->input('price'),
            'goods_effect'=>$request->input('goods_effect'),
            'goods_duration'=>$request->input('goods_duration'),
            'goods_process'=>$request->input('goods_process'),
            'goods_frequency'=>$request->input('goods_frequency'),
            'goods_overdue_time'=> $request->input('goods_overdue_time'),
            'goods_appointment'=>$request->input('goods_appointment'),
            'goods_use_rule'=>$request->input('goods_use_rule'),
            'goods_planting_picture'=>$request->input('goods_planting_picture'),
            'picture'=>$request->input('picture'),
            'picture2'=>$request->input('picture2'),
            'picture3'=>$request->input('picture3'),
            'goods_picture_detail'=>$request->input('goods_picture_detail'),
            'shop_id'=>$request->input('shop_id')
        ];
        $gooodsInfoUpdate = DB::table('mt_goods')->where('goods_id',$goods_id)->update($update);
        if($gooodsInfoUpdate>=0){
            $response=[
                'code'=>0,
                'msg'=>'修改成功 '
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }


    //是否开启拼团
    public function admin_Assemble(Request $request){
        $is_promotion = $request->input('is_promotion');   //是否开启  0为关闭  1为开启
        $goods_id = $request->input('goods_id');   //商品id
        $promotion_price = $request->input('promotion_price');    //拼团价格
        $promotion_prople = $request->input('promotion_prople');       //人数
        if($is_promotion == 1){
            $limited_buy = DB::table('mt_goods')->where('goods_id',$goods_id)->first(['limited_buy']);     //是否开启抢购   0关闭  1开启
            $is_coupon = DB::table('mt_goods')->where('goods_id',$goods_id)->first(['is_coupon']);    //是否开启优惠 1开启  2关闭
            if($limited_buy->limited_buy == 0 && $is_coupon->is_coupon == 2){
                $update = [
                    'promotion_price'=>$promotion_price,
                    'promotion_prople'=>$promotion_prople,
                    'is_promotion'=>$is_promotion,
                    'promotion_type'=>1
                ];
                $update_promotion = DB::table('mt_goods')->where('goods_id',$goods_id)->update($update);
                if($update_promotion >= 0){
                    $response=[
                        'code'=>0,
                        'msg'=>'开启成功'
                    ];
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'同一商品只能同时开启一种活动'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }

        }else{
            $update = [
                'is_promotion'=>$is_promotion,
                'promotion_price'=>0
            ];
            $update_promotion = DB::table('mt_goods')->where('goods_id',$goods_id)->update($update);
            if($update_promotion >= 0){
                $response=[
                    'code'=>0,
                    'msg'=>'该商品已关闭拼团'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    //是否开启限时抢
    public function admin_Limited(Request $request){
        $limited_buy = $request->input('limited_buy');      //是否开启限时抢  0为否 1为是
        $goods_id = $request->input('goods_id');        //商品id
        $limited_price = $request->input('limited_price');        //抢购价格
        $limited_start_time = $request->input('limited_start_time');     //开始时间
        $limited_stop_time = $request->input('limited_stop_time');   //结束时间
        $limited_prople = $request->input('limited_prople');     //抢购人数
        //$limited_ready_prople = $request->input('limited_ready_prople');

        if($limited_buy == 1){
            $is_promotion = DB::table('mt_goods')->where('goods_id',$goods_id)->first(['is_promotion']);     //是否开启拼团   0关闭  1开启
            $is_coupon = DB::table('mt_goods')->where('goods_id',$goods_id)->first(['is_coupon']);    //是否开启优惠 1开启  2关闭
            if($is_promotion->is_promotion == 0 && $is_coupon->is_coupon == 2){
                $update = [
                    'limited_price'=>$limited_price,
                    'limited_prople'=>$limited_prople,
                    'limited_start_time'=>$limited_start_time,
                    'limited_stop_time'=>$limited_stop_time,
                    'limited_buy'=>$limited_buy,
                    'promotion_type'=>4
                ];
                $update_promotion = DB::table('mt_goods')->where('goods_id',$goods_id)->update($update);
                if($update_promotion >= 0){
                    $response=[
                        'code'=>0,
                        'msg'=>'已开启抢购'
                    ];
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'同一商品只能同时开启一种活动'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }else{
            $update = [
                'limited_buy'=>$limited_buy,
                'promotion_type'=>0
            ];
            $update_promotion = DB::table('mt_goods')->where('goods_id',$goods_id)->update($update);
            if($update_promotion >= 0){
                $response=[
                    'code'=>0,
                    'msg'=>'抢购关闭'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    //拼团列表
    public function admin_Assemble_list(Request $request){
        $admin_judge = $request->input('admin_judge');
        $shop_id = $request->input('shop_id');
        if($admin_judge == 1){
            $data=DB::table('mt_goods')
                ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
                ->where(['is_promotion'=>1])
                ->select(['mt_goods.goods_id','mt_goods.goods_name','mt_goods.promotion_price','mt_goods.promotion_prople','mt_goods.prople','mt_shop.shop_name','mt_shop.shop_id'])
                ->paginate(6);
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'成功'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }elseif($admin_judge == 2){
            $data=DB::table('mt_goods')
                ->where(['is_promotion'=>1,'shop_id'=>$shop_id])
                ->select(['goods_id','goods_name','promotion_price','promotion_prople','prople','picture'])
                ->paginate(6);
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'成功'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>2,
                'msg'=>'请先登录'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //限时抢列表
    public function admin_Limited_list(Request $request){
        //echo date('Y-m-d H:i:s',1572843823);exit;
        $admin_judge = $request->input('admin_judge');
        $shop_id = $request->input('shop_id');
        $time = time();
        if($admin_judge == 1){
            $data=DB::table('mt_goods')
                ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
                ->where('limited_buy',1)
                ->where('limited_stop_time','>',$time)
                ->select(['mt_goods.goods_id','mt_goods.goods_name','mt_goods.limited_price','mt_goods.limited_prople','mt_goods.limited_ready_prople','mt_shop.shop_name','mt_shop.shop_id','limited_start_time','limited_stop_time'])
                ->paginate(6);
//            var_dump($data);exit;
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'成功'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }elseif($admin_judge == 2){
            $data=DB::table('mt_goods')
                ->where(['limited_buy'=>1,'shop_id'=>$shop_id])
                ->where('limited_stop_time','>',$time)
                ->select(['goods_id','goods_name','limited_price','limited_prople','limited_ready_prople','picture','limited_start_time','limited_stop_time'])
                ->paginate(6);
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'成功'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>2,
                'msg'=>'请先登录'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //定时任务 -定时查找过期限时抢数据 修改状态
    public function admin_Limited_list_update_status(Request $request){
        $goodsInfo = DB::table('mt_goods')->where('limited_stop_time','<',time())->get(['goods_id'])->toArray();
//        var_dump($goodsInfo);exit;
        foreach ($goodsInfo as $k => $v) {
            DB::table('mt_goods')->where('goods_id',$v->goods_id)->update(['promotion_type'=>0,'limited_buy'=>0,'limited_stop_time'=>NULL,'limited_start_time'=>NULL]);
        }

    }



        //无限级分类
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

    //分类管理
    public function admin_typeInfo(Request $request){
        $info = DB::table('mt_type')->get();
        $result = $this->list_level($info,$pid=0,$level=0);
//        var_dump($result);exit;
        $response=[
            'code'=>0,
            'data'=>$result,
            'msg'=>'数据请求成功'
        ];
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    //分类添加
    public function admin_typeAdd(Request $request){
        $pid = $request->input('p_id');      //最大级为0   二级为最大级t_id
        $t_name = $request->input('t_name');    //分类名称
        $t_img = $request->input('t_img');
        $tInfo = DB::table('mt_type')->where('t_name',$t_name)->first();
        if(!$tInfo){
            $insert = [
                't_name'=>$t_name,
                'p_id'=>$pid,
                't_img'=>$t_img
            ];
            $oneInsert = DB::table('mt_type')->insertGetId($insert);
            if($oneInsert){
                $response=[
                    'code'=>0,
                    'msg'=>'分类添加成功'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'请求失败,请重试'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>2,
                'msg'=>'该分类已存在,请重试'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    //分类修改
    public function admin_typeUpdate(Request $request){
        $t_id = $request->input('t_id');    //分类id
        $t_name = $request->input('t_name');   //分类名称
        $t_img = $request->input('t_img');
        $typeInfo = DB::table('mt_type')->where('t_id',$t_id)->first();
        $pid = $typeInfo->p_id;
//        var_dump($pid);exit;
        if($pid == 0){
            $response=[
                'code'=>0,
                'msg'=>'您无权修改最大级分类信息'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $update = DB::table('mt_type')->where('t_id',$t_id)->update(['t_name'=>$t_name,'t_img'=>$t_img]);
            if($update >0){
                $response=[
                    'code'=>0,
                    'msg'=>'分类修改成功'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $response=[
                    'code'=>0,
                    'msg'=>'您并未修改任何信息'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }

    }

    //订单列表
    public function admin_orderList(Request $request){
        $admin_judge = $request->input('admin_judge');
        $shop_id = $request->input('shop_id');
        if($admin_judge == 1){
            $orderInfo = DB::table('mt_order')->get()->toArray();
            //var_dump($orderInfo);exit;
            $response = [
                'code'=>0,
                'data'=>$orderInfo,
                'msg'=>'数据请求成功'
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }elseif($admin_judge == 2){
            $orderInfo = DB::table('mt_order_detail')->where('shop_id',$shop_id)->get(['order_id','order_no','goods_id','goods_name','price','picture','buy_num','order_status','shop_id','shop_name','create_time'])->toArray();
//            var_dump($orderInfo);exit;
            $response = [
                'code'=>0,
                'data'=>$orderInfo,
                'msg'=>'数据请求成功'
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $response=[
                'code'=>2,
                'msg'=>'请先登录'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    //订单详情-平台
    public function admin_orderDetail(Request $request){
        $order_id = $request->input('order_id');
        $orderInfo = DB::table('mt_order_detail')
            ->join('mt_order','mt_order.order_id','=','mt_order_detail.order_id')
            ->where('mt_order_detail.order_id',$order_id)->get(['mt_order_detail.order_id','mt_order_detail.order_no','mt_order_detail.goods_id','mt_order_detail.goods_name','mt_order_detail.price','mt_order_detail.picture','mt_order_detail.buy_num','mt_order.order_status','mt_order_detail.shop_id','mt_order_detail.shop_name','mt_order_detail.create_time'])->toArray();
//        var_dump($orderInfo);exit;
        if($orderInfo){
            $response = [
                'code'=>0,
                'data'=>$orderInfo,
                'msg'=>'数据请求成功'
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $response = [
                'code'=>1,
                'msg'=>'数据请求失败'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }

    //案例添加
    public function admin_caseAdd(Request $request){
        $shop_id = $request->input('shop_id');    //店铺id
        $goods_id = $request->input('goods_id');    //商品id
        $case_front = $request->input('case_front');    //案例-前
        $case_after = $request->input('case_after');    //案例-后
        $case_trouble = $request->input('case_trouble');    ////案例-毛病

        $insert = [
            'shop_id'=>$shop_id,
            'goods_id'=>$goods_id,
            'case_front'=>$case_front,
            'case_after'=>$case_after,
            'case_trouble'=>$case_trouble
        ];
        $insertCase = DB::table('mt_case')->insertGetId($insert);
        if($insertCase){
            $response = [
                'code'=>0,
                'msg'=>'添加成功'
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $response = [
                'code'=>1,
                'msg'=>'添加失败'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //案例列表
    public function admin_caseList(Request $request){
        $admin_judge = $request->input('admin_judge');
        $shop_id = $request->input('shop_id');
        if($admin_judge == 1){
            $caseInfo = DB::table('mt_case')      //案例
                ->join('mt_goods','mt_case.goods_id','=','mt_goods.goods_id')
                ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
                ->select(['case_id','case_front','case_after','case_trouble','goods_name','shop_name'])
                ->paginate(2);


            $response = [
                'code'=>0,
                'data'=>$caseInfo,
                'msg'=>'数据请求成功'
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }elseif($admin_judge == 2){
            $caseInfo = DB::table('mt_case')      //案例
                ->join('mt_goods','mt_case.goods_id','=','mt_goods.goods_id')
                ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
                ->where('mt_case.shop_id',$shop_id)
                ->select(['case_id','case_front','case_after','case_trouble','goods_name','shop_name'])
                ->paginate(2);
            $response = [
                'code'=>0,
                'data'=>$caseInfo,
                'msg'=>'添加成功'
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $response=[
                'code'=>2,
                'msg'=>'请先登录'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    //案例删除
    public function admin_caseDelete(Request $request){
        $case_id = $request->input('case_id');
        $deleteCase = DB::table('mt_case')->where('case_id',$case_id)->delete();
        if($deleteCase){
            $response = [
                'code'=>0,
                'msg'=>'删除成功'
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $response = [
                'code'=>1,
                'msg'=>'请求错误,请重试'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //案例修改
    public function admin_caseUpdate(Request $request){
        $case_id = $request->input('case_id');    //案例id
        $case_front = $request->input('case_front');    //案例-前
        $case_after = $request->input('case_after');    //案例-后
        $case_trouble = $request->input('case_trouble');    //案例-毛病

        $update = [
            'case_id'=>$case_id,
            'case_front'=>$case_front,
            'case_after'=>$case_after,
            'case_trouble'=>$case_trouble,
        ];

        $updateInfo = DB::table('mt_case')->where('case_id',$case_id)->update($update);
        if($updateInfo >0){
            $response=[
                'code'=>0,
                'msg'=>'案例修改成功'
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $response=[
                'code'=>0,
                'msg'=>'您并未修改任何信息'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }
    //获得店铺下的所有商品
    public function admin_shop_goodsInfo(Request $request){
        $shop_id=$request->input('shop_id');
        $data=DB::table('mt_shop')
            ->join('mt_goods','mt_shop.shop_id','=','mt_goods.shop_id')
            ->where(['mt_shop.shop_id'=>$shop_id])
            ->get();
//        var_dump($data);die;
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'所有商品获得成功'
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $response=[
                'code'=>0,
                'msg'=>'所有商品获得失败'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //预约
    public function admin_appointment(Request $request){

    }

    //判断是否为分销商
    public function admin_is_reseller(Request $request){
        $shop_id = $request->input('shop_id');
        $shop_resellerInfo = DB::table('mt_shop')->where('shop_id',$shop_id)->first(['shop_reseller']);
        $shop_reseller = $shop_resellerInfo->shop_reseller;
        $response=[
            'code'=>0,
            'data'=>$shop_reseller,
            'msg'=>'数据请求成功'
        ];
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    //申请成为分销商
    public function admin_apply_reseller(Request $request)
    {
        $shop_id=$request->input('shop_id');
        $upinfo=[
            'shop_reseller'=>2,
            'shop_reseller_time'=>time()
        ];
//        var_dump($upinfo);die;
        $data=DB::table('mt_shop')
            ->where(['shop_id'=>$shop_id])
            ->update($upinfo);
        $admin_data=DB::table('admin_user')
            ->where(['shop_id'=>$shop_id])
            ->update(['shop_reseller'=>2]);
//        var_dump($data);die;
        if($data >0 && $admin_data >0   ){
            $response=[
                'code'=>0,
                'msg'=>'申请成功,请耐心等待审核'
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $response=[
                'code'=>1,
                'msg'=>'请求出现错误,请重试'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //申请列表
    public function admin_apply_reseller_list(Request $request){
        $admin_judge = $request->input('admin_judge');
        if($admin_judge == 1){
            $shop_apply_reseller = DB::table('mt_shop')->where('shop_reseller',2)->select(['shop_id','shop_name','shop_img','shop_address_provice','shop_address_city','shop_address_area','shop_reseller_time'])->paginate(7);
//            var_dump($shop_apply_reseller);exit;
            $response=[
                'code'=>0,
                'data'=>$shop_apply_reseller,
                'msg'=>'数据请求成功'
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $response=[
                'code'=>1,
                'msg'=>'没有权限'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //获取access_Token
    public function admin_accessToken1(){
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

    //审核
    public function admin_reseller_examine(Request $request){
        $accessToken = $this->admin_accessToken1();
        $shop_id = $request->input('shop_id');
        $admin_judge = $request->input('admin_judge');
        if($admin_judge == 1){
            $shopUpdate = DB::table('mt_shop')->where('shop_id',$shop_id)->update(['shop_reseller'=>1]);
            $admin_userUpdate = DB::table('admin_user')->where('shop_id',$shop_id)->update(['shop_reseller'=>1]);
            if($shopUpdate >0 && $admin_userUpdate > 0){
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
                $shopInfo = DB::table('mt_shop')->where('shop_id',$shop_id)->first(['uid']);
                $uid = $shopInfo->uid;

                DB::table('mt_user')->where('uid',$uid)->update(['shop_rand'=>$img,'shop_random_str'=>$scene,'p_id'=>0,'a_id'=>$uid,'mt_reseller'=>1]);

//                echo 111;exit;
                $response=[
                    'code'=>0,
                    'msg'=>'审核成功'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'系统出现错误，请重试'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>1,
                'msg'=>'抱歉，您没有权限执行此功能'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //取消分销商资格
    public function admin_reseller_delete(Request $request){
        $shop_id = $request->input('shop_id');
        $admin_judge = $request->input('admin_judge');
        if($admin_judge == 1){
            $shopUpdate = DB::table('mt_shop')->where('shop_id',$shop_id)->update(['shop_reseller'=>0]);
            $admin_userUpdate = DB::table('admin_user')->where('shop_id',$shop_id)->update(['shop_reseller'=>0]);
            if($shopUpdate >0 && $admin_userUpdate > 0){
//                echo 111;exit;
                $response=[
                    'code'=>0,
                    'msg'=>'成功取消分销商资格'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'系统出现错误，请重试'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>1,
                'msg'=>'抱歉，您没有权限执行此功能'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //分销商列表
    public function admin_reseller_list(Request $request){
        $admin_judge = $request->input('admin_judge');
        if($admin_judge == 1){
            $resellerInfo = DB::table('mt_shop')
                ->join('admin_user','mt_shop.shop_id','=','admin_user.shop_id')
                ->where('mt_shop.shop_reseller',1)
                ->select(['mt_shop.shop_id','mt_shop.shop_name','mt_shop.shop_img','mt_shop.shop_address_provice','mt_shop.shop_address_city','mt_shop.shop_address_area','admin_user.admin_user','admin_user.admin_tel'])->paginate(7);
//            var_dump($resellerInfo);exit;
            $response=[
                'code'=>0,
                'data'=>$resellerInfo,
                'msg'=>'数据请求成功'
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $response=[
                'code'=>1,
                'msg'=>'抱歉，您没有权限执行此功能'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //上传分销商品
    public function admin_reseller_upload_goods(Request $request){
        $shop_id = $request->input('shop_id');          //店铺id
        $re_goods_name = $request->input('re_goods_name');     //商品名称
        $re_goods_price = $request->input('re_goods_price');    //价格
        $re_goods_stock = $request->input('re_goods_stock');    //销量
        $re_goods_picture = $request->input('re_goods_picture');    //主图
        $re_goods_introduction = $request->input('re_goods_introduction');  //商品简介
        $is_distribution = $request->input('is_distribution');      //是否开启分销   0为否  1为是
        $re_goods_planting_picture = $request->input('re_goods_planting_picture');      //轮播图
        $re_goods_picture_detail = $request->input('re_goods_picture_detail');      //图文详情
        $re_production_time = $request->input('re_production_time');        //生产时间
        $re_expiration_time = $request->input('re_expiration_time');        //过期时间

        $admin_userInfo = DB::table('admin_user')->where('shop_id', $shop_id)->first(['shop_reseller']);
        $shop_reseller = $admin_userInfo->shop_reseller;
        //            var_dump($shop_reseller);exit;
        if ($shop_reseller == 1) {
            $insert = [
                're_goods_name'=>$re_goods_name,
                're_goods_price'=>$re_goods_price,
                're_goods_stock'=>$re_goods_stock,
                're_goods_picture'=>$re_goods_picture,
                're_goods_introduction'=>$re_goods_introduction,
                'is_distribution'=>$is_distribution,
                're_goods_planting_picture'=>$re_goods_planting_picture,
                're_goods_picture_detail'=>$re_goods_picture_detail,
                're_production_time'=>$re_production_time,
                're_expiration_time'=>$re_expiration_time,
                'shop_id'=>$shop_id,
                'create_time'=>time()
            ];
            $re_goodsInsert = DB::table('re_goods')->insertGetId($insert);
//            var_dump($re_goodsInsert);exit;
            if($re_goodsInsert){
                $response=[
                    'code'=>0,
                    'msg'=>'上传成功'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'系统出现错误,请重试'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>1,
                'msg'=>'抱歉，您还不是分销商，暂无权限'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //分销商品列表
    public function admin_reseller_goods_list(Request $request){
        $admin_judge = $request->input('admin_judge');
        $shop_id = $request->input('shop_id');
        if($admin_judge == 1){
            $resellerInfo=DB::table('re_goods')
                ->join('mt_shop','mt_shop.shop_id','=','re_goods.shop_id')
                ->select(['mt_shop.shop_id','mt_shop.shop_name','re_goods.re_goods_id','re_goods_name','re_goods_price','re_goods_stock','re_goods_picture','re_goods_introduction','is_distribution','re_goods_volume','re_goods_planting_picture','re_goods_picture_detail','re_production_time','re_expiration_time'])
                ->paginate(7);
//        var_dump($resellerInfo);exit;
            if($resellerInfo){
                $response=[
                    'code'=>0,
                    'data'=>$resellerInfo,
                    'msg'=>'数据请求成功'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'数据请求失败'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }elseif($admin_judge == 2){
            $admin_userInfo = DB::table('admin_user')->where('shop_id', $shop_id)->first(['shop_reseller']);
            $shop_reseller = $admin_userInfo->shop_reseller;
            if($shop_reseller == 1){
                $resellerInfo=DB::table('re_goods')
                    ->join('mt_shop','mt_shop.shop_id','=','re_goods.shop_id')
                    ->where('re_goods.shop_id',$shop_id)
                    ->select(['mt_shop.shop_id','mt_shop.shop_name','re_goods.re_goods_id','re_goods_name','re_goods_price','re_goods_stock','re_goods_picture','re_goods_introduction','is_distribution','re_goods_volume','re_goods_planting_picture','re_goods_picture_detail','re_production_time','re_expiration_time'])
                    ->paginate(6);
//        var_dump($resellerInfo);exit;
                if($resellerInfo){
                    $response=[
                        'code'=>0,
                        'data'=>$resellerInfo,
                        'msg'=>'数据请求成功'
                    ];
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }else{
                    $response=[
                        'code'=>1,
                        'msg'=>'数据请求失败'
                    ];
                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                }
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'您还不是分销商，暂无查看权限'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>2,
                'msg'=>'请先登录'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //是否开启分销
    public function admin_reseller_goods(Request $request){
        $re_goods_id = $request->input('re_goods_id');
        $shop_id = $request->input('shop_id');
        $goods_reseller = $request->input('goods_reseller');    //0为否  1为是
//        var_dump($goods_reseller);exit;
        $shopInfo = DB::table('mt_shop')->where('shop_id',$shop_id)->first(['shop_reseller']);      //查看当前店铺是否为分销商
        $shop_reseller = $shopInfo->shop_reseller;
        $admin_userInfo = DB::table('admin_user')->where('shop_id',$shop_id)->first(['shop_reseller']);    //查看当前用户是否为分销商
        $admin_shop_reseller = $admin_userInfo->shop_reseller;
//        var_dump($admin_shop_reseller);exit;
        $goods_resellerInfo = DB::table('re_goods')->where('re_goods_id',$re_goods_id)->first(['is_distribution']);      //查看当前商品是否开启分销
//        var_dump($goods_resellerInfo);exit;
        $is_distribution = $goods_resellerInfo->is_distribution;

        if($shop_reseller == 1 && $admin_shop_reseller == 1){
            if($goods_reseller == 1){
                if($is_distribution == 0){
                    $update_goods_reseller = DB::table('re_goods')->where('re_goods_id',$re_goods_id)->update(['is_distribution'=>$goods_reseller]);
                    if($update_goods_reseller>0){
                        $response=[
                            'code'=>0,
                            'msg'=>'商品开启分销成功'
                        ];
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }else{
                        $response=[
                            'code'=>1,
                            'msg'=>'系统出现错误，请重试'
                        ];
                        die(json_encode($response, JSON_UNESCAPED_UNICODE));
                    }
                }else{
                    $response=[
                        'code'=>1,
                        'msg'=>'该商品已是分销商品，请勿重复点击'
                    ];
                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                }
            }else{
                if($is_distribution == 1){
                    $update_goods_reseller = DB::table('re_goods')->where('re_goods_id',$re_goods_id)->update(['is_distribution'=>$goods_reseller]);
                    if($update_goods_reseller>0){
                        $response=[
                            'code'=>0,
                            'msg'=>'商品关闭分销成功'
                        ];
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }else{
                        $response=[
                            'code'=>1,
                            'msg'=>'系统出现错误，请重试'
                        ];
                        die(json_encode($response, JSON_UNESCAPED_UNICODE));
                    }
                }else{
                    $response=[
                        'code'=>1,
                        'msg'=>'该商品并未开启分销，请勿重复点击'
                    ];
                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                }
            }

        }else{
            $response=[
                'code'=>1,
                'msg'=>'抱歉，您还不是分销商，暂无权限'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    //订单
    public function admin_reseller_order(Request $request){
        $admin_judge = $request->input('admin_judge');
        $shop_id = $request->input('shop_id');
        if($admin_judge == 1){
            $orderInfo = DB::table('re_order')->select()->paginate(7);
//            var_dump($orderInfo);exit;
            if($orderInfo){
                $response=[
                    'code'=>0,
                    'data'=>$orderInfo,
                    'msg'=>'数据请求成功'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'系统出现错误，请重试'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }elseif($admin_judge == 2){
            $orderInfo = DB::table('re_order')->where(['re_order.shop_id'=>$shop_id])->select()->paginate(7);
            if($orderInfo){
                $response=[
                    'code'=>0,
                    'data'=>$orderInfo,
                    'msg'=>'数据请求成功'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'系统出现错误，请重试'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>2,
                'msg'=>'请先登录'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //所有物流方式
    public function admin_Logistics_type(Request $request){
        $logisticsInfo = DB::table('mt_logistics')->get()->toArray();
        $response=[
            'code'=>0,
            'data'=>$logisticsInfo,
            'msg'=>'数据请求成功'
        ];
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    //确认发货
    public function admin_reseller_Confirm_shipment(Request $request){
        $re_order_id = $request->input('re_order_id');
        $admin_judge = $request->input('admin_judge');
        $log_id = $request->input('log_id');
        $log_num = $request->input('log_num');
        if($admin_judge == 2){
            $orderUpdate = DB::table('re_order')->where('re_order_id',$re_order_id)->update(['shipping_type'=>$log_id,'logistics_no'=>$log_num,'order_status'=>2]);
            if($orderUpdate > 0){
                $response=[
                    'code'=>0,
                    'msg'=>'确认发货成功'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'系统出现错误,请重试'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>1,
                'msg'=>'只有店铺才能使用此功能'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //获取物流信息
    public function admin_reseller_order_information(Request $request){
        $re_order_id = $request->input('re_order_id');
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
            'msg'=>'数据请求成功'
        ];
        $response = [
            'data' => $data
        ];
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    //分销商品修改
    public function admin_reseller_goods_update(Request $request){
        $shop_id = $request->input('shop_id');
        $re_goods_id = $request->input('re_goods_id');
        $re_goods_name = $request->input('re_goods_name');     //商品名称
        $re_goods_price = $request->input('re_goods_price');    //价格
        $re_goods_stock = $request->input('re_goods_stock');    //库存
        $re_goods_picture = $request->input('re_goods_picture');    //主图
        $re_goods_introduction = $request->input('re_goods_introduction');  //商品简介
        $is_distribution = $request->input('is_distribution');      //是否开启分销   0为否  1为是
        $re_goods_planting_picture = $request->input('re_goods_planting_picture');      //轮播图
        $re_goods_picture_detail = $request->input('re_goods_picture_detail');      //图文详情
        $re_production_time = $request->input('re_production_time');        //生产时间
        $re_expiration_time = $request->input('re_expiration_time');        //过期时间
        $shopInfo = DB::table('mt_shop')->where('shop_id',$shop_id)->first(['shop_reseller']);      //查看当前店铺是否为分销商
        $shop_reseller = $shopInfo->shop_reseller;
        $admin_userInfo = DB::table('admin_user')->where('shop_id',$shop_id)->first(['shop_reseller']);    //查看当前用户是否为分销商
        $admin_shop_reseller = $admin_userInfo->shop_reseller;
        if($shop_reseller == 1 && $admin_shop_reseller == 1){
            $update = [
                're_goods_name'=>$re_goods_name,
                're_goods_price'=>$re_goods_price,
                're_goods_stock'=>$re_goods_stock,
                're_goods_picture'=>$re_goods_picture,
                're_goods_introduction'=>$re_goods_introduction,
                'is_distribution'=>$is_distribution,
                're_goods_planting_picture'=>$re_goods_planting_picture,
                're_goods_picture_detail'=>$re_goods_picture_detail,
                're_production_time'=>$re_production_time,
                're_expiration_time'=>$re_expiration_time,
                'shop_id'=>$shop_id
            ];
            $update_re_goodsInfo = DB::table('re_goods')->where('re_goods_id',$re_goods_id)->update($update);
            if($update_re_goodsInfo){
                $response=[
                    'code'=>0,
                    'msg'=>'修改成功'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'系统出现错误,请重试'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>2,
                'msg'=>'抱歉，您还不是分销商，暂无权限'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //分销商品删除
    public function admin_reseller_goods_delete(Request $request){
        $shop_id = $request->input('shop_id');
        $re_goods_id = $request->input('re_goods_id');
        $shopInfo = DB::table('mt_shop')->where('shop_id',$shop_id)->first(['shop_reseller']);      //查看当前店铺是否为分销商
        $shop_reseller = $shopInfo->shop_reseller;
        $admin_userInfo = DB::table('admin_user')->where('shop_id',$shop_id)->first(['shop_reseller']);    //查看当前用户是否为分销商
        $admin_shop_reseller = $admin_userInfo->shop_reseller;
        if($shop_reseller == 1 && $admin_shop_reseller == 1){
            $delete_re_goodsInfo = DB::table('re_goods')->where('re_goods_id',$re_goods_id)->delete();
            if($delete_re_goodsInfo){
                $response=[
                    'code'=>0,
                    'msg'=>'删除成功'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'系统出现错误，请重试'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>2,
                'msg'=>'抱歉，您还不是分销商，暂无权限'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //我的团队
    public function admin_my_team(Request $request){
        $admin_judge = $request->input('admin_judge');
        if($admin_judge == 2){
            $shop_id = $request->input('shop_id');
            $shopInfo = DB::table('mt_shop')->where('shop_id',$shop_id)->first(['uid']);
//        var_dump($shopInfo);exit;
            $uid = $shopInfo->uid;
            $teamInfo = DB::table('mt_user')->where('a_id',$uid)->get()->toArray();
            $total_num = DB::table('mt_user')->where('a_id',$uid)->count();   //总人数
            $data = [
                'teamInfo'=>$teamInfo,
                'total_num'=>$total_num,
            ];
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'数据请求成功'
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $response=[
                'code'=>1,
                'msg'=>'暂无权限'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //分销商申请列表
    public function admin_Application_reseller_list(Request $request){
        $is_distribution = $request->input('is_distribution');   //1为分销代理   2为分销商  3为异业联盟
        $mt_distributionInfo = DB::table('mt_distribution')->where('is_distribution',$is_distribution)->paginate(7);
        $response=[
            'code'=>0,
            'data'=>$mt_distributionInfo,
            'msg'=>'数据请求成功'
        ];
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    //协议添加
    public function admin_rule_Add(Request $request){
        $rule_title = $request->input('rule_title');
        $rule_con = $request->input('rule_con');
        $admin_judge = $request->input('admin_judge');
        if($admin_judge == 1){
            $insert = [
                'rule_title'=>$rule_title,
                'rule_con'=>$rule_con,
                'rule_time'=>time()
            ];
            $insertInfo = DB::table('mt_rules')->insert($insert);
            if($insertInfo){
                $response=[
                    'code'=>0,
                    'msg'=>'添加成功'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'系统出现错误,请重试'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>2,
                'msg'=>'暂无权限'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //协议列表
    public function admin_rule_List(Request $request){
        $admin_judge = $request->input('admin_judge');
        if($admin_judge == 1){
            $rulesInfo = DB::table('mt_rules')->get()->toArray();
            $response=[
                'code'=>0,
                'data'=>$rulesInfo,
                'msg'=>'数据请求成功'
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $response=[
                'code'=>2,
                'msg'=>'暂无权限'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }


    //协议修改
    public function admin_rule_Update(Request $request){
        $rule_id = $request->input('rule_id');
        $rule_title = $request->input('rule_title');
        $rule_con = $request->input('rule_con');
        $admin_judge = $request->input('admin_judge');
        if($admin_judge == 1){
            $update = [
                'rule_title'=>$rule_title,
                'rule_con'=>$rule_con,
            ];
            $updateInfo = DB::table('mt_rules')->where('rule_id',$rule_id)->update($update);
            if($updateInfo > 0){
                $response=[
                    'code'=>0,
                    'msg'=>'修改成功'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'系统出现错误,请重试'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>2,
                'msg'=>'暂无权限'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

    }

    //权限管理
    public function admin_rule_Delete(Request $request){
        $rule_id = $request->input('rule_id');
        $admin_judge = $request->input('admin_judge');
        if($admin_judge == 1){
            $deleteInfo = DB::table('mt_rules')->where('rule_id',$rule_id)->delete();
            if($deleteInfo){
                $response=[
                    'code'=>0,
                    'msg'=>'删除成功'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'系统出现错误,请重试'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>2,
                'msg'=>'暂无权限'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //协议详情
    public function admin_rule_Detail(Request $request){
        $rule_id = $request->input('rule_id');
        $admin_judge = $request->input('admin_judge');
        if($admin_judge == 1){
            $ruleInfo = DB::table('mt_rules')->where('rule_id',$rule_id)->first();
            if($ruleInfo){
                $response=[
                    'code'=>0,
                    'data'=>$ruleInfo,
                    'msg'=>'数据请求成功'
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }else{
                $response=[
                    'code'=>1,
                    'msg'=>'系统出现错误,请重试'
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'code'=>2,
                'msg'=>'暂无权限'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }


















    //分类删除3hy77
//    public function admin_typeDelete(Request $request){
//        $t_id = $request->input('t_id');
//        $typeInfo = DB::table('mt_type')->where('t_id',$t_id)->first();
//        $pid = $typeInfo->p_id;
////        var_dump($pid);exit;
//        if($pid == 0){
//
//        }
//        $typeDelete = DB::table('mt_user')->where('t_id',$t_id)->delete();
//        if($typeDelete){
//            $response=[
//                'code'=>0,
//                'msg'=>'分类删除成功'
//            ];
//            return json_encode($response, JSON_UNESCAPED_UNICODE);
//        }
//    }




}
