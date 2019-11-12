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
                'error'=>1,
                'msg'=>'商品展示失败'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

/*
 * 商品添加
 */
    public function goodsAdd(Request $request)
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

        $path = $destination . $filename;
        if($upload){
            $response = [
                'code' => 0,
                'path' => $path,
                'url_name' => $url_name,
                'msg' => '上传成功'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }




//        $type_id = $request->input('type_id');          //类型id
//        $goods_name=$request->input('goods_name');      //商品名称
//        $price=$request->input('price');    //价格
//        $goods_effect = $request->input('goods_effect');   //功效
//        $goods_duration = $request->input('goods_duration');     //时长
//        $goods_process = $request->input('goods_process');    //流程
//        $goods_frequency = $request->input('goods_frequency');     //次数
//        $goods_overdue_time = $request->input('goods_overdue_time');        //有效期
//        $goods_appointment = $request->input('goods_appointment');          //预约提醒
//        $goods_use_rule = $request->input('goods_use_rule');
//
////        $picture=$request->input('picture');
//        $stock=$request->input('stock');
//        $promotion_type=$request->input('promotion_type');
//        $goods_gd_num=$request->input('goods_gd_num');
//        $dainser=[
//            'goods_name'=>$goods_name,
//            'price'=>$price,
////            'picture'=>$picture,
//            'stock'=>$stock,
//            'promotion_type'=>$promotion_type,
//            'goods_gd_num'=>$goods_gd_num,
//
//        ];
//        $data=DB::table('mt_goods')->insert($dainser);
////        var_dump($data);die;
//        if($data){
//            $response=[
//                'code'=>0,
//                'data'=>$data,
//                'msg'=>'商品添加成功'
//            ];
//            return (json_encode($response, JSON_UNESCAPED_UNICODE));
//        }else{
//            $response=[
//                'error'=>1,
//                'msg'=>'商品添加失败'
//            ];
//            return (json_encode($response, JSON_UNESCAPED_UNICODE));
//        }
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
