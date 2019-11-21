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
        $shop_address_city=$request->input('shop_address_city');
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
                ->join('mt_goods','mt_goods.shop_id','=','mt_shop.shop_id')
                ->where(['mt_shop.shop_address_city'=>$shop_address_city])
                ->orderBy('shop_add_time')
                ->limit(3)
                ->get(['mt_shop.shop_id','shop_name','shop_Ename','shop_desc','shop_label','shop_address_provice','shop_address_city','shop_address_area','shop_score'])->toArray();    //本周新店
            //var_dump($week_newshop);exit;
            $recommend = DB::table('mt_goods')
                ->join('mt_shop','mt_shop.shop_id','=','mt_goods.shop_id')
                ->where(['is_recommend'=>1])
                ->get(['goods_id','goods_name','price','picture']);       //推荐
//            var_dump($recommend);exit;

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
//            $goodsInfo = DB::table('mt_goods')
//                ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
//                ->where(['promotion_type'=>1,'mt_shop.shop_address_city'=>$shop_address_city])
//                ->get(['shop_name','shop_address_provice','shop_address_city','shop_address_area','shop_score','goods_id','goods_name','price','market_price','introduction','picture','promotion_price','prople','shop_label'])->toArray();   //店铺精选   默认为1
            //var_dump($goodsInfo);exit;
            //服务精选的店铺
//            $goodsInfo = DB::table('mt_goods')
//                ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
//                ->where(['promotion_type'=>1],['mt_shop.shop_address_city'=>$shop_address_city])
//                ->get(['shop_name','shop_address_provice','shop_address_city','shop_address_area','shop_score','goods_id','goods_name','price','market_price','introduction','picture','promotion_price','prople','shop_label'])->toArray();
//            var_dump($goodsInfo);exit;
            $goodsInfo = DB::table('mt_goods')
                ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
                ->join('mt_type','mt_goods.t_id','=','mt_type.t_id')
                ->where(['promotion_type'=>1],['mt_shop.shop_address_city'=>$shop_address_city])
                ->limit(6)
                ->get(['promotion_type','mt_goods.goods_id','goods_name','goods_type','market_price','mt_goods.price','picture','description','mt_shop.shop_name','mt_goods.prople','promotion_price','mt_type.t_name','star'])->toArray();
//            var_dump($goodsInfo);exit;
            $discountInfo= DB ::table('mt_goods')
                ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
                ->join('mt_type','mt_goods.t_id','=','mt_type.t_id')
                ->where(['promotion_type'=>2],['mt_shop.shop_address_city'=>$shop_address_city])
                ->limit(6)
                ->get(['promotion_type','mt_goods.goods_id','goods_name','goods_type','coupon_redouction','coupon_price','price','picture','mt_type.t_name','introduction','star','mt_shop.shop_name','goods_gd_num'])->toArray();
//            var_dump($discountInfo);die;
            $salesInfo= DB ::table('mt_goods')
                ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
                ->join('mt_type','mt_goods.t_id','=','mt_type.t_id')
                ->orderBy('goods_gd_num','desc')
                ->limit(6)
                ->get(['promotion_type','mt_goods.goods_id','goods_name','goods_type','coupon_redouction','coupon_price','market_price','price','picture','mt_type.t_name','introduction','star','mt_shop.shop_name','goods_gd_num','promotion_price','promotion_prople','limited_ready_prople','limited_prople'])->toArray();
//            var_dump($salesInfo);die;
            $limitedInfo= DB ::table('mt_goods')
                ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
                ->join('mt_type','mt_goods.t_id','=','mt_type.t_id')
                ->where(['promotion_type'=>4],['mt_shop.shop_address_city'=>$shop_address_city])
                ->limit(6)
                ->get(['promotion_type','mt_goods.goods_id','goods_name','goods_type','limited_price','price','picture','mt_type.t_name','star','mt_shop.shop_name','limited_prople','limited_ready_prople'])->toArray();
//            var_dump($salesInfo);die;
            $shop_id = DB::table('mt_shop')
                ->join('mt_goods','mt_goods.shop_id','=','mt_shop.shop_id')
