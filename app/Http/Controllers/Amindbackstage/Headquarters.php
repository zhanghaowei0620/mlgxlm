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
        $type_id = $request->input('type_id');          //类型id
        $goods_name=$request->input('goods_name');      //商品名称
        $price=$request->input('price');    //价格
        $goods_effect = $request->input('goods_effect');   //功效
        $goods_duration = $request->input('goods_duration');     //时长
        $goods_process = $request->input('goods_process');    //流程
//        $picture=$request->input('picture');
        $stock=$request->input('stock');
        $promotion_type=$request->input('promotion_type');
        $goods_gd_num=$request->input('goods_gd_num');
        $dainser=[
            'goods_name'=>$goods_name,
            'price'=>$price,
//            'picture'=>$picture,
            'stock'=>$stock,
            'promotion_type'=>$promotion_type,
            'goods_gd_num'=>$goods_gd_num,

        ];
        $data=DB::table('mt_goods')->insert($dainser);
//        var_dump($data);die;
        if($data){
            $response=[
                'code'=>0,
                'data'=>$data,
                'msg'=>'商品添加成功'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'error'=>1,
                'msg'=>'商品添加失败'
            ];
            return (json_encode($response, JSON_UNESCAPED_UNICODE));
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
