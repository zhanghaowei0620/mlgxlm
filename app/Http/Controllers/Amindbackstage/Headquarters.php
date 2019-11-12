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
        $data=DB::table('mt_goods')
            ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
            ->select(['goods_id','goods_name','market_price','price','picture','stock','promotion_type','goods_gd_num','mt_shop.shop_name'])
            ->paginate(6);
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

/*
 * 商品添加
 */
    public function upload(Request $request)
    {
        $url_name = $request->input('url_name');
//        var_dump($url_name);exit;
        $destination = '/imgadvertis/';
        $file = $_FILES['file']; // 获取上传的图片
        //var_dump($file);exit;
        $filename = $file['name'];
        $filesize = $file['size'];
        $filetype = $file['type'];
        $upload   = move_uploaded_file($file['tmp_name'], $destination . iconv("UTF-8", "gb2312", $filename));

        $path = $destination . $filename;
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
                'typeInfo'=>$typeInfo
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
        $t_id = $request->input('t_id');          //类型id
        $shop_id = $request->input('shop_id');   //店铺id
        $goods_name=$request->input('goods_name');      //商品名称
        $price=$request->input('price');    //价格
        $goods_effect = $request->input('goods_effect');   //功效
        $goods_duration = $request->input('goods_duration');     //时长
        $goods_process = $request->input('goods_process');    //流程
        $goods_frequency = $request->input('goods_frequency');     //次数
        $goods_overdue_time = $request->input('goods_overdue_time');        //有效期
        $goods_appointment = $request->input('goods_appointment');          //预约提醒
        $goods_use_rule = $request->input('goods_use_rule');    //使用规则
        $goods_planting_picture = $request->input('goods_planting_picture');   //轮播图
        $goods_picture = $request->input('picture');     //主图(最大)
        $goods_picture2 = $request->input('picture2');     //主图(第二大)
        $goods_picture3 = $request->input('picture3');     //主图(最小)
        $goods_picture_detail = $request->input('goods_picture_detail');     //图文详情

        $insert = [
            't_id'=>$t_id,
            'goods_name'=>$goods_name,
            'price'=>$price,
            'goods_effect'=>$goods_effect,
            'goods_duration'=>$goods_duration,
            'goods_process'=>$goods_process,
            'goods_frequency'=>$goods_frequency,
            'goods_overdue_time'=>$goods_overdue_time,
            'goods_appointment'=>$goods_appointment,
            'goods_use_rule'=>$goods_use_rule,
            'goods_planting_picture'=>$goods_planting_picture,
            'picture'=>$goods_picture,
            'picture2'=>$goods_picture2,
            'picture3'=>$goods_picture3,
            'goods_picture_detail'=>$goods_picture_detail,
            'shop_id'=>$shop_id
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
                'code'=>1,
                'data'=>$data,
                'msg'=>'商品删除成功'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'code'=>1,
                'msg'=>'商品删除失败'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    //商品修改
    public function goodsUpdate(Request $request){
        $goods_id = $request->input('goods_id');

    }
}