//                ->where(['mt_shop.shop_address_city'=>$shop_address_city])
                ->orderBy('shop_add_time')
                ->limit(3)
                ->get('mt_shop.shop_id');
//            var_dump($shop_id);exit;
            $week_newshop = DB::table('mt_shop')
                ->join('mt_goods','mt_goods.shop_id','=','mt_shop.shop_id')
//                ->where(['mt_shop.shop_address_city'=>$shop_address_city])
                ->orderBy('shop_add_time')
                ->limit(3)
                ->get(['mt_shop.shop_id','shop_name','shop_Ename','shop_desc','shop_label','shop_address_provice','shop_address_city','shop_address_area','shop_score'])->toArray();    //本周新店
//            var_dump($week_newshop);exit;
            $recommend = DB::table('mt_goods')
                ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
                ->where(['is_recommend'=>1],['shop_address_city'=>$shop_address_city])
                ->limit(6)
                ->get(['goods_id','goods_name','price','picture']);       //推荐
//            var_dump($shop_set);exit;

            $data = [
                'type'          =>  $type,
                's_type1'      =>  $s_type1,
                's_type2'      =>  $s_type2,
                's_type3'      =>  $s_type3,
                's_type4'      =>  $s_type4,
                'goodsInfo'     =>  $goodsInfo,
                'week_newshop'  =>  $week_newshop,
                'recommend'     =>  $recommend,
                'discountInfo' => $discountInfo,
                'salesInfo'   => $salesInfo,
                'limitedInfo'=>$limitedInfo,
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
            ->join('mt_coupon','mt_coupon.goods_id','=','mt_goods.goods_id')
            ->where('is_coupon',1)
            ->get(['mt_coupon.coupon_id','mt_goods.goods_id','mt_shop.shop_id','mt_coupon.coupon_draw','mt_shop.shop_name','mt_coupon.discount','mt_shop.shop_id','mt_coupon.coupon_price','mt_coupon.coupon_redouction','mt_coupon.create_time','mt_coupon.expiration','mt_goods.picture','mt_coupon.coupon_type','mt_goods.goods_name','mt_coupon.discount','mt_goods.picture']);
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
        $ip = $_SERVER['SERVER_ADDR'];
        $key = 'openid'.$ip;
        $openid =  Redis::get($key);
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
        $limited_type = $request->input('limited_type');
        $two_bar = $request->input('two_bar');
        $lat1 = $request->input('lat1');
        $lng1 = $request->input('lng1');
//        $two_bar = 2;
//        $limited_type = 2;
        if($limited_type ==1){
            if($two_bar == 1){
                //echo 111;exit;
//                $lat1 = '112.558505';
//                $lng1 = '37.818498';
                $time = time();
                $limitedInfo = DB::select("select mt_shop.shop_id,mt_shop.shop_name,mt_goods.goods_id,mt_goods.goods_name,mt_goods.limited_price,mt_goods.picture,mt_goods.prople,6378.138*2*ASIN(SQRT(POW(SIN(($lat1*PI()/180-lat*PI()/180)/2),2)+COS($lat1*PI()/180)*COS(lat*PI()/180)*POW(SIN(($lng1*PI()/180-lng*PI()/180)/2),2))) AS juli FROM mt_goods inner join mt_shop on mt_goods.shop_id = mt_shop.shop_id where mt_goods.shop_id in (SELECT mt_shop.shop_id FROM mt_shop where shop_status = 2) and limited_start_time<$time and limited_stop_time>$time order by juli");
//                $limitedInfo = DB::select("SELECT s.shop_id,shop_name,goods_id,goods_name,market_price,picture,limited_price,limited_prople,shop_status, 6378.138*2*ASIN(SQRT(POW(SIN(($lat1*PI()/180-lat*PI()/180)/2),2)+COS($lat1*PI()/180)*COS(lat*PI()/180)*POW(SIN(($lng1*PI()/180-lng*PI()/180)/2),2))) AS juli  FROM mt_shop s inner join mt_goods g on s.shop_id = g.shop_id  where limited_start_time<$time and limited_stop_time>$time and limited_buy = 1 and shop_status = 2 group by juli order by juli");
//                var_dump($limitedInfo);exit;
                $data = [
                    'code'=>0,
                    'limitedInfo'=>$limitedInfo
                ];
                $response = [
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else if($two_bar == 2){
                $time = time();
                $limitedInfo = DB::select("SELECT s.shop_id,shop_name,goods_id,goods_name,market_price,picture,limited_price,limited_prople,shop_status FROM mt_shop s inner join mt_goods g on s.shop_id = g.shop_id  where limited_start_time<$time and limited_stop_time>$time && limited_buy = 1 && shop_status = 2 order by limited_price");
//                var_dump($limitedInfo);exit;
                $data = [
                    'code'=>0,
                    'limitedInfo'=>$limitedInfo
                ];
                $response = [
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $data = [
                    'code'=>1,
                    'msg'=>'暂无商品抢购中'
                ];
                $response = [
                    'data'=>$data
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else if ($limited_type == 2){
            $start_time=strtotime(date("Y-m-d",time()));    //求今天开始时间
            $tomorrow = $start_time+86400;    //明日开始时间
//            var_dump($tomorrow);exit;
            if($two_bar == 1){
//                $lat1 = '112.558505';
//                $lng1 = '37.818498';
                $time = time();
                $limitedInfo = DB::select("select mt_shop.shop_id,mt_shop.shop_name,mt_goods.goods_id,mt_goods.goods_name,6378.138*2*ASIN(SQRT(POW(SIN(($lat1*PI()/180-lat*PI()/180)/2),2)+COS($lat1*PI()/180)*COS(lat*PI()/180)*POW(SIN(($lng1*PI()/180-lng*PI()/180)/2),2))) AS juli FROM mt_goods inner join mt_shop on mt_goods.shop_id = mt_shop.shop_id where mt_goods.shop_id in (SELECT mt_shop.shop_id FROM mt_shop where shop_status = 2) and limited_start_time>$time and limited_stop_time>$time order by juli");
//                $limitedInfo = DB::select("SELECT s.shop_id,shop_name,goods_id,goods_name,market_price,picture,limited_price,limited_prople,shop_status, 6378.138*2*ASIN(SQRT(POW(SIN(($lat1*PI()/180-lat*PI()/180)/2),2)+COS($lat1*PI()/180)*COS(lat*PI()/180)*POW(SIN(($lng1*PI()/180-lng*PI()/180)/2),2))) AS juli  FROM mt_shop s inner join mt_goods g on s.shop_id = g.shop_id  where limited_start_time>$time and limited_stop_time>$time && limited_buy = 1 && shop_status = 2 group by juli order by juli");
//                var_dump($limitedInfo);exit;
                $data = [
                    'code'=>0,
                    'limitedInfo'=>$limitedInfo
                ];
                $response = [
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else if($two_bar == 2){
                $time = time();
                $limitedInfo = DB::select("SELECT s.shop_id,shop_name,goods_id,goods_name,market_price,picture,limited_price,limited_prople,shop_status FROM mt_shop s inner join mt_goods g on s.shop_id = g.shop_id  where limited_start_time>$time and limited_stop_time>$time && limited_buy = 1 && shop_status = 2 order by limited_price");
                //var_dump($limitedInfo);exit;
                $data = [
                    'code'=>0,
                    'limitedInfo'=>$limitedInfo
                ];
                $response = [
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $data = [
                    'code'=>1,
                    'msg'=>'暂无商品抢购中'
                ];
                $response = [
                    'data'=>$data
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else if ($limited_type == 3){
            $start_time=strtotime(date("Y-m-d",time()));    //求今天开始时间
            $tomorrow = $start_time+86400;    //明日开始时间
            //var_dump($tomorrow);exit;
            if($two_bar == null || $two_bar == 1){
//                $lat1 = '112.558505';
//                $lng1 = '37.818498';
//                $time = time();
                $limitedInfo = DB::select("select mt_shop.shop_id,mt_shop.shop_name,mt_goods.goods_id,mt_goods.goods_name,6378.138*2*ASIN(SQRT(POW(SIN(($lat1*PI()/180-lat*PI()/180)/2),2)+COS($lat1*PI()/180)*COS(lat*PI()/180)*POW(SIN(($lng1*PI()/180-lng*PI()/180)/2),2))) AS juli FROM mt_goods inner join mt_shop on mt_goods.shop_id = mt_shop.shop_id where mt_goods.shop_id in (SELECT mt_shop.shop_id FROM mt_shop where shop_status = 2) and limited_start_time<=$tomorrow and limited_stop_time>$tomorrow order by juli");
//                $limitedInfo = DB::select("SELECT s.shop_id,shop_name,goods_id,goods_name,market_price,picture,limited_price,limited_prople,shop_status, 6378.138*2*ASIN(SQRT(POW(SIN(($lat1*PI()/180-lat*PI()/180)/2),2)+COS($lat1*PI()/180)*COS(lat*PI()/180)*POW(SIN(($lng1*PI()/180-lng*PI()/180)/2),2))) AS juli  FROM mt_shop s inner join mt_goods g on s.shop_id = g.shop_id  where limited_start_time>$tomorrow and limited_stop_time>$tomorrow && limited_buy = 1 && shop_status = 2 group by juli order by juli");
                //var_dump($limitedInfo);exit;
                $data = [
                    'code'=>0,
                    'limitedInfo'=>$limitedInfo
                ];
                $response = [
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else if($two_bar == 2){
                $time = time();
                $limitedInfo = DB::select("SELECT s.shop_id,shop_name,goods_id,goods_name,market_price,picture,limited_price,limited_prople,shop_status FROM mt_shop s inner join mt_goods g on s.shop_id = g.shop_id  where limited_start_time>$tomorrow and limited_stop_time>$tomorrow && limited_buy = 1 && shop_status = 2 order by limited_price");
                //var_dump($limitedInfo);exit;
                $data = [
                    'code'=>0,
                    'limitedInfo'=>$limitedInfo
                ];
                $response = [
                    'data'=>$data
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $data = [
                    'code'=>1,
                    'msg'=>'暂无商品抢购中'
                ];
                $response = [
                    'data'=>$data
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }
    }

    //拼团
    public function assemble(Request $request){
        $seller = DB::table('mt_goods')       //销量榜
            ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
            ->where(['promotion_type'=>1])
            ->orderBy('goods_gd_num','desc')
            ->limit(3)
            ->get(['shop_name','goods_id','goods_name','market_price','picture','promotion_price'])->toArray();
        $lat1=$request->input('lat1');
        $lng1=$request->input('lng1');
//        $lat1 = '112.558505';
//        $lng1 = '37.818498';    //距离最近
        $distance = DB::select("SELECT s.shop_id,shop_name,goods_id,goods_name,market_price,picture,prople,promotion_price, 6378.138*2*ASIN(SQRT(POW(SIN(($lat1*PI()/180-lat*PI()/180)/2),2)+COS($lat1*PI()/180)*COS(lat*PI()/180)*POW(SIN(($lng1*PI()/180-lng*PI()/180)/2),2))) AS juli  FROM mt_shop s inner join mt_goods g on s.shop_id = g.shop_id  where promotion_type = 1  group by juli order by juli");
//        var_dump($distance);exit;

        $assembleInfo = DB::table('mt_goods')       //价格最优
        ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
            ->where(['promotion_type'=>1])
            ->orderBy('promotion_price')
            ->get(['shop_name','shop_address_provice','shop_address_city','shop_address_area','shop_score','goods_id','goods_name','market_price','introduction','picture','promotion_price','prople','shop_label'])->toArray();
        //var_dump($assembleInfo);exit;

        $data = [
            'seller' => $seller,        //销量榜
            'distance' => $distance,          //距离最近
            'assembleInfo' => $assembleInfo,    //价格最优
            'code' => 0
        ];

        $response = [
            'data'=>$data
        ];
        //var_dump($response);exit;
        return json_encode($response,JSON_UNESCAPED_UNICODE);
    }





}
