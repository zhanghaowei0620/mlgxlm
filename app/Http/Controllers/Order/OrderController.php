<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    //生成订单
    public function add_order(Request $request){
        $goods_id = $request->input('goods_id');
    }
}
