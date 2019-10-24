<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Route::get('/访问方式', '文件夹名\控制器名@方法名');
Route::get('/index', 'Index\IndexController@index');   //首页

Route::get('/shop_goods', 'Index\IndexController@shop_goods');   //根据店铺id 获取店铺下所有商品

Route::get('/weChat', 'User\UserController@weChat');   //微信登陆

Route::get('/goodsInfo', 'Goods\GoodsController@goodsInfo');    //获取商品详情信息

Route::get('/type_shop', 'Goods\GoodsController@type_shop');     //根据导航栏子级分类获取店铺

Route::get('/father_type_shop', 'Goods\GoodsController@father_type_shop');      //根据导航栏父级分类获取子级分类及店铺信息

Route::get('/add_cart', 'Goods\GoodsController@add_cart');      //点击商品加入购物车

Route::get('/cartList', 'Goods\GoodsController@cartList');      //购物车列表

Route::get('/add_order', 'Order\OrderController@add_order');      //生成订单

Route::get('/order_list', 'Order\OrderController@order_list');     //订单列表

Route::get('/order_status_list', 'Order\OrderController@order_status_list');     //根据订单状态获取订单信息

Route::get('/order_detail', 'Order\OrderController@order_detail');     //订单详情

Route::get('/user_Address', 'User\UserController@user_Address');      //添加用户地址

Route::get('/user_Address_list', 'User\UserController@user_Address_list');    //地址列表

Route::get('update_address', 'User\UserController@update_address');     //地址修改

Route::get('delete_address', 'User\UserController@delete_address');    //地址删除

Route::get('user_center', 'User\UserController@user_center');