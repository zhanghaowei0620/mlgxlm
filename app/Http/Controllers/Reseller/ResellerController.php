<?php

namespace App\Http\Controllers\Reseller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ResellerController extends Controller
{
    //分销系统
    public function reseller(Request $request)
    {
        $data=DB::table('mt_shop')
                ->where(['shop_reseller'=>1])
                ->get();
        var_dump($data);die;
    }
}
