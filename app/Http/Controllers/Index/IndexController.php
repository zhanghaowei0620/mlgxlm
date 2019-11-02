<?php

namespace App\Http\Controllers\Index;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
Use Illuminate\Support\Facades\DB;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged;
use yii\console\widgets\Table;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{
    //获取首页数据
    public function index(Request $request){
        $promotion_type = $request->input('promotion_type');
        $type = DB::table('mt_type')->where(['p_id'=>0])->get()->toArray();  //父级分类
        $s_type1 = DB::table('mt_type')->where(['p_id'=>1])->get()->toArray();          //子集分类 第一行
        $s_type2 = DB::table('mt_type')->where(['p_id'=>2])->get()->toArray();         //子集分类 第二行
        $s_type3 = DB::table('mt_type')->where(['p_id'=>3])->get()->toArray();         //子集分类 第二行
        $s_type4 = DB::table('mt_type')->where(['p_id'=>4])->get()->toArray();          //子集分类 第二行
        //var_dump($s_type4);

        if($promotion_type){
            $goodsInfo = DB::table('mt_goods')
                ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
                ->where(['promotion_type'=>$promotion_type])
                ->get(['shop_name','shop_address_provice','shop_address_city','shop_address_area','shop_score','goods_id','goods_name','price','market_price','introduction','picture','promotion_price','prople','shop_label'])->toArray();   //店铺精选   默认为1
            //var_dump($goodsInfo);exit;


            $week_newshop = DB::table('mt_shop')
                ->orderBy('shop_add_time')
                ->limit(3)
                ->get(['shop_id','shop_name','shop_Ename','shop_desc','shop_label','shop_address_provice','shop_address_city','shop_address_area','shop_score'])->toArray();    //本周新店
            //var_dump($week_newshop);exit;
            $recommend = DB::table('mt_goods')->where(['is_recommend'=>1])->get(['goods_id','goods_name','price','picture']);       //推荐
            //var_dump($recommend);exit;

            $data = [
                'type'          =>  $type,
                's_type1'      =>  $s_type1,
                's_type2'      =>  $s_type2,
                's_type3'      =>  $s_type3,
                's_type4'      =>  $s_type4,
                'goodsInfo'     =>  $goodsInfo,
                'week_newshop'  =>  $week_newshop,
                'recommend'     =>  $recommend,
                'code'         =>  0
            ];

            $response = [
                'data'=>$data
            ];
            //var_dump($response);exit;
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $goodsInfo = DB::table('mt_goods')
                ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
                ->where(['promotion_type'=>1])
                ->get(['shop_name','shop_address_provice','shop_address_city','shop_address_area','shop_score','goods_id','goods_name','price','market_price','introduction','picture','promotion_price','prople','shop_label'])->toArray();   //店铺精选   默认为1
            //var_dump($goodsInfo);exit;


            $shop_id = DB::table('mt_shop')->orderBy('shop_add_time')->limit(3)->get('shop_id');
            //var_dump($shop_id);exit;
            $week_newshop = DB::table('mt_shop')
                ->orderBy('shop_add_time')
                ->limit(3)
                ->get(['shop_id','shop_name','shop_Ename','shop_desc','shop_label','shop_address_provice','shop_address_city','shop_address_area','shop_score'])->toArray();    //本周新店
            //var_dump($week_newshop);exit;
            $recommend = DB::table('mt_goods')->where(['is_recommend'=>1])->get(['goods_id','goods_name','price','picture']);       //推荐
            //var_dump($recommend);exit;

            $data = [
                'type'          =>  $type,
                's_type1'      =>  $s_type1,
                's_type2'      =>  $s_type2,
                's_type3'      =>  $s_type3,
                's_type4'      =>  $s_type4,
                'goodsInfo'     =>  $goodsInfo,
                'week_newshop'  =>  $week_newshop,
                'recommend'     =>  $recommend,
                'code'         =>  0
            ];

            $response = [
                'data'=>$data
            ];
            //var_dump($response);exit;
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }

    }

    //首页优惠券
    public function index_coupon(Request $request){
        $couponInfo = DB::table('mt_goods')
            ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
            ->where('is_coupon',1)
            ->get(['mt_goods.goods_id','mt_shop.shop_id','mt_shop.shop_name','mt_shop.shop_id','mt_goods.coupon_price','mt_goods.coupon_redouction','coupon_start_time','expiration','mt_goods.picture']);
//        var_dump($couponInfo);
        if($couponInfo){
            $data = [
                'code'=>0,
                'couponInfo'=>$couponInfo
            ];
            $response = [
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $data = [
                'code'=>1,
                'msg'=>'暂时没有商品优惠券'
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }

    //点击优惠券 领取
    public function coupon_receive(Request $request){
        $openid =  Redis::get('openid');
        if($openid){
            $userInfo = DB::table('mt_user')->where('openid',$openid)->first('uid');
            //var_dump($userInfo);
            $uid = $userInfo->uid;
            $goods_id = $request->input('goods_id');
            $shop_id = $request->input('shop_id');
            $coupon_redouction = $request->input('coupon_redouction');
            $coupon_price = $request->input('coupou_price');
            $coupon_start_time = $request->input('coupon_start_time');
            $expiration = $request->input('expiration');

            $where = [
                'uid'=>$userInfo,
                'goods_id'=>$goods_id,
                'shop_id'=>$shop_id
            ];
            $coupon = DB::table('mt_coupon')->where($where)->get()->toArray();

            if($coupon){
                $data = [
                    'code'=>1,
                    'msg'=>'您已领取过此优惠券，请尽快使用'
                ];
                $response = [
                    'data'=>$data
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }else{
                $insert = [
                    'goods_id' => $goods_id,
                    'shop_id' => $shop_id,
                    'uid' => $uid,
                    'coupon_price' => $coupon_price,
                    'coupon_redouction' => $coupon_redouction,
                    'coupon_start_time' => $coupon_start_time,
                    'expiration' => $expiration
                ];
                $insertCoupon = DB::table('mt_coupon')->insertGetId($insert);
                if($insertCoupon == true){
                    $data = [
                        'code'=>0,
                        'msg'=>'领取成功'
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    $data = [
                        'code'=>3,
                        'msg'=>'领取失败'
                    ];
                    $response = [
                        'data'=>$data
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }
        }else{
            $data = [
                'code'=>2,
                'msg'=>'请先登录'
            ];
            $response = [
                'data'=>$data
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }

    //限时抢
    public function limited_time(Request $request){

    }





}
