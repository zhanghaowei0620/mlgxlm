<?php

namespace App\Http\Controllers\Index;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
Use Illuminate\Support\Facades\DB;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged;
use yii\console\widgets\Table;

class IndexController extends Controller
{
    //获取首页数据
    public function index(){
        $type = DB::table('mt_type')->where(['p_id'=>0])->get()->toArray();  //父级分类
        $s_type1 = DB::table('mt_type')->where(['p_id'=>1])->get()->toArray();          //子集分类 第一行
        $s_type2 = DB::table('mt_type')->where(['p_id'=>2])->get()->toArray();         //子集分类 第二行
        $s_type3 = DB::table('mt_type')->where(['p_id'=>3])->get()->toArray();         //子集分类 第二行
        $s_type4 = DB::table('mt_type')->where(['p_id'=>4])->get()->toArray();          //子集分类 第二行
        //var_dump($s_type4);

        $goodsInfo = DB::table('mt_goods')
            ->join('mt_shop','mt_goods.shop_id','=','mt_shop.shop_id')
            ->where(['promotion_type'=>1])
            ->get(['shop_name','shop_address_provice','shop_address_city','shop_address_area','shop_score','goods_id','goods_name','price','market_price','introduction','picture'])->toArray();   //店铺精选   默认为1
        //var_dump($goodsInfo);exit;


        $week_newshop = DB::table('mt_shop')->orderBy('shop_add_time')->limit(3)->get(['shop_id','shop_name','shop_Ename','shop_desc'])->toArray();    //本周新店
        //var_dump($week_newshop);exit;
        $recommend = DB::table('mt_goods')->where(['is_recommend'=>1])->get(['goods_id','price','picture']);       //推荐
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
