<?php

namespace App\Http\Controllers\Goods;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class GoodsController extends Controller
{
    //根据导航栏父级分类获取子级分类及店铺信息
    public function father_type_shop(Request $request){
        $f_type_id = $request->input('f_type_id');
        //$f_type_id = 1;
        if($f_type_id){
            $type = DB::table('mt_type')->where('t_id',$f_type_id)->first();        //获取分类数据
            //var_dump($type);exit;
            $p_id = $type->p_id;
            if($p_id==0){     //判断分类是否为最大级
                $t_id = $type->t_id;
                $s_type = DB::table('mt_type')->where('p_id',$t_id)->get();    //获取父级分类下所有的子级分类
                $is_hot = DB::table('mt_type')->where('is_hot',1)->get();    //热门项目
                //var_dump($is_hot);exit;
                $recommend_picture = DB::table('mt_goods')->where('is_recommend',1)->limit('4')->get(['goods_id','picture']);    //推荐位图片
                $where = [
                    'mt_type.p_id'=>$t_id,
                    'mt_goods.is_recommend'=>1
                ];
                $select = DB::table('mt_shop')                      //美容美发导航栏-精选
                ->join('mt_type','mt_shop.t_id','=','mt_type.t_id')
                    ->join('mt_goods','mt_shop.shop_id','=','mt_goods.shop_id')
                    ->where($where)
                    ->get(['mt_shop.shop_id','shop_name','shop_address','shop_score','shop_desc','shop_label','shop_logo','goods_id','goods_name','price','picture'])->toArray();//
                //var_dump($select);exit;

                $response = [
                    'error'             =>       '0',
                    's_type'            =>      $s_type,
                    'hot'               =>      $is_hot,
                    'recommend_picture' =>      $recommend_picture,
                    'select'            =>      $select
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $response = [
                    'error'=>'1',
                    'msg'=>'暂未开通该类型店铺'
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response = [
                'error'=>'1',
                'msg'=>'暂未开通该类型店铺'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //点击店铺获取店铺下所有的商品
    public function shop_goods(Request $request){
        $shop_id = $request->input('shop_id');
        $shop_id = 1;
        $shop_goodsInfo = DB::table('mt_goods')->where('shop_id',$shop_id)->paginate(7);
        //var_dump($shop_goodsInfo);exit;
        if($shop_goodsInfo){
            $response = [
                'error'=>'0',
                'shop_goodsInfo'=>$shop_goodsInfo
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $response = [
                'error'=>'1',
                'msg'=>'该店铺下暂未任何商品'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //点击商品获取商品详情+店铺详情信息
    public function goodsinfo(Request $request){
        $goods_id = $request->input('goods_id');
        //$goods_id = 1;
        $goodsInfo = DB::table('mt_shop')
            ->join('mt_goods','mt_shop.shop_id','=','mt_shop.shop_id')
            ->where('mt_goods.goods_id',$goods_id)
            ->first();
        var_dump($goodsInfo);exit;

        if($goodsInfo==NULL){
            $response = [
                'error'=>'1',
                'msg'=>'商品不存在'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response = [
                'error'=>'0',
                'goodsInfo'=>$goodsInfo
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }

    //根据导航栏子级分类获取店铺  根据热门项目分类id获取店铺
    public function type_shop(Request $request){
        $type_id = $request->input('type_id');
        //$page_num = $request->input('page_num');  //当前展示页数
        //$type_id = 7;
        if($type_id){
            $shop_type = DB::table('mt_shop')->where('t_id',$type_id)->paginate(7);
            //var_dump($shop_type);exit;
            $response = [
                'error'=>'0',
                'shop_goodsInfo'=>$shop_type
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $response = [
                'error'=>'1',
                'msg'=>'暂未开通该类型店铺'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    






}
