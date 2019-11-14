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
    }

/*
 * 上传文件
 */
    public function upload(Request $request)
    {
        $url_name = $request->input('url_name');
//        var_dump($url_name);exit;
        $destination = './imgadvertis/';
        $file = $_FILES['file']; // 获取上传的图片
        //var_dump($file);exit;
        $filename = $file['name'];
        $filesize = $file['size'];
        $filetype = $file['type'];
        $upload   = move_uploaded_file($file['tmp_name'], $destination . iconv("UTF-8", "gb2312", $filename));

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
    public function goodsUpdate(Request $request){
        $goods_id = $request->input('goods_id');

    }


    //是否开启拼团
    public function admin_Assemble(Request $request){
        $is_promotion = $request->input('is_promotion');
        $goods_id = $request->input('goods_id');
        $promotion_price = $request->input('promotion_price');
        $promotion_prople = $request->input('promotion_prople');

    }
}
