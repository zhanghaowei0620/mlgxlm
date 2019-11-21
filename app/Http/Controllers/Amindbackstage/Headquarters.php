<?php

namespace App\Http\Controllers\Amindbackstage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

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
        $da = 111;

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
        }else{
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
        }else{
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
        }else{
            $orderInfo = DB::table('mt_order_detail')->where('shop_id',$shop_id)->get(['order_id','order_no','goods_id','goods_name','price','picture','buy_num','order_status','shop_id','shop_name','create_time'])->toArray();
//            var_dump($orderInfo);exit;
            $response = [
                'code'=>0,
                'data'=>$orderInfo,
                'msg'=>'数据请求成功'
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }

    }

    //订单详情-平台
    public function admin_orderDetail(Request $request){
        $order_id = $request->input('order_id');
        $orderInfo = DB::table('mt_order_detail')->where('order_id',$order_id)->get(['order_id','order_no','goods_id','goods_name','price','picture','buy_num','order_status','shop_id','shop_name','create_time'])->toArray();
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
        }else{
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



    

    //分类删除
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
