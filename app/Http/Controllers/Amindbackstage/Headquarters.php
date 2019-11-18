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

        $path = '/imgadvertis/' . $filename;
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
                'is_promotion'=>$is_promotion
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
                'limited_buy'=>$limited_buy
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


}
