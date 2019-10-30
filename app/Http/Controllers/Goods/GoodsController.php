<?php

namespace App\Http\Controllers\Goods;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use App\Models\ShopModel;

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

    //点击店铺获取店铺详情信息及店铺下所有的商品
    public function shop_goods(Request $request){
        $shop_id = $request->input('shop_id');
        $shop_id = 2;
        $shop_goodsInfo = DB::table('mt_goods')
            ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
            ->where('mt_goods.shop_id',$shop_id)->paginate(7);
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
//        $goods_id = 3;
        $goodsInfo = DB::table('mt_shop')
            ->join('mt_goods','mt_shop.shop_id','=','mt_goods.shop_id')
            ->where('mt_goods.goods_id',$goods_id)
            ->first();
        //var_dump($goodsInfo);exit;
        $reconmend_shop = DB::table('mt_goods')->where(['shop_id'=>$goodsInfo->shop_id,'is_recommend'=>1])->limit(3)->get();
        //var_dump($reconmend_shop);exit;

        if($goodsInfo==NULL){
            $response = [
                'error'=>'1',
                'msg'=>'商品不存在'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $openid = Redis::get('openid');
            //var_dump($openid);exit;
            $userInfo = DB::table('mt_user')->where('openid',$openid)->first();
           // var_dump($userInfo);exit;
            $uid = $userInfo->uid;
            $where = [
                'uid'=>$uid,
                'goods_id'=>$goods_id
            ];
            $historyInfo = DB::table('mt_history')->where($where)->get()->toArray();
            //var_dump($historyInfo);exit;
            if($historyInfo){
                $update = [
                    'create_time'=>time()
                ];
                $updateInfo = DB::table('mt_history')->update($update);
            }else{
                $data = [
                    'uid'=>$uid,
                    'goods_id'=>$goods_id,
                    'create_time'=>time()
                ];
                DB::table('mt_history')->insertGetId($data);
            }
            $response = [
                'error'=>'0',
                'goodsInfo'=>$goodsInfo,
                'recommend_shop'=>$reconmend_shop
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }

    }

    //根据导航栏子级分类获取店铺  根据热门项目分类id获取店铺
    public function type_shop(Request $request){
        $type_id = $request->input('type_id');
        //$page_num = $request->input('page_num');  //当前展示页数
        $type_id = 7;
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

    //点击加入购物车
    public function add_cart(Request $request){
        $goods_id = $request->input('goods_id');
        $openid = Redis::get('openid');
        if($openid){
            $buy_num = $request->input('buy_num');
            $user_info = DB::table('mt_user')->where('openid',$openid)->first();
            $uid = $user_info->uid;
//            $buy_num = 1;
//            $goods_id = 7;
            $where = [
                'goods_id'=>$goods_id,
                'collection'=>0
            ];
            $goods_cart = DB::table('mt_cart')->where($where)->get()->toArray();
            //var_dump($goods_cart);exit;
            if($goods_cart){
                $update = [
                    'buy_num'=>$goods_cart[0]->buy_num+$buy_num
                ];
                $update_buynum = DB::table('mt_cart')->where('goods_id',$goods_id)->update($update);
                //var_dump($update_buynum);exit;
                if($update_buynum==true){
                    $response = [
                        'error'=>'0',
                        'msg'=>'加入购物车成功'
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                } else{
                    $response = [
                        'error'=>'1',
                        'msg'=>'加入购物车失败13131'
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }else{
                $goodsInfo = DB::table('mt_shop')
                    ->join('mt_goods','mt_shop.shop_id','=','mt_shop.shop_id')
                    ->where('mt_goods.goods_id',$goods_id)
                    ->first();
                //var_dump($goodsInfo);exit;
                $data = [
                    'goods_id'=>$goodsInfo->goods_id,
                    'shop_id'=>$goodsInfo->shop_id,
                    'openid'=>$openid,
                    'shop_name'=>$goodsInfo->shop_name,
                    'goods_name'=>$goodsInfo->goods_name,
                    'price'=>$goodsInfo->price,
                    'buy_num'=>$buy_num,
                    'create_time'=>time(),
                    'collection'=>0,
                    'uid'=>$uid
                ];
                //var_dump($data);exit;
                $add_cart = DB::table('mt_cart')->insertGetId($data);
                if($add_cart){
                    $response = [
                        'error'=>'0',
                        'msg'=>'加入购物车成功'
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    $response = [
                        'error'=>'1',
                        'msg'=>'加入购物车失败'
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }
        }else{
            $response = [
                'error'=>'2',
                'msg'=>'请先登录'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }

    //获取购物车列表
    public function cartList(Request $request){
        $openid = Redis::get('openid');
        $where = [
            'openid'=>$openid,
            'collection'=>0
        ];
        $cartInfo = DB::table('mt_cart')->where($where)->get()->toArray();
        //var_dump($cartInfo);exit;
        if($cartInfo){
            $response = [
                'error'=>'0',
                'cartInfo'=>$cartInfo
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $response = [
                'error'=>'1',
                'msg'=>'购物车暂无数据，快去添加商品吧'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //购物车删除
    public function cart_delete(Request $request){

    }

    //点击加入收藏-商品
    public function  add_collection(Request $request){
        $goods_id = $request->input('goods_id');
        $openid = Redis::get('openid');
        if($openid){
            $user_info = DB::table('mt_user')->where('openid',$openid)->first();
            $uid = $user_info->uid;
            //        $buy_num = 1;
            $goods_id = 8;
            $where = [
                'goods_id'=>$goods_id,
                'collection'=>1
            ];
            $goods_cart = DB::table('mt_cart')->where($where)->get()->toArray();
            //var_dump($goods_cart);exit;
            if($goods_cart){
                $response = [
                    'error'=>'0',
                    'msg'=>'该商品已在您的收藏夹中'
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $goodsInfo = DB::table('mt_shop')
                    ->join('mt_goods','mt_shop.shop_id','=','mt_shop.shop_id')
                    ->where('mt_goods.goods_id',$goods_id)
                    ->first();
                //var_dump($goodsInfo);exit;
                $data = [
                    'goods_id'=>$goodsInfo->goods_id,
                    'shop_id'=>$goodsInfo->shop_id,
                    'openid'=>$openid,
                    'shop_name'=>$goodsInfo->shop_name,
                    'goods_name'=>$goodsInfo->goods_name,
                    'price'=>$goodsInfo->price,
                    'create_time'=>time(),
                    'collection'=>1,
                    'uid'=>$uid
                ];
                //var_dump($data);exit;
                $add_cart = DB::table('mt_cart')->insertGetId($data);
                if($add_cart){
                    $response = [
                        'error'=>'0',
                        'msg'=>'加入收藏成功'
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    $response = [
                        'error'=>'1',
                        'msg'=>'加入收藏失败'
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }
        }else{
            $response = [
                'error'=>'2',
                'msg'=>'请先去登录'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }

    //收藏列表-商品
    public function collection_list(Request $request){
        $openid = Redis::get('openid');
        if($openid){
            $where = [
                'openid'=>$openid,
                'collection'=>1
            ];
            $cartInfo = DB::table('mt_cart')->where($where)->get()->toArray();
            //var_dump($cartInfo);exit;
            if($cartInfo){
                $response = [
                    'error'=>'0',
                    'cartInfo'=>$cartInfo
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $response = [
                    'error'=>'1',
                    'msg'=>'收藏夹暂无数据，快去添加商品吧'
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response = [
                'error'=>'2',
                'msg'=>'请先登录'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //店铺收藏
    public function shop_collection(Request $request){
        $shop_id = $request->input('shop_id');
        $openid = Redis::get('openid');
        if($openid){
            $user_info = DB::table('mt_user')->where('openid',$openid)->first();
            $uid = $user_info->uid;
            //$shop_id = 1;
            $where = [
                'shop_id'=>$shop_id,
                'uid'=>$uid
            ];
            $shop_collection = DB::table('mt_shop_collection')->where($where)->get()->toArray();
            //var_dump($shop_collection);exit;
            if($shop_collection){
                $response = [
                    'error'=>'0',
                    'msg'=>'该商品已在您的收藏夹中'
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $data = [
                    'shop_id'=>$shop_id,
                    'uid'=>$uid
                ];
                //var_dump($data);exit;
                $add_shop_collection = DB::table('mt_shop_collection')->insertGetId($data);
                if($add_shop_collection){
                    $response = [
                        'error'=>'0',
                        'msg'=>'店铺收藏成功'
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    $response = [
                        'error'=>'1',
                        'msg'=>'店铺收藏失败'
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }
        }else{
            $response = [
                'error'=>'2',
                'msg'=>'请先去登录'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //店铺收藏列表
    public function shop_collection_list(Request $request){
        $openid = Redis::get('openid');
        if($openid){
            $user_info = DB::table('mt_user')->where('openid',$openid)->first();
            $uid = $user_info->uid;
            $where = [
                'mt_shop_collection.uid'=>$uid,
            ];
            $collectionInfo = DB::table('mt_shop_collection')
                ->join('mt_shop','mt_shop_collection.shop_id','=','mt_shop.shop_id')
                ->where($where)
                ->get()->toArray();
            //var_dump($collectionInfo);exit;
            if($collectionInfo){
                $response = [
                    'error'=>'0',
                    'cartInfo'=>$collectionInfo
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $response = [
                    'error'=>'1',
                    'msg'=>'收藏夹暂无数据，快去添加商品吧'
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response = [
                'error'=>'2',
                'msg'=>'请先登录'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    //导航栏附近店铺-全部店铺
    public function whole_shop(Request $request){
        $shopInfo = DB::table('mt_shop')->paginate(7);
        //var_dump($shopInfo);
        if($shopInfo){
            $response = [
                'error'=>'0',
                'data'=>$shopInfo
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            $response = [
                'error'=>'1',
                'msg'=>'暂无店铺'
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }

    }

    //附近店铺-附近店铺
    public function nearby_shop(Request $request){
//        $slat = '112.558505';
//        $slng = '37.818498';
//        //$sql =  "";
//        $shop =  DB::select("SELECT shop_id,shop_name,lat,lng,shop_status, ROUND(6378.138*2*ASIN(SQRT(POW(SIN(($slat*PI()/180-lat*PI()/180)/2),2)+COS($slat*PI()/180)*COS(lat*PI()/180)*POW(SIN(($slng*PI()/180-lng*PI()/180)/2),2)))) AS juli  FROM mt_shop where shop_status = 1 group by shop_id  ,shop_name,shop_status,lat,lng,juli HAVING AVG(juli) <= 10");
//        var_dump($shop);exit;

        //$shopInfo = DB::table('mt_shop')->orderBy('shop_id')->limit(6)->get()->toArray();
        $shopInfo = DB::table('mt_shop')
            ->join('mt_goods','mt_shop.shop_id','=','mt_goods.shop_id')
            ->orderBy('mt_shop.shop_id')
            ->limit(6)
            ->where('mt_goods.is_recommend',1)
            ->get(['mt_shop.shop_id','shop_name','shop_address_provice','shop_address_city','shop_address_area','shop_score','shop_desc','shop_label','shop_logo','goods_id','goods_name','price','picture','latitude_longitude'])->toArray();
        foreach ($shopInfo as $k => $v){

            //var_dump($j++);
            $latitude_longitude = explode(',',$v->latitude_longitude);
            $user = array(
                'lat'=>'112.558505',
                'lng'=>'37.818498'
            );
            $shop = array(
                'lat'=>$latitude_longitude[0],
                'lng'=>$latitude_longitude[1]
            );
            $nearby = $this->GetDistance($user['lat'],$user['lng'],$shop['lat'],$shop['lng'],2);
            $data = [
                'shop_id'=>$v->shop_id,
                'shop_name'=>$v->shop_name,
                'shop_address_provice'=>$v->shop_address_provice,
                'shop_address_city'=>$v->shop_address_city,
                'shop_address_area'=>$v->shop_address_area,
                'shop_score'=>$v->shop_score,
                'shop_desc'=>$v->shop_desc,
                'shop_label'=>$v->shop_label,
                'shop_logo'=>$v->shop_logo,
                'goods_name'=>$v->goods_name,
                'price'=>$v->price,
                'nearby'=>$nearby,
            ];

            $response = [
                'error'=>'0',
                'data'=>$data
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            //var_dump($response);

            //echo '距离'.$v->shop_name.$this->GetDistance($user['lat'],$user['lng'],$shop['lat'],$shop['lng'],2).'Km';
            //var_dump('距离'.$v->shop_name.$nearby.'Km');
        }
    }

    //距离算法+
    public function GetDistance($lat1, $lng1, $lat2, $lng2, $len_type = 1, $decimal = 2)
    {
        $radLat1 = $lat1 * PI ()/ 180.0;   //PI()圆周率
        $radLat2 = $lat2 * PI() / 180.0;
        $a = $radLat1 - $radLat2;
        $b = ($lng1 * PI() / 180.0) - ($lng2 * PI() / 180.0);
        $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
        $s = $s * 6378.137;
        $s = round($s * 1000);
        if ($len_type --> 1)
        {
            $s /= 1000;
        }
        return round($s, $decimal);
    }









}
